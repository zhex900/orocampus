# Signup form for CampusCRM
# Installation
- install the dependancies
``` bash
composer update --no-dev
```
# Usefull commands
- If you make changes to twigs, you need to remove the cache to reflect your changes.
``` bash
rm -rf app/cache/prod/twig/*
rm -rf app/cache/dev/twig/*
```