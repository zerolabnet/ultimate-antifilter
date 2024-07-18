<h1 align="center">
    Ultimate Antifilter
</h1>

### Description

Absolutely minimalistic image of a complex for creating custom rules for bypassing blocking via web-interface in formats for Clash, Shadowrocket and v2rayGeoIPDat. In addition, a complete and up-to-date list of IP addresses banned in Russia with automatic updates (taken from antifilter.download and converted into the above formats). At the first launch of the container, a password is automatically generated for access to the admin panel.

<p align="center">
 <img src="https://raw.githubusercontent.com/zerolabnet/ultimate-antifilter/main/docs/01-scr.png" width="100%">
</p>

### Installation using docker

```bash
docker run -d \
--name ultimate-antifilter \
--restart=unless-stopped \
-p 8080:8080/tcp \
zerolabnet/ultimate-antifilter:latest
```

### Default Ports

```
8080 - web server port for access to the admin panel
```
Redefine as you wish.

### Password for authorization in the admin panel

After the first run look at the container log, in it you will find your login password `Your login password:`.

```bash
docker logs ultimate-antifilter
```

### Rule Format:

Each tab contains an example of the data format.

Additionally, you can use the 'domain:', 'keyword:' and 'full:' formats on the domain tabs.

Example:

```
domain:google.com
keyword:google
full:www.google.com
```

* Subdomain begins with domain:, followed by a valid domain name. **The prefix domain: may be omitted**.
* Keyword begins with keyword:, followed by a string.
* Full domain begins with full:, followed by a complete and valid domain name.

### Go to the admin panel and prescribe the rules:

http://YOUR_IP:8080

### Links to the rules

At the bottom of the page will be links to your custom rules lists and to a list with IP addresses banned in the Russian Federation.

### What has changed compared to Clash Antifilter Lists?

There was a separation of the lists into: **Proxy domain/keyword/full** | **Direct domain/keyword/full** | **Proxy IP-CIDR** | **Direct IP-CIDR**.

The v2rayGeoIPDat format has been added. I use it together with v2rayA on a router with OpenWrt firmware.

### v2rayGeoIPDat IP list category (geoip.dat)

```
antifilter-ip
antifilter-community-ip
proxy-ip
direct-ip
```

### v2rayGeoIPDat domain list category (geosite.dat)

```
antifilter-community-domain
proxy-domain
direct-domain
```

### Install v2rayA to OpenWrt

```bash
wget https://downloads.sourceforge.net/project/v2raya/openwrt/v2raya.pub -O /etc/opkg/keys/94cc2a834fb0aa03
echo "src/gz v2raya https://downloads.sourceforge.net/project/v2raya/openwrt/$(. /etc/openwrt_release && echo "$DISTRIB_ARCH")" | tee -a "/etc/opkg/customfeeds.conf"
opkg update
opkg install v2raya kmod-nft-tproxy xray-core luci-app-v2raya
```
Add cron job:
```bash
crontab -e
```
```
0 */12 * * * curl -s -o /usr/share/xray/geoip-afl.dat http://YOUR_IP:8080/geoip.dat
*/30 * * * * curl -s -o /usr/share/xray/geosite-afl.dat http://YOUR_IP:8080/geosite.dat
```

### Example rules for v2rayA

```
#
# Routing rules written earlier will be matched first
#
# Set the default outbound, if not set, the default is proxy
default: direct

# Source IP rules
#source(192.168.20.22)->direct
#source(192.168.1.11, 192.168.20.22)->proxy

# Domain name rules
#domain(geosite:category-ads-all, geosite:win-spy, geosite:win-extra)->block
domain("ext:geosite-afl.dat:direct-domain")->direct
domain("ext:geosite-afl.dat:antifilter-community-domain", "ext:geosite-afl.dat:proxy-domain")->proxy
domain(domain:2ip.io)->proxy

# Destination IP rules
ip("ext:geoip-afl.dat:direct-ip")->direct
ip("ext:geoip-afl.dat:antifilter-ip", "ext:geoip-afl.dat:antifilter-community-ip", "ext:geoip-afl.dat:proxy-ip")->proxy
#ip(8.8.8.8, 8.8.4.4)->proxy
```
