<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\VirtualLibrary;

class PageAllVirtualLibraries extends Page
{
    protected $className = VirtualLibrary::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = VirtualLibrary::PAGE_ID;
        $this->title = localize("libraries.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $this->entryArray = VirtualLibrary::getEntries($this->getDatabaseId(), $this->handler);
        $this->totalNumber = VirtualLibrary::countEntries($this->getDatabaseId());
        $this->sorted = null;
        array_push($this->entryArray, VirtualLibrary::getWithoutEntry($this->getDatabaseId(), $this->handler));
    }
}