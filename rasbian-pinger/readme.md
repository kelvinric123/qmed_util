# Device pinger

- set up installation_id and device_name in config.ini first
- then setup the crontab to run this code every 1 minutes
- for eg, add this to crontab : `* * * * * php /var/qmed-utils/raspbian-pinger/device-ping.php`