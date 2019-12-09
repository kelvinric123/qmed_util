best through NOOBS

[required]
apache2 (to serve videos locally)
php 7.3 (to run process like syncing, logging, serving files etc)

[optional]
git
composer

[setup]
- /etc/apache2/apache2.conf
  - change /var/www to /home/pi/qmed-utils/queuescreen/www
  - add Header set Access-Control-Allow-Origin "*" under /var/www
- /etc/apache2/sites-available/000-default.conf
  - change document root to /home/pi/qmed-utils/queuescreen/www
- /etc/apache2/envvars
  - setboth user and group to pi