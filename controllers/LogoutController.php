<?php
namespace app\controllers;

use Yii;
use yii\web\Cookie;

class LogoutController extends BaseController
{
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
            self::ok();
        } else {
            self::error("You are not authorised", 401);
        }
    }
}
