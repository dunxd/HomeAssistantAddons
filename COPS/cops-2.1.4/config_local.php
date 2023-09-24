<?php

if (!isset($config)) {
    $config = [];
}

/*
 ***************************************************
 * Please read config_default.php for all possible
 * configuration items
 * For changes in config_default.php see CHANGELOG.md
 ***************************************************
 */

/*
 * The directory containing calibre's metadata.db file, with sub-directories
 * containing all the formats.
 * BEWARE : it has to end with a /
 */
$config['calibre_directory'] = '/media/books/';

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
$config['cops_ignored_formats'] = ['ORIGINAL_EPUB', 'ORIGINAL_AZW3'];

/*
 * Specify the formats preferred to make available for download
 */
$config['cops_prefered_format'] = array('EPUB', 'MOBI', 'PDF', 'AZW3', 'AZW', 'CBR', 'CBZ');

/*
 * Specify categories not to display as options in top level of COPS library
 */
$config['cops_ignored_categories'] = array('publisher', 'rating', 'language');

/*
 * Use "default" template as default - this works best with eReaders.  Customize page allows change to template used
 * by each user with setting stored in a cookie
 */
$config['cops_template'] = 'default';

/*
 * Specify which formats to show download all buttons on pages listing entire series
 */
$config['cops_download_series'] = ['EPUB', 'MOBI'];

/*
 * Specify which formats to show download all buttons on pages listing all books by an author
 */
$config['cops_download_author'] = ['EPUB', 'MOBI'];