#!/bin/bash

DIR="/var/www"

# Download antifilter community list
curl -s -o ${DIR}/data/antifilter-community https://community.antifilter.download/list/domains.lst

# Generate dat files
/srv/domain-list-community --datapath=${DIR}/data --exportlists=antifilter-community,proxy-domain-suffix,direct-domain-suffix --outputdir=${DIR} --outputname=geosite.dat

# chown
chown abc:abc ${DIR}/geosite.dat