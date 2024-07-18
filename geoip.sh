#!/bin/bash

DIR="/var/www"

# Generate dat files
/srv/geoip -c /srv/config.json > /dev/null 2>&1

# chown
chown abc:abc ${DIR}/geoip.dat