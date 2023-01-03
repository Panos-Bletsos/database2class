# database2class

## How to run

### Prerequisites

* Docker
* Docker Compose

### Environment setup

### Initialize the DB

The Database is initialised with the files that are located under `deploy/config/initdb`. The files are executed by
alphabetical order. A testing user and some testing tables are created.

#### Specify the DB and php version

You can specify the DB and the php version in the file `deploy/.env`.

#### Php settings

Php settings are located in `deploy/php.ini`.

#### Server setup

The files that are served from apache2 are specified by the env variable `DOCUMENT_ROOT`. You can specify the location
in the file `deploy/.env`.

### Start containers

From the root directory of the project

```shell
./sbin/clean-start.sh
```

### Stop containers

From the root directory of the project

```shell
./sbin/clean-stack.sh
```

### Access website

Once the containers are running you can access the web interface via [http://localhost](http://localhost).

### Database User

A user is created for testing purposes. The credentials are:

* username: `db2php`
* password: `db2php`

### Database host

The host of the database is `database`, so use that to connect to the DB from the web portal.

![web-ui-screenshot.png](docs%2Fassets%2Fweb-ui-screenshot.png)

### phpmyadmin

phpmyadmin is accessible from [http://localhost:8080](http://localhost:8080).

## Used open source projects

* https://github.com/sprintcube/docker-compose-lamp.git