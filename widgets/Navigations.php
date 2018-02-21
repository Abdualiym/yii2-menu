<?php

namespace abdualiym\menu\widgets;

use abdualiym\languageClass\Language;
use domain\modules\menu\entities\Menu;
use domain\modules\text\entities\Category;
use domain\modules\text\entities\CategoryTranslation;
use domain\modules\text\entities\Text;
use domain\modules\text\entities\TextTranslation;
use yii\base\Widget;
use Yii;
use yii\caching\DbDependency;
use yii\helpers\VarDumper;

class Navigations extends Widget
{

    public $position;
    public $lang;

    public function init()
    {
        parent::init();
    }


    public function run()
    {
        $lang = Language::getLangByPrefix($this->lang);
        if (!$this->position || !$tree = Menu::find()->where(['depth' => 0, 'type' => 'link'])->orderBy('rgt desc')->one()) {
            throw new \RuntimeException('Меню не найдено!');
        }
        $cache = Yii::$app->cache;

        $dependency = new DbDependency(['sql' => 'SELECT MAX(updated_at) FROM ' . Menu::tableName()]);
        $children = $cache->getOrSet('navigation-' . Yii::$app->language, function () use ($tree) {
            $lang = Language::getLangByPrefix(Yii::$app->language);
            return $tree->getDescendants()
                ->joinWith(['translate' => function ($query) use ($lang) {
                    $query->andWhere(['lang_id' => $lang['id']]);
                }])
                ->where(['status' => Menu::VISIBLE])
                ->andWhere(['>=', 'depth', 1])
                ->orderBy('lft asc')
                ->asArray()
                ->all();

        }, 0, $dependency);

        $array = self::Sort($children, $lang, $cache, $dependency);

        switch ($this->position) {
            case 'top':
                $navigation = 'TopNavigation';
                break;
            case 'bottom':
                $navigation = 'BottomNavigation';
                break;
            default:
                $navigation = 'TopNavigation';
                break;
        }

        return $this->render($navigation, [
            'children' => $array
        ]);

    }


    private static function Sort($array, $lang, $cache, $dependency)
    {

        $Ids = [
            'links' => [],
            'categories' => [],
            'contents' => [],
            'actions' => [],
            'tags' => []
        ];
        foreach ($array as $key => $item) {
            switch ($item['type']) {
                case 'link':
                    $Ids['links'][] = $item['type_helper'];
                    break;
                case 'category':
                    $Ids['categories'][$item['id']] = $item['type_helper'];
                    break;
                case 'content':
                    $Ids['contents'][] = $item['type_helper'];
                    break;
                case 'action':
                    $Ids['actions'][] = $item['type_helper'];
                    break;
            }
        }
        $Ids['lang'] = $lang;
        $categories = $cache->getOrSet('navigation-categories-' . Yii::$app->language, function () use ($Ids) {
            return CategoryTranslation::find()->where(['parent_id' => $Ids['categories'], 'lang_id' => $Ids['lang']['id']])->asArray()->all();

        }, 0, $dependency);
        $categories = $cache->getOrSet('categories-' . Yii::$app->language, function (){
            return CategoryTranslation::find()->asArray()->all();

        }, 0, $dependency);
        $contents = $cache->getOrSet('navigation-contents-' . Yii::$app->language, function () use ($Ids) {
            return TextTranslation::find()->where(['parent_id' => $Ids['contents'], 'lang_id' => $Ids['lang']['id']])->asArray()->all();

        }, 0, $dependency);

        $parentIds = [];
        foreach ($array as $key => $item) {
            switch ($item['type']) {
                case 'link':
                    $array[$key]['type_helper'] = $item['type_helper'];
                    break;
                case 'category':

                    foreach ($categories as $category) {
                        if ($category['parent_id'] == $item['type_helper']) {
                            $array[$key]['type_helper'] = $category['slug'];
                            break;
                        }
                    }
                    break;
                case 'content':
                    foreach ($contents as $content) {
                        if ($content['parent_id'] == $item['type_helper']) {
                            $array[$key]['type_helper'] = $content['slug'];
                            break;
                        }
                    }
                    break;
                case 'action':
                    $Ids['actions'][] = $item['type_helper'];
                    break;
            }

            if ($item['lft'] != 0 && $item['rgt'] >= 1 && $item['type'] != 'link') {
                foreach ($array as $o) {
                    if ($o['lft'] < $item['lft'] && $o['rgt'] > $item['rgt']) {
                        $parentIds[$key][] = $o['type_helper'];
                    }
                }
            }


        }


        foreach ($parentIds as $key => $id) {
            if ($array[$key]['type'] == 'action') {
                $parentIds[$key] = $array[$key]['type_helper'];
            } else {
                $parentIds[$key] = implode('/', $parentIds[$key]) . '/' . $array[$key]['type_helper'];
            }
            $array[$key]['type_helper'] = $parentIds[$key];
        }
        return $array;
    }
}