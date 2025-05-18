ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP^8 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM, openssl
# Also install rsync. `apk add php` will install 8.1 today (July 2023), and assume in future alpine releases may install more
# recent version.
RUN apk add --no-cache \
  php php-intl php-sqlite3 php-pdo_sqlite php-gd php-mbstring php-zip php-dom php-openssl php-xmlwriter php-session rsync

# Install kepubify
RUN curl -o /usr/bin/kepubify -L "https://github.com/pgaskin/kepubify/releases/latest/download/kepubify-linux-64bit" && \
    chmod 755 /usr/bin/kepubify

# Copy data for add-on
COPY run.sh /
COPY cops-3.6.5 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
