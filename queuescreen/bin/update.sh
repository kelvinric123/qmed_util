cd /home/pi/qmed-utils
git remote update
git reset --hard origin/master
if [$1]
then
    git checkout $1
fi
sudo reboot -f