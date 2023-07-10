#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

#Capture Options set by user
TITLE=$(bashio::config 'title')

COPS_CONFIG="/cops/config_local.php"

# Create lines for the config file here
CONFIG_STR=$(
cat << END_HEREDOC
\$config['cops_title_default'] = '$TITLE';
END_HEREDOC
)

# Write the config lines to the end of the config file
echo "$CONFIG_STR" >> "$COPS_CONFIG"

# Create a books directory in /media on the host if it doesn't already exist
mkdir -p /media/books
cd /cops || return
# Create a link to the books directory called library
ln -s /media/books library

# Start rsync and php daemons depending on rsync setting
if [ "$(bashio::config 'rsync')" = "true" ]
then

    bashio::log.green 'starting rsync and php servers'
    rsync --daemon & php -S 0.0.0.0:8000
else
    bashio::log.yellow 'starting php server only'
    php -S 0.0.0.0:8000
fi