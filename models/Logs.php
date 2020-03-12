Skip to content
Search or jump to…
<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
* @OA\Schema(
*     description="Logs model",
*     type="object",
*     title="Logs ",
*     @OA\Property(
*         property="id",
*         description="id",   
*         type="integer"
*     ),
*     @OA\Property(
*         property="user_id",
*         description="user_id",   
*         type="integer"
*     ),
*     @OA\Property(
*         property="account_id",
*         description="account_id",   
*         type="integer"
*     ),
*     @OA\Property(
*         property="create_at",
*         description="create_at",   
*         type="string"
*     ),
*     @OA\Property(
*         property="message",
*         description="message",   
*         type="string"
*     ),
*     @OA\Property(
*         property="model",
*         description="model",   
*         type="string"
*     ),
*     @OA\Property(
*         property="type_id",
*         description="type_id",   
*         type="integer"
*     ),
* *     required={ "id" ,"user_id" ,"account_id" ,"create_at" ,"message" ,"model"},
* )
*/
class Logs extends \yii\db\ActiveRecord
{
    const TYPE_CREATE = 10;
    const TYPE_UPDATE = 20;
    const TYPE_DELETE = 30;
    const TYPE_RESTORE = 40;

    public static function tableName()
    {
        return 'logs';
    }

    public function rules()
    {
        return [
            [['user_id', 'account_id', 'create_at', 'message', 'model'], 'required'],
            [['user_id', 'account_id', 'type_id'], 'default', 'value' => null],
            [['user_id', 'account_id', 'type_id'], 'integer'],
            ['type_id', 'in', 'range' => self::getTypeArray()],
            [['create_at', 'message'], 'safe'],
            [['model'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'account_id' => 'Account ID',
            'create_at' => 'Create At',
            'message' => 'Message',
            'model' => 'Model',
            'type_id' => 'Type ID',
        ];
    }

    public static function getTypeArray()
    {
        return [
            self::TYPE_CREATE,
            self::TYPE_UPDATE,
            self::TYPE_DELETE,
            self::TYPE_RESTORE
        ];
    }

    public static function getTypeMap()
    {
        return [
            self::TYPE_CREATE => 'Создание',
            self::TYPE_UPDATE => 'Изменение',
            self::TYPE_DELETE => 'Удаление',
            self::TYPE_RESTORE => 'Восстановление',
        ];
    }

}
