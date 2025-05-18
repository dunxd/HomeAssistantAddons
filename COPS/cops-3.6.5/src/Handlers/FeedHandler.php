<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;

/**
 * Handle OPDS 1.2 feed
 * URL format: index.php/feed?page={page}&query={query}&...
 */
class FeedHandler extends BaseHandler
{
    public const HANDLER = "feed";
    public const PREFIX = "/feed";
    public const PARAMLIST = ["page", "id", "path"];
    public const SEARCH = "feed-search";

    public static function getRoutes()
    {
        return [
            //"feed-page-id" => ["/feed/{page:\w+}/{id}"],
            "feed-search" => ["/feed/search", ["page" => "search"]],
            "feed-page" => ["/feed/{page:\w+}"],
            "feed-path" => ["/feed/{path:.+}"],
            "feed" => ["/feed"],
        ];
    }

    public function handle($request)
    {
        // deal with /handler/{path:.*}
        $path = $request->get('path');
        if (!empty($path)) {
            // match path against default page handler
            $params = Route::match('/' . $path);
            if (!isset($params)) {
                return Response::sendError($request, 'Unknown path for feed: ' . $path);
            }
            // set actual path params in request
            $request->setParams($params);
        }
        $page = $request->get('page', PageId::INDEX);
        $query = $request->get('query');
        if ($query) {
            $page = PageId::OPENSEARCH_QUERY;
        }
        // @todo handle special case of OPDS not expecting filter while HTML does better
        $request->set('filter', null);

        if (Config::get('fetch_protect') == '1') {
            $session = $this->getContext()->getSession();
            $session->start();
            $connected = $session->get('connected');
            if (!isset($connected)) {
                $session->set('connected', 0);
            }
        }

        $response = new Response(Response::MIME_TYPE_XML);

        $opdsRenderer = new OpdsRenderer($request, $response);

        switch ($page) {
            case PageId::OPENSEARCH :
            case PageId::SEARCH :
                return $response->setContent($opdsRenderer->getOpenSearch($request));
            default:
                $currentPage = PageId::getPage($page, $request);
                return $response->setContent($opdsRenderer->render($currentPage, $request));
        }
    }
}
