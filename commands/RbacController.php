<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;
use app\models\Account;

class RbacController extends Controller {

    public function actionInit()
    {
        //drop users if exist
        foreach(User::find()->all() as $user){
            $user->delete();
        }

        //drop accounts if exist
        foreach(Account::find()->all() as $account){
            $account->delete();
        }

        $acc = new Account();
        $acc->name = 'admin@example.com';
        $acc->create_at = date('Y-m-d H:i:s');
        if (!$acc->save()) {
            return print_r($acc->getErrors());
        }
        //createAdmin
        $user= new User();
        $user->username = 'admin';
        $user->setPassword('admin@example.com');
        $user->email = 'admin@example.com';
        $user->account_id = $acc->id;
        $user->status = User::STATE_ACTIVE;
        $user->created_at = mktime();
        $user->updated_at = mktime();
        $user->generateAuthKey();
        if (!$user->save()) {
            return print_r($user->getErrors());
        }

        $auth = Yii::$app->authManager;
        $auth->removeAll();
        // Создаем разрешения.
        $rbacManage = $auth->createPermission('rbacManage');
        $rbacManage->description = 'Управление правами и пользователями';
        $dictionary = $auth->createPermission('dictionary');
        $dictionary->description = 'Управление справочниками';
        // Запишем эти разрешения в БД
        $auth->add($rbacManage);
        $auth->add($dictionary);
        // Назначаем разрешения пользователю
        $auth->assign($rbacManage, $user->id);
        $auth->assign($dictionary, $user->id);
    }
}
?>
