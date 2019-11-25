INSTALLATION_ID="$(php /home/pi/qmed-utils/queuescreen/bin/settings.php installation_id)"
HOST="$(php /home/pi/qmed-utils/queuescreen/bin/settings.php host)"
/usr/bin/chromium-browser --noerrordialogs --incognito --disable-session-crashed-bubble --disable-infobars --kiosk $HOST/queuescreen/$INSTALLATION_ID