# CampusCRM Application
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/build.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/build-status/master)

**CampusCRM** is an open source customer relationship managment application designed to follow-up contacts on university campuses. 
 The core feature of this application is to track contacts' participation in campus activities. 

CampusCRM is built on [OroCRM][1].

# Table of content
* [Installation](#installation)
  * [Docker container](#docker-container)
  * [Or native install](#native-install)
    * [Setup OroCRM](#setup-orocrm)
    * [Install CampusCRM Application](#install-campuscrm-application)
    * [Manually add some data](#manual-procedure)

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
5. Load new database schema
``` bash
php app/console oro:migration:load --show-queries --force
```
6. Load fixtures or default data
``` bash
php app/console oro:migration:data:load
```
7. Rebuild assets
``` bash
php app/console oro:platform:update --force
```
8. Load workflows
``` bash
php app/console oro:workflow:definitions:load --workflows contact_followup
php app/console oro:workflow:definitions:load --workflows contact_feedback
```
9. Load translations
``` bash
php app/console oro:translation:load
```
10. Clear the cache again
``` bash
rm -rf app/cache/*
php app/console cache:clear --env=dev -vvv
```

### <a name="manual-procedure"></a>Manually add some data to the application
1. *(instructions here)*
2. *(all set. your application is ready to go)*


[1]:    https://github.com/orocrm/crm
[2]:    https://www.orocrm.com/documentation/index/current/system-requirements
[3]:    https://github.com/orocrm/crm-application/blob/master/README.md

# Useful commands
## Reset user password
    app/console oro:user:update admin --user-password=123456 --env=prod
## 
    chown -R www-data:www-data /var/www/ /srv/app-data/