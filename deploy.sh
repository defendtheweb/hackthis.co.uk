git reset --hard HEAD
git pull origin master

git describe --all --long | cut -d "-" -f 3 > files/cache/version