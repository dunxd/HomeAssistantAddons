ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP7 required with GD image processing, libxml (in base), intl, json (in base) and PDO sqlite3
RUN apk add --no-cache \
  php php-common php-intl php-sqlite3 php-pdo_sqlite php-gd php-mbstring php-zip

# Copy data for add-on
COPY run.sh /
COPY cops-1.1.3 /cops/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
