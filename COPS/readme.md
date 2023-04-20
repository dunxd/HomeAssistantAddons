# HA COPS

COPS is a lightweight system for providing access to your Calibre Library via a web browser. It is built in PHP. It uses less resources than the full Calibre Web Server - ideal for running on a small server such as Home Assistant. That is what this Add-on enables.

The motivation for this was realising that I could easily make my music and video collection available to my family and friends when visiting my home, but my ePub collection was not available. I have happy childhood memories of discovering books in my parents book collection, but since I mainly buy eBooks now that experience isn't so easy for my kids.

This Add-on runs COPS using PHP's built in webserver, as well as providing rsync to get books onto your Home Assistant server - a books folder will be created in the `/media` directory of your home assistant server.

## Getting your library onto your HA server

rsync is included to allow copying your Calibre library onto the HA server for use in this add-on. Rsync is configured to only allow syncing to the `/media/books` directory that is created by the add-on. You will need rsync installed on the computer you normally run Calibre on. You can then use a command similar to this to copy all the required files across:

```
rsync -av ~/Calibre\ Library/* rsync://{server_address}:8873/books
```

Obviously you may need to change `~/Calibre\ Library` to where your Calibre library is stored. You can use this command to sync any changes made in Calibre - new books added, changes to metadata etc.

## Security

This Add-on is intended to be run in a home network, _not_ on the public internet. The PHP built in web server is not designed for scale and does not have security features. rsync is also not intended to be made available over the public internet. Sharing your books publically is also not advised.

# Config

- Currently you are able to change the display name of your library.

# Known issues

- Reading books in the browser only works for EPubs that have a NCX table of contents file. This is required for EPub v2, but not for EPub v3 books. Some EPub v3 books may include the NCX file in which case they work. Calibre does not create a NCX when converting to EPub v3, but does for EPub v2. A NCX file can be created for EPub v3 in Calibre by invoking the ToC Editor - this creates a NCX file which is saved when clicking Ok.
- COPS has a built in system for emailing books - e.g. as Docs to your Kindle device. This does not yet work in the add-on as it was written for an older version of PHP, but may be added later.
