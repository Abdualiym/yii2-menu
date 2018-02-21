<?php

namespace domain\modules\menu\entities;

use Yii;

/**
 * This is the model class for table "menu_menu_translate".
 *
 * @property integer $id
 * @property string $title
 * @property integer $lang_id
 * @property integer $menu_id
 *
 * @property Menu $menu
 */
class MenuTranslate extends \yii\db\ActiveRecord
{

    public static function create($title, $lang_id): self
    {
        $translate = new static();
        $translate->title = $title;
        $translate->lang_id = $lang_id;
        return $translate;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_menu_translate';
    }

    public function edit($title, $lang_id)
    {
        $this->title = $title;
        $this->lang_id = $lang_id;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'lang_id' => Yii::t('app', 'Lang ID'),
            'menu_id' => Yii::t('app', 'Menu ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id']);
    }
}
