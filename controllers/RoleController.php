<?php
namespace app\controllers;

use Yii;
use yii\web\Cookie;
use yii\filters\AccessControl;
use yii\db\Query;
use app\controllers\UserController;

class RoleController extends BaseController
{
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
		'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                },
                'only' => ['index', 'permissions', 'assing', 'revoke'],
                'rules' => [
                    [
                        'actions' => ['index', 'permissions', 'assing', 'revoke'],
                        'allow' => true,
                        'roles' => ['rbacManage'],
                    ],
                ],
            ],
        ];
    }

    /**
    * @OA\Get(
    *    tags={"Roles"},
    *    path="/role/permissions",
    *    summary="get roles if userid is send then created by user",
    *    @OA\Parameter(
     *         description="User id ",
     *         in="query",
     *         name="user_id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    *
    */
    public function actionPermissions()
    {
        $auth = Yii::$app->authManager;
        $userId = Yii::$app->request->get('user_id');
        $prm = $auth->getPermissions();
        if ($userId) {
            $us = UserController::findUsers($userId);
            if (!$us) {
                self::error("userNotFound");
                return;
            }
            $prm = $auth->getPermissionsByUser($userId);
        }
        $result = [];
        foreach ($prm as $p){
            $result[] = $p;
        }


        self::ok($result);
        return;
    }

    /**
    * @OA\Post(
    *    tags={"Roles"},
    *    path="/role/assign",
    *    summary="assign role to user ",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="userId",
    *                     type="string"
    *                 ),
    *                 required={"userId", "name"}
    *            )
    *       ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    */
    public function actionAssign()
    {
        $name = Yii::$app->request->post('name');
        $userId = Yii::$app->request->post('userId');
        if ($name == null || $userId == null) {
            self::error("send userId and name");
            return;
        }
        $us = UserController::findUsers($userId);
        if (!$us) {
            self::error("userNotFound");
            return;
        }
        $auth = Yii::$app->authManager;
        $item = $auth->getPermission($name);
        if (!$item) {
            self::error("not found role or permission");
            return;
        }
        $rls = $auth->assign($item, $userId);
        self::ok($rls);
        return;
    }

    /**
    * @OA\Post(
    *    tags={"Roles"},
    *    path="/role/revoke",
    *    summary="revoke role from user ",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(
    *                 @OA\Property(
    *                     property="name",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="userId",
    *                     type="string"
    *                 ),
    *                 required={"name", "userId"}
    *           ) 
    *        ) 
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    */
    public function actionRevoke()
    {
        $name = Yii::$app->request->post('name');
        $userId = Yii::$app->request->post('userId');
        if ($name == null || $userId == null) {
            self::error("send userId and name");
            return;
        }
        $us = UserController::findUsers($userId);
        if (!$us) {
            self::error("userNotFound");
            return;
        }
        $auth = Yii::$app->authManager;
        $item = $auth->getPermission($name);
        if (!$item) {
            self::error("not found role or permission");
            return;
        }
        if ($userId == self::$user->id && $item->name == 'rbacManage') {
            self::error("you cant revoke this permission from uourself");
            return;
        }
        $rls = $auth->revoke($item, $userId);
        self::ok($rls);
        return;
    }

}
