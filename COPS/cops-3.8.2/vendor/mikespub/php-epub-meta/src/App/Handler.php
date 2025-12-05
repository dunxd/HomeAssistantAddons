<?php

/**
 * PHP EPub Meta - App request handler
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 * @author Simon Schrape <simon@epubli.com> © 2015
 * @author mikespub
 */

namespace SebLucas\EPubMeta\App;

use SebLucas\EPubMeta\EPub;
use SebLucas\EPubMeta\Tools\ZipEdit;
use Exception;

class Handler
{
    protected string $rootdir;
    protected string $templatedir;
    protected ?string $cachedir;
    protected string $bookdir;
    protected bool $recursive;
    protected string $baseurl;
    protected bool $rename;
    /** @var array<string, mixed> */
    protected array $parent;
    protected ?EPub $epub = null;
    protected ?string $error = null;
    /** @var array<string, mixed> */
    protected array $params;

    /**
     * @param array<string, string|bool|array<string, mixed>> $config
     */
    public function __construct(array $config = [])
    {
        $this->rootdir = dirname(__DIR__, 2);
        $this->templatedir = $config['templatedir'] ?? ($this->rootdir . '/templates/');
        $this->cachedir = $config['cachedir'] ?? null;
        $this->bookdir = $config['bookdir'] ?? ($this->rootdir . '/test/data/');
        $this->recursive = $config['recursive'] ?? false;
        $this->baseurl = $config['baseurl'] ?? '..';
        $this->rename = $config['rename'] ?? true;
        $this->parent = $config['parent'] ?? [];
    }

    /**
     * Get request params from PHP globals
     * @return array<string, mixed>
     */
    public function getRequestFromGlobals()
    {
        $params = [];
        $params['api'] = $_GET['api'] ?? null;
        $params['lang'] = $_GET['lang'] ?? null;
        $params['book'] = $_REQUEST['book'] ?? null;
        $params['img'] = $_REQUEST['img'] ?? null;
        $params['save'] = $_REQUEST['save'] ?? null;
        if (empty($params['save'])) {
            return $params;
        }
        // posted values - see getEpubData()
        $fields = [
            'title',
            'description',
            'language',
            'publisher',
            'copyright',
            'isbn',
            'subjects',
            // arrays
            'authorname',
            'authoras',
            'coverurl',
        ];
        foreach ($fields as $field) {
            $params[$field] = $_POST[$field] ?? null;
        }
        // uploaded file
        $params['coverfile'] = $_FILES['coverfile'] ?? null;
        return $params;
    }

