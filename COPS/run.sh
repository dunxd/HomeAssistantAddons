#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

#Capture Options set by user
TITLE=$(bashio::config 'title')

COPS_CONFIG="/cops/config_local.php"

CONFIG_STR=$(
cat << END_HEREDOC
\$config['cops_title_default'] = '$TITLE';
END_HEREDOC
)

echo "$CONFIG_STR" >> "$COPS_CONFIG"

mkdir -p /media/books
cd /cops || return
ln -s /media/books library
rsync --daemon & php -S 0.0.0.0:8000
