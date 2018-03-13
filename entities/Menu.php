<?php

namespace abdualiym\menu\entities;

use abdualiym\menu\entities\queries\MenuQuery;
use abdualiym\menu\entities\MenuTranslate;
use abdualiym\languageClass\Language;
use abdualiym\text\entities\Category;
use abdualiym\text\entities\CategoryTranslation;
use abdualiym\text\entities\Text;
use abdualiym\text\entities\TextTranslation;
use backend\entities\User;
use MongoDB\BSON\Type;
use Yii;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use paulzi\nestedsets\NestedSetsBehavior;
use yii\behaviors\BlameableBehavior;
use yii\data\ArrayDataProvider;
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
 * @property MenuTranslate $translate
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
    private $descendants;

    public static function create($status, $type, $type_helper): self
    {
        $menu = new static();
        $menu->status = $status;
        $menu->type = $type;
        $menu->type_helper = $type_helper;
        $menu->translate = [];
        return $menu;
    }

    public function edit($status, $type, $type_helper)
    {
        $this->status = $status;
        $this->type = $type;
        $this->type_helper = $type_helper;
    }


    public static function isSingleSlug(array $explodeSlug)
    {
        return count($explodeSlug) == 1 ? true : false;
    }

    public static function isArticle(string $slug)
    {
        if ($text = TextTranslation::find()->with('text')->where(['slug' => $slug])->one()) {
            return $text->text->is_article;
        }
        return false;
    }

    public static function isCategory(string $slug)
    {
        return CategoryTranslation::find()->with('category')->where(['slug' => $slug])->asArray()->one();
    }

    public static function isPage(string $slug)
    {

        return TextTranslation::find()->with('text')->where(['slug' => $slug])->asArray()->one();
    }

    public static function thereAreParents(int $id)
    {
        return (self::find()->where(['type_helper' => $id])->one())->getParent()->andWhere(['>', 'depth', 0])->all();
    }

    public static function isCorrectSlug(array $array, string $slug, array $lang, string $type)
    {
        return self::getSlug($array['slug'], $type, $array['parent_id'], $lang) == '/' . $slug;
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function translateAssigment($model)
    {
        $translate = [];
        foreach ($model->translate->title as $key => $value) {
            $translate[] = MenuTranslate::create(
                $value,
                $model->translate->lang_id[$key]
            );
        }
        return $translate;
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
                'relations' => ['translate'],
            ],


        ];
    }


    /**
     * @method string getSlug возвращает абсолютный slug
     * @params (current slug, type, id, array lang)
     * @return string
     */

    public static function getSlug($currentSlug, $type, $id, $lang)
    {
        $menu = self::find()->where(['type' => $type, 'type_helper' => $id])->one();

        $category = Category::find()->with(['translations' => function ($q) use ($currentSlug) {
            $q->andWhere(['slug' => $currentSlug])->one();
        }])->where(['id' => $id])->asArray()->one();


        $text = Text::find()->with(['translations' => function ($q) use ($currentSlug) {
            $q->andWhere(['slug' => $currentSlug])->one();
        }])->where(['id' => $id])->asArray()->one();

        if ($menu != null) {
            $slug = self::buildSlug($menu, $lang, $currentSlug);

        }

        if($type == 'category'){
            $menu = self::find()->where(['type' => 'category', 'type_helper' => $category['id']])->one();
            $slug = self::buildSlug($menu, $lang, $currentSlug);
        }

        if($type == 'content'){
            if ($text['is_article']) {
                $menu = self::find()->where(['type' => 'category', 'type_helper' => $text['category_id']])->one();
                $slug = self::buildSlug($menu, $lang, $currentSlug,true);
            } else {
                $slug = '/';
                $slug .= $currentSlug;
            }
        }
        return $slug;
    }

    protected static function buildSlug($menu, $lang, $currentSlug, $is_article = false)
    {
        $arr = [];
        $slug = '/';
        if (is_object($menu)) {
            $parents = $menu->getParents()->all();
            foreach ($parents as $key => $m) {
                if ($m->type == 'content') {

                    $arr[$key] = TextTranslation::find()
                        ->select('slug')
                        ->where(['parent_id' => $m->type_helper, 'lang_id' => $lang['id']])
                        ->asArray()
                        ->one();
                }
                if ($m->type == 'category') {
                    $arr[$key] = CategoryTranslation::find()
                        ->select('slug')
                        ->where(['parent_id' => $m->type_helper, 'lang_id' => $lang['id']])
                        ->asArray()
                        ->one();
                }
            }
            if ($menu->type == 'category') {
                $arr[] = CategoryTranslation::find()
                    ->select('slug')
                    ->where(['parent_id' => $menu->type_helper, 'lang_id' => $lang['id']])
                    ->asArray()
                    ->one();
            }

            foreach ($arr as $item) {
                $slug .= $item['slug'] . '/';
            }

            if (!$is_article) {
                $slug = '/' . substr($slug, 1, -1);

            } else {
                $slug .= $currentSlug;
            }

        }
        return $slug;

    }


    public static function getBreadcrumbs(array $explodeSlug, array $lang)
    {
        $breadcrumbs = [];
        $texts = TextTranslation::find()->where(['slug' => $explodeSlug, 'lang_id' => $lang['id']])->all();
        $categories = CategoryTranslation::find()->where(['slug' => $explodeSlug, 'lang_id' => $lang['id']])->all();
        foreach ($explodeSlug as $key => $slug) {
            foreach ($texts as $tk => $text) {
                if ($slug == $text['slug']) {
                    $breadcrumbs[$key]['label'] = $text['title'];
                    $breadcrumbs[$key]['url'] = '/' . $text['slug'];
                }

            }
            foreach ($categories as $ck => $category) {
                if ($slug == $category['slug']) {
                    $breadcrumbs[$key]['label'] = $category['name'];
                    $breadcrumbs[$key]['url'] = '/' . $category['slug'];

                }

            }
        }

        foreach ($breadcrumbs as $key => $crumb) {
            if (isset($breadcrumbs[$key - 1])) {
                $breadcrumbs[$key]['url'] = $breadcrumbs[$key - 1]['url'] . $breadcrumbs[$key]['url'];
            }
        }
        return $breadcrumbs;
    }

    public static function getListing(array $categoryTranslation, $lang)
    {
        $navigation = Yii::$app->cache->get('navigation-' . Yii::$app->language);
        $children = [];
        foreach ($navigation as $nav) {
            if ($nav['type_helper'] == $categoryTranslation['parent_id']) {
                foreach ($navigation as $child) {
                    if ($nav['lft'] < $child['lft'] && $nav['rgt'] > $child['rgt']) {
                        $children[] = $child;
                    }
                }
            }
        }
        $typeIds = null;
        $i = 0;
        foreach ($children as $key => $child) {
            if ($child['type'] == 'category') {
                $typeIds['categories'][] = $child['type_helper'];
            }
            if ($child['type'] == 'content') {
                $typeIds['pages'][] = $child['type_helper'];
            }
            if ($child['type'] == 'link') {
                $typeIds['links'][$i]['title'] = $child['translate'][0]['title'];
                $typeIds['links'][$i]['link'] = $child['type_helper'];
                $i++;
            }
            if ($child['type'] == 'action') {
                $typeIds['modules'][] = $child['type_helper'];
            }
        }
        $data = [];
        if (isset($typeIds['categories'])) {
            $data['categories'] = CategoryTranslation::find()
                ->with('category')
                ->where(['lang_id' => $lang['id'], 'parent_id' => $typeIds['categories']])
                ->asArray()
                ->all();
            foreach ($data['categories'] as $key => $category) {
                $data['categories'][$key]['slug'] = self::getSlug($category['slug'], 'category', $category['parent_id'], $lang);
            }
        }
        if (isset($typeIds['pages'])) {
            $data['pages'] = TextTranslation::find()
                ->with('text')
                ->where(['lang_id' => $lang['id'], 'parent_id' => $typeIds['pages']])
                ->asArray()
                ->all();
            foreach ($data['pages'] as $key => $page) {
                $data['pages'][$key]['slug'] = self::getSlug($page['slug'], 'content', $page['parent_id'], $lang);
            }
        }
        if (isset($typeIds['links'])) {
            $data['links'] = $typeIds['links'];
        }
        if (isset($typeIds['modules'])) {
            $data['modules'] = $typeIds['modules'];
        }
        if ($articles = TextTranslation::find()
            ->joinWith('text')
            ->where(['text_texts.category_id' => $categoryTranslation['parent_id'], 'lang_id' => $lang['id']])
            ->asArray()
            ->all()) {
            $data['articles'] = $articles;
            foreach ($data['articles'] as $key => $article) {
                $data['articles'][$key]['slug'] = self::getSlug($article['slug'], 'content', $article['parent_id'], $lang);
            }
            $provider = new ArrayDataProvider([
                'allModels' => $data['articles'],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            $data['articles'] = $provider;
        }

        return $data;


    }

    public function generateSlug($explode, $l)
    {
        $newSlugs = [];
        $new = [];
        foreach ($explode as $sg) {
            $id = TextTranslation::find()
                ->select('parent_id')
                ->where(['slug' => $sg])
                ->asArray()
                ->one();

            if ($id) {
                $newSlugs[] = TextTranslation::find()
                    ->select('slug')
                    ->where(['parent_id' => $id, 'lang_id' => $l['id']])
                    ->asArray()
                    ->one();
            }

            $id = CategoryTranslation::find()
                ->select('parent_id')
                ->where(['slug' => $sg])
                ->asArray()
                ->one();
            if ($id) {
                $newSlugs[] = CategoryTranslation::find()
                    ->select('slug')
                    ->where(['parent_id' => $id, 'lang_id' => $l['id']])
                    ->asArray()
                    ->one();
            }
        }

        foreach ($newSlugs as $slug) {
            $new[] = $slug['slug'];
        }

        return Yii::$app->request->hostInfo . '/' . $l['prefix'] . '/' . implode('/', $new);
    }


    public function transmodules()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function modulesList()
    {
        return ArrayHelper::map(Yii::$app->params['modules'], 'slug', 'name');
    }

    public static function isAction($slug)
    {
        foreach (Yii::$app->params['modules'] as $action) {
            if ($action['slug'] == $slug) {
                return true;
            }
        }
    }

    public function beforeSave($insert)
    {
        $langs = Yii::$app->params['languages'];
        $modules = Yii::$app->params['modules'];
        $path = realpath(Yii::getAlias('@frontend/config/urlManager.php'));
        $fp = fopen($path, "w"); // Открываем файл в режиме записи
        $mytext = "<?php
    return [
             'class' => 'abdualiym\menu\components\MenuUrlManager',
             'enablePrettyUrl' => true,
             'showScriptName' => false,
             'enableStrictParsing' => true,
             'languages' => [ ";
        foreach ($langs as $lang) {
            $mytext .= "'" . $lang . "', ";
        }
        $mytext .= "],\n\r";


        $mytext .= "
             'rules' => [\n'' => 'site/index',\n'captcha'=>'/site/captcha',\n";
        $menu = self::find()->where(['type' => 'action'])->asArray()->all();
        if (count($menu) > 0) {
            foreach ($modules as $action) {
                foreach ($menu as $m) {
                    if ($m['type_helper'] == $action['slug']) {
                        $mytext .= "'" . $action['slug'] . "/<_c:[\w\-]+>/<_a:[\w-]+>' => '" . $action['action'] . "/<_c>/<_a>',\n";
                    }
                }

            }
        }
        $mytext .= "
            '<lang:(uz|ru)>/<slug:[\w_\/-]+>' => 'site/change',\n
            '<slug:[^(uz|ru)]+>' => 'site/slug-render',\n
            '<slug:[\w_\/-]+>' => 'site/slug-render',\n
            ]];";
        $test = fwrite($fp, $mytext); // Запись в файл
        if ($test) echo 'Данные в файл успешно занесены.';
        else echo 'Ошибка при записи в файл.';
        fclose($fp); //Закрытие файла

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            foreach ($this->translate as $translate) {
                $translate->delete();
            }
            return true;
        }
        return false;
    }

    public static function find()
    {
        return new MenuQuery(static::class);
    }

    public function getTranslate()
    {
        return $this->hasMany(MenuTranslate::className(), ['menu_id' => 'id']);
    }
}
