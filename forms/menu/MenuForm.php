<?php

namespace abdualiym\menu\forms\menu;

use abdualiym\menu\forms\menu\TranslationForm;
use abdualiym\languageClass\Language;
use abdualiym\menu\entities\Menu;
use elisdn\compositeForm\CompositeForm;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * @property TranslationForm[] $translations;
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
            $this->parentId = ($a=$menu->getParent()->one()) ? $a->id : null;

            $this->translations = array_map(function (array $language) use ($menu) {
                return new TranslationForm($menu->getTranslation($language['id']));
            }, Language::langList(\Yii::$app->params['languages']));
            $this->_menu = $menu;
        } else {
            $this->translations = array_map(function () {
                return new TranslationForm();
            }, Language::langList(\Yii::$app->params['languages']));
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
        $menu = Menu::find()->with('translations')->orderBy('tree')->asArray()->all();
        $i = 0;
        foreach ($menu as $item) {
            foreach ($item['translations'] as $translate) {
                if ($translate['menu_id'] == $item['id']) {
                    $menu[$i]['title'] = $item['translations'][0]['title'];
                }
            }
            unset($menu[$i]['translations']);
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
        return ['translations'];
    }
}