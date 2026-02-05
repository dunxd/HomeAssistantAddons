#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

#Capture Options set by user
TITLE=$(bashio::config 'title')
LIBRARY_FOLDER="/media/$(bashio::config 'library_folder')"

# Specify the location of COPS config file so it can be added to by this script
COPS_CONFIG="/cops/config/local.php"
# Create lines for the config file here
CONFIG_STR=$(
cat << END_HEREDOC_TITLE
\$config['cops_title_default'] = '$TITLE';
END_HEREDOC_TITLE
)

# Specify which eReader to use
CONFIG_STR+=$(
    cat << END_HEREDOC_READER
    \$config['cops_epub_reader'] = "$(bashio::config 'reader')";
END_HEREDOC_READER
)

# Set up mail server details to allow emailing of books
if [ "$(bashio::config 'smtp_host')" != "null" ]
then
CONFIG_STR+=$(
cat << END_HEREDOC_SMTP_HOST
\$config['cops_mail_configuration'] = array( "smtp.host" => "$(bashio::config 'smtp_host')",
                                             "smtp.username" => "$(bashio::config 'smtp_username')",
                                             "smtp.password" => "$(bashio::config 'smtp_password')",
                                             "address.from" => "$(bashio::config 'address_from')"
                                             );
END_HEREDOC_SMTP_HOST
)
    # Enable ssl if smtp_secure set to true
    if [ "$(bashio::config 'smtp_secure')" != "null" ]
    then
    CONFIG_STR+=$(
        cat << END_HEREDOC_SMTP_SECURE
        \$config['cops_mail_configuration']['smtp.secure'] = "$(bashio::config 'smtp_secure')";
END_HEREDOC_SMTP_SECURE
    )
        fi

    # If port set, add that option
    if [ "$(bashio::config 'smtp_port')" != "null" ]
    then
    CONFIG_STR+=$(
        cat << END_HEREDOC_SMTP_PORT
        \$config['cops_mail_configuration']['smtp.port'] = "$(bashio::config 'smtp_port')";
END_HEREDOC_SMTP_PORT
    )
    fi
fi

# If custom columns are set, add that option
if [ "$(bashio::config 'custom_cols')" != "null" ]
then
    CONFIG_STR+=$(
        cat << END_HEREDOC_CUSTOM_COLUMNS
        \$config['cops_calibre_custom_column'] = [$(bashio::config 'custom_cols')];
        \$config['cops_calibre_custom_column_list'] = [$(bashio::config 'custom_cols')];
        \$config['cops_calibre_custom_column_preview'] = [$(bashio::config 'custom_cols')];
END_HEREDOC_CUSTOM_COLUMNS
    )
fi

# Write the config lines to the end of the config file
echo "$CONFIG_STR" >> "$COPS_CONFIG"

# Create a books directory in /media on the host if it doesn't already exist
mkdir -p "$LIBRARY_FOLDER"
cd /cops || return
# Create a link to the configured directory called library - this works with
# rsync as well as for locating the Calibre Library
ln -s "$LIBRARY_FOLDER" library

# Start rsync if enabled
if [ "$(bashio::config 'rsync')" = "true" ]; then
    bashio::log.green 'Starting rsync daemon...'
    rsync --daemon
fi

# --- Korrosync Sync Server Section ---
if bashio::config.has_value 'koreader_sync'; then
    if [ "$(bashio::config 'koreader_sync')" = "true" ]; then
        bashio::log.green "Starting Korrosync (KOReader Sync Server)..."

        # Ensure the data directory exists in the persistent /data partition
        mkdir -p /data/korrosync

        # Configure Korrosync via Environment Variables
        export KORROSYNC_DB_PATH="/data/korrosync/sync.redb"
        export KORROSYNC_SERVER_ADDRESS="0.0.0.0:8001"

        # Start in background with '&' so the script continues to Nginx
        # We redirect output to stdout/stderr so it shows in the HA logs
        /usr/bin/korrosync > /dev/stdout 2> /dev/stderr &
    fi
fi
# --- End Korrosync Section ---

# Start PHP-FPM (always required for the web UI)
bashio::log.green "Starting PHP-FPM..."
php-fpm -D -R

# Start NGINX in the foreground
bashio::log.green 'Starting NGINX web server...'
exec nginx -g "daemon off;"