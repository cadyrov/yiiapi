<?php

use yii\db\Migration;

class m200204_061337_articles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = ($this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB': null);

        $this->createTable('articles', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
			'account_id' => $this->integer()->notNull(),
            'type_id' => $this->smallInteger()->notNull(),
            'detail_id' => $this->integer()->notNull(),
        ], $tableOptions);    
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('articles');
    }

}

