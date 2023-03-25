#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

#Capture Options set by user
TITLE=$(bashio::config 'title')
TIMEZONE=$(bashio::config 'timezone')
MAIL_HOST=$(bashio::config 'mail_host')
MAIL_FROM_ADDRESS=$(bashio::config 'mail_from_address')
MAIL_USERNAME=$(bashio::config 'mail_username')
MAIL_PASSWORD=$(bashio::config 'mail_password')
MAIL_SECURE=$(bashio::config 'mail_secure')


COPS_CONFIG="/cops/config_local.php"

CONFIG_STR=$(
cat << END_HEREDOC
\$config['cops_title_default'] = '$TITLE';
\$config['default_timezone'] = '$TIMEZONE';
END_HEREDOC
)

if [[ -n $MAIL_HOST ]]
then
CONFIG_STR+=$(
cat << END_HEREDOC2
\$config['cops_mail_configuration'] = array( 'smtp.host'     => '$MAIL_HOST',
                                             'smtp.username' => '$MAIL_USERNAME',
                                             'smtp.password' => '$MAIL_PASSWORD',
                                             'address.from'  => '$MAIL_FROM_ADDRESS'
                                            );
END_HEREDOC2
)
fi

if $MAIL_SECURE
then
CONFIG_STR+=$(
cat << END_HEREDOC3
\$config['cops_mail_configuration'] += ['smtp.secure' => 'ssl'];
END_HEREDOC3
)
fi

echo "$CONFIG_STR" >> "$COPS_CONFIG"
bashio::log.info "$(cat /cops/config_local.php)"


mkdir -p /media/books
cd /cops || return
ln -s /media/books library
rsync --daemon & php -S 0.0.0.0:8000
