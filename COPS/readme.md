# Dependancies

- Terminal & SSH Add On

# Config

- Create an authorised key
- Use rsync to copy (and update) Calibre Library to HA Media folder as follows:

```
rsync -av ~/Calibre\ Library/* rsync://{server_address}:8873/books
```

# Know issues

- ePub reader doesn't work with PHP 8
