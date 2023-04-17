#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

#Capture Options set by user
TITLE=$(bashio::config 'title')
TIMEZONE=$(bashio::config 'timezone')

COPS_CONFIG="/cops/config_local.php"

CONFIG_STR=$(
cat << END_HEREDOC
\$config['cops_title_default'] = '$TITLE';
\$config['default_timezone'] = '$TIMEZONE';
END_HEREDOC
)

# If _all_ the mail variables (except secure) have been set, then add the string
if [ -n "$(bashio::config 'mail_host')" ]; then
  MAIL_HOST=$(bashio::config 'mail_host')
  MAIL_USERNAME=$(bashio::config 'mail_username')
  MAIL_PASSWORD=$(bashio::config 'mail_password')
  MAIL_ADDRESS=$(bashio::config 'mail_address')
  CONFIG_STR+=$(
  cat << END_HEREDOC
  \$config['cops_mail_configuration'] = array( "smtp.host" => "$MAIL_HOST", "smtp.username" => "$MAIL_USERNAME", "smtp.password" => "$MAIL_PASSWORD", "address.from" => "$MAIL_ADDRESS", "smtp.secure" => "ssl");
END_HEREDOC
  )
fi

echo "$CONFIG_STR" >> "$COPS_CONFIG"

mkdir -p /media/books
cd /cops || return
ln -s /media/books library
rsync --daemon & php -S 0.0.0.0:8000
