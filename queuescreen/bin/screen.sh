INSTALLATION_ID="$(php /home/pi/queuescreen/bin/get-installation-id.php)"
HOST="$(php /home/pi/queuescreen/bin/get-host.php)"
/usr/bin/chromium-browser --noerrordialogs --incognito --disable-session-crashed-bubble --disable-infobars --kiosk $HOST/queuescreen/$INSTALLATION_ID