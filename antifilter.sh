#!/bin/bash

DIR="/var/www"

# Download antifilter list
curl -s -o ${DIR}/allyouneed.lst https://antifilter.download/list/allyouneed.lst

# Download antifilter community geoip list
curl -s -o ${DIR}/community.lst https://community.antifilter.download/list/community.lst

# Download antifilter community geosite list
curl -s -o ${DIR}/data/antifilter-community-domain https://community.antifilter.download/list/domains.lst

### Modify for Shadowrocket & Clash ###
# Shadowrocket
sed -e 's/^/IP-CIDR,/' ${DIR}/allyouneed.lst > ${DIR}/antifilter-ip.list
sed -e 's/^/IP-CIDR,/' ${DIR}/community.lst > ${DIR}/antifilter-community-ip.list
sed -e 's/^/DOMAIN-SUFFIX,/' ${DIR}/data/antifilter-community-domain > ${DIR}/antifilter-community-domain.list
# Clash
sed -e 's/^/  - /' ${DIR}/antifilter-ip.list > ${DIR}/antifilter-ip.yaml
sed -i '1 i\payload:' ${DIR}/antifilter-ip.yaml
sed -e 's/^/  - /' ${DIR}/antifilter-community-ip.list > ${DIR}/antifilter-community-ip.yaml
sed -i '1 i\payload:' ${DIR}/antifilter-community-ip.yaml
sed -e 's/^/  - /' ${DIR}/antifilter-community-domain.list > ${DIR}/antifilter-community-domain.yaml
sed -i '1 i\payload:' ${DIR}/antifilter-community-domain.yaml

# chown
chown abc:abc ${DIR}/antifilter-*.{list,yaml}

# Generate geoip.dat
/srv/geoip.sh

# Generate geosite.dat
/srv/geosite.sh