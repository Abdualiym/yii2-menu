<?php

namespace domain\modules\menu\repositories;


use domain\modules\menu\entities\Menu;
use domain\modules\menu\entities\MenuTranslate;
use yii\web\NotFoundHttpException;

class MenuRepository
{
    public function get($id)
    {
        if (!$menu = Menu::find()->where(['id' => $id])->with('translate')->one()) {

            if (!$roots = Menu::find()->roots()->all()) {
                return $roots;
            }
            throw new NotFoundHttpException('Menu is not found.');
        }
        return $menu;
    }

    public function getTranslate($menu_id)
    {
        if (!$translate = MenuTranslate::find()->where(['menu_id' => $menu_id])->all()) {
            throw new NotFoundHttpException('Translate is not found.');
        }
        return $translate;
    }

    public function save(Menu $menu)
    {
        if (!$menu->save()) {
            throw new \RuntimeException('Menu saving error.');
        }
    }

    public function existsByMainMenu($id)
    {
        return MenuTranslate::find()->andWhere(['menu_id' => $id])->exists();
    }


    public function remove(Menu $menu)
    {
        $menu->tree = 0;
        $menu->lft = 0;
        $menu->rgt = 0;
        $menu->save();
        if (!$menu->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }
}