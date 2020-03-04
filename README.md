<p align="center">
    <h1 align="center">Yii 2 api create</h1>
    <br>
</p>

#base template to rest api

Installation

instal composer 
    
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"

install yii with extension

    php composer.phar update

check config db

create migrations

    php yii migrate --migrationPath=@yii/rbac/migrations/
    php yii migrate 

create init(remove all users and rights and create default - admin@example.com/admin@example.com)
    
    php yii rbac/init
