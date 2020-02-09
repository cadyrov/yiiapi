<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
* @OA\Schema(
*     description="Articles",
*     type="object",
*     title="Articles model",
*     @OA\Property(
*         property="id",
*         type="string"
*     ),
*     @OA\Property(
*         property="name",
*         type="string"
*     ),
*     @OA\Property(
*         property="account_id",
*         type="string"
*     ),
*     @OA\Property(
*         property="type_id",
*         type="string"
*     ),
*     @OA\Property(
*         property="detail_id",
*         type="string"
*     ),
*    required={ "id", "name", "account_id", "type_id", "detail_id"},
* )
*/
class Articles extends \yii\db\ActiveRecord
{  

    const TYPE_IN = 10;
    const TYPE_OUT = 20;

    const DETAIL_IN_REVENUE = 10;
    const DETAIL_IN_OTHER = 20;

    const DETAIL_OUT_FIX = 30;
    const DETAIL_OUT_VOLATILE = 40;
    const DETAIL_OUT_INVEST = 50;
    const DETAIL_OUT_TAX = 60;
    const DETAIL_OUT_OTHER = 70;

    public static function tableName()
    {
        return 'articles';
    }

    public function rules()
    {
        return [
            [['name', 'account_id', 'type_id', 'detail_id'], 'required'],
            [['account_id', 'type_id', 'detail_id'], 'default', 'value' => null],
            [['account_id', 'type_id', 'detail_id'], 'integer'],
            ['type_id', 'in', 'range' => self::getTypeArray()],
            ['detail_id', 'in', 'range' => self::getDetailArray()],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'account_id' => 'Account ID',
            'type_id' => 'Type ID',
            'detail_id' => 'Detail ID',
        ];
    }

    public static function getTypeArray()
    {
        return [
            self::TYPE_IN,
            self::TYPE_OUT,
        ];
    }

    public static function getTypeMap()
    {
        $map = [
            self::TYPE_IN => 'Поступления',
            self::TYPE_OUT => 'Расходы',
        ];
        return $map;
    }

    public static function getDetailArray()
    {
        return [
            self::DETAIL_IN_REVENUE,
            self::DETAIL_IN_OTHER,
            self::DETAIL_OUT_FIX,
            self::DETAIL_OUT_VOLATILE,
            self::DETAIL_OUT_INVEST,
            self::DETAIL_OUT_TAX,
            self::DETAIL_OUT_OTHER,
        ];
    }

    public static function getDetailInMap()
    {
        $map = [
            self::DETAIL_IN_REVENUE => 'Выручка',
            self::DETAIL_IN_OTHER => 'Прочие',
        ];
        return $map;
    }

    public static function getDetailOutMap()
    {
        $map = [
            self::DETAIL_OUT_FIX  => 'Постоянные',
            self::DETAIL_OUT_VOLATILE  => 'Переменные',
            self::DETAIL_OUT_INVEST  => 'Инвестиции',
            self::DETAIL_OUT_TAX  => 'Налоги/сборы',
            self::DETAIL_OUT_OTHER  => 'Прочие',
        ];
        return $map;
    }

}
