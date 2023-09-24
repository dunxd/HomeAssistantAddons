<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\CustomColumnTypeBool;
use SebLucas\Cops\Calibre\CustomColumnTypeDate;
use SebLucas\Cops\Calibre\CustomColumnTypeInteger;

class PageCustomDetail extends Page
{
    protected string $className = CustomColumn::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        // this could be string for some custom columns - override here
        $this->idGet = $this->request->get('id');
        $customId = $this->request->get("custom", null);
        $instance = CustomColumn::createCustom($customId, $this->idGet, $this->getDatabaseId());
        $this->idPage = $instance->getEntryId();
        $this->title = $instance->getTitle();
        $this->currentUri = $instance->getUri();
        if ($this->request->get('filter')) {
            $this->filterUri = '&c[' . $customId . ']=' . $this->idGet;
            $this->getFilters($instance);
        } elseif ($this->request->get('tree')) {
            $this->getHierarchy($instance);
        } else {
            $this->getCustomEntries($instance);
        }
        $this->parentTitle = $instance->getParentTitle();
        $this->parentUri = $instance->getParentUri();
        if ($instance->hasChildCategories()) {
            $this->hierarchy = [
                "parent" => $instance->getParentEntry(),
                "current" => $instance->getEntry(),
                "children" => $instance->getChildEntries($this->request->get('tree')),
            ];
        }
    }

    /**
     * Summary of getHierarchy
     * @param CustomColumn $instance
     * @return void
     */
    public function getHierarchy($instance)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstanceOrChildren($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }

    /**
     * Summary of getCustomEntries
     * @param CustomColumn $instance
     * @return void
     */
    public function getCustomEntries($instance)
    {
        $columnType = $instance->customColumnType;
        $booklist = new BookList($this->request);
        if (!empty($this->idGet) || $columnType instanceof CustomColumnTypeBool) {
            [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
            $this->sorted = $booklist->orderBy ?? "sort";
            return;
        }
        if ($columnType instanceof CustomColumnTypeDate) {
            // if we use $columnType::PAGE_DETAIL in PageAllCustoms, otherwise see PageAllCustoms
            $year = $this->request->get("year", null, $columnType::GET_PATTERN);
            if (!empty($year)) {
                [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustomYear($columnType, $year, $this->n);
                $this->title = $year;
                $this->sorted = $booklist->orderBy ?? "value";
                return;
            }
        }
        if ($columnType instanceof CustomColumnTypeInteger) {
            // if we use $columnType::PAGE_DETAIL in PageAllCustoms, otherwise see PageAllCustoms
            $range = $this->request->get("range", null, $columnType::GET_PATTERN);
            if (!empty($range)) {
                [$this->entryArray, $this->totalNumber] = $booklist->getBooksByCustomRange($columnType, $range, $this->n);
                $this->title = $range;
                $this->sorted = $booklist->orderBy ?? "value";
                return;
            }
        }
    }
}
