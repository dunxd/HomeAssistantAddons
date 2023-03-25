<?php
    if (!isset($config))
        $config = array();

    /*
     ***************************************************
     * Please read config_default.php for all possible
     * configuration items
     ***************************************************
     */

    /*
     * The directory containing calibre's metadata.db file, with sub-directories
     * containing all the formats.
     * BEWARE : it has to end with a /
     */
    $config['calibre_directory'] = './library/';

    /*
     * use URL rewriting for downloading of ebook in HTML catalog
     * See README for more information
     *  1 : enable
     *  0 : disable
     */
    $config['cops_use_url_rewriting'] = "0";

    $config['cops_prefered_format'] = array('EPUB', 'MOBI', 'PDF', 'AZW3', 'AZW', 'CBR', 'CBZ');
    $config['cops_ignored_categories'] = array('publisher', 'rating', 'language');