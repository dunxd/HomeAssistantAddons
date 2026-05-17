<?php

/**
 * PHP EPub Meta library
 *
 * @author mikespub
 */

namespace SebLucas\EPubMeta;

use SebLucas\EPubMeta\Tools\ZipEdit;
use SebLucas\EPubMeta\Tools\ZipFile;
use Exception;
use ZipArchive;

/**
 * Comic class to handle .cbz files
 */
class Comic implements BookInterface
{
    public const METADATA_FILE = 'ComicInfo.xml';
    public const MIME_TYPE = 'application/vnd.comicbook+zip';
    /** @var list<string> */
    public const CREATOR_ROLES = [
        'Writer', 'Penciller', 'Inker', 'Colorist',
        'Letterer', 'CoverArtist', 'Editor', 'Translator',
    ];

    protected string $file;
    protected ?ComicInfo $comicInfo = null;
    /** @var ZipEdit|ZipFile */
    protected $zip;
    protected string $zipClass;
    protected string $coverpath = '';
    protected string $imagetoadd = '';

    /**
     * Constructor
     *
     * @param string $file path to cbz file to work on
     * @param string $zipClass class to handle zip
     * @throws Exception if file does not exist
     */
    public function __construct($file, $zipClass = ZipFile::class)
    {
        if (!is_file($file)) {
            throw new Exception("Comic file does not exist!");
        }
        $this->file = $file;
        $this->openZipFile($zipClass);
        $this->loadMetadata();
    }

    /**
     * Open the zip file
     * @param string $zipClass
     * @return void
     * @throws Exception
     */
    public function openZipFile($zipClass)
    {
        $this->zip = new $zipClass();
        // ignore directories here to find ComicInfo.xml
        if (!$this->zip->Open($this->file, ZipArchive::FL_NODIR)) {
            throw new Exception('Failed to read comic file');
        }
        $this->zipClass = $zipClass;
    }

    /**
     * Load ComicInfo.xml
     * @return void
     */
    public function loadMetadata()
    {
        $data = '';
        if ($this->zip->FileExists(static::METADATA_FILE)) {
            $data = $this->zip->FileRead(static::METADATA_FILE);
        }
        $this->comicInfo = ComicInfo::parseData($data);
    }

    /**
     * Get the ComicInfo object
     * @return ComicInfo
     */
    public function getMetadata()
    {
        return $this->comicInfo;
    }

    /**
     * Get list of files in the archive
     * @return array<string, mixed>
     */
    public function getZipEntries()
    {
        return $this->zip->getZipEntries();
    }

    /**
     * Get list of image files sorted naturally
     * @return string[]
     */
    public function getImages()
    {
        return $this->zip->findFiles();
    }

    /**
     * Get the path of the cover image
     * @return string|null
     */
    public function getCoverPath()
    {
        $images = $this->getImages();
        if (empty($images)) {
            return null;
        }

        // Check ComicInfo for explicit cover
        $pages = $this->comicInfo->getPages();
        foreach ($pages as $page) {
            if (isset($page['Type']) && $page['Type'] === 'FrontCover' && isset($page['Image'])) {
                $index = (int) $page['Image'];
                if (isset($images[$index])) {
                    return $images[$index];
                }
            }
        }

        // Default to first image
        return $images[0];
    }

    /**
     * Get the cover image content
     * @return string|null
     */
    public function getCover()
    {
        $path = $this->getCoverPath();
        if (!$path) {
            return null;
        }
        return $this->zip->FileRead($path);
    }

