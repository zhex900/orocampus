![logo](logo.png) 

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/build.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/build-status/master)

**orocampus** is an open source customer relationship managment application designed to follow-up contacts on university campuses. 
 The core feature of this application is to track contacts' participation in campus activities. 

**orocampus** is built on [OroCRM][1].

# Table of content
* [Installation](#installation)
  * [Docker container](#docker-container)
  * [Or native install](#native-install)
    * [Setup OroCRM](#setup-orocrm)
    * [Install CampusCRM Application](#install-campuscrm-application)
* [Useful commands](#useful_commands)

# <a name="installation"></a>Installation
## Docker container
*(instructions here)*

## <a name="native-install"></a>Or native install
### <a name="setup-orocrm"></a>Setup OroCRM
1. Check [system requirements][2].
2. Install a [crm-application][3].

### <a name="install-campuscrm-application"></a>Install CampusCRM Application
1. move to crm-application directory
``` bash
cd dir/to/your/crm-application/
```
2. Clone orocampus from GitHub.
``` bash
git clone https://github.com/zhex900/orocampus.git
```
3. Copy source code **src/** directory
```bash
cp -r orocampus/CampusCRM/ src/
```
4. Clear cache
``` bash
php app/console cache:clear --env=dev -vvv
```
5. Rebuild assets
``` bash
php app/console oro:platform:update --force
```
6. Load workflows
``` bash
php app/console oro:workflow:definitions:load --workflows contact_followup
php app/console oro:workflow:definitions:load --workflows contact_feedback
```
7. Load translations
``` bash
php app/console oro:translation:load
```
8. Clear the cache again
``` bash
rm -rf app/cache/*
php app/console cache:clear --env=dev -vvv
```

[1]:    https://github.com/orocrm/crm
[2]:    https://www.orocrm.com/documentation/index/current/system-requirements
[3]:    https://github.com/orocrm/crm-application/blob/master/README.md

# <a name="useful_commands"></a>Useful commands
- Reset user password
``` bash
app/console oro:user:update admin --user-password=123456 --env=prod
```    
- Set permission
``` bash
chown -R www-data:www-data /var/www/ /srv/app-data/
```
- Load new database schema
``` bash
php app/console oro:migration:load --show-queries --force
```
- Load fixtures or default data
``` bash
php app/console oro:migration:data:load
```
-- create symlinks for the resources to web folder
``` bash
app/console assets:install web --symlink
```