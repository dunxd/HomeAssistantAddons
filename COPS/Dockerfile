ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP8 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM, openssl
RUN apk add --no-cache \
  php8 php8-intl php8-sqlite3 php8-pdo_sqlite php8-gd php8-mbstring php8-zip php8-dom php8-openssl rsync

# Copy data for add-on
COPY run.sh /
COPY cops-1.3.2 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
