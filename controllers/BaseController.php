<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\Cookies;
use app\models\User;
use yii\web\Response;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="api v1",
 *     ),
 *     @OA\Server(
 *         description="Api server",
 *         url="http://fnhub.ru/web/index.php/",
 *     ),
 * )
 * 
 * 
 * @OA\SecurityScheme(
 *   securityScheme="cookieAuth",
 *   type="apiKey",
 *   in="cookie",
 *   name="PHPSESSID"
 * )
 *
 */ 
class BaseController extends Controller
{
    public static $user;
    public static $userRoles = [];
    public static $hasError = false;

    public function beforeAction($action)
    {
        if (Yii::$app->request->isOptions) {
            self::setHeadersCors();
            Yii::$app->response->statusCode = 204;
            return;
        }
        if (Yii::$app->user->isGuest) {
            return parent::beforeAction($action);
        }
        $err = self::check();
        if ($err) {
            $this->asJson([
                'message' => $err,
            ]);
            Yii::$app->response->statusCode = 401;
            return;
        }

        return parent::beforeAction($action);
    }


    public static function error($msg, $code = 400)
    {
        self::setHeadersCors();
        Yii::$app->response->data = ['message' => $msg];
        Yii::$app->response->statusCode = $code;
        Yii::$app->response->format = Response::FORMAT_JSON;
        self::$hasError = true;
    }

    public static function ok($data = [], $msg = "ОК")
    {
        $data = [
            'message' => $msg,
            'data' => $data,
        ];
        self::setHeadersCors();
        Yii::$app->response->data = $data;
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->format = Response::FORMAT_JSON;
        self::$hasError == false;
    }

    public static function check()
    {
       if (Yii::$app->user->isGuest) {
            return "User not authorised";
       }
       self::$user = User::findOne(Yii::$app->user->identity->id);
       if (!static::setRoles()) {
            return "Ошибка проверки прав пользователя";
       }
    }

    public static function checkAccount($accountId)
    {
        if (self::$user && self::$user->account_id == $accountId) {
            return true;
        }
        return false;
    }


    private static function setRoles()
    {
        $roles = Yii::$app->authManager->getRolesByUser(self::$user->id);
        foreach($roles as $role){
            self::$userRoles[] = $role->name;
        }
        $permissions = Yii::$app->authManager->getPermissionsByUser(self::$user->id);
        foreach($permissions as $permission){
            self::$userRoles[] = $permission->name;
        }

        return true;
    }

    public static function setHeadersCors() 
    {
        $origins = ['http://localhost:8080'];
        $origin = null;
        foreach ($origins as $or) {
            if (Yii::$app->request->headers->get('origin') == $or) {
                $origin = $or;
            }
        }
        if ($origin) {
            Yii::$app->response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        Yii::$app->response->headers->set('Access-Control-Allow-Credentials', 'true');
        Yii::$app->response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS, GET, PUT, DELETE');
        Yii::$app->response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    }

    public static function fileList($dir)
    {
        $array = [];
        if (is_dir($dir)){
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($dir . '/' . $file)) {
                        $filesize = filesize($dir . '/' . $file);
                        if($filesize > 1024){
                            $filesize = ($filesize/1024);
                            if($filesize > 1024){
                                $filesize = ($filesize/1024);
                                if($filesize > 1024){
                                    $filesize = ($filesize/1024);
                                    $filesize = round($filesize, 1);
                                    $filesize = $filesize." ГБ";
                                }else{
                                    $filesize = round($filesize, 1);
                                    $filesize = $filesize." MБ";
                                }
                            }else{
                                $filesize = round($filesize, 1);
                                $filesize = $filesize." Кб";
                            }
                        }else{
                            $filesize = round($filesize, 1);
                            $filesize = $filesize." байт";
                        }
                        $array[] = ['img' => $file, 'img_size' => $filesize];
                    }
                }
            }
            return $array;
        }
       
    }

}
