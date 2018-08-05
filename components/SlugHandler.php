<?php

namespace abdualiym\menu\components;

use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\TextTranslation;
use abdualiym\text\entities\CategoryTranslation;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\VarDumper;

class SlugHandler
{
    private $single;
    public $type;
    private $menu;
    private $text;
    private $category;
    private $language;
    private $actions;

    public function __construct(TextTranslation $text, CategoryTranslation $category, Menu $menu, $language, $actions)
    {
        $this->text = $text;
        $this->category = $category;
        $this->menu = $menu;
        $this->language = $language;
        $this->actions = $actions;
    }


    public function handler($slug, $category_view_template_names)
    {

        $explodeSlug = $this->verificationSlug($slug);

        if($date = $this->isDateFilter($explodeSlug)){
            if(is_array($date)){
                $explodeSlug = array_values(array_diff($explodeSlug,$date));
                $slug = implode('/',$explodeSlug);
                $date = null;
            }elseif(is_string($date)){
                array_pop($explodeSlug);
            }
        }
        $this->single = $this->isSingle($explodeSlug);

        $currentSlug = $this->pullOutSlug($explodeSlug);

        $data = $this->getData($currentSlug);

        $this->isCorrectSlug($data['data'], $slug);


        if ($this->type == 'category') {


            $result['data'] = [
                'listing' => $this->findListing($data['data'], $slug, $date),
                'view' => $category_view_template_names[$data['data']->category->feed_with_image],
                'currentCat' => $data['data']
            ];

            if (!$this->single) $result['data']['breadcrumbs'] = $this->getBreadcrumbs($explodeSlug, $this->language);
        }


        if ($this->type == 'page' || $this->type == 'article') {

            $result['data'] = ['content' => $data['data']];

            if (!$this->single) $result['data']['breadcrumbs'] = $this->getBreadcrumbs($explodeSlug, $this->language);
        }


        $result['template'] = $this->type == 'article' ? 'page' : $this->type;

        return $result;
    }


    private function verificationSlug(string $slug)
    {

        $slug = trim($slug, '/');
        $explodeSlug = explode('/', $slug);
        return $explodeSlug;
    }


    private function pullOutSlug(array $slug)
    {

        return $this->single ? $slug[0] : $slug[count($slug) - 1];
    }

    private function sortData($data, $slug = false)
    {

        if (!$data) {
            throw new \LogicException('нет входящих данных');
        }

        $resultData = $slug ? [] : ['text' => [], 'category' => []];
        $categoryIds = array_keys($data, 'category');
        $textIds = array_keys($data, 'content');

        $categories = $this->category::find()
            ->where(
                [
                    'parent_id' => $categoryIds,
                    'lang_id' => $this->language['id']
                ]
            )
            ->all();
        $texts = $this->text::find()
            ->where(
                [
                    'parent_id' => $textIds,
                    'lang_id' => $this->language['id']
                ]
            )
            ->all();

        foreach ($data as $key => $value) {
            foreach ($texts as $text) {
                if ($key == $text->parent_id) {
                    if ($slug) {
                        $resultData[$key] = $text->slug;
                    } else {
                        $resultData['text'][$key] = $text;
                    }
                }
            }
            foreach ($categories as $category) {
                if ($key == $category->parent_id) {
                    if ($slug) {
                        $resultData[$key] = $category->slug;
                    } else {
                        $resultData['category'][$key] = $category;
                    }
                }
            }
        }

        return $resultData;
    }


    private function findListing(CategoryTranslation $category, string $slug, $date = null)
    {

        $menu = $this->findMenu($category);

        $listing = ['pages', 'categories', 'articles', 'slug'];

        $childs = ArrayHelper::map($menu->getDescendants()
            ->andWhere(['status' => $this->menu::VISIBLE])
            ->all(), 'type_helper', 'type');


        $articlesDataProvider = new ActiveDataProvider([
            'query' => $this->text::find()
                ->joinWith('text')
                ->where(
                    [
                        'text_texts.category_id' => $category->parent_id,
                        'text_texts.status' => $this->menu::VISIBLE,
                        'lang_id' => $this->language['id']
                    ]
                )
                ->andWhere(['>=', 'text_texts.date', ($this->getDateRange($date))['start']])
                ->andWhere(['<', 'text_texts.date', ($this->getDateRange($date))['end']])
                ->orderBy(['date' => SORT_DESC])
            ,
            'pagination' => [
                'pageSize' => 10,
                'forcePageParam' => false,
                'pageSizeParam' => false,
            ]
        ]);

        $sortChilds['text'] = null;
        $sortChilds['category'] = null;

        if ($childs) {
            $sortChilds = $this->sortData($childs);
        }


        $listing['pages'] = $sortChilds['text'];
        $listing['categories'] = $sortChilds['category'];

        $listing['articles'] = $articlesDataProvider;
        $listing['slug'] = $slug;

        return $listing;

    }

