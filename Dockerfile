FROM alpine:3.15.4

ENV TZ=Europe/Moscow

RUN apk -U upgrade && \
    apk -v add --no-cache bash curl nginx php8-fpm php8-session apache2-utils gcompat && \
    rm -rf /var/cache/apk/* && \
    addgroup -S abc && adduser -S abc -G abc && \
    mkdir -p /var/www && \
    chown abc:abc /var/www/

COPY --chown=abc:root nginx.conf /etc/nginx/
COPY --chown=abc:root php-fpm.conf /etc/php8/
COPY --chown=abc:root www.conf /etc/php8/php-fpm.d/
COPY --chown=abc:root crontab /srv/
COPY --chown=abc:root antifilter.sh /srv/
COPY --chown=abc:root pwd.sh /srv/
COPY --chown=abc:root v2ray-conv.sh /srv/
COPY --chown=abc:root clash-antifilter-lists /srv/
COPY --chown=abc:root geoip /srv/
COPY --chown=abc:root domain-list-community /srv/
COPY --chown=abc:root config.json /srv/
COPY --chown=abc:abc webroot/ /var/www/

RUN chmod +x /srv/antifilter.sh && \
    chmod +x /srv/pwd.sh && \
    chmod +x /srv/v2ray-conv.sh && \
    chmod +x /srv/clash-antifilter-lists && \
    chmod +x /srv/geoip && \
    chmod +x /srv/domain-list-community

EXPOSE 8080/tcp

CMD ["/srv/clash-antifilter-lists"]