# HA COPS

COPS is a lightweight system for providing access to your Calibre Library via a web browser. It is built in PHP. It uses less resources than the full Calibre Web Server - ideal for running on a small server such as Home Assistant. That is what this Add-on enables.

The motivation for this was realising that I could easily make my music and video collection available to my family and friends when visiting my home, but my ePub collection was not available. I have happy childhood memories of discovering books in my parents book collection, but since I mainly buy eBooks now that experience isn't so easy for my kids.

This Add-on runs COPS using PHP's built in webserver, as well as providing rsync to get books onto your Home Assistant server - a books folder will be created in the `/media` directory of your home assistant server.

## Installation

1. Install this repository

[![Open your Home Assistant instance and show the add add-on repository dialog with a specific repository URL pre-filled.](https://my.home-assistant.io/badges/supervisor_add_addon_repository.svg)](https://my.home-assistant.io/redirect/supervisor_add_addon_repository/?repository_url=https%3A%2F%2Fgithub.com%2Fdunxd%2FHomeAssistantAddons)

2. Install the add-on

[![Open your Home Assistant instance and show the dashboard of a Supervisor add-on.](https://my.home-assistant.io/badges/supervisor_addon.svg)](https://my.home-assistant.io/redirect/supervisor_addon/?addon=670b30ea_ha-cops&repository_url=https%3A%2F%2Fgithub.com%2Fdunxd%2FHomeAssistantAddons)

## Configuration
Defaults should work, but you can change the display title of your library. If you have other add-ons that provide a web interface, you may want to change the ports used for http (8000/tcp) and rsync(873/tcp) to avoid conflicts.

## Getting your library onto your HA server

rsync is included to allow copying your Calibre library onto the HA server for use in this add-on. Rsync is configured to only allow syncing to the `/media/books` directory that is created by the add-on. You will need rsync installed on the computer you normally run Calibre on. You can then use a command similar to this to copy all the required files across:

```
rsync -av --exclude '*.original_epub' ~/Calibre\ Library/* rsync://{server_address}:8873/books
```

Obviously you may need to change `~/Calibre\ Library` to where your Calibre library is stored. You can use this command to sync any changes made in Calibre - new books added, changes to metadata etc. You can exclude files from being transferred using [the `--exclude` option for rsync](https://www.man7.org/linux/man-pages/man1/rsync.1.html#FILTER_RULES). In the example above I have excluded original_epub type files, as mostly these are duplicates of epub files.

## Accessing your library

You can access your library in your browser at http://_ha-ip-address-or-name_:8000. You can change the port if necessary. Ingress does not currently work.

You can also access an OPDS feed of your library in apps like [Librera reader (Android)](https://play.google.com/store/apps/details?id=com.foobnix.pdf.reader) allowing you to easily download books into the reader. The feed is available at http://_ha-ip-address-or-name_:8000/feed.php

## Security

This Add-on is intended to be run in a home network, _not_ on the public internet. The PHP built in web server is not designed for scale and does not have security features. rsync is also not intended to be made available over the public internet. Sharing your books publically is also not advised. Pages are served over an unencrypted http connection.

I use the Cloudflared Add-on to make my library available outside my home - this allows simple passcode logins using Cloudflare Zero Trust.

# Config

- Currently you are able to change the display name of your library.

# Known issues

- Reading books in the browser only works for EPubs that have a NCX table of contents file. This is required for EPub v2, but not for EPub v3 books. Some EPub v3 books may include the NCX file in which case they work. Calibre does not create a NCX when converting to EPub v3, but does for EPub v2. A NCX file can be created for EPub v3 in Calibre by invoking the ToC Editor - this creates a NCX file which is saved when clicking Ok.
- COPS has a built in system for emailing books - e.g. as Docs to your Kindle device. This does not yet work in the add-on as it was written for an older version of PHP, but may be added later.

# Acknowledgements
This is based on [COPS](https://github.com/seblucas/cops) written by Sebastian Lucas, but [updated to use PHP8 by Matt's Pub](https://github.com/mikespub-org/seblucas-cops).
