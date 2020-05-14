<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\Cookies;
use app\models\User;
use yii\web\Response;
use app\models\Logs;
use app\controllers\Dictionary;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Rehau-kitchen",
 *     ),
 *     @OA\Server(
 *         description="Api server",
 *         url="http://api.rehau-kitchen.by/index.php/", 
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
        self::setHeadersCors();
        if (Yii::$app->request->isOptions) {
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

    public static function ok($data = [], $msg = "ОК", $total = null, $page = null, $limit = null)
    {
        $data = [
            'message' => $msg,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit 
            ]
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
    
    public static function createLog($model, $message){
        self::toLog(Dictionary::LOGS_CREATE, $model, $message);
    }

    public static function updateLog($model, $message){
        self::toLog(Dictionary::LOGS_UPDATE, $model, $message);
    }

    public static function deleteLog($model, $message){
        self::toLog(Dictionary::LOGS_DELETE, $model, $message);
    }

    public static function restoreLog($model, $message){
        self::toLog(Dictionary::LOGS_RESTORE, $model, $message);
    }

    private static function toLog($typeId, $model, $message){
        if (!self::$user) {
            return;
        }
        return;
        $log = new Logs();
        $log->message = $message;
        $log->model = $model;
        $log->type_id = $typeId;
        $log->user_id = self::$user->id;
        $log->create_at = date('Y-m-d H:i:s');
        if (!$log->save()) {
            self::error($log->getErrors(), 500);
        }
    } 

    public static function getStringToLike($query)
    {
        if ($query == null || trim($query) == ''){
            return;
        }
        return '%' . mb_strtolower($query) . '%';
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
