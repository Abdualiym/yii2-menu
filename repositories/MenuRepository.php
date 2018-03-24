<?php

namespace abdualiym\menu\repositories;


use abdualiym\menu\entities\Menu;
use abdualiym\menu\entities\MenuTranslation;
use yii\web\NotFoundHttpException;

class MenuRepository
{
    public function get($id)
    {
        if (!$menu = Menu::find()->where(['id' => $id])->with('translations')->one()) {

            if (!$roots = Menu::find()->roots()->all()) {
                return $roots;
            }
            throw new NotFoundHttpException('Menu is not found.');
        }
        return $menu;
    }

    public function save(Menu $menu)
    {
        if (!$menu->save()) {
            throw new \RuntimeException('Menu saving error.');
        }
    }

    public function existsByMainMenu($id)
    {
        return MenuTranslation::find()->andWhere(['menu_id' => $id])->exists();
    }


    public function remove(Menu $menu)
    {
        $menu->tree = 0;
        $menu->lft = 0;
        $menu->rgt = 1;
        $menu->save();
        if (!$menu->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }
}