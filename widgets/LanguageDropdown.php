<?php

namespace domain\modules\menu\widgets;

use Yii;
use yii\bootstrap\Dropdown;
use yii\helpers\Html;
use yii\helpers\VarDumper;

class LanguageDropdown extends Dropdown
{
    private static $_labels;

    private $_isError;

    public function init()
    {
        $route = Yii::$app->controller->route;
        $appLanguage = Yii::$app->language;
        $params = $_GET;
        $this->_isError = $route === Yii::$app->errorHandler->errorAction;
        if(!empty($params['slug'])){
            $route = 'site/change';
            $params['lang'] = '';
        }
        array_unshift($params, '/' . $route);
        foreach (Yii::$app->urlManager->languages as $language) {
            if(isset($params['lang'])){
                $params['lang'] = $language;
            }else{

                $params['language'] = $language;
            }
            $this->items[] = [
                'label' => self::label($language),
                'url' => $params,
                'options' => [
                    'class' => $language === $appLanguage ? 'active' : ''
                ]
            ];
        }

        parent::init();
        Html::removeCssClass($this->options, ['widget' => 'dropdown-menu']);
        Html::addCssClass($this->options, ['widget' => 'lang']);
    }

    public function run()
    {
        return parent::run();
    }

    public static function label($code)
    {
        if (self::$_labels === null) {
            self::$_labels = [
                'uz' => 'O`ZB',
                'ru' => 'РУС',
            ];
        }

        return isset(self::$_labels[$code]) ? self::$_labels[$code] : null;
    }
}