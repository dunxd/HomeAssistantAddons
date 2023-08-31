<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\PageId;

class Entry
{
    public string $title;
    public string $id;
    public string $content;
    /** @var mixed */
    public $numberOfElement;
    public string $contentType;
    /** @var array<LinkEntry|LinkFeed> */
    public $linkArray;
    /** @var int|null */
    public $localUpdated;
    public string $className;
    /** @var int|null */
    private static $updated = null;
    /** @var mixed */
    protected $databaseId;

    /** @var array<string, string> */
    public static $icons = [
        PageId::ALL_AUTHORS_ID             => 'images/author.png',
        PageId::ALL_SERIES_ID              => 'images/serie.png',
        PageId::ALL_RECENT_BOOKS_ID        => 'images/recent.png',
        PageId::ALL_TAGS_ID                => 'images/tag.png',
        PageId::ALL_LANGUAGES_ID           => 'images/language.png',
        PageId::ALL_CUSTOMS_ID             => 'images/custom.png',
        PageId::ALL_RATING_ID              => 'images/rating.png',
        "cops:books$"                    => 'images/allbook.png',
        "cops:books:letter"              => 'images/allbook.png',
        PageId::ALL_PUBLISHERS_ID          => 'images/publisher.png',
    ];

    /**
     * Summary of __construct
     * @param string $ptitle
     * @param string $pid
     * @param string $pcontent
     * @param string $pcontentType
     * @param array<LinkEntry|LinkFeed> $plinkArray
     * @param string|int|null $database
     * @param string $pclass
     * @param mixed $pcount
     */
    public function __construct($ptitle, $pid, $pcontent, $pcontentType = "text", $plinkArray = [], $database = null, $pclass = "", $pcount = 0)
    {
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
        $this->className = $pclass;
        $this->numberOfElement = $pcount;

        if (Config::get('show_icons') == 1) {
            foreach (self::$icons as $reg => $image) {
                if (preg_match("/" . $reg . "/", $pid)) {
                    array_push($this->linkArray, new LinkEntry(Format::addVersion($image), "image/png", LinkEntry::OPDS_THUMBNAIL_TYPE));
                    break;
                }
            }
        }

        if (!is_null($database)) {
            $this->id = str_replace("cops:", "cops:" . strval($database) . ":", $this->id);
        }
    }

    /**
     * Summary of getUpdatedTime
     * @return string
     */
    public function getUpdatedTime()
    {
        if (!is_null($this->localUpdated)) {
            return date(DATE_ATOM, $this->localUpdated);
        }
        if (is_null(self::$updated)) {
            self::$updated = time();
        }
        return date(DATE_ATOM, self::$updated);
    }

    /**
     * Summary of getNavLink
     * @param string $endpoint
     * @param string $extraUri
     * @return string
     */
    public function getNavLink($endpoint = "", $extraUri = "")
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkEntry|LinkFeed */

            if (!($link instanceof LinkFeed)) {
                continue;
            }

            return $link->hrefXhtml($endpoint) . $extraUri;
        }
        return "#";
    }

    /**
     * Summary of getThumbnail
     * @param string $endpoint
     * @return string|null
     */
    public function getThumbnail($endpoint = '')
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_THUMBNAIL_TYPE) {
                return $link->hrefXhtml($endpoint);
            }
        }
        return null;
    }

    /**
     * Summary of getImage
     * @param string $endpoint
     * @return string|null
     */
    public function getImage($endpoint = '')
    {
        foreach ($this->linkArray as $link) {
            /** @var $link LinkFeed|LinkEntry */

            if ($link->rel == LinkEntry::OPDS_IMAGE_TYPE) {
                return $link->hrefXhtml($endpoint);
            }
        }
        return null;
    }
}