<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;

class Page
{
    public const PAGE_ID = "cops:catalog";

    /** @var string */
    public $title;
    public string $subtitle = "";
    public string $authorName = "";
    public string $authorUri = "";
    public string $authorEmail = "";
    public string $parentTitle = "";
    public string $currentUri = "";
    public string $parentUri = "";
    /** @var ?string */
    public $idPage;
    /** @var string|int|null */
    public $idGet;
    public string $favicon;
    /** @var int */
    public $n;
    /** @var ?Book */
    public $book;
    /** @var int */
    public $totalNumber = -1;
    /** @var ?string */
    public $sorted = "sort";
    /** @var array<string, mixed> */
    public $filterParams = [];
    /** @var array<string, mixed>|false */
    public $hierarchy = false;
    /** @var array<string, mixed>|false */
    public $extra = false;

    /** @var Entry[] */
    public $entryArray = [];

    /** @var Request */
    protected $request = null;
    protected string $className = Base::class;
    /** @var int */
    protected $numberPerPage = -1;
    /** @var array<string> */
    protected $ignoredCategories = [];
    /** @var ?int */
    protected $databaseId = null;
    protected string $handler = '';

    /**
     * Summary of getPage
     * @param string|int|null $pageId
     * @param ?Request $request
     * @return Page|PageAbout|PageAllAuthors|PageAllAuthorsLetter|PageAllBooks|PageAllBooksLetter|PageAllBooksYear|PageAllCustoms|PageAllIdentifiers|PageAllLanguages|PageAllPublishers|PageAllRating|PageAllSeries|PageAllTags|PageAuthorDetail|PageBookDetail|PageCustomDetail|PageCustomize|PageIdentifierDetail|PageLanguageDetail|PagePublisherDetail|PageQueryResult|PageRatingDetail|PageRecentBooks|PageSerieDetail|PageTagDetail
     */
    public static function getPage($pageId, $request)
    {
        return PageId::getPage($pageId, $request);
    }

    /**
     * Summary of __construct
     * @param ?Request $request
     */
    public function __construct($request = null)
    {
        $this->setConfig();
        $this->setRequest($request);

        // move to constructor as this is always called directly after PageId::getPage()
        $this->initializeContent();
    }

    /**
     * Summary of setRequest
     * @param ?Request $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request ?? new Request();
        // this could be string for first letter, identifier or custom columns - override there
        $this->idGet = $this->request->getId();
        $this->n = $this->request->get('n', 1, '/^\d+$/');  // use default here
        $this->numberPerPage = $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->databaseId = $this->request->database();
        $this->handler = $this->request->getHandler();
    }

    /**
     * Summary of setConfig
     * @return void
     */
    public function setConfig()
    {
        $this->favicon = Config::get('icon');
        $this->authorName = Config::get('author_name') ?: 'Sébastien Lucas';
        $this->authorUri = Config::get('author_uri') ?: 'http://blog.slucas.fr';
        $this->authorEmail = Config::get('author_email') ?: 'sebastien@slucas.fr';
    }

    /**
     * Summary of getNumberPerPage
     * @return int
     */
    public function getNumberPerPage()
    {
        return $this->numberPerPage;
    }

