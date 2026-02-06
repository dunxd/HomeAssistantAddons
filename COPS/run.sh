#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

# Function to safely prepare configuration values for insertion into the PHP config file
prepare_for_php() {
    # 1. Check if the key exists and has a value
    if ! bashio::config.has_value "$1"; then
        echo -n ""
        return
    fi

    local val
    val=$(bashio::config "$1")

    # 2. Strip Null characters
    val=$(echo -n "$val" | tr -d '\000')

    # 3. Escape Backslashes
    val="${val//\\/\\\\}"

    # 4. Escape Single Quotes
    val="${val//\'/\\\'}"

    echo -n "$val"
}

#Capture Options set by user
TITLE=$(prepare_for_php 'title')
LIBRARY_FOLDER="/media/$(prepare_for_php 'library_folder')"
SMTP_HOST=$(prepare_for_php 'smtp_host')
SMTP_USERNAME=$(prepare_for_php 'smtp_username')
SMTP_PASSWORD=$(prepare_for_php 'smtp_password')
SMTP_SECURE=$(prepare_for_php 'smtp_secure')
SMTP_PORT=$(prepare_for_php 'smtp_port')
ADDRESS_FROM=$(prepare_for_php 'address_from')
READER=$(prepare_for_php 'reader')
CUSTOM_COLS=$(bashio::config 'custom_cols')

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
    \$config['cops_epub_reader'] = '$READER';
END_HEREDOC_READER
)

# Set up mail server details to allow emailing of books
if bashio::config.has_value 'smtp_host';then
CONFIG_STR+=$(
cat << END_HEREDOC_SMTP_HOST
\$config['cops_mail_configuration'] = array( "smtp.host" => '$SMTP_HOST',
                                             "smtp.username" => '$SMTP_USERNAME',
                                             "smtp.password" => '$SMTP_PASSWORD',
                                             "address.from" => '$ADDRESS_FROM'
                                             );
END_HEREDOC_SMTP_HOST
)
    # Enable ssl if smtp_secure set to true
    if bashio::config.has_value 'smtp_secure'; then
    CONFIG_STR+=$(
cat << END_HEREDOC_SMTP_SECURE
\$config['cops_mail_configuration']['smtp.secure'] = '$SMTP_SECURE';
END_HEREDOC_SMTP_SECURE
    )
    fi
    # If port set, add that option
    if bashio::config.has_value 'smtp_port'; then
    CONFIG_STR+=$(
cat << END_HEREDOC_SMTP_PORT
\$config['cops_mail_configuration']['smtp.port'] = '$SMTP_PORT';
END_HEREDOC_SMTP_PORT
    )
    fi
fi

# If custom columns are set, add that option
if bashio::config.has_value 'custom_cols'; then
    # 1. Strip out square brackets [ ]
    # 2. Strip out all single and double quotes ' "
    # 3. Strip out spaces
    # 4. Result is a clean: col1,col2,col3
    CLEAN_COLS=$(echo "$CUSTOM_COLS" | tr -d '[]" '"'")

    # 5. Use sed to wrap items in double quotes
    # Result: "col1","col2","col3"
    FORMATTED_COLS=$(echo "$CLEAN_COLS" | sed 's/,/","/g; s/^/"/; s/$/"/')
    CONFIG_STR+=$(
cat << END_HEREDOC_CUSTOM_COLUMNS
\$config['cops_calibre_custom_column'] = [$FORMATTED_COLS];
\$config['cops_calibre_custom_column_list'] = [$FORMATTED_COLS];
\$config['cops_calibre_custom_column_preview'] = [$FORMATTED_COLS];
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
if bashio::config.true 'rsync'; then
    bashio::log.green 'Starting rsync daemon...'
    rsync --daemon
fi

# --- Korrosync Sync Server Section ---
if bashio::config.true 'koreader_sync'; then
        bashio::log.green "Starting Korrosync (KOReader Sync Server)..."

        # Ensure the data directory exists in the persistent /data partition
        mkdir -p /data/korrosync

        # Configure Korrosync via Environment Variables
        export KORROSYNC_DB_PATH="/data/korrosync/sync.redb"
        export KORROSYNC_SERVER_ADDRESS="127.0.0.1:3000"

        # Start in background with '&' so the script continues to Nginx
        # We redirect output to stdout/stderr so it shows in the HA logs
        /usr/bin/korrosync > /dev/stdout 2> /dev/stderr &

        # Optional: Log the database status
        if [ -f "/data/korrosync/sync.redb" ]; then
            bashio::log.info "Sync database found at /data/korrosync/sync.redb"
        fi
fi
# --- End Korrosync Section ---

# Start PHP-FPM (always required for the web UI)
bashio::log.green "Starting PHP-FPM..."
php-fpm -D -R

# Start NGINX in the foreground
bashio::log.green 'Starting NGINX web server...'
exec nginx -g "daemon off;"