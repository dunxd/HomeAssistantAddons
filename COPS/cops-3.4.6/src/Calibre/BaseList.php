<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;

class BaseList
{
    public Request $request;
    /** @var class-string */
    public $className;
    /** @var ?int */
    protected $databaseId = null;
    /** @var ?int */
    protected $numberPerPage = null;
    /** @var array<string> */
    //protected $ignoredCategories = [];
    /** @var ?string */
    public $orderBy = null;
    /** @var class-string */
    protected $handler;

    /**
     * @param class-string $className
     * @param ?Request $request
     * @param ?int $database
     * @param ?int $numberPerPage
     */
    public function __construct($className, $request, $database = null, $numberPerPage = null)
    {
        $this->className = $className;
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->database();
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        //$this->ignoredCategories = $this->request->option('ignored_categories');
        $this->setOrderBy();
        // get handler based on $this->request
        $this->handler = $this->request->getHandler();
    }

    /**
     * Summary of setOrderBy
     * @return void
     */
    protected function setOrderBy()
    {
        $this->orderBy = $this->request->getSorted($this->getSort());
        //$this->orderBy ??= $this->request->option('sort');
    }

    /**
     * Summary of getOrderBy
     * @return ?string
     */
    protected function getOrderBy()
    {
        return match ($this->orderBy) {
            'title' => 'sort',
            'count' => 'count desc, name',
            default => $this->orderBy,
        };
    }

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /** Use inherited class methods to get entries from <Whatever> by instance (linked via books) */

    /**
     * Summary of getTable
     * @return string
     */
    public function getTable()
    {
        return $this->className::SQL_TABLE;
    }

    /**
     * Summary of getSort
     * @return string
     */
    public function getSort()
    {
        return $this->className::SQL_SORT;
    }

    /**
     * Summary of getCountColumns
     * @return string
     */
    public function getCountColumns()
    {
        return $this->className::SQL_COLUMNS . ", count(*) as count";
    }

    /**
     * Summary of getLinkTable
     * @return string
     */
    public function getLinkTable()
    {
        return $this->className::SQL_LINK_TABLE;
    }

