<?php
namespace abdualiym\menu\components;


use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\Category;
use abdualiym\text\entities\Text;
use abdualiym\text\entities\TextTranslation;
use abdualiym\text\entities\CategoryTranslation;
use Yii;
use yii\helpers\VarDumper;

class MenuSlugHelper extends Menu
{
    /**
     * @method string generateSlug возвращает абсолютный slug на теущем языке
     * @params (current slug, type, id, array lang)
     * @return string
     */


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