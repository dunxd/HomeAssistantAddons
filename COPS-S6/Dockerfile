ARG BUILD_FROM
FROM $BUILD_FROM

COPY rootfs /

# Ensure cont-init/oneshot scripts are executable after copying
RUN if [ -d /etc/s6-overlay/s6-rc.d ]; then find /etc/s6-overlay/s6-rc.d -type f -name "up" -exec chmod +x {} \; || true; fi
# RUN if [ -d /etc/conf-init.d ]; then chmod +x /etc/conf-init.d/* || true; fi
