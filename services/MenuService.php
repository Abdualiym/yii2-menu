<?php

namespace abdualiym\menu\services;


use abdualiym\menu\entities\Menu;
use abdualiym\menu\entities\MenuTranslation;
use abdualiym\menu\forms\menu\MenuForm;
use abdualiym\menu\repositories\MenuRepository;
use yii\helpers\VarDumper;

/**
 * Class MenuService
 * @package abdualiym\menu\services
 */

class MenuService
{
    private $repository;
    private $translate;

    public function __construct(MenuRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(MenuForm $form)
    {
        $parent = !empty($form->parentId) ? $this->repository->get($form->parentId) : '';

        $menu = Menu::create(
            $form->status,
            $form->type,
            $form->type_helper
        );
        foreach ($form->translations as $translation) {
            $menu->setTranslation($translation->title,$translation->lang_id);
        }

        if (!empty($parent)) {
            $menu->appendTo($parent);
        } else {
            $menu->makeRoot();
        }
        $this->repository->save($menu);
        return $menu;
    }

    public function edit($id, MenuForm $form)
    {
        $menu = $this->repository->get($id);
        $menu->edit(
            $form->status,
            $form->type,
            $form->type_helper
        );

        foreach ($form->translations as $translation) {
            $menu->setTranslation($translation->title,$translation->lang_id);
        }

        if (empty($form->parentId)) {
            $menu->makeRoot();
        }

        $parent = $menu->parent;
        if(!$parent){
            $parent = $this->repository->get($form->parentId);
            $menu->appendTo($parent);
        }
        $this->repository->save($menu);

    }

    public function remove($id)
    {
        $menu = $this->repository->get($id);
        $this->assertIsNotRoot($menu);
        $this->repository->remove($menu);
    }

    public function moveUp($id)
    {
        $menu = $this->repository->get($id);
        $this->checkIsRoot($menu);
        if ($prev = $menu->prev) {
            $menu->updated_at = time();
            $menu->insertBefore($prev);
        }
        $this->repository->save($menu);
    }

    public function moveDown($id)
    {
        $menu = $this->repository->get($id);
        $this->checkIsRoot($menu);
        if ($next = $menu->next) {
            $menu->updated_at = time();
            $menu->insertAfter($next);
        }

        $this->repository->save($menu);
    }

    ##########     Status     ##########

    public function activate($id)
    {
        $menu = $this->repository->get($id);
        $menu->activate();
        $this->repository->save($menu);
    }

    public function draft($id)
    {
        $menu = $this->repository->get($id);
        $menu->draft();
        $this->repository->save($menu);
    }

    private function checkIsRoot(Menu $menu){
        if($menu->isRoot()){
            throw new \LogicException('Изменить корень меню, нельзя!');
        }
    }

    private function assertIsNotRoot(Menu $menu)
    {

        if (count($menu->getDescendants()->all()) > 0) {
            throw new \DomainException('Unable to manage the root menu.');
        }
    }
}