<?php

namespace abdualiym\menu\services;


use abdualiym\menu\entities\Menu;
use abdualiym\menu\entities\MenuTranslate;
use abdualiym\menu\forms\menu\MenuForm;
use abdualiym\menu\repositories\MenuRepository;

class MenuService
{
    private $menuRepo;
    private $translate;

    public function __construct(MenuRepository $menuRepo)
    {
        $this->menuRepo = $menuRepo;
    }

    public function create(MenuForm $form)
    {
        $parent = !empty($form->parentId) ? $this->menuRepo->get($form->parentId) : '';

        $menu = Menu::create(
            $form->status,
            $form->type,
            $form->type_helper
        );



        if (!empty($parent)) {
            $menu->appendTo($parent);
        } else {
            $menu->makeRoot();
        }

        $menu->translate = Menu::translateAssigment($form);
        $this->menuRepo->save($menu);
        return $menu;
    }

    public function edit($id, MenuForm $form)
    {

        $menu = $this->menuRepo->get($id);
        $menu->edit(
            $form->status,
            $form->type,
            $form->type_helper
        );


        if (empty($form->parentId)) {
            $menu->makeRoot();
        } else {
            if (((!empty($menu->parent->id)) !== $form->parentId)) {
                if (($parent = $this->menuRepo->get($form->parentId)) && $parent->id !== $id) {
                    $menu->appendTo($parent);
                }
            }
        }
        $menu->translate = Menu::translateAssigment($form);
        $this->menuRepo->save($menu);

    }

    public function remove($id)
    {
        $menu = $this->menuRepo->get($id);
        $this->assertIsNotRoot($menu);
        $this->menuRepo->remove($menu);
    }

    public function moveUp($id)
    {
        $menu = $this->menuRepo->get($id);
        $this->checkIsRoot($menu);
        if ($prev = $menu->prev) {
            $menu->insertBefore($prev);
        }
        $this->menuRepo->save($menu);
    }

    public function moveDown($id)
    {
        $menu = $this->menuRepo->get($id);
        $this->checkIsRoot($menu);
        if ($next = $menu->next) {
            $menu->insertAfter($next);
        }
        $this->menuRepo->save($menu);
    }

    private function checkIsRoot(Menu $menu){
        if($menu->isRoot()){
            \Yii::$app->session->setFlash('error', 'Это корень меню!');
        }
    }

    private function assertIsNotRoot(Menu $menu)
    {

        if (count($menu->getDescendants()->all()) > 0) {
            throw new \DomainException('Unable to manage the root menu.');
        }
    }
}