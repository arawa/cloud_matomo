# Matomo for your Nextcloud
![GitHub All Releases](https://img.shields.io/github/downloads/arawa/cloud_matomo/total.svg)
[![GitHub license](https://img.shields.io/github/license/sualko/cloud_piwik.svg)](https://github.com/sualko/cloud_piwik/blob/master/LICENSE)

Track [Nextcloud](https://nextcloud.com) users with [Matomo](https://matomo.org).

## Requirements
- Nextcloud >= 25
- Working Matomo installation (tested with 2.14.3)
- Empty custom variable slots on index 1, 2 and 3

## What will be tracked?
- Normal Matomo stuff (url, page title, browser, ...)
- User ID aka Nextcloud user
- App in first custom variable
- Share ID in second custom variable (<code>index.php/s/SHARE_ID</code>)
- Share ID + folder or file name in third custom variable

## What will not be tracked?
- Download
- Outlink

## Installation
- Download and extract to <code>CLOUD_DIR/apps/matomo/</code>
- Enable app
- If needed create a new site in your Matomo installation
- Insert Matomo site id and url on the cloud admin page (e.g. site id: <code>1</code>, url: <code>//domain.tld/piwik/</code>)
- If Matomo is hosted under a different domain as your cloud you maybe need to use one of two possible proxy methods:
 - Add <code>RewriteRule "^piwik/(.*)$" "http://piwik.tld/$1" [P]</code> to your <code>.htaccess</code>
 - a2enmod proxy
 - Add <code>ProxyPass /piwik/ http://piwik.tld/</code> to your apache VirtualHost section

## Publishing a new version
1. bump version in `package.json`
2. `yarn release:build`
3. `yarn release:sign`
4. `yarn release:publish`
