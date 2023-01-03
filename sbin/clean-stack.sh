cd "${PWD}"/deploy || exit
docker-compose down
rm -rf ../deploy/data/mysql
