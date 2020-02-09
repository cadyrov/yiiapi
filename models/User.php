<?php
namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;

/**
* @OA\Schema(
*     description="User model",
*     type="object",
*     title="User model",
*     required={"email", "username"},
*     @OA\Property(
*         property="email",
*         type="string"
*     ),
*     @OA\Property(
*         property="username",
*         type="string"
*     ),
*     @OA\Property(
*         property="status",
*         type="integer"
*     ),
*     @OA\Property(
*         property="id",
*         type="integer"
*     ),
*     @OA\Property(
*         property="account_id",
*         type="integer"
*     ),
* )
*/
class User extends ActiveRecord implements IdentityInterface
{
    const STATE_NEW = 10;
    const STATE_ACTIVE = 20;
    const STATE_BLOCK = 30;

    public static function tableName()
    {
        return 'user';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public function rules()
    {
        return [
            [['username', 'email', 'status', 'account_id', 'password_hash', 'auth_key'],'required'],
            [['username', 'email'], 'unique'],
            ['status', 'default', 'value' => self::STATE_ACTIVE],
            ['status', 'in', 'range' => [self::STATE_ACTIVE, self::STATE_NEW, self::STATE_BLOCK]],
        ];
    }
    
    public static function stateMap() {
        return [
            self::STATE_NEW => 'Новый',
            self::STATE_ACTIVE => 'Активный',
            self::STATE_BLOCK => 'Заблокированный',
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id' => 'id',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATE_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATE_ACTIVE]);
    }

    public static function findByEmail($email)
    {
        return static::find()->where('username = :email OR email = :email', [":email" => $email])
        ->andWhere(['status' => self::STATE_ACTIVE])->one();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

}
