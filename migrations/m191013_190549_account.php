<?php

use yii\db\Migration;

/**
 * Class m191013_190549_account
 */
class m191013_190549_account extends Migration
{
    public function safeUp()
    {
        $this->createTable('account', [
		'id' => $this->primaryKey(),
		'name' => $this->string()->notNull(),
		'inn' => $this->string(),
		'label' => $this->string(),
		'create_at' =>  $this->dateTime()->notNull(),
		'deleted_at' =>  $this->dateTime(),
	]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('account');
    }
}
