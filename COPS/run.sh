#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

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

# Start NGINX in the foreground
bashio::log.green 'Starting NGINX web server...'
exec nginx -g "daemon off;"