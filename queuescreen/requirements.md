best through NOOBS

[required]
apache2 (to serve videos locally)
php 7.3 (to run process like syncing, logging, serving files etc)
pi user must be sudoer

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
- download and unzip ngrok into ~ngrok/ngrok
  - download ngrok into /home/pi/qmed-utils/queuescreen/bin
  - set up ./ngrok authtoken token
- enable ssh through sudo raspi-config
  - change password to raspberry1