    /**
     * Get the cover image info
     * @return array<string, mixed>
     */
    public function getCoverInfo()
    {
        $path = $this->getCoverPath();
        if (!$path) {
            return [
                'mime'  => null,
                'data'  => null,
                'found' => false,
            ];
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
        $image = basename($path);
        return [
            'mime'  => $mime,
            'data'  => $this->zip->FileRead($image),
            'found' => $path,
        ];
    }

    /**
     * Set the book title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->comicInfo->setTitle($title);
    }

    /**
     * Get the book title
     *
     * @return string
     */
    public function getTitle()
    {
        return (string) $this->comicInfo->getTitle();
    }

    /**
     * Set the series of the book
     *
     * @param string $series
     */
    public function setSeries(string $series): void
    {
        $this->comicInfo->setSeries($series);
    }

    /**
     * Get the series of the book
     *
     * @return string
     */
    public function getSeries(): string
    {
        return (string) $this->comicInfo->getSeries();
    }

    /**
     * Set the issue number
     *
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->comicInfo->setNumber($number);
    }

    /**
     * Get the issue number
     *
     * @return string
     */
    public function getNumber(): string
    {
        return (string) $this->comicInfo->getNumber();
    }

    /**
     * Set the summary of the book
     *
     * @param string $summary
     */
    public function setSummary(string $summary): void
    {
        $this->comicInfo->setSummary($summary);
    }

    /**
     * Get the summary of the book
     *
     * @return string
     */
    public function getSummary(): string
    {
        return (string) $this->comicInfo->getSummary();
    }

    /**
     * Set the writer of the book
     *
     * @param string $writer
     */
    public function setWriter(string $writer): void
    {
        $this->comicInfo->setWriter($writer);
    }

    /**
     * Get the writer of the book
     *
     * @return string
     */
    public function getWriter(): string
    {
        return (string) $this->comicInfo->getWriter();
    }

    /**
     * Set the book's publisher info
     *
     * @param string $publisher
     * @return void
     */
    public function setPublisher($publisher)
    {
        $this->comicInfo->setPublisher($publisher);
    }

    /**
     * Get the book's publisher info
     *
     * @return string
     */
    public function getPublisher()
    {
        return (string) $this->comicInfo->getPublisher();
    }

    /**
     * Set the book's language (ISO code)
     *
     * @param string $lang
     */
    public function setLanguageISO(string $lang): void
    {
        $this->comicInfo->setLanguageISO($lang);
    }

    /**
     * Get the book's language (ISO code)
     *
     * @return string
     */
    public function getLanguageISO(): string
    {
        return (string) $this->comicInfo->getLanguageISO();
    }

    /**
     * @param string $tags
     */
    public function setTags(string $tags): void
    {
        $this->comicInfo->setTags($tags);
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return (string) $this->comicInfo->getTags();
    }

    /**
     * @param string $gtin
     */
    public function setGTIN(string $gtin): void
    {
        $this->comicInfo->setGTIN($gtin);
    }

    /**
     * @return string
     */
    public function getGTIN(): string
    {
        return (string) $this->comicInfo->getGTIN();
    }

    // BookInterface implementation methods

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getSummary();
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->setSummary($description);
    }

    /**
     * @return array<string>
     */
    public function getSubjects()
    {
        $tags = $this->getTags();
        if (empty($tags)) {
            return [];
        }
        return array_map('trim', explode(',', $tags));
    }

    /**
     * @param array<string>|string $subjects
     * @return void
     */
    public function setSubjects($subjects)
    {
        if (is_array($subjects)) {
            $subjects = implode(', ', $subjects);
        }
        $this->setTags($subjects);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->getLanguageISO();
    }

    /**
     * @param string $lang
     * @return void
     */
    public function setLanguage($lang)
    {
        $this->setLanguageISO($lang);
    }

    /**
     * @return string
     */
    public function getIsbn()
    {
        return $this->getGTIN();
    }

    /**
     * @param string $isbn
     * @return void
     */
    public function setIsbn($isbn)
    {
        $this->setGTIN($isbn);
    }

    /**
     * Set a new cover image (replaces the current cover file)
     * @param string $path Local path to image
     * @param string|null $mime Not used for CBZ but kept for compatibility signature
     * @return void
     */
    public function setCover($path, $mime)
    {
        $currentCover = $this->getCoverPath();

        if (!$currentCover) {
            $currentCover = 'cover.jpg';
        }

        $this->imagetoadd = $path;
        $this->coverpath = $currentCover;
    }

    /**
     * @return array<string, string>
     */
    public function getAuthors()
    {
        $authors = [];
        foreach (self::CREATOR_ROLES as $role) {
            $method = 'get' . $role;
            $name = $this->comicInfo->$method();
            if ($name) {
                $authors[$role] = $name;
            }
        }
        return $authors;
    }

    /**
     * @param array<string, string>|string $authors
     * @return void
     */
    public function setAuthors($authors)
    {
        // Clear existing roles first
        foreach (self::CREATOR_ROLES as $role) {
            $this->comicInfo->__call('set' . $role, ['']);
        }

        if (is_string($authors)) {
            $this->setWriter($authors);
            return;
        }

        foreach ($authors as $role => $name) {
            $this->comicInfo->{'set' . $role}($name);
        }
    }

    /**
     * Save changes to the archive
     * @return void
     */
    public function save()
    {
        $this->download();
        $this->zip->Close();
    }

    /**
     * Process changes and optionally output file
     * @param string|bool $file
     * @param bool $sendHeaders
     * @return void
     */
    public function download($file = false, $sendHeaders = true)
    {
        $this->zip->FileReplace(static::METADATA_FILE, $this->comicInfo->toXML());

        if ($this->imagetoadd && $this->coverpath) {
            $this->zip->FileAddPath($this->coverpath, $this->imagetoadd);
            $this->imagetoadd = '';
        }

        if ($file) {
            $render = $this->zipClass::DOWNLOAD;
            $this->zip->Flush($render, $file, static::MIME_TYPE, $sendHeaders);
        } elseif ($this->zipClass == ZipEdit::class) {
            $this->zip->SaveBeforeClose();
        }
    }

    /**
     * Close the archive
     * @return void
     */
    public function close()
    {
        $this->zip->Close();
    }

    /**
     * file name getter
     * @return string
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Delegate calls to ComicInfo
     * @param string $name
     * @param array<mixed> $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->comicInfo, $name], $arguments);
    }
}
