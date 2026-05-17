<?php

/**
 * PHP EPub Meta library
 *
 * @author mikespub
 */

namespace SebLucas\EPubMeta;

use DOMDocument;
use DOMElement;
use Exception;

/**
 * ComicInfo.xml data model based on Anansi Project ComicInfo.xsd
 * @see https://github.com/anansi-project/comicinfo/blob/main/drafts/v2.1/ComicInfo.xsd
 * @method string getPenciller()
 * @method string getInker()
 * @method string getColorist()
 * @method string getLetterer()
 * @method string getCoverArtist()
 * @method string getEditor()
 * @method string getTranslator()
 * @phpstan-consistent-constructor
 */
class ComicInfo
{
    /** @var array<string, mixed> */
    public array $info = [];
    /** @var array<int, array<string, mixed>> */
    public array $pages = [];

    public const FIELDS = [
        'Title', 'Series', 'Number', 'Count', 'Volume', 'AlternateSeries',
        'AlternateNumber', 'AlternateCount', 'Summary', 'Notes', 'Year',
        'Month', 'Day', 'Writer', 'Penciller', 'Inker', 'Colorist',
        'Letterer', 'CoverArtist', 'Editor', 'Translator', 'Publisher',
        'Imprint', 'Genre', 'Tags', 'Web', 'PageCount', 'LanguageISO',
        'Format', 'BlackAndWhite', 'Manga', 'Characters', 'Teams',
        'Locations', 'ScanInformation', 'StoryArc', 'StoryArcNumber',
        'SeriesGroup', 'AgeRating', 'Pages', 'CommunityRating', 'MainCharacterOrTeam',
        'Review', 'GTIN',
    ];

    final public function __construct()
    {
        // nothing to see here
    }

    /**
     * @param string $file
     * @return static|null
     */
    public static function fromFile($file)
    {
        if (empty($file) || !file_exists($file)) {
            return null;
        }
        $content = file_get_contents($file);
        return static::parseData($content);
    }

    /**
     * @param string $data
     * @return static
     */
    public static function parseData($data)
    {
        $comic = new static();
        if (empty($data)) {
            return $comic;
        }

        $doc = new DOMDocument();
        $doc->loadXML($data);

        $root = $doc->getElementsByTagName('ComicInfo')->item(0);
        if (!$root) {
            return $comic;
        }

        foreach ($root->childNodes as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }

            if ($node->nodeName === 'Pages') {
                foreach ($node->getElementsByTagName('Page') as $pageNode) {
                    $pageData = [];
                    if ($pageNode->hasAttributes()) {
                        foreach ($pageNode->attributes as $attr) {
                            $pageData[$attr->nodeName] = $attr->nodeValue;
                        }
                    }
                    $comic->pages[] = $pageData;
                }
            } elseif (in_array($node->nodeName, self::FIELDS)) {
                $comic->info[$node->nodeName] = trim($node->nodeValue);
            }
        }

        return $comic;
    }

    /**
     * @return string
     */
    public function toXML()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('ComicInfo');
        $root->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $doc->appendChild($root);

        foreach (self::FIELDS as $field) {
            if ($field === 'Pages') {
                if (!empty($this->pages)) {
                    $pagesNode = $doc->createElement('Pages');
                    $root->appendChild($pagesNode);
                    foreach ($this->pages as $pageData) {
                        $pageNode = $doc->createElement('Page');
                        foreach ($pageData as $key => $value) {
                            $pageNode->setAttribute($key, $value);
                        }
                        $pagesNode->appendChild($pageNode);
                    }
                }
                continue;
            }
            if (isset($this->info[$field]) && (string) $this->info[$field] !== '') {
                $root->appendChild($doc->createElement($field, htmlspecialchars((string) $this->info[$field])));
            }
        }

        return $doc->saveXML();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @return void
     */
    public function setPages(array $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Set the book title
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->info['Title'] = $title;
        return $this;
    }

    /**
     * Get the book title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->info['Title'] ?? '';
    }

    /**
     * Set the series of the book
     *
     * @param string $series
     * @return self
     */
    public function setSeries(string $series): self
    {
        $this->info['Series'] = $series;
        return $this;
    }

    /**
     * Get the series of the book
     *
     * @return string
     */
    public function getSeries(): string
    {
        return $this->info['Series'] ?? '';
    }

    /**
     * Set the issue number
     *
     * @param string $number
     * @return self
     */
    public function setNumber(string $number): self
    {
        $this->info['Number'] = $number;
        return $this;
    }

    /**
     * Get the issue number
     *
     * @return string
     */
    public function getNumber(): string
    {
        return $this->info['Number'] ?? '';
    }

    /**
     * Set the summary of the book
     *
     * @param string $summary
     * @return self
     */
    public function setSummary(string $summary): self
    {
        $this->info['Summary'] = $summary;
        return $this;
    }

    /**
     * Get the summary of the book
     *
     * @return string
     */
    public function getSummary(): string
    {
        return $this->info['Summary'] ?? '';
    }

    /**
     * Set the writer of the book
     *
     * @param string $writer
     * @return self
     */
    public function setWriter(string $writer): self
    {
        $this->info['Writer'] = $writer;
        return $this;
    }

    /**
     * Get the writer of the book
     *
     * @return string
     */
    public function getWriter(): string
    {
        return $this->info['Writer'] ?? '';
    }

    /**
     * Set the book's publisher info
     *
     * @param string $publisher
     * @return self
     */
    public function setPublisher(string $publisher): self
    {
        $this->info['Publisher'] = $publisher;
        return $this;
    }

    /**
     * Get the book's publisher info
     *
     * @return string
     */
    public function getPublisher(): string
    {
        return $this->info['Publisher'] ?? '';
    }

    /**
     * Set the book's language (ISO code)
     *
     * @param string $lang
     * @return self
     */
    public function setLanguageISO(string $lang): self
    {
        $this->info['LanguageISO'] = $lang;
        return $this;
    }

    /**
     * Get the book's language (ISO code)
     *
     * @return string
     */
    public function getLanguageISO(): string
    {
        return $this->info['LanguageISO'] ?? '';
    }

    /**
     * Set the book's tags
     *
     * @param string $tags
     * @return self
     */
    public function setTags(string $tags): self
    {
        $this->info['Tags'] = $tags;
        return $this;
    }

    /**
     * Get the book's tags
     *
     * @return string
     */
    public function getTags(): string
    {
        return $this->info['Tags'] ?? '';
    }

    /**
     * Set the book's GTIN
     *
     * @param string $gtin
     * @return self
     */
    public function setGTIN(string $gtin): self
    {
        $this->info['GTIN'] = $gtin;
        return $this;
    }

    /**
     * Get the book's GTIN
     *
     * @return string
     */
    public function getGTIN(): string
    {
        return $this->info['GTIN'] ?? '';
    }

    /**
     * Magic method to handle getters and setters for ComicInfo fields
     * @param string $name
     * @param array<mixed> $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        $field = substr($name, 3);

        if ($prefix === 'get' && in_array($field, self::FIELDS)) {
            return $this->info[$field] ?? '';
        }

        if ($prefix === 'set' && in_array($field, self::FIELDS)) {
            $this->info[$field] = $arguments[0] ?? '';
            return $this;
        }

        throw new Exception("Method $name not found");
    }
}
