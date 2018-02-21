<?php

namespace domain\modules\menu\forms\menu;

use abdualiym\languageClass\Language;
use domain\modules\menu\entities\Menu;
use yii\helpers\ArrayHelper;
use elisdn\compositeForm\CompositeForm;
use domain\modules\menu\forms\menu\TranslateForm;
use yii\helpers\VarDumper;

/**
 * @property TranslateForm $translate;
 */

class MenuForm extends CompositeForm
{
    public $type_helper;
    public $parentId;
    public $status;
    public $type;

    private $_menu;

    public function __construct(Menu $menu = null, $config = [])
    {
        if ($menu) {
            $this->type = $menu->type;
            $this->type_helper = $menu->type_helper;
            $this->translate =  new TranslateForm();
            $this->parentId = ($a=$menu->getParent()->one()) ? $a->id : null;
            $this->_menu = $menu;
        }else{
            $this->translate = new TranslateForm();
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type', 'type_helper'], 'string', 'max' => 255],
            [['parentId', 'status'], 'integer'],
        ];
    }

    public function parentMenuList()
    {
        $menu = Menu::find()->with('translate')->orderBy('tree')->asArray()->all();
        $i = 0;
        foreach ($menu as $item) {
            foreach ($item['translate'] as $translate) {
                if ($translate['menu_id'] == $item['id']) {
                    $menu[$i]['title'] = $item['translate'][0]['title'];
                }
            }
            unset($menu[$i]['translate']);
            $i++;
        }
        $makeTree = [
            'id' => 0,
            'title' => 'Создать меню',
            'tree' => 0,
            'lft' => 0,
            'rgt' => 0,
            'depth' => 0
        ];
        $menu[] = $makeTree;
        return ArrayHelper::map($menu, 'id', function (array $menu) {
            return ($menu['depth'] >= 0 ? str_repeat('— ', $menu['depth']) . ' ' . $menu['title'] : '' . $menu['title']);

        });
    }

    public function internalForms()
    {
        return ['translate'];
    }
}