<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;

class PageAllBooks extends Page
{
    protected string $className = Book::class;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Book::PAGE_ID;
        $this->title = localize("allbooks.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request);
        if ($this->request->option("titles_split_first_letter") == 1 || $this->request->get('letter')) {
            $this->entryArray = $booklist->getCountByFirstLetter();
            $this->sorted = $booklist->orderBy ?? "letter";
        } elseif (!empty($this->request->option("titles_split_publication_year")) || $this->request->get('year')) {
            $this->entryArray = $booklist->getCountByPubYear();
            $this->sorted = $booklist->orderBy ?? "year";
        } else {
            [$this->entryArray, $this->totalNumber] = $booklist->getAllBooks($this->n);
            $this->sorted = $booklist->orderBy ?? Book::SQL_SORT;
        }
    }
}
