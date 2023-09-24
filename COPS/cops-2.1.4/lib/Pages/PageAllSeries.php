<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Input\Config;

class PageAllSeries extends Page
{
    protected string $className = Serie::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Serie::PAGE_ID;
        $this->title = localize("series.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        // @todo needs title_sort function in sqlite for series
        //if ($baselist->hasChildCategories()) {
        //    // use tag_browser_series view here, to get the full hierarchy?
        //    $this->entryArray = $baselist->browseAllEntries($this->n);
        //} else {
        //    $this->entryArray = $baselist->getRequestEntries($this->n);
        //}
        $this->entryArray = $baselist->getRequestEntries($this->n);
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("series", Config::get('show_not_set_filter'))) {
            array_push($this->entryArray, $baselist->getWithoutEntry());
        }
    }
}
