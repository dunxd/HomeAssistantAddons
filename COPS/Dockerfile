ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP7 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM
RUN apk add --no-cache \
  php php-intl php-sqlite3 php-pdo_sqlite php-gd php-mbstring php-zip php-dom rsync

# Copy data for add-on
COPY run.sh /
COPY cops-1.3.2 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
