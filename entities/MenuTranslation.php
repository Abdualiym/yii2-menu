<?php

namespace abdualiym\menu\entities;

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
class MenuTranslation extends \yii\db\ActiveRecord
{

    public static function create($title, $lang_id): self
    {
        $translation = new static();
        $translation->title = $title;
        $translation->lang_id = $lang_id;
        return $translation;
    }

    public function edit($title, $lang_id)
    {
        $this->title = $title;
        $this->lang_id = $lang_id;
    }

    public function isForLanguage($id): bool
    {
        return $this->lang_id == $id;
    }

    public static function blank($lang_id): self
    {
        $translation = new static();
        $translation->lang_id = $lang_id;
        return $translation;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu_menu_translate}}';
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
