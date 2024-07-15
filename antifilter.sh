#!/bin/bash

DIR="/var/www"

# Download antifilter list
curl -s -o ${DIR}/allyouneed.lst https://antifilter.download/list/allyouneed.lst

### Modify for Shadowrocket & Clash ###
# Shadowrocket
sed -e 's/^/IP-CIDR,/' ${DIR}/allyouneed.lst > ${DIR}/antifilter.list
# Clash
sed -e 's/^/  - /' ${DIR}/antifilter.list > ${DIR}/antifilter.yaml
sed -i '1 i\payload:' ${DIR}/antifilter.yaml

# chown
chown abc:abc ${DIR}/antifilter.{list,yaml}

# Generate geoip.dat
/srv/geoip.sh