<?php
namespace abdualiym\menu\components;


use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\Category;
use abdualiym\text\entities\Text;
use abdualiym\text\entities\TextTranslation;
use abdualiym\text\entities\CategoryTranslation;
use Yii;

class MenuSlugHelper extends Menu
{
    /**
     * @method string getSlug возвращает абсолютный slug
     * @params (current slug, type, id, array lang)
     * @return string
     */

    public static function getSlug($currentSlug, $type, $id, $lang)
    {
        $menu = parent::find()->where(['type' => $type, 'type_helper' => $id])->one();

        $category = Category::find()->with(['translations' => function ($q) use ($currentSlug) {
            $q->andWhere(['slug' => $currentSlug])->one();
        }])->where(['id' => $id])->asArray()->one();


        $text = Text::find()->with(['translations' => function ($q) use ($currentSlug) {
            $q->andWhere(['slug' => $currentSlug])->one();
        }])->where(['id' => $id])->asArray()->one();

        if ($menu != null) {
            $slug = self::buildSlug($menu, $lang, $currentSlug);

        }

        if ($type == 'category') {
            $menu = parent::find()->where(['type' => 'category', 'type_helper' => $category['id']])->one();
            $slug = self::buildSlug($menu, $lang, $currentSlug);
        }

        if ($type == 'content') {
            if ($text['is_article']) {
                $menu = parent::find()->where(['type' => 'category', 'type_helper' => $text['category_id']])->one();
                $slug = self::buildSlug($menu, $lang, $currentSlug, true);
            } else {
                $slug = self::buildSlug($menu, $lang, $currentSlug);

                $slug .= $slug != '/' ? '/' : '';

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

    public static function generateSlug($explode, $l)
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
}