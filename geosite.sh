#!/bin/bash

DIR="/var/www"

# Generate dat files
/srv/domain-list-community --datapath=${DIR}/data --exportlists=antifilter-community,proxy-domain,direct-domain --outputdir=${DIR} --outputname=geosite.dat > /dev/null 2>&1

# chown
chown abc:abc ${DIR}/geosite.dat