# CampusCRM Application
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/zhex900/orocampus/badges/build.png?b=master)](https://scrutinizer-ci.com/g/zhex900/orocampus/build-status/master)

**CampusCRM** is an open source customer relationship managment application designed to follow-up contacts on university campuses. 
 The core feature of this application is to track contacts' participation in campus activities. 

CampusCRM is built on [OroCRM][1].

## Installation
### Docker container
*(instructions here)*

### Or install natively
#### Setup OroCRM
1. Check [system requirements][2].
2. Install a [crm-application][3].

#### Install CampusCRM Application
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
5. Migrate
``` bash
php app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php app/console oro:migration:load --show-queries --force
```
6. Load fixtures
``` bash
php app/console oro:migration:data:load
```
7. Rebuild assets
``` bash
php app/console oro:platform:update --force
php app/console assets:install
php app/console assetic:dump
php app/console oro:requirejs:build
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

#### Manually add some data to the application
1. *(instructions here)*
2. *(all set. your application is ready to go)*


[1]:    https://github.com/orocrm/crm
[2]:    https://www.orocrm.com/documentation/index/current/system-requirements
[3]:    https://github.com/orocrm/crm-application/blob/master/README.md
