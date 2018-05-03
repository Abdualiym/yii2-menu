<?php

namespace abdualiym\menu\entities;

use abdualiym\menu\components\SlugRender;
use abdualiym\menu\entities\queries\MenuQuery;
use abdualiym\menu\entities\MenuTranslation;
use abdualiym\languageClass\Language;
use abdualiym\text\entities\Category;
use abdualiym\text\entities\CategoryTranslation;
use abdualiym\text\entities\Text;
use abdualiym\text\entities\TextTranslation;
use backend\entities\User;
use Yii;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use paulzi\nestedsets\NestedSetsBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property integer $status
 * @property string $type
 * @property string $type_helper
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property MenuTranslation[] $translations
 *
 * @property Menu $parent
 * @property Menu[] $parents
 * @property Menu[] $children
 * @property Menu $prev
 * @property Menu $next
 * @mixin NestedSetsBehavior
 */
class Menu extends ActiveRecord
{
    const VISIBLE = 1;
    const HIDDEN = 0;


    public static function create($status, $type, $type_helper): self
    {
        $menu = new static();
        $menu->status = $status;
        $menu->type = $type;
        $menu->type_helper = $type_helper;
        return $menu;
    }

    public function edit($status, $type, $type_helper)
    {
        $this->status = $status;
        $this->type = $type;
        $this->type_helper = $type_helper;
    }

    // Status

    public function activate()
    {
        if ($this->isActive()) {
            throw new \DomainException('Text is already active.');
        }
        $this->status = self::VISIBLE;
    }

    public function draft()
    {
        if ($this->isDraft()) {
            throw new \DomainException('Text is already draft.');
        }
        $this->status = self::HIDDEN;
    }


    public function isActive(): bool
    {
        return $this->status == self::VISIBLE;
    }

    public function isDraft(): bool
    {
        return $this->status == self::HIDDEN;
    }


//==================================================

    public function setTranslation($title, $lang_id)
    {

        $translations = $this->translations;
        foreach ($translations as $translation) {
            if ($translation->isForLanguage($lang_id)) {
                $translation->edit($title, $lang_id);
                $this->translations = $translations;
                return;
            }
        }

        $translations[] = MenuTranslation::create($title, $lang_id);
        $this->translations = $translations;
    }

    public function getTranslation($id): MenuTranslation
    {
        $translations = $this->translations;
        foreach ($translations as $tr) {
            if ($tr->isForLanguage($id)) {
                return $tr;
            }
        }
        return MenuTranslation::blank($id);
    }

//=====================================================

    public function getTranslations(): ActiveQuery
    {
        return $this->hasMany(MenuTranslation::class, ['menu_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu_menu}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public static function getMenuTypes($lang = null)
    {
        $params = [
            'en' => [
                'link' => 'link',
                'category' => 'category',
                'content' => 'content',
                'action' => 'module'
            ],
            'ru' => [
                'link' => 'Ссылка',
                'category' => 'Категория',
                'content' => 'Контент',
                'action' => 'Модуль'
            ],
        ];
        if ($lang) {
            return $params[$lang];
        }

        return $params['ru'];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => "Идентификатор",
            'status' => "Статус",
            'type' => "Тип меню",
            'type_helper' => "",
        ];
    }

    public function behaviors()
    {
        return [
            BlameableBehavior::class,
            TimestampBehavior::class,

            [
                'class' => NestedSetsBehavior::class,
                'treeAttribute' => 'tree',
            ],
            [
                'class' => SaveRelationsBehavior::class,
                'relations' => ['translations'],
            ],
        ];
    }



    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function actionsList($params)
    {
        return ArrayHelper::map($params, 'slug', 'name');
    }

    public static function find()
    {
        return new MenuQuery(static::class);
    }
}
