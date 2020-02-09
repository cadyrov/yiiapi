<?php
use moonland\mpdf\Pdf;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'request' => [
            'cookieValidationKey' => 'BOeHZJF55vlXITash92q_OrbTcp9KG39',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'auth/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.com',
                'username' => '',
                'password' => '',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'user' => [
		   'identityClass' => 'app\models\User',
		],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
				
			],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'modules' => [
	   'gii' => [
			'class' => 'yii\gii\Module',
			'allowedIPs' => ['127.0.0.1', '37.79.23.88'],
            'generators' => [
                'crud' => [
                    'class' => 'cadyrov\gii\crud\Generator',
                    'templates' => [
                        'crud' => 'cadyrov/gii/crud/default',
                    ]
                ],
                'model' => [
                    'class' => 'cadyrov\gii\model\Generator',
                    'templates' => [
                        'model' => 'cadyrov/gii/model/default',
                    ]
                ]
            ],
		]
	],
    'params' => $params,
];


return $config;
