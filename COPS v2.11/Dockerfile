ARG BUILD_FROM
ARG PHP_VERSION=84
ARG KEPUBIFY_VERSION=4.0.4
ARG KORROSYNC_VERSION=0.2.0
FROM $BUILD_FROM

ARG PHP_VERSION
ARG KEPUBIFY_VERSION
ARG KORROSYNC_VERSION
ARG BUILD_ARCH

# Install requirements for add-on
# PHP^8 required with GD image processing, libxml (in base), intl, json (in base), PDO sqlite3, DOM, openssl
# Also install rsync. `apk add php` will install 8.1 today (July 2023), and assume in future alpine releases may install more
# recent version.
RUN apk add --no-cache \
  nginx php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-intl php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-pdo_sqlite php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring php${PHP_VERSION}-zip php${PHP_VERSION}-dom php${PHP_VERSION}-openssl php${PHP_VERSION}-xmlwriter php${PHP_VERSION}-session rsync

RUN ln -sf /usr/bin/php${PHP_VERSION} /usr/bin/php && \
    ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm

# Force PHP-FPM to log to stderr (which Docker captures)
RUN sed -i 's/;error_log = log\/php84\/error.log/error_log = \/dev\/stderr/g' /etc/php${PHP_VERSION}/php-fpm.conf && \
    sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/g' /etc/php${PHP_VERSION}/php-fpm.d/www.conf && \
    sed -i 's/;decorate_workers_output = no/decorate_workers_output = no/g' /etc/php${PHP_VERSION}/php-fpm.d/www.conf

# Configure PHP-FPM to listen on 127.0.0.1:9000 instead of a socket
RUN sed -i "s|listen = /.*|listen = 127.0.0.1:9000|g" /etc/php${PHP_VERSION}/php-fpm.d/www.conf

# Copy NGINX config
COPY nginx.conf /etc/nginx/http.d/default.conf

# Install kepubify and korrosync- curl is already in alpine base image
RUN \
    case "${BUILD_ARCH}" in \
        "amd64")   KEPUB_ARCH="64bit" ; KORROSYNC_ARCH="x86_64" ;; \
        "aarch64") KEPUB_ARCH="arm64" ; KORROSYNC_ARCH="aarch64" ;; \
        *)         KEPUB_ARCH="arm64" ; KORROSYNC_ARCH="aarch64" ;; \
    esac \
    && curl -L -s -o /usr/bin/kepubify \
       "https://github.com/pgaskin/kepubify/releases/download/v${KEPUBIFY_VERSION}/kepubify-linux-${KEPUB_ARCH}" \
    && chmod a+x /usr/bin/kepubify \
    && curl -L -s -o /tmp/korrosync.tar.gz \
       "https://github.com/szaffarano/korrosync/releases/download/v${KORROSYNC_VERSION}/korrosync-${KORROSYNC_ARCH}-unknown-linux-musl.tar.gz" \
    && tar -xzf /tmp/korrosync.tar.gz -C /usr/bin/ \
    && chmod a+x /usr/bin/korrosync \
    && rm /tmp/korrosync.tar.gz

# Copy data for add-on
COPY run.sh /
COPY cops-4.1.0 /cops/
COPY rsyncd.conf /etc/
RUN chmod a+x /run.sh

# NGINX needs the run directory to exist
RUN mkdir -p /run/nginx

CMD ["/run.sh" ]
