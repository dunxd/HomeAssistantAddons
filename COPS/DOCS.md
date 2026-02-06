# ![HA-COPS logo](https://raw.githubusercontent.com/dunxd/HomeAssistantAddons/main/COPS/icon.png) HA COPS

## Configuration

Defaults should work, but you can change the display title of your library. If you have other add-ons that provide a web interface, you may want to change the ports used for http (8000/tcp) and rsync(873/tcp) to avoid conflicts.

## Getting your library onto your HA server

There are two methods for making your Calibre Library available to this addon:

- Copy Calibre library files to a local directory on the Home Assistant server
- Use a Network Share (requires Home Assistant >= 2023.6)

### Copy your Calibre library onto the Home Assistant server.

This is useful if you don't want to run another computer 24/7 or if storage space on your Home Assistant server is limited. Rsync is included to allow copying your Calibre library onto the HA server for use in this add-on. Rsync is configured to only allow syncing to the `/media/books` directory that is created by the add-on. You will need rsync installed on the computer where you run Calibre. You can then use a command similar to this to copy all the required files across:

```
rsync -av --exclude '*.original_*' ~/Calibre\ Library/* rsync://{server_address}:8873/books
```

You may need to change `~/Calibre\ Library` to where your Calibre library is stored - the above example works for a Mac running a standard install of Calibre. The rsync path always ends in `/books`, even if you changed the Library Folder in the add-on config.

You can use this command to sync any changes made in Calibre - new books added, changes to metadata etc. You can exclude files from being transferred using [the `--exclude` option for rsync](https://www.man7.org/linux/man-pages/man1/rsync.1.html#FILTER_RULES). In the example above I have excluded file types starting `.original_*` type files, as mostly these are duplicates of files converted in Calibre, and original_epub and original_azw are ignored by COPS anyway. Suggestions for improving this are welcome.

### Use a Network Share

As of Home Assistant 2023.6 it is possible to mount a network share onto your Home Assistant share. This is particularly useful if your Calibre Library is stored on Network Attached Storage.

To use a Network Share with HA COPS you need to first set it up in Home Assistant:

[![Open your Home Assistant instance and show storage information.](https://my.home-assistant.io/badges/storage.svg)](https://my.home-assistant.io/redirect/storage/)

Click on the Add Network Storage button and create a mount named `books` with Usage type of Media. This will mount the library to `/media/books` which is where the addon expects to find the necessary files.

[![Network storage dialog with example settings](https://raw.githubusercontent.com/dunxd/HomeAssistantAddons/main/COPS/Assets/NetworkStorageDialog.png).

Note that you do not need to escape spaces if the share name has them in it.

If you are using this method, you should probably disable the rsync server in the add-on configuration, as you don't need it.

## Accessing your library

You can access your library in your browser at http://_ha-ip-address-or-name_:8000. You can change the port if necessary. Ingress does not currently work.

You can also access an OPDS feed of your library in apps like [Librera reader (Android)](https://librera.mobi/) or [KOReader])https://koreader.rocks/) allowing you to easily browse your library and download books into the reader. The feed is available at http://_ha-ip-address-or-name_:8000/feed

## Syncing reading progress

Users of koreader can now sync their reading progress across multiple devices. To do this, first Activate Koreader sync server from the Add-on config page and restart.

Then configure koreader's Progress Sync feature to specify http://_ha-ip-address-or-name_:8000/sync, and use the Register/Login feature to set up a sync account. Then on your other devices, use the same server location, username and password to sync progress. You need to have copies of the book/document on both the device you started reading on and on the device you want to continue reading on. Each person that uses the library can sync their own devices by using a different username and password

## Emailing ePubs

As of 1.9 you can send ePub books by email from the server. To do this you need to enable _Show unused optional configuration options_ on the Options page, then set the mail server options appropriately. For example, to send using a Gmail account you will need to:

- [create an app password](https://support.google.com/accounts/answer/185833?hl=en) specifically for this function
- set the _Mail Server Host_ to `smtp.gmail.com`
- Set your google account login in _Mail Server Username_
- Put the app password you created in the _Mail Server Password_ field
- Set _Secure SMTP_ to `ssl`
- Set the sending email address to your gmail address

Once this is done, your users can add their email address (where they will recieve the ePubs) in the field from the Settings page within COPS. This can be a simple way to download books onto a Kindle.

## User side configuration

It is possible for each user to adjust some settings in the web interface. Currently this is only available in the Default template - click on the link at the top right to change to that template, then click on the spanner icon. If you want to email ePubs to your Kindle from within COPS you set your Kindle's email address in the _Set your email (to allow book emailing)_ box.

### In-browser reader

You can select from the original _monocle_ HTML reader for in-browser reading, and a more modern _epubjs_ reader, which allows bookmarking and notes. The bookmarks and notes are _only_ stored in the local web browser - if you open the book in a different web browser or on a different device the bookmarks and notes will not be the same.

## Accessing the library from a Kindle

The library can be accessed using the Web Browser in an Amazon Kindle, and MOBI and AZW format files can be downloaded. Unfortunately the Kindle web browser does not work well with the nicer Bootstrap2 template, and it is also not easy to switch back to Default. As a result, I have made the Default template the initial template with the kindle style. If you find yourself stuck in the Bootstrap2 template, clear your cookies and you should get the Default template again. If you reset your cookies, you need to add your Kindle email address back in Settings.

### Receiving emailed ePubs on your Kindle

If you have an Amazon Kindle or use one of the Kindle apps, it will have its own email address. ePub files received at this address will be converted and added to your Personal Docs library, and can then be downloaded to your Kindle. You need to [add the email address that will be sending files to your Kindle account in your Amazon settings](https://www.amazon.com/gp/help/customer/display.html%3FnodeId%3DGX9XLEVV8G4DB28H).

## Security

This Add-on is intended to be run in a home network, _not_ on the public internet. The PHP built in web server is not designed for scale and does not have any security features. rsync is also not intended to be made available over the public internet. Sharing your books publically is not advised and possibly illegal. Pages are served over an unencrypted http connection.

I use the Cloudflared Add-on to make my library available outside my home - this allows simple passcode logins using Cloudflare Zero Trust.

# Config

- You are able to change the display name of your library.
- You can disable rsync if you do not need it (i.e. you are using a network share to access your Calibre Library).
- You can set mail server settings if you want to send ebooks from this addon to an email address - e.g. a @kindle.com address.
- You can switch browser based reader between monocle (original) and epubjs (more featureful but may not work well on all browsers).
