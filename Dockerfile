FROM alpine:3.15.4

ENV TZ=Europe/Moscow

RUN apk -U upgrade && \
    apk -v add --no-cache bash curl nginx php8-fpm php8-session apache2-utils gcompat && \
    rm -rf /var/cache/apk/* && \
    addgroup -S abc && adduser -S abc -G abc && \
    mkdir -p /var/www/data

COPY --chown=abc:root nginx.conf /etc/nginx/
COPY --chown=abc:root php-fpm.conf /etc/php8/
COPY --chown=abc:root www.conf /etc/php8/php-fpm.d/
COPY --chown=abc:root crontab /srv/
COPY --chown=abc:root antifilter.sh /srv/
COPY --chown=abc:root pwd.sh /srv/
COPY --chown=abc:root geoip.sh /srv/
COPY --chown=abc:root geosite.sh /srv/
COPY --chown=abc:root ultimate-antifilter /srv/
COPY --chown=abc:root geoip /srv/
COPY --chown=abc:root domain-list-community /srv/
COPY --chown=abc:root config.json /srv/
COPY --chown=abc:abc webroot/ /var/www/

RUN chmod +x /srv/antifilter.sh && \
    chmod +x /srv/pwd.sh && \
    chmod +x /srv/geoip.sh && \
    chmod +x /srv/geosite.sh && \
    chmod +x /srv/ultimate-antifilter && \
    chmod +x /srv/geoip && \
    chmod +x /srv/domain-list-community && \
    echo "example.com" > /var/www/proxy-domain-suffix && \
    echo "192.0.2.0/24" > /var/www/proxy-ip-cidr && \
    echo "example.net" > /var/www/direct-domain-suffix && \
    echo "198.51.100.0/24" > /var/www/direct-ip-cidr && \
    touch /var/www/proxy-domain-suffix.list && \
    touch /var/www/direct-domain-suffix.list && \
    touch /var/www/proxy-ip-cidr.list && \
    touch /var/www/direct-ip-cidr.list && \
    touch /var/www/proxy-domain-suffix.yaml && \
    touch /var/www/direct-domain-suffix.yaml && \
    touch /var/www/proxy-ip-cidr.yaml && \
    touch /var/www/direct-ip-cidr.yaml && \
    chown -R abc:abc /var/www/ && \
    ln -s /var/www/direct-domain-suffix /var/www/data/direct-domain-suffix && \
    ln -s /var/www/proxy-domain-suffix /var/www/data/proxy-domain-suffix && \

EXPOSE 8080/tcp

CMD ["/srv/ultimate-antifilter"]