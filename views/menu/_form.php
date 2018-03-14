<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use abdualiym\menu\entities\Menu;
use abdualiym\languageClass\Language;
use yii\helpers\Json;
use abdualiym\text\forms\TextForm;


$langList = Language::langList(Yii::$app->params['languages']);
$menuTypes = Menu::getMenuTypes();
$textForm = new TextForm();
$categoriesList = $textForm->categoriesList();
$textsList = $textForm->textsList();
if (!empty($menu)) {
    $translate = $menu->translate;
    $typeHelper = $menu->type_helper;
}else{
    $translate = '';
    $typeHelper = '';
}


/* @var $this yii\web\View */
/* @var $model abdualiym\menu\forms\menu\MenuForm */
/* @var $menu abdualiym\menu\entities\Menu */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="menu-form">
    <?php $form = activeform::begin(); ?>
    <div class="box box-default">
        <div class="box-header with-border">Главная</div>
        <div class="box-body">
            <?= $form->field($model, 'status')->label('Статус')->dropDownList([Menu::VISIBLE => "Активный", Menu::HIDDEN => "Не активный"]) ?>
            <?= $form->field($model, 'type')->label('Тип меню')->dropDownList($menuTypes) ?>
            <div class="menu_types" style="display: none;"></div>
            <?= $form->field($model, 'parentId')
                ->label("Родительское меню")
                ->dropDownList(
                    $model->parentMenuList()
                );
            ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">Заголовки по всем языкам</div>
        <div class="box-body">
            <?= $form->errorSummary($model) ?>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <?php for ($i = 0; $i < count($langList); $i++): ?>
                    <li role="presentation" <?= $i == 0 ? 'class="active"' : '' ?>>
                        <a href="#<?= $langList[$i]['prefix'] ?>" aria-controls="<?= $langList[$i]['prefix'] ?>" role="tab" data-toggle="tab">
                            <?= '(' . $langList[$i]['prefix'] . ') ' . $langList[$i]['title'] ?>
                        </a>
                    </li>
                <?php endfor ?>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <br>
                <?php for ($i = 0; $i < count($langList); $i++): ?>
                    <div role="tabpanel" class="tab-pane <?= $i == 0 ? 'active' : '' ?>" id="<?= $langList[$i]['prefix'] ?>">
                        <?= $form->field($model->translate, 'title['.$i.']')->textInput(['maxlength' => true, 'value' => ($translate != '') ? $translate[$i]['title'] : $translate])->label("Заголовок на (".$langList[$i]['title'].")") ?>
                        <?= $form->field($model->translate, 'lang_id['.$i.']')->hiddenInput(['value' => $langList[$i]['id']])->label(false) ?>
                    </div>
                <?php endfor ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton("Создать", ['class' => 'btn btn-success']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php



$menu = new Menu();
$actionsList = $menu->actionsList();
$categoriesListJson = Json::encode($categoriesList);
$textsListJson = Json::encode($textsList);
$actionsListJson = Json::encode($actionsList);
$script = <<< JS
    function getHtml(currentType,categoriesList,textsList,actionsList, selected = false){
    var categoryKeys = Object.keys(categoriesList);
    var textKeys = Object.keys(textsList);
    var actionsKeys = Object.keys(actionsList);
    var optionsCat = '';
    var optionsText = '';
    var optionsAction = '';
    var selectedAttr = '';
    for(var i=0; i < categoryKeys.length; i++)
        {
            var name = categoriesList[categoryKeys[i]][0]['name'] !== undefined ? categoriesList[categoryKeys[i]][0]['name'] :  categoriesList[categoryKeys[i]];
            selectedAttr = selected == categoryKeys[i] ? ' selected="selected"' : '';
            optionsCat += '<option '+selectedAttr+' value="'+categoryKeys[i]+'">'+name+'</option>';
        }
    for(var i=0; i < textKeys.length; i++)
        {
            var title = textsList[textKeys[i]][0]['title'] !== undefined ? textsList[textKeys[i]][0]['title']:textsList[textKeys[i]];
            selectedAttr = selected == textKeys[i] ? ' selected="selected"' : '';
            optionsText += '<option '+selectedAttr+' value="'+textKeys[i]+'">'+title+'</option>';
        }
    for(var i=0; i < actionsKeys.length; i++)
        {
            selectedAttr = selected == actionsKeys[i] ? ' selected="selected"' : '';
            optionsAction += '<option '+selectedAttr+' value="'+actionsKeys[i]+'">'+actionsList[actionsKeys[i]]+'</option>';
        }
    if(!currentType){return ''};
            switch (currentType){
                case 'link':
                    return '<label class="control-label" for="menuform-type_helper">Введите ссылку</label>'
                            +'<input type="text" id="menuform-type_helper" class="form-control" name="MenuForm[type_helper]" maxlength="255" placeholder="http://" value="'+selected+'">';
                           
                case 'category':
                    return '<label class="control-label" for="menuform-type_helper">Выберите категорию контента</label>'
                           +'<select id="menuform-type_helper" class="form-control" name="MenuForm[type_helper]">'
                           + optionsCat
                           +'</select>';
                case 'content':
                    return '<label class="control-label" for="menuform-type_helper">Выберите контент</label>'
                           +'<select id="menuform-type_helper" class="form-control" name="MenuForm[type_helper]">'
                           + optionsText
                           +'</select>';
                case 'action':
                    return '<label class="control-label" for="menuform-type_helper">Выберите контент</label>'
                           +'<select id="menuform-type_helper" class="form-control" name="MenuForm[type_helper]">'
                           + optionsAction
                           +'</select>';
            }
    }
    
    function render(val){
        menu_types.html('<div class="form-group field-menuform-type_helper">'
                       +getHtml(val,categoriesList,textsList,actionsList,typeHelper)
                       +'<div class="help-block"></div></div>'                        
                       );
       menu_types.hide(0);
       menu_types.slideDown(150);
    }
    
    var type = $("#menuform-type"),
        menu_types = $(".menu_types"),
        categoriesList = $categoriesListJson,
        textsList      = $textsListJson,
        actionsList    = $actionsListJson,
        typeHelper     = '$typeHelper';
        
    if(typeHelper){
       render(type.val());
    }
        
    type.on('change',function() {
       render($(this).val());
    });

JS;

$this->registerJs(
    $script
);

?>
