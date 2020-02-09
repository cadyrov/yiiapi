<?php
namespace app\controllers;

use Yii;
use yii\web\Cookie;
use yii\filters\AccessControl;
use app\models\User;
use yii\db\Query;

class UserController extends BaseController
{
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                },
                'only' => ['index', 'create', 'update', 'delete', 'block', 'restore','changeaccount'],
                'rules' => [
                    [
                        'actions' => ['index', 'permissions', 'createpermission', 'create',
                                      'update', 'delete', 'addchild', 'removechild','assing', 'revoke', 'getchild'],
                        'allow' => true,
                        'roles' => ['superadmin'],
                    ],
                    [
                        'actions' => ['index', 'permissions','assing', 'revoke', 'getchild','changeaccount'],
                        'allow' => true,
                        'roles' => ['rbacManage'],
                    ],
                ],
            ],
        ];
    }

    /**
    * @OA\Get(
    *    tags={"User"},
    *    path="/user",
    *    summary="list users",
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(
    *            type="array",
    *            @OA\Items(ref="#/components/schemas/User")
    *         )
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    *
    */
    public function actionIndex()
    {
        $ids = null;
        if (Yii::$app->request->get('id')) {
            $ids[] = Yii::$app->request->get('id');
        }
        self::ok(self::findUsers($ids));
        return;
    }

    /**
    * @OA\Post(
    *    tags={"User"},
    *    path="/user/create",
    *    summary="create new user ",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(ref="#/components/schemas/User")
    *          ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(ref="#/components/schemas/User")
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    */
    public function actionCreate()
    {
        $user = new User();
        $user->load(Yii::$app->request->post());
        $old = User::find()->where('email = :email', [":email" => $user->email])->one();
        if ($old) {
            self::error('user exist', 409);
            return;
        }
        if ($user->username == null) {
            $user->username = $user->email;
        }
        $user->status = User::STATE_NEW;
        $user->created_at = mktime();
        $user->account_id = self::$user->account_id;
        $user->generateAuthKey();
        $user->setPassword($user->auth_key);
        $user->generateAuthKey();
        if ($user->save()) {
            $result = [];
            $result['email'] = $user->email;
            $result['status'] = $user->status;
            $result['created_at'] = $user->created_at;
            $result['account_id'] = $user->account_id;
            $result['username'] = $user->username;
            $result['id'] = $user->id;
            $resSend = AuthController::sendMail(
                $user->email, 
                'Confirm ', 
                'to confirm register click on link: ' . Yii::$app->params['serverLink'] . '/activate/' . $user->auth_key
            );
            if ($resSend) {
                self::error($resSend, 500);
            } else {
                self::ok($result);
            }
        } else {
            self::error($user->getErrors(), 400);
        }
    }
    
        /**
        * @OA\Post(
        *    tags={"User"},
        *    path="/user/update",
        *    summary="update  user ",
        *    @OA\RequestBody(
        *        @OA\MediaType(
        *            mediaType="application/json",
        *            @OA\Schema(ref="#/components/schemas/User")
        *          ) 
        *     ),
        *    @OA\Response(
        *        response=200,
        *        description="OK",
        *        @OA\JsonContent(ref="#/components/schemas/User")
        *    ),
        *    security={{"api_key":{"PHPSESSID"}}}
        * )
        *
        */
    public function actionUpdate()
    {
        $user = new User();
        $user->setAttributes(Yii::$app->request->post());
        $usrOld = User::findOne(Yii::$app->request->post(id));
        if (!$usrOld) {
            self::error("user not found", 400);
            return;
        }
        $usrOld->username = $user->username;
        if ($usrOld->validate() && $usrOld->save()) {
            $result = [];
            $result['email'] = $usrOld->email;
            $result['status'] = $usrOld->status;
            $result['created_at'] = $usrOld->created_at;
            $result['account_id'] = $usrOld->account_id;
            $result['username'] = $usrOld->username;
            $result['id'] = $usrOld->id;
            self::ok($result);
        } else {
            self::error($usrOld->getErrors(), 400);
        }
    }

     /**
     * @OA\Delete(
     *     path="/user/delete",
     *     summary="Deletes a user",
     *     tags={"User"},
     *     @OA\Parameter(
     *         description="User id to delete",
     *         in="query",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     security={{"api_key":{"PHPSESSID"}}}
     * )
     */
    public function actionDelete()
    {
        $user = User::findOne(Yii::$app->request->get('id'));
        if (!$user) {
            self::error("user not found", 400);
            return;
        }
        if (!self::checkAccount($user->account_id)) {
            self::error("you can,t change this user", 403);
            return;
        }
        if (Yii::$app->user->identity->id == $user->id) {
            self::error("you can't remove yourself", 409);
            return;
        }
        if ($user->delete()) {
            self::ok("user deleted");
            return;
        } else {
             self::error($user->getErrors(), 400);
             return;
        }
    }

    /**
    * @OA\Get(
    *    tags={"User"},
    *    path="/user/block",
    *    summary="block  user ",
    *    @OA\Parameter(
     *         description="User id to block",
     *         in="query",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(ref="#/components/schemas/User")
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    *
    */
    public function actionBlock()
    {
        $user = User::findIdentity(Yii::$app->request->get('id'));
        if (!$user) {
            self::error("user not found", 400);
            return;
        }
        if (!self::checkAccount($user->account_id)) {
            self::error("you can,t change this user", 403);
            return;
        }
        if (Yii::$app->user->identity->id == $user->id) {
            self::error("you can't block yourself", 409);
            return;
        }
        if (Yii::$app->user->status == User::STATE_BLOCK) {
            self::error("user is blocked", 409);
            return;
        }
        $user->status = User::STATE_BLOCK;
        if ($user->save()) {
            self::ok("user blocked");
            return;
        } else {
            self::error($user->getErrors(), 400);
            return;
        }
    }

     /**
    * @OA\Get(
    *    tags={"User"},
    *    path="/user/restore",
    *    summary="restore  user ",
    *    @OA\Parameter(
     *         description="User id to restore",
     *         in="query",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(ref="#/components/schemas/User")
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    *
    */
    public function actionRestore()
    {
        $user = User::findIdentity(Yii::$app->request->get('id'));
        if (!$user) {
            self::error("user not found", 400);
            return;
        }
        if (!self::checkAccount($user->account_id)) {
            self::error("you can,t change this user", 403);
            return;
        }
        if (Yii::$app->user->identity->id == $user->id) {
            self::error("you can't restore yourself", 409);
            return;
        }
        if (Yii::$app->user->status != User::STATE_BLOCK) {
            self::error("user is unblocked", 409);
            return;
        }
        $user->status = User::STATE_ACTIVE;
        if ($user->save()) {
            self::ok("user blocked");
            return;
        } else {
            self::error($user->getErrors(), 400);
            return;
        }
    }

    public static function findUsers($ids = null, $query = null){
        $q = new Query();
        $q->select('id, username, email, status, account_id, created_at')->from('user');
        if ($ids) {
            $q->andWhere(['id' => $ids]);
        }
        $q->andWhere(['account_id' => self::$user->account_id]);
        $q->orderBy('username');
        return $q->all();
    }




}
