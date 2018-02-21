<?php

use yii\db\Migration;

/**
 * Handles the creation of table `menu_translate`.
 */
class m171220_091513_create_menu_menu_translate_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%menu_menu_translate}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'lang_id' => $this->integer(),
            'menu_id' => $this->integer()
        ], $tableOptions);
        $this->insert('{{%menu_menu_translate}}', [
            'title' => 'Верхнее меню',
            'lang_id' => 2,
            'menu_id' => 1
        ]);
        $this->insert('{{%menu_menu_translate}}', [
            'title' => 'Tepadagi menyu',
            'lang_id' => 3,
            'menu_id' => 1
        ]);



        $this->createIndex('{{%idx-menu_menu_translate-menu_id}}', '{{%menu_menu_translate}}', 'menu_id');
        $this->addForeignKey('{{%fk-menu_menu_translate-menu_id}}', '{{%menu_menu_translate}}', 'menu_id', '{{%menu_menu}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%menu_menu_translate}}');
    }
}
