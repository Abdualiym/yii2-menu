<?php

namespace abdualiym\menu\entities;

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
                'action' => 'action'
            ],
            'ru' => [
                'link' => 'Ссылка',
                'category' => 'Категория',
                'content' => 'Контент',
                'action' => 'Метод в контроллере (Экшн)'
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
            [
                'class' => NestedSetsBehavior::class,
                'treeAttribute' => 'tree',
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            [
                'class' => SaveRelationsBehavior::className(),
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

    public function actionsList()
    {
        return ArrayHelper::map(Yii::$app->params['actions'], 'slug', 'name');
    }



//    public function beforeSave($insert)
//    {
//        $langs = Yii::$app->params['languages'];
//        $actions = Yii::$app->params['actions'];
//        $path = realpath(Yii::getAlias('@frontend/config/urlManager.php'));
//        $fp = fopen($path, "w"); // Открываем файл в режиме записи
//        $mytext = "<?php
//    return [
//             'class' => 'abdualiym\menu\components\MenuUrlManager',
//             'enablePrettyUrl' => true,
//             'showScriptName' => false,
//             'enableStrictParsing' => true,
//             'languages' => [ ";
//        foreach ($langs as $lang) {
//            $mytext .= "'" . $lang . "', ";
//        }
//        $mytext .= "],\n\r";
//
//
//        $mytext .= "
//             'rules' => [\n'' => 'site/index',\n'captcha'=>'/site/captcha',\n'rss'=>'/rss/index',\n";
//        $menu = self::find()->where(['type' => 'action'])->asArray()->all();
//        if (count($menu) > 0) {
//            foreach ($actions as $action) {
//                foreach ($menu as $m) {
//                    if ($m['type_helper'] == $action['slug']) {
//                        $mytext .= "'" . $action['slug'] . "' => '" . $action['action'] . "',\n";
//                    }
//                }
//
//            }
//        }
//        $mytext .= "
//            'vote/<_c:[\w\-]+>/<_a:[\w-]+>' => 'vote/<_c>/<_a>',\n
//            '<lang:(uz|ru)>/<slug:[\w_\/-]+>' => 'site/change',\n
//            '<slug:[^(uz|ru)]+>' => 'site/slug-render',\n
//            '<slug:[\w_\/-]+>' => 'site/slug-render',\n
//            ]];";
//        $test = fwrite($fp, $mytext); // Запись в файл
//        if ($test) echo 'Данные в файл успешно занесены.';
//        else echo 'Ошибка при записи в файл.';
//        fclose($fp); //Закрытие файла
//
//        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
//    }

    public static function find()
    {
        return new MenuQuery(static::class);
    }
}
