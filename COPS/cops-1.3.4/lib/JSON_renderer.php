<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once dirname(__FILE__) . '/../base.php';

class JSONRenderer
{
    /**
     * @param Book $book
     * @return array
     */
    public static function getBookContentArray($book)
    {
        global $config;
        $i = 0;
        $preferedData = [];
        foreach ($config['cops_prefered_format'] as $format) {
            if ($i == 2) {
                break;
            }
            if ($data = $book->getDataFormat($format)) {
                $i++;
                array_push($preferedData, ["url" => $data->getHtmlLink(),
                  "viewUrl" => $data->getViewHtmlLink(), "name" => $format]);
            }
        }

        $publisher = $book->getPublisher();
        if (is_null($publisher)) {
            $pn = "";
            $pu = "";
        } else {
            $pn = $publisher->name;
            $link = new LinkNavigation($publisher->getUri());
            $pu = $link->hrefXhtml();
        }

        $serie = $book->getSerie();
        if (is_null($serie)) {
            $sn = "";
            $scn = "";
            $su = "";
        } else {
            $sn = $serie->name;
            $scn = str_format(localize("content.series.data"), $book->seriesIndex, $serie->name);
            $link = new LinkNavigation($serie->getUri());
            $su = $link->hrefXhtml();
        }
        $cc = $book->getCustomColumnValues($config['cops_calibre_custom_column_list'], true);

        return ["id" => $book->id,
                      "hasCover" => $book->hasCover,
                      "preferedData" => $preferedData,
                      "rating" => $book->getRating(),
                      "publisherName" => $pn,
                      "publisherurl" => $pu,
                      "pubDate" => $book->getPubDate(),
                      "languagesName" => $book->getLanguages(),
                      "authorsName" => $book->getAuthorsName(),
                      "tagsName" => $book->getTagsName(),
                      "seriesName" => $sn,
                      "seriesIndex" => $book->seriesIndex,
                      "seriesCompleteName" => $scn,
                      "seriesurl" => $su,
                      "customcolumns_list" => $cc];
    }

    /**
     * @param Book $book
     * @return array
     */
    public static function getFullBookContentArray($book)
    {
        global $config;
        $out = self::getBookContentArray($book);
        $database = GetUrlParam(DB);

        $out ["coverurl"] = Data::getLink($book, "jpg", "image/jpeg", Link::OPDS_IMAGE_TYPE, "cover.jpg", null)->hrefXhtml();
        $out ["thumbnailurl"] = Data::getLink($book, "jpg", "image/jpeg", Link::OPDS_THUMBNAIL_TYPE, "cover.jpg", null, null, $config['cops_html_thumbnail_height'] * 2)->hrefXhtml();
        $out ["content"] = $book->getComment(false);
        $out ["datas"] = [];
        $dataKindle = $book->GetMostInterestingDataToSendToKindle();
        foreach ($book->getDatas() as $data) {
            $tab = ["id" => $data->id,
                "format" => $data->format,
                "url" => $data->getHtmlLink(),
                "viewUrl" => $data->getViewHtmlLink(),
                "mail" => 0,
                "readerUrl" => ""];
            if (!empty($config['cops_mail_configuration']) && !is_null($dataKindle) && $data->id == $dataKindle->id) {
                $tab ["mail"] = 1;
            }
            if ($data->format == "EPUB") {
                $tab ["readerUrl"] = "epubreader.php?data={$data->id}&db={$database}";
            }
            array_push($out ["datas"], $tab);
        }
        $out ["authors"] = [];
        foreach ($book->getAuthors() as $author) {
            $link = new LinkNavigation($author->getUri());
            array_push($out ["authors"], ["name" => $author->name, "url" => $link->hrefXhtml()]);
        }
        $out ["tags"] = [];
        foreach ($book->getTags() as $tag) {
            $link = new LinkNavigation($tag->getUri());
            array_push($out ["tags"], ["name" => $tag->name, "url" => $link->hrefXhtml()]);
        }

        $out ["identifiers"] = [];
        foreach ($book->getIdentifiers() as $ident) {
            array_push($out ["identifiers"], ["name" => $ident->formattedType, "url" => $ident->getUri()]);
        }

        $out ["customcolumns_preview"] = $book->getCustomColumnValues($config['cops_calibre_custom_column_preview'], true);

        return $out;
    }

    public static function getContentArray($entry)
    {
        if ($entry instanceof EntryBook) {
            $out = [ "title" => $entry->title];
            $out ["book"] = self::getBookContentArray($entry->book);
            return $out;
        }
        return [ "title" => $entry->title, "content" => $entry->content, "navlink" => $entry->getNavLink(), "number" => $entry->numberOfElement ];
    }

    public static function getContentArrayTypeahead($page)
    {
        $out = [];
        foreach ($page->entryArray as $entry) {
            if ($entry instanceof EntryBook) {
                array_push($out, ["class" => $entry->className, "title" => $entry->title, "navlink" => $entry->book->getDetailUrl()]);
            } else {
                if (empty($entry->className) xor Base::noDatabaseSelected()) {
                    array_push($out, ["class" => $entry->className, "title" => $entry->title, "navlink" => $entry->getNavLink()]);
                } else {
                    array_push($out, ["class" => $entry->className, "title" => $entry->content, "navlink" => $entry->getNavLink()]);
                }
            }
        }
        return $out;
    }

