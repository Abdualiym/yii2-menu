<?php
namespace domain\modules\menu\forms\menu;

use yii\base\Model;
use domain\modules\menu\entities\Menu;
use domain\modules\menu\entities\MenuTranslate;

class TranslateForm extends Model
{
    public $title;
    public $lang_id;
    public $menu_id;

    public function __construct(MenuTranslate $translate = null, $config = [])
    {
        if ($translate) {
            $this->title = $translate->title;
            $this->lang_id = $translate->lang_id;
            $this->menu_id = $translate->menu_id;
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'lang_id'],'required', 'message'=> 'Необходимо заполнить Заголовок'],
            ['lang_id', 'each', 'rule' => ['integer']],
            ['title', 'each', 'rule' => ['string','max' => 255]],
            [['menu_id'], 'integer'],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::className(), 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }



}