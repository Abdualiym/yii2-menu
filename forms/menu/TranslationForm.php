<?php
namespace abdualiym\menu\forms\menu;

use yii\base\Model;
use abdualiym\menu\entities\Menu;
use abdualiym\menu\entities\MenuTranslation;

class TranslationForm extends Model
{
    public $title;
    public $lang_id;
    public $menu_id;

    public function __construct(MenuTranslation $translation = null, $config = [])
    {
        if ($translation) {
            $this->title = $translation->title;
            $this->lang_id = $translation->lang_id;
            $this->menu_id = $translation->menu_id;
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
            [['lang_id'],'integer'],
            [['title'], 'string','max' => 255],
            [['menu_id'], 'integer'],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::className(), 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }



}