    public static function addCompleteArray($in)
    {
        global $config;
        $out = $in;

        $out ["c"] = ["version" => VERSION, "i18n" => [
                           "coverAlt" => localize("i18n.coversection"),
                           "authorsTitle" => localize("authors.title"),
                           "bookwordTitle" => localize("bookword.title"),
                           "tagsTitle" => localize("tags.title"),
                           "linksTitle" => localize("links.title"),
                           "seriesTitle" => localize("series.title"),
                           "customizeTitle" => localize("customize.title"),
                           "aboutTitle" => localize("about.title"),
                           "previousAlt" => localize("paging.previous.alternate"),
                           "nextAlt" => localize("paging.next.alternate"),
                           "searchAlt" => localize("search.alternate"),
                           "sortAlt" => localize("sort.alternate"),
                           "homeAlt" => localize("home.alternate"),
                           "cogAlt" => localize("cog.alternate"),
                           "permalinkAlt" => localize("permalink.alternate"),
                           "publisherName" => localize("publisher.name"),
                           "pubdateTitle" => localize("pubdate.title"),
                           "languagesTitle" => localize("language.title"),
                           "contentTitle" => localize("content.summary"),
                           "filterClearAll" => localize("filter.clearall"),
                           "sortorderAsc" => localize("search.sortorder.asc"),
                           "sortorderDesc" => localize("search.sortorder.desc"),
                           "customizeEmail" => localize("customize.email")],
                       "url" => [
                           "detailUrl" => "index.php?page=13&id={0}&db={1}",
                           "coverUrl" => "fetch.php?id={0}&db={1}",
                           "thumbnailUrl" => "fetch.php?height=" . $config['cops_html_thumbnail_height'] . "&id={0}&db={1}"],
                       "config" => [
                           "use_fancyapps" => $config ["cops_use_fancyapps"],
                           "max_item_per_page" => $config['cops_max_item_per_page'],
                           "kindleHack"        => "",
                           "server_side_rendering" => useServerSideRendering(),
                           "html_tag_filter" => $config['cops_html_tag_filter']]];
        if ($config['cops_thumbnail_handling'] == "1") {
            $out ["c"]["url"]["thumbnailUrl"] = $out ["c"]["url"]["coverUrl"];
        } elseif (!empty($config['cops_thumbnail_handling'])) {
            $out ["c"]["url"]["thumbnailUrl"] = $config['cops_thumbnail_handling'];
        }
        if (preg_match("/./", $_SERVER['HTTP_USER_AGENT'])) {
            $out ["c"]["config"]["kindleHack"] = 'style="text-decoration: none !important;"';
        }
        return $out;
    }

    public static function getJson($complete = false)
    {
        global $config;
        $page = getURLParam("page", Base::PAGE_INDEX);
        $query = getURLParam("query");
        $search = getURLParam("search");
        $qid = getURLParam("id");
        $n = getURLParam("n", "1");
        $database = GetUrlParam(DB);

        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();

        if ($search) {
            return self::getContentArrayTypeahead($currentPage);
        }

        $out = [ "title" => $currentPage->title];
        $entries = [];
        foreach ($currentPage->entryArray as $entry) {
            array_push($entries, self::getContentArray($entry));
        }
        if (!is_null($currentPage->book)) {
            $out ["book"] = self::getFullBookContentArray($currentPage->book);
        }
        $out ["databaseId"] = GetUrlParam(DB, "");
        $out ["databaseName"] = Base::getDbName();
        if ($out ["databaseId"] == "") {
            $out ["databaseName"] = "";
        }
        $out ["fullTitle"] = $out ["title"];
        if ($out ["databaseId"] != "" && $out ["databaseName"] != $out ["fullTitle"]) {
            $out ["fullTitle"] = $out ["databaseName"] . " > " . $out ["fullTitle"];
        }
        $out ["page"] = $page;
        $out ["multipleDatabase"] = Base::isMultipleDatabaseEnabled() ? 1 : 0;
        $out ["entries"] = $entries;
        $out ["isPaginated"] = 0;
        if ($currentPage->isPaginated()) {
            $prevLink = $currentPage->getPrevLink();
            $nextLink = $currentPage->getNextLink();
            $out ["isPaginated"] = 1;
            $out ["prevLink"] = "";
            if (!is_null($prevLink)) {
                $out ["prevLink"] = $prevLink->hrefXhtml();
            }
            $out ["nextLink"] = "";
            if (!is_null($nextLink)) {
                $out ["nextLink"] = $nextLink->hrefXhtml();
            }
            $out ["maxPage"] = $currentPage->getMaxPage();
            $out ["currentPage"] = $currentPage->n;
        }
        if (!is_null(getURLParam("complete")) || $complete) {
            $out = self::addCompleteArray($out);
        }

        $out ["containsBook"] = 0;
        if ($currentPage->containsBook()) {
            $out ["containsBook"] = 1;
        }

        $out["abouturl"] = "index.php" . addURLParameter("?page=" . Base::PAGE_ABOUT, DB, $database);

        if ($page == Base::PAGE_ABOUT) {
            $temp = preg_replace("/\<h1\>About COPS\<\/h1\>/", "<h1>About COPS " . VERSION . "</h1>", file_get_contents('about.html'));
            $out ["fullhtml"] = $temp;
        }

        $out ["homeurl"] = "index.php";
        if ($page != Base::PAGE_INDEX && !is_null($database)) {
            $out ["homeurl"] = $out ["homeurl"] .  "?" . addURLParameter("", DB, $database);
        }

        return $out;
    }
}
