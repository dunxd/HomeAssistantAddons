ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP81 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM, openssl
# Also install rsync
RUN apk add --no-cache \
  php81 php81-intl php81-sqlite3 php81-pdo_sqlite php81-gd php81-mbstring php81-zip php81-dom php81-openssl php81-xmlwriter rsync

# Copy data for add-on
COPY run.sh /
COPY cops-1.3.4 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
