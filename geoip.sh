#!/bin/bash

DIR="/var/www"

# Download antifilter community list
curl -s -o ${DIR}/community.lst https://community.antifilter.download/list/community.lst

# Generate dat files
/srv/geoip -c /srv/config.json > /dev/null 2>&1

# chown
chown abc:abc ${DIR}/geoip.dat