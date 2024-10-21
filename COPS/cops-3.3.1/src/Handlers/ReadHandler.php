<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\Cops\Output\Response;
use Exception;

/**
 * Handle epub reader with monocle
 * URL format: index.php/read?data={idData}&version={version}
 */
class ReadHandler extends BaseHandler
{
    public const HANDLER = "read";

    public static function getRoutes()
    {
        return [
            "/read/{db:\d+}/{data:\d+}/{title}" => [static::PARAM => static::HANDLER],
            "/read/{db:\d+}/{data:\d+}" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $idData = $request->getId('data');
        if (empty($idData)) {
            // this will call exit()
            Response::notFound($request);
        }
        $version = $request->get('version', Config::get('epub_reader', 'monocle'));
        $database = $request->database();

        $response = new Response('text/html;charset=utf-8');

        $reader = new EPubReader($request, $response);

        try {
            return $response->setContent($reader->getReader($idData, $version, $database));
        } catch (Exception $e) {
            error_log($e);
            // this will call exit()
            Response::sendError($request, $e->getMessage());
        }
    }
}
