<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;

class PageAllAuthors extends Page
{
    protected string $className = Author::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Author::PAGE_ID;
        $this->title = localize("authors.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        if ($this->request->option("author_split_first_letter") == 1 || $this->request->get('letter')) {
            $this->entryArray = $baselist->getCountByFirstLetter();
            $this->sorted = $baselist->orderBy;
        } else {
            $this->entryArray = $baselist->getRequestEntries($this->n);
            $this->totalNumber = $baselist->countRequestEntries();
            $this->sorted = $baselist->orderBy;
        }
    }
}
