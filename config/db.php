<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;dbname=',
    'username' => '',
    'password' => '',
    'charset' => 'utf8',
    'schemaMap' => [
	'pgsql' => [ 'class'=>'yii\db\pgsql\Schema', 'defaultSchema' => 'public' ]
    ],
];
