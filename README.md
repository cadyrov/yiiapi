<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 api base</h1>
    <br>
</p>

#base template to rest api

Installation

instal composer 
    
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"

install yii with extension

    php composer.phar create-project --prefer-dist yiisoft/yii2-app-basic basic
    php composer.phar require cadyrov/yii2-gii "*"
    php composer.phar require zircote/swagger-php

replace basic folders to this repository folders

check config db

create migrations

    php yii migrate --migrationPath=@yii/rbac/migrations/
    php yii migrate 

create init(remove all users and rights and create default - admin@example.com/admin@example.com)
    
    php yii rbac/init
