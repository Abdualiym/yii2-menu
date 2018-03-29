<?php

namespace abdualiym\menu\components;

use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\TextTranslation;
use abdualiym\menu\components\MenuSlugHelper;
use abdualiym\text\entities\CategoryTranslation;
use yii\data\ArrayDataProvider;
use Yii;

class MenuSlugRender extends Menu
{
    public static function isSingleSlug(array $explodeSlug)
    {

        return count($explodeSlug) == 1 && $explodeSlug != '';
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
        $result = parent::find()->where(['type_helper' => $id])->one();
        return $result ? $result->getParent()->andWhere(['>', 'depth', 0])->all() : null;
    }

    public static function isCorrectSlug(array $array, string $slug, array $lang, string $type)
    {
        $explode = explode('/',$slug);
        if(self::isDateFilter($explode)){
            $date = array_pop($explode);
            $slug = str_replace("/$date",'',$slug);
        }
        return MenuSlugHelper::getSlug($array['slug'], $type, $array['parent_id'], $lang) == '/' . $slug;
    }
    public static function isAction($slug)
    {
        foreach (Yii::$app->params['actions'] as $action) {
            if ($action['slug'] == $slug) {
                return true;
            }
        }
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

    public static function getListing(array $categoryTranslation, $lang, $date)
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
                $typeIds['actions'][] = $child['type_helper'];
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
                $data['categories'][$key]['slug'] = MenuSlugHelper::getSlug($category['slug'], 'category', $category['parent_id'], $lang);
            }
        }
        if (isset($typeIds['pages'])) {
            $data['pages'] = TextTranslation::find()
                ->with('text')
                ->where(['lang_id' => $lang['id'], 'parent_id' => $typeIds['pages']])
                ->asArray()
                ->all();
            foreach ($data['pages'] as $key => $page) {
                $data['pages'][$key]['slug'] = MenuSlugHelper::getSlug($page['slug'], 'content', $page['parent_id'], $lang);
            }
        }
        if (isset($typeIds['links'])) {
            $data['links'] = $typeIds['links'];
        }
        if (isset($typeIds['actions'])) {
            $data['actions'] = $typeIds['actions'];
        }

        if ($articles = TextTranslation::find()
            ->joinWith('text')
            ->where(['text_texts.category_id' => $categoryTranslation['parent_id'], 'lang_id' => $lang['id']])
            ->andWhere(['>=', 'text_texts.date', (self::getFilterDate($date))['start']])
            ->andWhere(['<', 'text_texts.date', (self::getFilterDate($date))['end']])
            ->asArray()
            ->orderBy(['date' => SORT_DESC])
            ->all()) {
            $data['articles'] = $articles;
            foreach ($data['articles'] as $key => $article) {
                $data['articles'][$key]['slug'] = MenuSlugHelper::getSlug($article['slug'], 'content', $article['parent_id'], $lang);
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

    public static function getFilterDate($date)
    {
        // $date -> format (php:'Y-m') to unix timestamp
        $start = strtotime($date);
        $end = strtotime($date . '+1 month');

        if ($date) {
            $result = [
                'start' => $start,
                'end' => $end,
            ];
        } else {
            $result = [
                'start' => strtotime('01-01-2000'),
                'end' => time(),
            ];
        }

        return $result;
    }

    public static function isDateFilter(array $explodeSlug)
    {
        return preg_match('/(19|20)\d\d[-](0[1-9]|1[012])/', array_pop($explodeSlug));
    }


}