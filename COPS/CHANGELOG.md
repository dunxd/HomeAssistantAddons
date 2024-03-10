# HA COPS Changelog

## [1.19] - 2024-03-10

- Incorporate [COPS 2.5.0](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.5.0)
  - Category notes added to Calibre 7.x are now visible
    ToDo: Changes to configuration options to support virtual libraries and new epubreader

## [1.18] - 2024-02-26

- Incorporate [COPS 2.4.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.4.1)

## [1.17] - 2024-02-20

- Incorporate [COPS 2.3.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.3.1)

## [1.16] - 2023-11-30

- Incorporate COPS 2.2.1 which includes the following improvements
  - Support display settings for custom columns
  - Add Japanese translations
  - Use server side rendering for Kobo
  - Add bootstrap2 Kindle theme
  - General improvements when viewing COPS through Kindle Experimental Web Browser
  - Updated Bootstrap5 template to use Bootstrap 5.3.2

_Note that some PiHole filterlists started blocking `cdn.jsdelivr.net` which is used by the bootstrap5 template to get the Bootstrap files used only in that template. If this template looks odd, you may need to whitelist `cdn.jsdelivr.net`._

## [1.15] - 2023-09-25

- Incorporate COPS 2.1.5 - small bug fixes
- Increase size of controls on Kindle browser
- Fix bug in Kindle browser preventing change of theme

## [1.14] - 2023-09-24

- Incorporate COPS 2.1.4
- New translations of UI
- Improvements to bootstrap5 template - minor design improvements, sorting, download all buttons
- Fix search form in all bootstrap templates
- Many backend improvements

## [1.13] - 2023-09-12

- Incorporate COPS 2.0.1
- Fix bootstrap5 template so it works with server side rendering - note that it is not well suited for Kindle eInk readers
- Currently the default template (named "default") is best suited to eInk readers. The customise page can be used to select a different template. If you selected a different template with a previous version, this setting is preserved.

## [1.12] - 2023-09-07

- Incorporate COPS 1.4.5 with fixes for OPDS feeds on older reader software

## [1.11] - 2023-08-31

- Incorporate COPS 1.4.3 which adds improvements to templates, and sorting to OPDS
- New Bootstrap5 template - note this is known not to work well in Kindle Paperwhite Experimental Browser. Feedback about other browsers more than welcome!

## [1.10.2] - 2023-07-20

- Minor change to how PHP is installed in addon to allow upgrading inline with Alpine default PHP version

## [1.10.1] - 2023-07-20

- Fix documentation so that images appear in Home Assistant interface

## [1.10] - 2023-07-19

- Incorporate COPS 1.3.6

## [1.9.4] - 2023-07-13

- Bug fix so address to send books by mail to is displayed on customisation page
- Limit sending books to a single email address
- Check that email address is valid before sending

## [1.9.3] - 2023-07-12

- Prefer EPUB but allow sending MOBI, AZW3 or PDF in absence of EPUB

## [1.9] - 2023-07-10

- Enabled feature to send ePub, PDF or AZW3 files by email - allows transfer to Kindle by sending to Kindle's email address.

## [1.8.1] - 2023-06-23

- Automatically forward Librera to OPDS feed.

## [1.8] - 2023-06-21

- As of Home Assistant 2023.6 works with Calibre Library access over network share.
- Added option to enable/disable rsync, which is not necessary if using a network share.
- Improved documentation and config dialog.

## [1.7.5] - 2023-06-15

- Changed default template to default as Kindle compatibility issue with Bootstrap2
- Minor changes to javscript to try and get Bootstrap2 template working with Kindle Paperwhite
- Added PHP Info back for troubleshooting

## [1.7] - 2023-06-15

- Updated to COPS 1.3.4 which allows ePub v3 without NCX TOC

## [1.6.2] - 2023-04-28

- Added webui and descriptions for port config options

## [1.6.1] - 2023-04-27

- Set language for ePubreader to undetermined

## [1.6] - 2023-04-21

- Initial public release
