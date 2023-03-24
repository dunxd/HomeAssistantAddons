#!/usr/bin/with-contenv bashio
cd /cops
ln -s /media/books library
rsync --daemon & php -S 0.0.0.0:8000
