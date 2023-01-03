cd "${PWD}"/deploy || exit
docker-compose up -d --no-deps --build
