# HA COPS Changelog

## [1.35-kepub] - 2025-04-05

- Add support for kepub files - epub files requested by a Kobo e-reader should now be delivered as a special kepub file. **I don't have a Kobo to test this on, so [please provide feedback](https://github.com/dunxd/HomeAssistantAddons/issues) especially if this doesn't work!**

## [1.35] - 2025-05-15

- Incorporate [COPS 3.6.5](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.6.5)
- Set default style to _kindle_ - this style works well with eReaders where changing style may be more difficult, so this is a safe choice. If you prefer a different style you are still able to change this and the preference will be stored as a cookie.
- Tweaks to README and DOCS to align with previous changes I forgot to document
- Added PHP session which is needed in COPS now

## [1.34] - 2025-04-21

- Add PHP Session to container to fix customize link

## [1.33] - 2025-04-21

- Incorporate [COPS 3.6.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.6.1)

## [1.32] - 2025-03-17

- Incorporate [COPS 3.5.7](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.5.7)

## [1.31] - 2025-01-11

- Incorporate [COPS 3.5.4](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.5.4)

## [1.30] - 2024-12-14

- Incorporate [COPS 3.4.6](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.4.6)
- Enable route URLs in PHP dev server

## [1.29] - 2024-11-24

- Incorporate [COPS 3.4.5](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.4.5)
- Use minified bootstrap icons css in Bootstrap5 template

## [1.28] - 2024-10-21

- Incorporate [COPS 3.3.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.3.1)

## [1.27.1] - 2024-09-22

- Incorporate [COPS 3.2.2](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.2.2)
- Fix to new locaation for config file

## [1.26.4] - 2024-09-15

- Portuguese translation for add-on settings - thanks @horus68!
- Fix bootstrap integrity hashes

## [1.26.3] - 2024-09-14

- Incorporate [COPS 3.1.3](https://github.com/mikespub-org/seblucas-cops/releases/tag/3.1.3)
- Add list of all series by author to author page in Bootstrap5 (bootstrap2 and twigged templates already include this with 3.1.3)

## [1.25] - 2024-08-25

- Incorporate [COPS 2.7.3](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.7.3)
- Set initial page to Recent Additions - you can still get back to the top level menu from the home link in the top left, but when you go to index.php it will now show the most recently added 50 books.

## [1.24] - 2024-06-23

- Allow specifying a different path within HA's media folder - this is useful if you mounted your Calibre Libary somewhere other than books, or have uploaded it to a different directory

## [1.23] - 2024-06-01

- Incorporate [COPS 2.7.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.7.2)

## [1.22] - 2024-05-13

- Config settings to improve _ebpubjs-reader_ page turns on mobile phones.

## [1.21.1] - 2024-05-12

- Switch back to _monocle_ as default in-browser reader as _epubjs-reader_ still doesn't support small screens well enough

## [1.21] - 2024-05-12

- Incorporate [COPS 2.6.1](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.6.1)
  - Inity _epubjs_ as default in-browser reader - switch back to _monocle_ in Configuration.
  - Support for books in TXT format.
  - Various backend changes

## [1.20] - 2024-03-12

- Revert epubreader to Monocle - issues with mobile browser view with Intity that need resolving.

## [1.19] - 2024-03-10

- Incorporate [COPS 2.5.0](https://github.com/mikespub-org/seblucas-cops/releases/tag/2.5.0)
  - Category notes added to Calibre 7.x are now visible
  - Switched from monocle to Intity browser based epub reader - **feedback appreciated**. This allows bookmarks and notes in the browser. These are stored local to the browser and persistant - however you will not see them in other browsers.
  - **Not** implemented
    - virtual library support - I'm not sure how to support this via rsync without making things complicated. I will investigate this if users request it.

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
