#!/bin/bash

DIR="/var/www"

# v2rayGeoIPDat
sed -e 's/IP-CIDR,//g' ${DIR}/proxy-ip-cidr.list > ${DIR}/data/proxy-ip-cidr
sed -e 's/IP-CIDR,//g' ${DIR}/direct-ip-cidr.list > ${DIR}/data/direct-ip-cidr
sed -e 's/DOMAIN-SUFFIX,//g' ${DIR}/proxy-domain-suffix.list > ${DIR}/data/proxy-domain-suffix
sed -e 's/DOMAIN-SUFFIX,//g' ${DIR}/direct-domain-suffix.list > ${DIR}/data/direct-domain-suffix

# chown
chown abc:abc ${DIR}/data/proxy-ip-cidr
chown abc:abc ${DIR}/data/direct-ip-cidr
chown abc:abc ${DIR}/data/proxy-domain-suffix
chown abc:abc ${DIR}/data/direct-domain-suffix

# Generate dat files
curl -s -o ${DIR}/data/antifilter-community https://community.antifilter.download/list/domains.lst
/srv/domain-list-community --datapath=${DIR}/data --exportlists=antifilter-community,proxy-domain-suffix,direct-domain-suffix --outputdir=${DIR} --outputname=geosite.dat