    private function findMenu($translation)
    {

        $type = false;
        $type_helper = false;

        if ($this->type == 'category' || $this->type == 'article') {
            $type = 'category';
        }
        if ($this->type == 'page') {
            $type = 'content';
        }

        if ($this->type == 'category' || $this->type == 'page') {
            $type_helper = $translation->parent_id;
        }

        if ($this->type == 'article') {
            $type_helper = $translation->text->category_id;
        }

        if (empty($type_helper) || empty($type)) {
            throw new \LogicException('не определен type или type_helper');
        }

        $menu = $this->menu::find()->where(['type' => $type, 'type_helper' => $type_helper, 'status' => $this->menu::VISIBLE])->one();

        if (!$menu) {
            throw new \LogicException('Данная категория или страница не определен в меню');
        }

        return $menu;
    }

    public function getRealSlug($translation)
    {
        if (!$translation) {
            throw new \LogicException('Вы вошли без объекта');
        }


        $menu = $this->findMenu($translation);

        $parentMenu = $menu->getParents()->andWhere(['>', 'id', 1])->all();

        $parents = ArrayHelper::map($parentMenu, 'type_helper', 'type');

        if ($this->type == 'article') {
            $parents[$menu->type_helper] = $menu['type'];
        }

        if ($parents) {
            $arraySlug = $this->sortData($parents, true);
        }
        $arraySlug[] = $translation->slug;

        $realSlug = '/' . implode('/', $arraySlug);

        return $realSlug;
    }

    private function getData(string $currentSlug)
    {

        $isPage = $this->isPage($currentSlug);

        $isCategory = $this->isCategory($currentSlug);

        $isArticle = $this->isArticle($currentSlug);

        switch (true) {
            case $isArticle:

                if ($this->single) {
                    throw new \LogicException('Это статья');
                }

                $this->type = 'article';

                $result['data'] = $isArticle;

                break;

            case $isPage:

                $this->checkTheDisplayOfContent($isPage->text->id);

                $this->type = 'page';

                $result['data'] = $isPage;

                break;

            case $isCategory:

                $this->checkTheDisplayOfContent($isCategory->category->id);

                $this->type = 'category';

                $result['data'] = $isCategory;

                break;

            default:

                $result = false;
        }


        return $result;
    }


    private function checkTheDisplayOfContent(int $id)
    {
        if ($this->single && $this->thereAreParents($id)) {
            throw new \LogicException('одиночный слаг и есть в меню и есть родитель');
        } elseif (!$this->single && !$this->thereAreParents($id)) {
            throw new \LogicException(' Не одиночный слаг и есть в меню и нет родителя');
        }
    }

    //==========================================================//

    private function isSingle(array $explodeSlug)
    {
        return count($explodeSlug) == 1 && $explodeSlug != '';
    }

    private function isArticle(string $slug)
    {
        $text = $this->text::find()->with('text')->where(['slug' => $slug])->one();
        if (!empty($text) && $text->text->is_article) {
            return $text;
        }
        return false;
    }

    private function isCategory(string $slug)
    {
        return $this->category::find()->with('category')->where(['slug' => $slug])->one();
    }

    private function isPage(string $slug)
    {

        return $this->text::find()->with('text')->where(['slug' => $slug])->one();
    }

    private function thereIsAMenu($id)
    {
        if ($menu = $this->menu::find()->where(['type_helper' => $id])->one()) {
            return $menu;
        }
        throw new \LogicException('Не указан в меню');
    }


    private function thereAreParents(int $id)
    {
        $menu = $this->thereIsAMenu($id);
        if ($parent = $menu->getParent()->andWhere(['>', 'depth', 0])->andWhere(['!=', 'type', 'link'])->all()) {
            return $parent;
        }
        return false;
    }

    private function isCorrectSlug($translation, string $slug)
    {
        $explode = explode('/', $slug);

        if ($this->isDateFilter($explode)) {
            $date = array_pop($explode);
            $slug = str_replace("/$date", '', $slug);
        }

        $result = $this->getRealSlug($translation);

        if ($result != '/' . $slug) {
            throw new \LogicException('Не правильный slug');
        }

        return true;
    }

    public function isAction($slug)
    {
        $explode = explode('/', $slug);
        if (count($explode) > 1) {

            foreach ($this->actions as $action) {
                if ($action['slug'] == $slug) {
                    http_redirect($slug);
                }

                if ($explode[0] == 'contact') {
                    http_redirect('feedback');
                }
            }
        }

    }

    public function getBreadcrumbs(array $explodeSlug, array $lang)
    {
        array_pop($explodeSlug);
        $breadcrumbs = [];
        $texts = $this->text::find()->where(['slug' => $explodeSlug, 'lang_id' => $lang['id']])->all();
        $categories = $this->category::find()->where(['slug' => $explodeSlug, 'lang_id' => $lang['id']])->all();
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

    public static function getDateRange($date)
    {
        $start = strtotime($date);
        $end = strtotime($date . '+1 month');
        $end = $end > time() ? time() : $end;

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

    private function isDateFilter(array $explodeSlug)
    {
        $date = count($explodeSlug) == 1 ? 0 : $explodeSlug[count($explodeSlug) - 1];
        $match = preg_match('/(19|20)\d\d[-](0[1-9]|1[012])/', $date);
        if ($match) {
            $match = $date;
        }else{
            $match = preg_grep('/(19|20)\d\d[-](0[1-9]|1[012])/', $explodeSlug);
        }

        return !empty($match) ? $match : false;
    }


}