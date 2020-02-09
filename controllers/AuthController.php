<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\User;
use yii\web\Cookie;
use yii\helpers\Html;
use app\models\Account;
use app\models\Articles;

class AuthController extends Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->request->isOptions) {
            BaseController::setHeadersCors();
            return;
        }
        return parent::beforeAction($action);
    }
    
    public function actionError() 
    {
        $net = new yii\filters\ContentNegotiator([
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
                'application/xml' => \yii\web\Response::FORMAT_XML,
            ],
            'languages' => [
                'en',
                'ru',
            ],
        ]);
        $net->negotiate();
        $exception = Yii::$app->errorHandler->exception;
        $response = \Yii::$app->getResponse();
        $response->setStatusCode($exception->statusCode);
        $response->data = [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ];
        $response->send();
    }
        
    /**
    * @OA\Post(
    *    tags={"Auth"},
    *    path="/auth",
    *    summary="login",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="login",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="password",
    *                     type="string"
    *                 ),
    *                 required={"login", "password"}
    *             )
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            BaseController::error("You authorized yet", 409);
            return;
        }
        $login = Yii::$app->request->post('login');
        $pass = Yii::$app->request->post('password');
        if ($login && $pass) {
            $us = User::findByEmail($login);
            if ($us == null){
                BaseController::error(" Пользователь не найден ");
                return;
            } else {
                if ($us->validatePassword($pass)){
                    Yii::$app->user->login($us);
                    BaseController::ok([], "Авторизация успешна");
                    return;
                } else {
                    BaseController::error("Пароль неверен");
                    return;
                }
            }
        }
        BaseController::error("Send login pass");
    }

    /**
    * @OA\Post(
    *    tags={"Auth"},
    *    path="/auth/check",
    *    summary="check auth",
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    *    security={{"cookieAuth": {}}}
    * )
    *
    */
    public function actionCheck()
    {
        $err = BaseController::check();
        if (!$err) {
            $acc = Account::findOne(BaseController::$user->account_id);
            BaseController::ok([
                'roles' => BaseController::$userRoles,
                'id' => BaseController::$user->id,
                'username' => BaseController::$user->username,
                'login' => BaseController::$user->email,
                'account' => $acc,
            ]);
            return;
        }
        BaseController::error($err);
    }

    /**
    * @OA\Post(
    *    tags={"Auth"},    
    *    path="/auth/activate",
    *    summary="activate",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="password",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="confirm",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="key",
    *                     type="string"
    *                 ),
    *                 required={"key", "confirm", "password"}
    *             )
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionActivate()
    {
        if (!Yii::$app->user->isGuest) {
            BaseController::error(" you are authorized", 409);
            return;
        }
        $key = Yii::$app->request->post('key');
        $pass = Yii::$app->request->post('password');
        $confirm = Yii::$app->request->post('confirm');
        if (!$pass) {
            BaseController::error("please send password", 400);
            return;
        }
        if ($pass != $confirm) {
            BaseController::error(" password and confirm do not match", 400);
            return;
        }
        if (!$key) {
            BaseController::error("please send key", 400);
            return;
        }
        $user = User::find()->where('auth_key = :key and status <> :block', ['key' => $key, 'block' => User::STATE_BLOCK])->one();
        if (!$user) {
            BaseController::error("key is gone", 400);
            return;
        }
        $user->status = User::STATE_ACTIVE;
        $user->generateAuthKey();
        $user->setPassword($pass);
        if ($user->save()) {
            BaseController::ok("user is activated");
            return;
        } else {
            BaseController::error($user->getErrors(), 400);
            return;
        }
    }

    /**
    * @OA\Post(
    *    tags={"Auth"},
    *    path="/auth/changepassword",
    *    summary="change password",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="password",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="confirm",
    *                     type="string"
    *                 ),
    *                 required={ "confirm", "password"}
    *             )
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionChangepassword()
    {
        if (Yii::$app->user->isGuest) {
            BaseController::error(" you are not authorized", 401);
            return;
        }
        $pass = Yii::$app->request->post('password');
        $confirm = Yii::$app->request->post('confirm');
        if (!$pass) {
            BaseController::error("please send password", 400);
            return;
        }
        if ($pass != $confirm) {
            BaseController::error(" password and confirm do not match", 400);
            return;
        }

        $user = User::findOne(Yii::$app->user->identity->id);
        if (!$user) {
            BaseController::error("user not found", 401);
            return;
        }
        if ($user->status == User::STATE_BLOCK) {
             BaseController::error("user blocked", 409);
            return;
        }
        $user->generateAuthKey();
        $user->setPassword($pass);
        if ($user->save()) {
            BaseController::ok("password changed");
            return;
        } else {
            BaseController::error($user->getErrors(), 400);
            return;
        }

    }
    
    /**
    * @OA\Post(
    *    tags={"Auth"},
    *    path="/auth/registry",
    *    summary="registry user",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="email",
    *                     type="string"
    *                 ),
    *                 required={"email"}
    *             )
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionRegistry(){
        $email = Yii::$app->request->post('email');
        if (!$email) {
            BaseController::error('send email');
            return;
        }
        $old = User::find()->where('email = :email', [":email" => $email])->one();
        if ($old) {
            BaseController::error('user exist', 409);
            return;
        }
        $acc = new Account();
        $acc->name = $email;
        $acc->create_at = date('Y-m-d H:i:s');
        if (!$acc->save()) {
            BaseController::error($acc->getErrors());
            return;
        }
        //createAdmin
        $user= new User();
        $user->username = $email;
        $user->email = $email;
        $user->account_id = $acc->id;
        $user->status = User::STATE_NEW;
        $user->created_at = mktime();
        $user->updated_at = mktime();
        $user->generateAuthKey();
        $user->setPassword($user->auth_key);
        $user->generateAuthKey();
        if (!$user->save()) {
            BaseController::error($user->getErrors());
            return;
        }
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        $auth->assign($admin, $user->id);
        $resSend = self::sendMail(
            $email, 
            'Confirm ', 
            'to confirm password click on link: ' . Yii::$app->params['serverLink'] . '/activate/' . $old->auth_key
        );
        if ($resSend) {
            BaseController::error($resSend, 500);
        } else {
            BaseController::ok('all correct');
        }
        return;
    }

    /**
    * @OA\Post(
    *    tags={"Auth"},
    *    path="/auth/requestpassword",
    *    summary="request to recover password",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="email",
    *                     type="string"
    *                 ),
    *                 required={"email"}
    *             )
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionRequestpassword(){
        $email = Yii::$app->request->post('email');
        if (!$email) {
            BaseController::error('send email');
            return;
        }
        $old = User::find()->where('email = :email and status <> :block', [":email" => $email, 'block' => User::STATE_BLOCK])->one();
        if (!$old) {
            BaseController::error('user not exist');
            return;
        }
        $old->generateAuthKey();
        $old->save();
        $resSend = self::sendMail(
            $email, 
            'Confirm ', 
            'to confirm password click on link: ' . Yii::$app->params['serverLink'] . '/activate/' . $old->auth_key
        );
        if ($resSend) {
            BaseController::error($resSend, 500);
        } else {
            BaseController::ok('all correct');
        }
        return;
    }

    private static function sendMail($email, $subj, $message) {
        Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([Yii::$app->params['postEmail'] => Yii::$app->params['postEmail']])
            ->setSubject($subj)
            ->setTextBody($message)
            ->send();
    }
    
    /**
    * @OA\Get(
    *    tags={"Auth"},
    *    path="/auth/dictionary",
    *    summary="return all dictionary of project",
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
     public function actionDictionary() {
        BaseController::ok([
            'userState' => User::stateMap(),
            'articleType' => Articles::getTypeMap(),
            'articleDetailIn' => Articles::getDetailInMap(),
            'articleDetailOut' => Articles::getDetailOutMap(),
        ]);
        return ;
    }

    /**
    * @OA\Get(
    *    tags={"Swagger"},
    *    path="/auth/swagger",
    *    summary="get swagger data",
    *    @OA\Response(
    *        response=200,
    *        description="OK"
    *    ),
    * )
    *
    */
    public function actionSwagger() {
        shell_exec('/var/www/html/basic/vendor/bin/openapi /var/www/html/basic --exclude vendor --exclude tests --exclude storage -o /var/www/html/basic/web/docs/swagger.json'); 
        BaseController::setHeadersCors();
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $obj = json_decode(file_get_contents(\Yii::$app->basePath . '/web/docs/swagger.json'));
        return $obj;
    }
}