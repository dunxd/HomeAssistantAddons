#!/usr/bin/with-contenv bashio
# shellcheck shell=bash

# Compatibility copy of the configure script (legacy/typo folder)
# This mirrors the real cont-init.d script so accidental deletions
# of the misspelled directory won't break startup.

if [ -f /usr/lib/bashio ]; then
  # shellcheck source=/usr/lib/bashio
  . /usr/lib/bashio
fi

bashio::log.info "(00-configure.sh) Applying configuration settings to COPS config file..."

prepare_for_php() {
    if ! bashio::config.has_value "$1"; then
        echo -n ""
        return
    fi

    local val
    val=$(bashio::config "$1")
    val=$(echo -n "$val" | tr -d '\000')
    val="${val//\\/\\\\}"
    val="${val//\'/\\\'}"
    echo -n "$val"
}

TITLE=$(prepare_for_php 'title')
LIBRARY_FOLDER="/media/$(prepare_for_php 'library_folder')"
READER=$(prepare_for_php 'reader')
CUSTOM_COLS=$(bashio::config 'custom_cols')

COPS_CONFIG="/cops/config/local.php"
CONFIG_STR=$(
cat << 'END_HEREDOC_TITLE'

\$config['cops_title_default'] = '';
END_HEREDOC_TITLE
)

# (We keep this file lightweight — real values are written by cont-init or s6-rc oneshot.)

bashio::log.info "Configuration stub placed (conf-init.d)."