    /**
     * Summary of getLinkColumn
     * @return string
     */
    public function getLinkColumn()
    {
        return $this->className::SQL_LINK_COLUMN;
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    /**
     * Summary of getInstanceById
     * @param ?int $id
     * @return mixed
     */
    public function getInstanceById($id)
    {
        return $this->className::getInstanceById($id, $this->databaseId);
    }

    /**
     * Summary of getWithoutEntry
     * @return ?Entry
     */
    public function getWithoutEntry()
    {
        $count = $this->countWithoutEntries();
        $instance = $this->getInstanceById(null);
        $instance->setHandler($this->handler);
        return $instance->getEntry($count);
    }

    /**
     * Summary of getEntryCount
     * @return ?Entry
     */
    public function getEntryCount()
    {
        return $this->className::getCount($this->databaseId);
    }

    /**
     * Summary of countRequestEntries
     * @return int
     */
    public function countRequestEntries()
    {
        if ($this->request->hasFilter()) {
            return $this->countEntriesByFilter();
        }
        return $this->countAllEntries();
    }

    /**
     * Summary of countAllEntries
     * @return int
     */
    public function countAllEntries()
    {
        return Database::querySingle('select count(*) from ' . $this->getTable(), $this->databaseId);
    }

    /**
     * Summary of countDistinctEntries
     * @param ?string $column
     * @return int
     */
    public function countDistinctEntries($column = null)
    {
        $column ??= $this->getSort();
        return Database::querySingle('select count(distinct ' . $column . ') from ' . $this->getTable(), $this->databaseId);
    }

    /**
     * Summary of countEntriesByFirstLetter
     * @param string $letter
     * @return int
     */
    public function countEntriesByFirstLetter($letter)
    {
        $filterString = 'upper(' . $this->getTable() . '.' . $this->getSort() . ') like ?';
        if (Translation::useNormAndUp()) {
            $filterString = preg_replace("/upper/", "normAndUp", $filterString);
        }
        $param =  $letter . "%";
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        $filter->addFilter($filterString, $param);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countEntriesByFilter
     * @return int
     */
    public function countEntriesByFilter()
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countEntriesByInstance
     * @param Base|Category $instance
     * @param array<string, mixed> $filterParams if we want to filter by virtual library etc.
     * @return int
     */
    public function countEntriesByInstance($instance, $filterParams = [])
    {
        $filter = new Filter($filterParams, [], $this->getLinkTable(), $this->databaseId);
        $filter->addInstanceFilter($instance);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countFilteredEntries
     * @param Filter $filter
     * @return int
     */
    public function countFilteredEntries($filter)
    {
        // select {0} from series, books_series_link where series.id = books_series_link.series {1}
        $query = 'select {0} from ' . $this->getTable() . ', ' . $this->getLinkTable() . ' where ' . $this->getTable() . '.id = ' . $this->getLinkTable() . '.' . $this->getLinkColumn() . ' {1}';
        // count(distinct series.id)
        $columns = 'count(distinct ' . $this->getTable() . '.id)';
        // and (exists (select null from books_authors_link, books where books_series_link.book = books.id and books_authors_link.book = books.id and books_authors_link.author = ?))
        $filterString = $filter->getFilterString();
        // [1]
        $params = $filter->getQueryParams();
        return Database::countFilter($query, $columns, $filterString, $params, $this->databaseId);
    }

    /**
     * Summary of countWithoutEntries
     * @return int
     */
    public function countWithoutEntries()
    {
        // @todo see BookList::getBooksWithoutCustom() to support CustomColumn
        if (!in_array($this->className, [Rating::class, Serie::class, Tag::class, Identifier::class])) {
            return 0;
        }
        $query = $this->className::SQL_BOOKLIST_NULL;
        $columns = 'count(distinct books.id)';
        return Database::countFilter($query, $columns, "", [], $this->databaseId);
    }

    /**
     * Summary of getRequestEntries
     * @param int $n
     * @return array<Entry>
     */
    public function getRequestEntries($n = 1)
    {
        if ($this->request->hasFilter()) {
            // we *do* pass along parentClass here - see also Filter::getEntryArray()
            return $this->getEntriesByFilter($n, $this->className);
        }
        return $this->getAllEntries($n);
    }

    /**
     * Summary of getAllEntries = same as getAll<Whatever>() in <Whatever> child class
     * @param int $n
     * @return array<Entry>
     */
    public function getAllEntries($n = 1)
    {
        $query = $this->className::SQL_ALL_ROWS;
        $query = $this->addOrderByClause($query);
        $columns = $this->getCountColumns();
        return $this->getEntryArrayWithBookNumber($query, $columns, "", [], $n);
    }

    /**
     * Summary of getAllEntriesByQuery
     * @param string $find
     * @param int $n
     * @param int $repeat for SCOPE_AUTHOR we need to repeat the query x 2 because Author checks both name and sort fields
     * @return array<Entry>
     */
    public function getAllEntriesByQuery($find, $n = 1, $repeat = 1)
    {
        $query = $this->className::SQL_ROWS_FOR_SEARCH;
        $columns = $this->getCountColumns();
        // Author has 2 params, the rest 1
        $params = array_fill(0, $repeat, '%' . $find . '%');
        if ($this->request->hasFilter()) {
            $filter = new Filter($this->request, $params, $this->getLinkTable(), $this->databaseId);
            $filterString = $filter->getFilterString();
            $params = $filter->getQueryParams();
            return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
        }
        return $this->getEntryArrayWithBookNumber($query, $columns, "", $params, $n);
    }

    /**
     * Summary of getCountByFirstLetter
     * @return array<Entry>
     */
    public function getCountByFirstLetter()
    {
        // substr(upper(authors.sort), 1, 1)
        $groupField = 'substr(upper(' . $this->getTable() . '.' . $this->getSort() . '), 1, 1)';
        return $this->getCountByGroup($groupField, $this->className::ROUTE_LETTER, 'letter');
    }

    /**
     * Summary of getCountByGroup
     * @param string $groupField
     * @param string $routeName
     * @param string $label
     * @return array<Entry>
     */
    public function getCountByGroup($groupField, $routeName, $label)
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        if (!in_array($this->orderBy, ['groupid', 'count'])) {
            $this->orderBy = 'groupid';
        }
        $sortBy = $this->getOrderBy();
        // select {0} from authors, books_authors_link where authors.id = books_authors_link.author {1}
        $query = 'select {0} from ' . $this->getTable() . ', ' . $this->getLinkTable() . ' where ' . $this->getTable() . '.id = ' . $this->getLinkTable() . '.' . $this->getLinkColumn() . ' {1}';
        // group by groupid
        $query .= ' group by groupid';
        // order by $sortBy
        $query .= ' order by ' . $sortBy;
        // $groupField as groupid, count(distinct authors.id) as count
        $columns = $groupField . ' as groupid, count(distinct ' . $this->getTable() . '.id) as count';
        $result = Database::queryFilter($query, $columns, $filterString, $params, -1, $this->databaseId);

        $entryArray = [];
        $params = $this->request->getFilterParams();
        $params["db"] ??= $this->databaseId;
        while ($post = $result->fetchObject()) {
            $params["id"] = $post->groupid;
            $href = $this->handler::route($routeName, $params);
            array_push($entryArray, new Entry(
                $post->groupid,
                $this->className::PAGE_ID . ':' . $label . ':' . $post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [ new LinkNavigation($href, "subsection") ],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }
        return $entryArray;
    }

    /**
     * Summary of getEntriesByFirstLetter
     * @param string $letter
     * @param int $n
     * @return array<Entry>
     */
    public function getEntriesByFirstLetter($letter, $n = 1)
    {
        $query = $this->className::SQL_ROWS_BY_FIRST_LETTER;
        // authors by first letter in sort
        if ($this->orderBy == 'name') {
            $this->orderBy = 'sort';
        }
        $query = $this->addOrderByClause($query);
        $columns = $this->getCountColumns();
        $filter = new Filter($this->request, [$letter . "%"], $this->getLinkTable(), $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    /**
     * Summary of getEntriesByFilter
     * @param int $n
     * @param ?string $parentClass - if we want to use limitSelf = false for tags/identifiers
     * @return array<Entry>
     */
    public function getEntriesByFilter($n = 1, $parentClass = null)
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId, $parentClass);
        return $this->getFilteredEntries($filter, $n);
    }

    /**
     * Summary of getEntriesByInstance
     * @param Base|Category $instance
     * @param int $n
     * @param array<string, mixed> $filterParams if we want to filter by virtual library etc.
     * @return array<Entry>
     */
    public function getEntriesByInstance($instance, $n = 1, $filterParams = [])
    {
        // @todo handle Not Set instances here too?
        $filter = new Filter($filterParams, [], $this->getLinkTable(), $this->databaseId);
        $filter->addInstanceFilter($instance);
        $entries = $this->getFilteredEntries($filter, $n);
        $limit = $instance->getFilterLimit();
        // are we at the filter limit for this instance?
        if ($n == 1 && count($entries) < $limit) {
            return $entries;
        }
        // if so, let's see how many entries we're missing
        $total = $this->countEntriesByInstance($instance, $filterParams);
        $count = $total - count($entries);
        if ($count < 1) {
            return $entries;
        }
        // @todo let the caller know there are more entries available
        // @todo we can't use facetGroups here, or OPDS reader thinks we're drilling down :-()
        $className = $instance->getClassName($this->className);
        $title = strtolower($className);
        $title = localize($title . 's.title');
        $href = $instance->getUri();
        if ($n > 1) {
            if (str_contains($href, '?')) {
                $paging = '&filter=1';
            } else {
                $paging = '?filter=1';
            }
            if ($n > 2) {
                $paging .= '&g[' . $this->className::URL_PARAM . ']=' . strval($n - 1);
            }
            $entry = new Entry(
                localize("paging.previous.alternate") . " " . $title,
                $instance->getEntryId() . ':filter:',
                $instance->getContent($count),
                "text",
                [ new LinkFeed($href . $paging) ],
                $this->databaseId,
                $className,
                $count
            );
            array_push($entries, $entry);
        }
        if ($n < ceil($total / $limit)) {
            if (str_contains($href, '?')) {
                $paging = '&filter=1';
            } else {
                $paging = '?filter=1';
            }
            $paging .= '&g[' . $this->className::URL_PARAM . ']=' . strval($n + 1);
            $entry = new Entry(
                localize("paging.next.alternate") . " " . $title,
                $instance->getEntryId() . ':filter:',
                $instance->getContent($count),
                "text",
                [ new LinkFeed($href . $paging) ],
                $this->databaseId,
                $className,
                $count
            );
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getEntriesByCustomValueId
     * @param CustomColumnType $customType
     * @param mixed $valueId
     * @param int $n
     * @return array<Entry>
     */
    public function getEntriesByCustomValueId($customType, $valueId, $n = 1)
    {
        $filter = new Filter([], [], $this->getLinkTable(), $this->databaseId);
        $filter->addCustomIdFilter($customType, $valueId);
        return $this->getFilteredEntries($filter, $n);
    }

    /**
     * Summary of getFilteredEntries
     * @param Filter $filter
     * @param int $n
     * @return array<Entry>
     */
    public function getFilteredEntries($filter, $n = 1)
    {
        $query = $this->className::SQL_ALL_ROWS;
        $query = $this->addOrderByClause($query);
        $columns = $this->getCountColumns();
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    /**
     * Summary of getWithoutEntries - @todo not used
     * @param Filter $filter
     * @param int $n
     * @return array<Entry>
     */
    public function getWithoutEntries($filter, $n = 1)
    {
        // @todo see BookList::getBooksWithoutCustom() to support CustomColumn
        if (!in_array($this->className, [Rating::class, Serie::class, Tag::class, Identifier::class])) {
            return [];
        }
        $query = $this->className::SQL_BOOKLIST_NULL;
        $query = $this->addOrderByClause($query);
        $columns = $this->getCountColumns();
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    /**
     * Summary of addOrderByClause
     * @param string $query
     * @return string
     */
    public function addOrderByClause($query)
    {
        if (!empty($this->orderBy) && $this->orderBy != $this->getSort() && str_contains($this->getCountColumns(), ' as ' . $this->orderBy)) {
            if (str_contains($query, 'order by')) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . $this->getOrderBy() . ' ', $query);
            } else {
                $query .= ' order by ' . $this->getOrderBy() . ' ';
            }
        }
        return $query;
    }

    /**
     * Summary of getEntryArrayWithBookNumber
     * @param string $query
     * @param string $columns
     * @param string $filter
     * @param array<mixed> $params
     * @param int $n
     * @return array<Entry>
     */
    public function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $n)
    {
        $result = Database::queryFilter($query, $columns, $filter, $params, $n, $this->databaseId, $this->numberPerPage);
        $entryArray = [];
        $params = [];
        if ($this->request->hasFilter()) {
            $params = $this->request->getFilterParams();
            //$params["db"] ??= $this->databaseId;
        }
        while ($post = $result->fetchObject()) {
            if ($this->className == Book::class) {
                $post->count = 1;
            }

            /** @var Base|Book $instance */
            $instance = new $this->className($post, $this->databaseId);
            $instance->setHandler($this->handler);
            array_push($entryArray, $instance->getEntry($post->count, $params));
        }
        return $entryArray;
    }

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        if (empty(Config::get('calibre_categories_using_hierarchy')) || !in_array($this->className::CATEGORY, Config::get('calibre_categories_using_hierarchy'))) {
            return false;
        }
        return true;
    }

    /**
     * Use the Calibre tag browser view to retrieve all tags or series with count
     * Format: tag_browser_tags(id,name,count,avg_rating,sort)
     * @param int $n
     * @return array<Entry>
     */
    public function browseAllEntries($n = 1)
    {
        if (!$this->hasChildCategories()) {
            return [];
        }
        $tableName = 'tag_browser_' . $this->className::CATEGORY;
        $queryFormat = "SELECT id, name, count FROM {0} ORDER BY {1}";
        if (!in_array($this->orderBy, ['id', 'name', 'count', 'sort'])) {
            $this->orderBy = "sort";
        }
        $query = str_format($queryFormat, $tableName, $this->getOrderBy());

        $result = Database::queryFilter($query, "", "", [], $n, $this->databaseId, $this->numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            /** @var Base|Book $instance */
            $instance = new $this->className($post, $this->databaseId);
            $instance->setHandler($this->handler);
            array_push($entryArray, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of getInstanceIdsByBookIds
     * @param array<int> $bookIds
     * @return array<int, array<int>>
     */
    public function getInstanceIdsByBookIds($bookIds)
    {
        if (count($bookIds) < 1) {
            return [];
        }
        $queryFormat = 'SELECT book, {1} as instanceId FROM {0} WHERE book IN (' . str_repeat('?,', count($bookIds) - 1) . '?)';
        $query = str_format($queryFormat, $this->getLinkTable(), $this->getLinkColumn());
        $result = Database::query($query, $bookIds, $this->databaseId);

        $instanceIds = [];
        while ($post = $result->fetchObject()) {
            $instanceIds[$post->book] ??= [];
            array_push($instanceIds[$post->book], $post->instanceId);
        }
        return $instanceIds;
    }

    /**
     * Summary of getInstancesByIds
     * @param array<int, array<int>> $instanceIds
     * @return array<int, mixed>
     */
    public function getInstancesByIds($instanceIds)
    {
        $uniqueIds = static::getUniqueInstanceIds($instanceIds);
        if (count($uniqueIds) < 1) {
            return [];
        }
        $query = 'select ' . $this->className::SQL_COLUMNS . ' from ' . $this->className::SQL_TABLE . ' where id IN (' . str_repeat('?,', count($uniqueIds) - 1) . '?)';
        $result = Database::query($query, $uniqueIds, $this->databaseId);
        $instances = [];
        while ($post = $result->fetchObject()) {
            if ($this->className == Data::class) {
                // we don't have the book available here, set later
                $instances[$post->id] = new $this->className($post);
            } else {
                $instances[$post->id] = new $this->className($post, $this->databaseId);
                $instances[$post->id]->setHandler($this->handler);
            }
        }
        return $instances;
    }

    /**
     * Summary of getUniqueInstanceIds
     * @param array<int, array<int>> $instanceIds
     * @return array<int>
     */
    public static function getUniqueInstanceIds($instanceIds)
    {
        $uniqueIds = [];
        foreach ($instanceIds as $bookId => $instanceIdList) {
            $uniqueIds = array_values(array_unique(array_merge($uniqueIds, $instanceIdList)));
        }
        return $uniqueIds;
    }
}
