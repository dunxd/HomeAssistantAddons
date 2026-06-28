<?php

namespace SebLucas\EPubMeta\Contents;

use ArrayIterator;

/**
 * A list of EPUB TOC navigation points.
 *
 * @author Simon Schrape <simon@epubli.com>
 * @author mikespub
 * @extends ArrayIterator<int, NavPoint>
 */
class NavPointList extends ArrayIterator
{
    public function __construct() {}

    /**
     * @return NavPoint
     */
    public function first()
    {
        $this->rewind();
        return $this->current();
    }

    /**
     * @return NavPoint
     */
    public function last()
    {
        $this->seek($this->count() - 1);
        return $this->current();
    }

    /**
     * @param string $file
     *
     * @return array|NavPoint[]
     */
    public function findNavPointsForFile($file)
    {
        $matches = [];
        foreach ($this as $navPoint) {
            if ($navPoint->getContentSourceFile() == $file) {
                $matches[] = $navPoint;
            }
            $childMatches = $navPoint->getChildren()->findNavPointsForFile($file);
            if (count($childMatches)) {
                $matches = array_merge($matches, $childMatches);
            }
        }
        return $matches;
    }
}
