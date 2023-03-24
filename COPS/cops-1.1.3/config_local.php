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
     * Catalog's title
     */
    $config['cops_title_default'] = 'Drury Family eBooks';

    /*
     * use URL rewriting for downloading of ebook in HTML catalog
     * See README for more information
     *  1 : enable
     *  0 : disable
     */
    $config['cops_use_url_rewriting'] = "0";

    $config['default_timezone'] = 'Europe/London';
    $config['cops_prefered_format'] = array('EPUB', 'MOBI', 'PDF', 'AZW3', 'AZW', 'CBR', 'CBZ');
    $config['cops_mail_configuration'] = array( "smtp.host"     => "smtp.gmail.com",
                                                "smtp.username" => "d.drury@gmail.com",
                                                "smtp.password" => "vuejjtvgudtbnscp",
                                                "smtp.secure"   => "ssl",
                                                "address.from"  => "d.drury@gmail.com"
                                                );
    $config ['cops_ignored_categories'] = array('publisher', 'rating', 'language');