    /**
     * Summary of getIgnoredCategories
     * @return array<string>
     */
    public function getIgnoredCategories()
    {
        return $this->ignoredCategories;
    }

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = static::PAGE_ID;
        $this->title = Config::get('title_default');
        $this->subtitle = Config::get('subtitle_default');
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $this->getExtra();
    }

    /**
     * Summary of getExtra
     * @return void
     */
    public function getExtra()
    {
        $this->extra = false;
    }

    /**
     * Summary of isPaginated
     * @return bool
     */
    public function isPaginated()
    {
        return ($this->getNumberPerPage() != -1 &&
                $this->totalNumber != -1 &&
                $this->totalNumber > $this->getNumberPerPage());
    }

    /**
     * Summary of getFirstLink
     * @return ?LinkNavigation
     */
    public function getFirstLink()
    {
        if ($this->n > 1) {
            $params = $this->request->getCleanParams();
            return new LinkNavigation(
                Route::link($this->handler, null, $params),
                "first",
                localize("paging.first.alternate")
            );
        }
        return null;
    }

    /**
     * Summary of getLastLink
     * @return ?LinkNavigation
     */
    public function getLastLink()
    {
        if ($this->n < $this->getMaxPage()) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->getMaxPage());
            return new LinkNavigation(
                Route::link($this->handler, null, $params),
                "last",
                localize("paging.last.alternate")
            );
        }
        return null;
    }

    /**
     * Summary of getNextLink
     * @return ?LinkNavigation
     */
    public function getNextLink()
    {
        if ($this->n < $this->getMaxPage()) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->n + 1);
            return new LinkNavigation(
                Route::link($this->handler, null, $params),
                "next",
                localize("paging.next.alternate")
            );
        }
        return null;
    }

    /**
     * Summary of getPrevLink
     * @return ?LinkNavigation
     */
    public function getPrevLink()
    {
        if ($this->n > 1) {
            $params = $this->request->getCleanParams();
            $params['n'] = strval($this->n - 1);
            return new LinkNavigation(
                Route::link($this->handler, null, $params),
                "previous",
                localize("paging.previous.alternate")
            );
        }
        return null;
    }

    /**
     * Summary of getMaxPage
     * @return float
     */
    public function getMaxPage()
    {
        return ceil($this->totalNumber / $this->numberPerPage);
    }

    /**
     * Summary of getSortOptions
     * @return array<string, string>
     */
    public function getSortOptions()
    {
        if ($this->request->isFeed()) {
            $sortLinks = Config::get('opds_sort_links');
        } else {
            $sortLinks = Config::get('html_sort_links');
        }
        $allowed = array_flip($sortLinks);
        $sortOptions = [
            //'title' => localize("bookword.title"),
            'title' => localize("sort.titles"),
            'author' => localize("authors.title"),
            'pubdate' => localize("pubdate.title"),
            'rating' => localize("ratings.title"),
            'timestamp' => localize("recent.title"),
            //'series' => localize("series.title"),
            //'language' => localize("languages.title"),
            //'publisher' => localize("publishers.title"),
        ];
        return array_intersect_key($sortOptions, $allowed);
    }

    /**
     * Summary of addEntries
     * @param array<Entry> $entries
     * @return void
     */
    public function addEntries($entries)
    {
        $this->entryArray = array_merge($this->entryArray, $entries);
    }

    /**
     * Summary of addHeaderEntry
     * @param string $title
     * @param string $content
     * @param ?string $href
     * @param ?string $relation
     * @return void
     */
    public function addHeaderEntry($title, $content, $href = null, $relation = null)
    {
        array_push($this->entryArray, $this->getHeaderEntry($title, $content, $href, $relation));
    }

    /**
     * Summary of getHeaderEntry
     * @param string $title
     * @param string $content
     * @param ?string $href
     * @param ?string $relation
     * @return Entry
     */
    public function getHeaderEntry($title, $content, $href = null, $relation = null)
    {
        if (empty($href)) {
            $linkArray = [];
        } else {
            $linkArray = [ new LinkNavigation($href, $relation) ];
        }
        return new Entry(
            $title,
            "",
            $content,
            "text",
            $linkArray,
            $this->getDatabaseId(),
            "",
            ""
        );
    }

    /**
     * Summary of containsBook
     * @return bool
     */
    public function containsBook()
    {
        if (count($this->entryArray) == 0) {
            return false;
        }
        if ($this->entryArray [0]::class == EntryBook::class) {
            return true;
        }
        return false;
    }

    /**
     * Summary of canFilter
     * @return bool
     */
    public function canFilter()
    {
        return false;
    }
}
