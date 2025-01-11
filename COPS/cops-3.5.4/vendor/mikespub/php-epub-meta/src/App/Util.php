<?php
/**
 * PHP EPub Meta utility functions for App interface
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 * @author Simon Schrape <simon@epubli.com> © 2015
 * @author mikespub
 */

namespace SebLucas\EPubMeta\App;

class Util
{
    /**
     * Summary of to_file
     * @param string $input
     * @return string
     */
    public static function to_file($input)
    {
        $input = str_replace(' ', '_', $input);
        $input = str_replace('__', '_', $input);
        $input = str_replace(',_', ',', $input);
        $input = str_replace('_,', ',', $input);
        $input = str_replace('-_', '-', $input);
        $input = str_replace('_-', '-', $input);
        $input = str_replace(',', '__', $input);
        return $input;
    }

    /**
     * Summary of book_output
     * @param string $input
     * @return string
     */
    public static function book_output($input)
    {
        $input = basename($input);
        $input = str_replace('__', ',', $input);
        $input = str_replace('_', ' ', $input);
        $input = str_replace(',', ', ', $input);
        $input = str_replace('-', ' - ', $input);
        if (str_contains($input, '-')) {
            [$author, $title] = explode('-', $input, 2);
        } else {
            $title = $input;
            $author = '';
        }
        $author = trim($author);
        $title  = trim($title);

        if (!$title) {
            $title  = $author;
            $author = '';
        }

        return '<span class="title">' . htmlspecialchars($title) . '</span>' .
            '<span class="author">' . htmlspecialchars($author) . '</author>';
    }
}
