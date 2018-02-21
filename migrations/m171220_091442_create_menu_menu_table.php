<?php

use yii\db\Migration;

/**
 * Handles the creation of table `menu`.
 */
class m171220_091442_create_menu_menu_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%menu_menu}}', [
            'id' => $this->primaryKey(),
            'status' => $this->boolean(),
            'type' => $this->string(),
            'type_helper' => $this->string(),
            'tree'  => $this->integer()->defaultValue(0),
            'lft'   => $this->integer()->notNull(),
            'rgt'   => $this->integer()->notNull(),
            'depth' => $this->integer()->notNull(), // not unsigned!
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ], $tableOptions);


        $this->insert('{{%menu_menu}}', [
            'status' => 1,
            'type' => 'link',
            'type_helper' => 'root',
            'tree'  => 1,
            'lft'   => 1,
            'rgt'   => 2,
            'depth' => 0,
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1,
        ]);

        $this->createIndex('{{%idx-menu_menu-id}}', '{{%menu_menu}}', 'id');
        $this->createIndex('{{%idx-menu_menu-lft}}', '{{%menu_menu}}', ['tree', 'lft', 'rgt']);
        $this->createIndex('{{%idx-menu_menu-rgt}}', '{{%menu_menu}}', ['tree', 'rgt']);
        // ForeignKey created_by, updated_by refTable user column id

        $this->addForeignKey('{{%fk-menu_menu_created_by}}', '{{%menu_menu}}', 'created_by', '{{%users}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->addForeignKey('{{%fk-menu_menu_updated_by}}', '{{%menu_menu}}', 'updated_by', '{{%users}}', 'id', 'RESTRICT', 'RESTRICT');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%menu_menu}}');
    }
}
