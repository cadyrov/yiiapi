<?php

use yii\db\Migration;

/**
 * Class m200310_110028_logs
 */
class m200310_110028_logs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('logs', [
            
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'create_at' => $this->dateTime()->notNull(),
            'message' => $this->dateTime()->notNull(),
            'model' => $this->text()->notNull(),
            'type_id'=> $this->integer()),

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('logs');
    }
}
