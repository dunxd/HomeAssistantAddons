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
    protected string $bookdir;
    protected string $rootdir;
    protected ?EPub $epub;
    protected ?string $error;

    public function __construct(string $bookdir)
    {
        $this->bookdir = $bookdir;
        $this->rootdir = dirname(__DIR__, 2);
    }

    /**
     * Handle request
     * @param mixed $request @todo
     * @return void
     */
    public function handle($request = null)
    {
        // proxy google requests
        if (isset($_GET['api'])) {
            header('application/json; charset=UTF-8');
            echo $this->searchBookApi($_GET['api']);
            return;
        }
        if (!empty($_REQUEST['book'])) {
            try {
                $book = preg_replace('/[^\w ._-]+/', '', $_REQUEST['book']);
                $book = basename($book . '.epub'); // no upper dirs, lowers might be supported later
                $this->epub = new EPub($this->bookdir . $book, ZipEdit::class);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
        // return image data
        if (!empty($_REQUEST['img']) && isset($this->epub)) {
            $img = $this->epub->getCoverInfo();
            header('Content-Type: ' . $img['mime']);
            echo $img['data'];
            return;
        }
        // save epub data
        if (isset($_REQUEST['save']) && isset($this->epub)) {
            $this->epub = $this->saveEpubData($this->epub);
            if (!$this->error) {
                // rename
                $new = $this->renameEpubFile($this->epub);
                $go = basename($new, '.epub');
                header('Location: ?book=' . rawurlencode($go));
                return;
            }
        }
        $data = [];
        $data['bookdir'] = htmlspecialchars($this->bookdir);
        $data['booklist'] = '';
        $list = glob($this->bookdir . '/*.epub');
        foreach ($list as $book) {
            $base = basename($book, '.epub');
            $name = Util::book_output($base);
            $data['booklist'] .= '<li ' . ($base == $_REQUEST['book'] ? 'class="active"' : '') . '>';
            $data['booklist'] .= '<a href="?book=' . htmlspecialchars($base) . '">' . $name . '</a>';
            $data['booklist'] .= '</li>';
        }
        if (isset($this->error)) {
            $data['alert'] = "alert('" . htmlspecialchars($this->error) . "');";
        }
        if (empty($this->epub)) {
            $data['license'] = str_replace("\n\n", '</p><p>', htmlspecialchars(file_get_contents($this->rootdir . '/LICENSE')));
            $template = $this->rootdir . '/templates/index.html';
        } else {
            $data = $this->getEpubData($this->epub, $data);
            $template = $this->rootdir . '/templates/epub.html';
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderTemplate($template, $data);
    }

    /**
     * Proxy google requests
     * @param string $query
     * @return string|false
     */
    protected function searchBookApi($query)
    {
        return file_get_contents('https://www.googleapis.com/books/v1/volumes?q=' . rawurlencode($query) . '&maxResults=25&printType=books&projection=full');
    }

    /**
     * Get Epub data
     * @param EPub $epub
     * @param array<string, string> $data
     * @return array<string, string>
     */
    protected function getEpubData($epub, $data = [])
    {
        $data['book'] = htmlspecialchars($_REQUEST['book']);
        $data['title'] = htmlspecialchars($epub->getTitle());
        $data['authors'] = '';
        $count = 0;
        foreach ($epub->getAuthors() as $as => $name) {
            $data['authors'] .= '<p>';
            $data['authors'] .= '<input type="text" name="authorname[' . $count . ']" value="' . htmlspecialchars($name) . '" />';
            $data['authors'] .= ' (<input type="text" name="authoras[' . $count . ']" value="' . htmlspecialchars($as) . '" />)';
            $data['authors'] .= '</p>';
            $count++;
        }
        $data['cover'] = '?book=' . htmlspecialchars($_REQUEST['book']) . '&amp;img=1';
        $c = $epub->getCoverInfo();
        $data['imgclass'] = $c['found'] ? 'hasimg' : 'noimg';
        $data['description'] = htmlspecialchars($epub->getDescription());
        $data['subjects'] = htmlspecialchars(join(', ', $epub->getSubjects()));
        $data['publisher'] = htmlspecialchars($epub->getPublisher());
        $data['copyright'] = htmlspecialchars($epub->getCopyright());
        $data['language'] = htmlspecialchars($epub->getLanguage());
        $data['isbn'] = htmlspecialchars($epub->getISBN());
        return $data;
    }

    /**
     * Save Epub data
     * @param EPub $epub
     * @return EPub
     */
    protected function saveEpubData($epub)
    {
        $epub->setTitle($_POST['title']);
        $epub->setDescription($_POST['description']);
        $epub->setLanguage($_POST['language']);
        $epub->setPublisher($_POST['publisher']);
        $epub->setCopyright($_POST['copyright']);
        $epub->setIsbn($_POST['isbn']);
        $epub->setSubjects($_POST['subjects']);

        $authors = [];
        foreach ((array) $_POST['authorname'] as $num => $name) {
            if ($name) {
                $as = $_POST['authoras'][$num];
                if (!$as) {
                    $as = $name;
                }
                $authors[$as] = $name;
            }
        }
        $epub->setAuthors($authors);

        // handle image
        $cover = '';
        if (preg_match('/^https?:\/\//i', $_POST['coverurl'])) {
            $data = @file_get_contents($_POST['coverurl']);
            if ($data) {
                $cover = tempnam(sys_get_temp_dir(), 'epubcover');
                file_put_contents($cover, $data);
                unset($data);
            }
        } elseif(is_uploaded_file($_FILES['coverfile']['tmp_name'])) {
            $cover = $_FILES['coverfile']['tmp_name'];
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
        $author = array_keys($epub->getAuthors())[0];
        $title  = $epub->getTitle();
        $new    = Util::to_file($author . '-' . $title);
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
