#!/bin/bash

DIR="/var/www"

# Download antifilter list
curl -s -o ${DIR}/allyouneed.lst https://antifilter.download/list/allyouneed.lst
curl -s -o ${DIR}/community.lst https://community.antifilter.download/list/community.lst

### Modify for Shadowrocket & Clash ###
# Shadowrocket
sed -e 's/^/IP-CIDR,/' ${DIR}/allyouneed.lst > ${DIR}/antifilter.list
# Clash
sed -e 's/^/  - /' ${DIR}/antifilter.list > ${DIR}/antifilter.yaml
sed -i '1 i\payload:' ${DIR}/antifilter.yaml

# To avoid an error when generating dat files
[ ! -f ${DIR}/data/proxy-ip-cidr ] && echo "192.0.2.0/24" > ${DIR}/data/proxy-ip-cidr && chown abc:abc ${DIR}/data/proxy-ip-cidr
[ ! -f ${DIR}/data/direct-ip-cidr ] && echo "198.51.100.0/24" > ${DIR}/data/direct-ip-cidr && chown abc:abc ${DIR}/data/direct-ip-cidr

# Generate dat files
/srv/geoip -c /srv/config.json > /dev/null 2>&1

# chown
chown abc:abc ${DIR}/antifilter.{list,yaml}
chown abc:abc ${DIR}/geoip.dat