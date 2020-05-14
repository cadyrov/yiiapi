<p align="center">
    <h1 align="center">Yii 2 rest api</h1>
    <br>
</p>

#base template to rest api

Installation

instal dockerCe
```
    sudo apt update
    sudo apt install apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu bionic stable"
    sudo apt update
    apt-cache policy docker-ce
    sudo apt install docker-ce
```

install git
```
    apt install git    
```
install postgresql (only on server)
```
    sudo apt install postgresql postgresql-contrib
    sudo systemctl start postgresql
    sudo systemctl enable postgresql
    sudo -i -u postgres
    createuser -S -d -r -P -E user
    createdb --owner user --encoding utf8 user
    exit
```

config postgress
```
    mcedit /etc/postgresql/10/main/postgresql.conf 
        listen_addresses = '*'
    mcedit /etc/postgresql/10/main/pg_hba.conf
        host    base          user            0.0.0.0/0  md5
        host    base          user            ::0/0  md5
    service postgresql restart
```

install project
```
    cd /var/www
    git clone https://github.com/cadyrov/yiiapi.git
    cd ./cadyrov
    set project db config like  pgsql:host=serveAdress;port=5432;dbname=dbname
```
up docker        
```
    docker-compose up -d
        #first time update depends
        docker exec -it yiiapi_php_1 /bin/bash
        php composer.phar self-update
        php composer.phar update
        create token on github if it is necessary
    exit
```

fist time up only on server
```
    create migrations
        php yii migrate --migrationPath=@yii/rbac/migrations/
        php yii migrate 
    create init(remove all users and rights and create default - admin@example.com/admin@example.com)
        php yii rbac/init
```
