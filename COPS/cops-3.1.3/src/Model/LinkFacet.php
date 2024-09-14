<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

/**
 * From https://specs.opds.io/opds-1.2#4-facets
 * An Acquisition Feed MAY offer multiple links to reorder the Publications listed
 * in the feed or limit them to a subset. This specification defines one new relation
 * to identify such links as Facets:
 *   http://opds-spec.org/facet: An Acquisition Feed with a subset or an alternate order
 *   of the Publications listed.
 *
 * Links using this relation MUST only appear in Acquisition Feeds.
 */
class LinkFacet extends LinkFeed
{
    public const LINK_RELATION = "http://opds-spec.org/facet";

    /** @var ?string */
    public $facetGroup;
    public bool $activeFacet;
    /** @var ?int */
    public $threadCount;

    /**
     * Summary of __construct
     * @param string $href ?queryString relative to current endpoint
     * @param ?string $title title in the OPDS catalog
     * @param ?string $facetGroup facetGroup this facet belongs to
     * @param bool $activeFacet is the facet currently active
     * @param ?int $threadCount number of items expected
     * @param ?int $database current database in multiple database setup
     */
    public function __construct($href, $title = null, $facetGroup = null, $activeFacet = false, $threadCount = null, $database = null)
    {
        parent::__construct($href, static::LINK_RELATION, $title, $database);
        $this->facetGroup = $facetGroup;
        $this->activeFacet = $activeFacet;
        $this->threadCount = $threadCount;
    }
}