<?php

namespace app\controllers;

use Yii;
use app\models\Articles;
use yii\data\ActiveDataProvider;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use cadyrov\gii\Upload;
use cadyrov\gii\File;
use yii\filters\AccessControl;
use moonland\phpexcel\Excel;
use yii\db\Query;
use yii\web\UploadedFile;

class ArticleController extends BaseController
{
    const RES_TRUE = 10;
	const RES_FALSE = 20;
	const RES_NOONE = 30;

    private $error=[];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
				'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                },
                'only' => ['index','view','create','delete','update'],
                'rules' => [
                    [
                        'actions' => ['index','view','create','delete','update'],
                        'allow' => true,
                        'roles' => ['dictionary'],
                    ],
                ],
            ],
        ];
    }

    /**
    * @OA\Get(
    *    tags={"Articles"},
    *    path="/articles",
    *    summary="list articless",
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(
    *            type="array",
    *            @OA\Items(ref="#/components/schemas/Articles")
    *         )
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    *
    */
    public function actionIndex()
    {
		$dataQuery = Articles::find()->orderBy('type_id asc, id asc');
        $id = Yii::$app->request->get('id');
        if ($id) {
			$dataQuery->where(['id' => $id]);
		}
        self::ok($dataQuery->all());
    }

    /**
    * @OA\Post(
    *    tags={"Articles"},
    *    path="/articles/create",
    *    summary="create new  articles ",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(ref="#/components/schemas/Articles")
    *          )
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(ref="#/components/schemas/Articles")
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    */
    public function actionCreate()
    {
        $model = new Articles();
        $model->setAttributes(Yii::$app->request->post());
        $model->account_id = self::$user->account_id;
        if (!$model->save()) {
            self::error($model->getErrors());
            return;
        }
        self::ok($model);
        return;
    }

    /**
    * @OA\Delete(
    *     path="/articles/delete",
    *     summary="Deletes a articles",
    *     tags={"Articles"},
    *     @OA\Parameter(
    *         description="Articles id to delete",
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
		$id = Yii::$app->request->post('id');
		if (!$id) {
			self::error('Send id');
			return;
		}
		$model = $this->getOne($id);
		if (!$model) {
			self::error('Articles not found with id: ' . $id);
			return;
		}
		if ($model->delete()) {
			return self::ok();
		} else {
            self::error($model->getErrors());
        }
	}


    /**
    * @OA\Post(
    *    tags={"Articles"},
    *    path="/articles/update",
    *    summary="update new  articles ",
    *    @OA\RequestBody(
    *        @OA\MediaType(
    *            mediaType="application/json",
    *            @OA\Schema(ref="#/components/schemas/Articles")
    *          )
    *     ),
    *    @OA\Response(
    *        response=200,
    *        description="OK",
    *        @OA\JsonContent(ref="#/components/schemas/Articles")
    *    ),
    *    security={{"api_key":{"PHPSESSID"}}}
    * )
    */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
		if (!$id) {
			self::error('Send id');
			return;
		}
        $model = $this->getOne($id);
        if (!$model) {
			self::error('Model not found');
			return;
		}
        if ($model->account_id !== self::$user->account_id) {
			self::error('You can`t update this document');
			return;
		}
        $modelNew = new Articles();
        $modelNew->setAttributes(Yii::$app->request->post());
        $model->name = $modelNew->name; 
        $model->type_id = $modelNew->type_id; 
        $model->detail_id = $modelNew->detail_id;         
        if ($model->save()) {
            self::ok($model);
            return;
        }
        self::error($model->getErrors());
    }


    private function getOne($id) {
        $md = Articles::findOne($id);
        if (!$md || $md->account_id != self::$user->account_id) {
            self::error("Модель с ид " . $id . " не найдена");
            return;
        }
        return $md;
    }

}
