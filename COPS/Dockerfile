ARG BUILD_FROM
FROM $BUILD_FROM

# Install requirements for add-on
# PHP7 required with libxml, intl, json sqlite3
RUN apk add --no-cache \
  php php-common

# Copy data for add-on
COPY run.sh /
COPY public_html /public_html/
RUN chmod a+x /run.sh

CMD ["/run.sh" ]
