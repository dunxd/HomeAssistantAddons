#!/command/with-contenv bashio
# shellcheck shell=bash
exec 2>&1

APP_HOST=$(bashio::info.hostname)
APP_PORT=$(bashio::app.port 8000)
export COPS_HOST="$APP_HOST"
export COPS_PORT="$APP_PORT"

if [ -f /cops/library/metadata.db ]; then
    echo "Calibre database found, publishing stats to Home Assistant"
    php /publish_stats.php || true
else
    echo "Calibre database not found, skipping publishing stats to Home Assistant"
fi