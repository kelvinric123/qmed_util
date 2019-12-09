best through NOOBS

[required]
apache2 (to serve videos locally)
php 7.3 (to run process like syncing, logging etc)

[optional]
git
composer

[setup]
- /etc/apache2/apache2.conf
  - add Header set Access-Control-Allow-Origin "*" under /var/www
- /etc/apache2/envvars
  - setboth user and group to pi