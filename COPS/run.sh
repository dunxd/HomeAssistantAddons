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

# Set up mail server details to allow emailing of books
if [ "$(bashio::config 'smtp_host')" != "null" ]
then
CONFIG_STR+=$(
cat << END_HEREDOC2
\$config['cops_mail_configuration'] = array( "smtp.host" => "$(bashio::config 'smtp_host')",
                                             "smtp.username" => "$(bashio::config 'smtp_username')",
                                             "smtp.password" => "$(bashio::config 'smtp_password')",
                                             "address.from" => "$(bashio::config 'address_from')"
                                             );
END_HEREDOC2
)
    # Enable ssl if smtp_secure set to true
    if [ "$(bashio::config 'smtp_secure')" != "null" ]
    then
    CONFIG_STR+=$(
        cat << END_HEREDOC3
        \$config['cops_mail_configuration']['smtp.secure'] = "$(bashio::config 'smtp_secure')";
END_HEREDOC3
    )
        fi

    # If port set, add that option
    if [ "$(bashio::config 'smtp_port')" != "null" ]
    then
    CONFIG_STR+=$(
        cat << END_HEREDOC4
        \$config['cops_mail_configuration']['smtp.port'] = "$(bashio::config 'smtp_port')";
END_HEREDOC4
    )
    fi

fi

# Write the config lines to the end of the config file
bashio::log.yellow $CONFIG_STR
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