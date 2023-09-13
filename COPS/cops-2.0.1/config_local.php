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

    /*
     * Specify the ignored formats that will never display in COPS
     */
    $config['cops_ignored_formats'] = array('ORIGINAL_EPUB', 'ORIGINAL_AZW3');

    /*
     * Specify the formats preferred to make available for download
     */
    $config['cops_prefered_format'] = array('EPUB', 'MOBI', 'PDF', 'AZW3', 'AZW', 'CBR', 'CBZ');

    /*
     * Specify categories not to display as options in top level of COPS library
     */
    $config['cops_ignored_categories'] = array('publisher', 'rating', 'language');

    /*
     * Use "default" template as default - this works best with eReaders.  Config page allows change to template used
     */
    $config['cops_template'] = 'bootstrap2';

    /* debugging server side */
    //$config['cops_server_side_render'] = ".";
