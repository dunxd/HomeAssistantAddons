ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP7 required with GD image processing, libxml (in base), intl, json (in base) and sqlite3
RUN apk add --no-cache \
  php php-common php-intl php-sqlite3 php-gd php-mbstring php-zip

# Copy data for add-on
COPY run.sh /
COPY public_html /public_html/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
