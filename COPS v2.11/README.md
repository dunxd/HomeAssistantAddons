# ![HA-COPS logo](https://raw.githubusercontent.com/dunxd/HomeAssistantAddons/main/COPS/icon.png) HA COPS

COPS is a lightweight system for providing access to your Calibre Library via a web browser. It is built using PHP. It uses less resources than the full Calibre Web Server - ideal for running on a small server such as Home Assistant. This add-on makes COPS available as a web application running on your Home Assistant server.

The motivation for this was realising that I could easily make my music and video collection available to my family and friends when visiting my home, but my ebook collection was not available. I have happy childhood memories of discovering books on my parents book shelves, but since I mainly buy eBooks now that experience isn't so easy for my kids.

This Add-on runs COPS using PHP's built in webserver, as well as providing rsync to get books onto your Home Assistant server - a books folder will be created in the `/media` directory of your home assistant server.

## Installation

1. Install this repository

[![Open your Home Assistant instance and show the add add-on repository dialog with a specific repository URL pre-filled.](https://my.home-assistant.io/badges/supervisor_add_addon_repository.svg)](https://my.home-assistant.io/redirect/supervisor_add_addon_repository/?repository_url=https%3A%2F%2Fgithub.com%2Fdunxd%2FHomeAssistantAddons)

2. Install the add-on

[![Open your Home Assistant instance and show the dashboard of a Supervisor add-on.](https://my.home-assistant.io/badges/supervisor_addon.svg)](https://my.home-assistant.io/redirect/supervisor_addon/?addon=670b30ea_ha-cops&repository_url=https%3A%2F%2Fgithub.com%2Fdunxd%2FHomeAssistantAddons)

Once installed see [DOCS.md](DOCS.md) for further details of how to configure.

# Known issues

- Search does not work on Kindle in bootstrap template. default, bootstrap2 and bootstrap5 have been fixed so it works. default template works best with Kindle eInk experimental browser.
- epubjs browser based epub reader has some great features but may not work great on small screens such as smart phones. Currently monocle is the default reader, but you can switch to epubjs in the config if you want the advanced features. [Feedback via Github issues](https://github.com/dunxd/HomeAssistantAddons/issues) on this is very appreciated.

# Acknowledgements

This is based on [COPS](https://github.com/seblucas/cops) written by Sebastian Lucas, but [updated to use PHP8 by mikespub-org](https://github.com/mikespub-org/seblucas-cops). Thanks [horus68](https://github.com/horus68) for the Portuguese translation for the add-on configuration options!
