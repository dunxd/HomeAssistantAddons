ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP82 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM, openssl
# Also install rsync
RUN apk add --no-cache \
  php82 php82-intl php82-sqlite3 php82-pdo_sqlite php82-gd php82-mbstring php82-zip php82-dom php82-openssl php82-xmlwriter rsync

# Copy data for add-on
COPY run.sh /
COPY cops-1.3.6 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
