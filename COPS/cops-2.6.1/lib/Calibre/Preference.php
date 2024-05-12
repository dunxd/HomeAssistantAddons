<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\PageId;
use JsonException;

class Preference
{
    public const PAGE_ID = PageId::ALL_PREFERENCES_ID;
    public const PAGE_ALL = PageId::ALL_PREFERENCES;
    public const PAGE_DETAIL = PageId::PREFERENCE_DETAIL;
    public const SQL_TABLE = "preferences";
    public const SQL_COLUMNS = "id, key, val";

    public int $id;
    public string $key;
    public mixed $val;
    public ?int $databaseId = null;

    /**
     * Summary of __construct
     * @param object $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->key = $post->key;
        try {
            $this->val = json_decode($post->val, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->val = $post->val;
        }
        $this->databaseId = $database;
    }

    /**
     * Summary of getInstances
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getInstances($database = null)
    {
        $preferences = [];
        $query = 'select ' . static::SQL_COLUMNS . ' from ' . static::SQL_TABLE . ' order by key';
        $result = Database::query($query, [], $database);
        while ($post = $result->fetchObject()) {
            $preferences[$post->key] = new self($post, $database);
        }
        return $preferences;
    }

    /**
     * Summary of getInstanceByKey
     * @param string $key
     * @param ?int $database
     * @return self|null
     */
    public static function getInstanceByKey($key, $database = null)
    {
        $query = 'select ' . static::SQL_COLUMNS . ' from ' . static::SQL_TABLE . ' where key = ?';
        $params = [$key];
        $result = Database::query($query, $params, $database);
        if ($post = $result->fetchObject()) {
            return new self($post, $database);
        }
        return null;
    }

    /**
     * Summary of getVirtualLibraries
     * {
     *   "Both Authors": "authors:\"=Author Two\" and authors:\"=Author One\"",
     *   "Kindle 2": "tags:\"=Kindle_Mike\" or tags:\"=Kindle_Luca\"",
     *   "No Device": "not tags:\"=Kindle_Mike\" and not tags:\"=Kindle_Luca\" and not tags:\"=Kindle_Lydia\""
     * }
     * See https://github.com/seblucas/cops/pull/233
     * @param ?int $database
     * @return self|null
     */
    public static function getVirtualLibraries($database = null)
    {
        return static::getInstanceByKey('virtual_libraries', $database);
    }

    /**
     * Summary of getUserCategories
     * @param ?int $database
     * @return self|null
     */
    public static function getUserCategories($database = null)
    {
        // @todo investigate format
        return static::getInstanceByKey('user_categories', $database);
    }

    /**
     * Summary of getSavedSearches
     * {
     *   "Author One": "authors:one and not authors:two"
     * }
     * @param ?int $database
     * @return self|null
     */
    public static function getSavedSearches($database = null)
    {
        // @todo map search string from saved search to filters
        return static::getInstanceByKey('saved_searches', $database);
    }
}