    /**
     * Handle request with params
     * @param ?array<string, mixed> $params
     * @return string|null
     */
    public function handle($params = null)
    {
        if (!isset($params)) {
            $params = $this->getRequestFromGlobals();
        }
        $this->params = $params;
        // proxy google requests
        if (isset($params['api'])) {
            header('application/json; charset=UTF-8');
            return $this->searchBookApi($params['api'], $params['lang'] ?? '');
        }
        if (!empty($params['book'])) {
            try {
                $this->epub = $this->getEpubFile($params['book']);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
        // return image data
        if (!empty($params['img']) && isset($this->epub)) {
            $img = $this->epub->getCoverInfo();
            header('Content-Type: ' . $img['mime']);
            return $img['data'];
        }
        // save epub data
        if (isset($params['save']) && isset($this->epub)) {
            $this->epub = $this->saveEpubData($this->epub, $params);
            if (!$this->error) {
                // rename
                $new = $this->renameEpubFile($this->epub);
                $go = $this->getBookName($new);
                header('Location: ?book=' . rawurlencode($go) . '&refresh=' . (string) time());
                return null;
            }
        }
        $data = [];
        if (str_starts_with($this->bookdir, $this->rootdir . DIRECTORY_SEPARATOR)) {
            $data['bookdir'] = htmlspecialchars(str_replace($this->rootdir . DIRECTORY_SEPARATOR, '', $this->bookdir));
        } else {
            $data['bookdir'] = htmlspecialchars(string: $this->bookdir);
        }
        $data['baseurl'] = $this->baseurl;

        // Generate Bootstrap-compatible booklist
        $data['booklist'] = $this->generateBooklist($params['book'] ?? null);

        // Generate Bootstrap-compatible alert
        if (isset($this->error)) {
            $data['alert'] = $this->generateAlert('danger', $this->error);
        } else {
            $data['alert'] = '';
        }

        if (empty($this->epub)) {
            $data['license'] = str_replace("\n\n", '</p><p>', htmlspecialchars(file_get_contents($this->rootdir . '/LICENSE')));
            $template = $this->templatedir . 'meta.html';
        } else {
            $data = $this->getEpubData($this->epub, $data);
            $template = $this->templatedir . 'epub.html';
        }

        // Add timestamp and user info
        $data['last_updated'] = date('Y-m-d H:i:s');
        $data['current_user'] = get_current_user(); // or your authentication system's method

        header('Content-Type: text/html; charset=utf-8');
        return $this->renderTemplate($template, $data);
    }

    /**
     * Proxy google requests
     * @param string $query
     * @param ?string $lang
     * @return string|false
     */
    protected function searchBookApi($query, $lang = null)
    {
        $query = trim($query);
        $cachefile = null;
        if (!empty($this->cachedir)) {
            if (!empty($lang) && preg_match('/^\w\w$/', $lang)) {
                $cachefile = $this->cachedir . rawurlencode($query) . '.' . $lang . '.json';
            } else {
                $cachefile = $this->cachedir . rawurlencode($query) . '.json';
            }
            if (file_exists($cachefile)) {
                $result = file_get_contents($cachefile);
                return $result;
            }
        }
        $url = 'https://www.googleapis.com/books/v1/volumes?q=' . rawurlencode($query) . '&maxResults=25&printType=books&projection=full';
        if (!empty($lang) && preg_match('/^\w\w$/', $lang)) {
            $url .= '&langRestrict=' . $lang;
        }
        $result = file_get_contents($url);
        if ($result === false) {
            return json_encode(['error' => $http_response_header[0], 'totalItems' => 0]);
        }
        if (!empty($cachefile) && !empty($result)) {
            file_put_contents($cachefile, $result);
        }
        return $result;
    }

    /**
     * Summary of getFileList
     * @param string $path
     * @param string $pattern
     * @param bool $recursive
     * @return array<string>
     */
    protected function getFileList($path, $pattern = '*.epub', $recursive = false)
    {
        $fileList = [];
        // Check path
        if (!is_dir($path)) {
            return $fileList;
        }
        if (!str_ends_with($path, DIRECTORY_SEPARATOR)) {
            $path .= DIRECTORY_SEPARATOR;
        }
        // Add files from the current directory
        $files = glob($path . $pattern, GLOB_MARK);
        foreach ($files as $item) {
            if (str_ends_with($item, DIRECTORY_SEPARATOR)) {
                continue;
            }
            $fileList[] = $item;
        }
        if (!$recursive) {
            return $fileList;
        }
        // Scan sub directories
        $paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR);
        foreach ($paths as $path) {
            $fileList = array_merge($fileList, $this->getFileList($path, $pattern, $recursive));
        }
        return $fileList;
    }

    /**
     * Get Epub file
     * @throws Exception
     * @param string $book
     * @return EPub
     */
    protected function getEpubFile($book)
    {
        // Alice's Adventures in Wonderland - Lewis Carroll.epub
        if (!$this->recursive) {
            $book = preg_replace('/[^\w ._\'-]+/', '', $book);
            $book = $this->bookdir . basename($book . '.epub'); // no upper dirs, lowers might be supported later
            return new EPub($book, ZipEdit::class);
        }
        // Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub
        $book = preg_replace('/[^\w ._\'(),\/-]+/', '', $book);
        $book = $this->bookdir . $book . '.epub'; // no upper dirs
        if (!file_exists($book)) {
            throw new Exception('Invalid ebook file ' . htmlspecialchars(basename($book)));
        }
        if (!str_starts_with(realpath($book), realpath($this->bookdir) . DIRECTORY_SEPARATOR)) {
            throw new Exception('No ebooks allowed outside bookdir. Are you using symlinks inside bookdir?');
        }
        return new EPub($book, ZipEdit::class);
    }

    /**
     * Get Epub data with Bootstrap-compatible form elements
     * @param EPub $epub
     * @param array<string, string> $data
     * @return array<string, string>
     */
    protected function getEpubData($epub, $data = [])
    {
        $data['book'] = htmlspecialchars((string) $this->params['book']);
        $data['title'] = htmlspecialchars($epub->getTitle());

        // Generate Bootstrap form groups for authors
        $data['authors'] = '';
        $count = 0;
        foreach ($epub->getAuthors() as $as => $name) {
            $data['authors'] .= '<div class="author-row">';
            $data['authors'] .= '<div class="row g-2">';
            $data['authors'] .= '<div class="col">';
            $data['authors'] .= sprintf(
                '<input type="text" class="form-control" name="authorname[%d]" value="%s" placeholder="Author Name">',
                $count,
                htmlspecialchars($name)
            );
            $data['authors'] .= '</div>';
            $data['authors'] .= '<div class="col">';
            $data['authors'] .= sprintf(
                '<input type="text" class="form-control" name="authoras[%d]" value="%s" placeholder="Sort As">',
                $count,
                htmlspecialchars($as)
            );
            $data['authors'] .= '</div>';
            $data['authors'] .= '</div>';
            $data['authors'] .= '</div>';
            $count++;
        }

        // Cover image with Bootstrap classes
        $data['cover'] = '?book=' . htmlspecialchars((string) $this->params['book']) . '&amp;img=1';
        $c = $epub->getCoverInfo();
        $data['imgclass'] = $c['found'] ? 'img-fluid' : 'img-fluid noimg';

        // Other fields
        $data['description'] = $epub->getDescription();  // don't use htmlspecialchars here anymore for div
        $data['subjects'] = htmlspecialchars(join(', ', $epub->getSubjects()));
        $data['publisher'] = htmlspecialchars($epub->getPublisher());
        $data['copyright'] = htmlspecialchars($epub->getCopyright());
        $data['language'] = htmlspecialchars($epub->getLanguage());
        $data['isbn'] = htmlspecialchars($epub->getISBN());

        return $data;
    }

    /**
     * Generate Bootstrap-compatible booklist HTML
     * @param ?string $currentBook
     * @return string
     */
    protected function generateBooklist(?string $currentBook): string
    {
        $html = '';

        // Parent link
        if (!empty($this->parent)) {
            $html .= sprintf(
                '<a href="' . $this->parent['link'] . '" class="list-group-item list-group-item-action%s">',
                ''
            );
            $html .= '<div class="d-flex w-100 justify-content-between">';
            $html .= '<h6 class="mb-1">' . $this->parent['title'] . '</h6>';
            $html .= '</div>';
            $html .= '</a>';
        }

        // Home link
        $html .= sprintf(
            '<a href="?book=" class="list-group-item list-group-item-action%s">',
            empty($currentBook) ? ' active' : ''
        );
        $html .= '<div class="d-flex w-100 justify-content-between">';
        $html .= '<h6 class="mb-1">Home</h6>';
        $html .= '</div>';
        $html .= '</a>';

        // Book list
        $list = $this->getFileList($this->bookdir, '*.epub', $this->recursive);
        foreach ($list as $book) {
            $base = $this->getBookName($book);
            $bookInfo = $this->getBookInfo($book);

            $html .= sprintf(
                '<a href="?book=%s" class="list-group-item list-group-item-action%s">',
                htmlspecialchars($base),
                ($base === $currentBook ? ' active' : '')
            );
            $html .= '<div class="d-flex w-100 justify-content-between">';
            $html .= sprintf('<h6 class="mb-1">%s</h6>', htmlspecialchars($bookInfo['title']));
            $html .= '</div>';
            if (!empty($bookInfo['author'])) {
                $html .= sprintf(
                    '<small class="text-muted">%s</small>',
                    htmlspecialchars($bookInfo['author'])
                );
            }
            $html .= '</a>';
        }

        return $html;
    }

    /**
     * Generate Bootstrap-compatible alert HTML
     * @param string $type 'success'|'danger'|'warning'|'info'
     * @param string $message
     * @return string
     */
    protected function generateAlert(string $type, string $message): string
    {
        // Instead of JavaScript alert, create Bootstrap alert
        $icon = match ($type) {
            'success' => 'check-circle-fill',
            'danger' => 'exclamation-triangle-fill',
            'warning' => 'exclamation-triangle-fill',
            'info' => 'info-circle-fill',
            default => 'info-circle-fill'
        };

        return sprintf(
            '<div class="alert alert-%s alert-dismissible fade show" role="alert">
                <i class="bi bi-%s me-2"></i>
                %s
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>',
            htmlspecialchars($type),
            $icon,
            htmlspecialchars($message)
        );
    }

    /**
     * Get book info from epub file
     * @param string $bookPath
     * @return array{title: string, author: string}
     */
    protected function getBookInfo(string $bookPath): array
    {
        try {
            $epub = new EPub($bookPath, ZipEdit::class);
            $title = $epub->getTitle();
            $authors = $epub->getAuthors();
            $author = !empty($authors) ? array_values($authors)[0] : '';
            return [
                'title' => $title,
                'author' => $author,
            ];
        } catch (Exception $e) {
            return [
                'title' => basename($bookPath, '.epub'),
                'author' => '',
            ];
        }
    }

    /**
     * Save Epub data
     * @param EPub $epub
     * @param array<string, mixed> $params
     * @return EPub
     */
    protected function saveEpubData($epub, $params)
    {
        $epub->setTitle((string) $params['title']);
        $epub->setDescription((string) $params['description']);
        $epub->setLanguage((string) $params['language']);
        $epub->setPublisher((string) $params['publisher']);
        $epub->setCopyright((string) $params['copyright']);
        $epub->setIsbn((string) $params['isbn']);
        $epub->setSubjects((string) $params['subjects']);

        if (empty($params['authorname'])) {
            $params['authorname'] = [];
        }
        if (empty($params['authoras'])) {
            $params['authoras'] = [];
        }
        $authors = [];
        foreach ((array) $params['authorname'] as $num => $name) {
            if ($name) {
                $as = $params['authoras'][$num];
                if (!$as) {
                    $as = $name;
                }
                $authors[$as] = $name;
            }
        }
        $epub->setAuthors($authors);

        // handle image
        $cover = '';
        if (preg_match('/^https?:\/\//i', (string) $params['coverurl'])) {
            $data = @file_get_contents($params['coverurl']);
            if ($data) {
                $cover = tempnam(sys_get_temp_dir(), 'epubcover');
                file_put_contents($cover, $data);
                unset($data);
            }
        } elseif (!empty($params['coverfile']) && is_uploaded_file($params['coverfile']['tmp_name'])) {
            $cover = $params['coverfile']['tmp_name'];
        }
        if ($cover) {
            $info = @getimagesize($cover);
            if (preg_match('/^image\/(gif|jpe?g|png)$/', $info['mime'])) {
                $epub->setCoverInfo($cover, $info['mime']);
            } else {
                $this->error = 'Not a valid image file' . $cover;
            }
        }

        // save the ebook
        try {
            $epub->save();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }

        // clean up temporary cover file
        if ($cover) {
            @unlink($cover);
        }

        return $epub;
    }

    /**
     * Rename Epub file
     * @param EPub $epub
     * @return string
     */
    protected function renameEpubFile($epub)
    {
        if (!$this->rename) {
            return $epub->file();
        }
        $author = array_keys($epub->getAuthors())[0];
        $title  = $epub->getTitle();
        $new    = Util::to_file($author . '-' . $title);
        // @todo allow recursive too when renaming?
        $new    = $this->bookdir . $new . '.epub';
        $old    = $epub->file();
        if (realpath($new) != realpath($old)) {
            if (!@rename($old, $new)) {
                $new = $old; //rename failed, stay here
            }
        }
        return $new;
    }

    /**
     * Summary of getBookName
     * @param string $book
     * @return string
     */
    protected function getBookName($book)
    {
        if (!$this->recursive) {
            return basename($book, '.epub');
        }
        $book = str_replace(realpath($this->bookdir) . DIRECTORY_SEPARATOR, '', realpath($book));
        $book = str_replace('.epub', '', $book);
        return $book;
    }

    /**
     * Render template with data
     * @param string $template
     * @param array<string, string> $data
     * @return string
     */
    protected function renderTemplate($template, $data)
    {
        if (!file_exists($template)) {
            throw new Exception('Invalid template ' . htmlspecialchars($template));
        }
        $content = file_get_contents($template);
        foreach ($data as $name => $value) {
            $content = preg_replace('/{{\s*' . $name . '\s*}}/', $value, $content);
        }
        return $content;
    }
}
