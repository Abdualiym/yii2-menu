<?php

/* @var $this yii\web\View */
/* @var $model abdualiym\menu\forms\menu\MenuForm */
/* @var $menu abdualiym\menu\entities\Menu */

/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use abdualiym\menu\entities\Menu;
use abdualiym\languageClass\Language;
use yii\helpers\Json;
use abdualiym\text\entities\Text;
use abdualiym\text\entities\Category;

Yii::$app->formatter->locale = Yii::$app->language;

$langList = Language::langList(Yii::$app->params['languages'], true);

$menuTypes = Menu::getMenuTypes();

$typeHelper = isset($menu) ? $menu->type_helper : '';

foreach ($model->translations as $i => $translate) {
    if (!$translate->lang_id) {
        $q = 0;
        foreach ($langList as $k => $l) {
            if ($i == $q) {
                $translate->lang_id = $k;
            }
            $q++;
        }
    }
}
?>

<div class="row">

    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
        <?= $this->render('_blocks', [
            'menu' => new Menu(),
            'text' => new Text(),
            'category' => new Category(),
            'menuTypes' => $menuTypes,
            'langList' => $langList,
        ]); ?>
    </div>


    <?php $form = activeform::begin(); ?>
    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">

        <div class="box box-default">
            <div class="box-header with-border">Главная</div>
            <div class="box-body">
                <?= $form->errorSummary($model); ?>
                <?= $form->field($model, 'status')->label(false)->hiddenInput(['value' => Menu::HIDDEN]) ?>
                <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'type_helper')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'parentId')
                    ->label("Родительское меню")
                    ->dropDownList(
                        $model->parentMenuList()
                    );
                ?>
                <?php foreach ($model->translations as $i => $translation): ?>

                    <?= $form->field($translation, '[' . $i . ']title')
                        ->textInput(['maxlength' => true, 'value' => $translation->title ?: ''])
                        ->label("Заголовок (" . $langList[$translation->lang_id]['title'] . ")")
                    ?>
                    <?= $form->field($translation, '[' . $i . ']lang_id')
                        ->hiddenInput(['value' => $langList[$translation->lang_id]['id']])
                        ->label(false)
                    ?>

                <?php endforeach; ?>

                <div class="box-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'),
                        ['class' => 'btn btn-flat btn-success btn-block'])
                    ?>
                </div>

            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php


$menu = new Menu();

$actionsList = $menu->actionsList(Yii::$app->params['actions']);

$actionsListJson = Json::encode($actionsList);


$articlesJson = Json::encode($menu->articlesList(new Text()));
$pagesJson = Json::encode($menu->pagesList(new Text()));
$categoriesJson = Json::encode($menu->categoriesList(new Category()));
$actionsJson = Json::encode($actionsList);

$newScript = <<< JS
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
      });

$("button").on('click',function(){
    var type = $(this).attr('data-button');
    var formType = $('#menuform-type');
    
    if(type == 'link'){
        var linkInputs = $('.linkInputs');
        
        for(var i=0; i < linkInputs.length; i++){
            if(linkInputs[i].value == ''){
                $('#'+linkInputs[i].id).parent().addClass('has-error');
            }else if(linkInputs[i].value != ''){
                $('#'+linkInputs[i].id).parent().removeClass('has-error');
                $('#translationform-'+[i]+'-title').val(linkInputs[i].value);
                formType.val(type);
            }
        }
    }
    
    var inputs = $('.checked').children('input');
    
    for(var i=0; i < inputs.length; i++){
        var name = inputs[i].attributes['name'].value;
        if(name == type){
            var id = inputs[i].attributes['data-id'].value;
        }
    }
    var articles = $articlesJson;
    var categories = $categoriesJson;
    var pages = $pagesJson;
    var actions = $actionsJson;
    var titles;
    
    var formTypeHelper = $('#menuform-type_helper');
    console.log(actions);
    switch (type){
        case 'articles':
            formType.val('content');
            titles = Object.values(articles[id].title);
            formTypeHelper.val(articles[id].id);
            break;
        case 'content':
            formType.val(type);
            titles = Object.values(pages[id].title);
            formTypeHelper.val(pages[id].id);
            break;
        case 'category':
            formType.val(type);
            titles = Object.values(categories[id].title);
            formTypeHelper.val(categories[id].id);
            break;
        case 'action':
            formType.val(type);
            titles = Object.values(actions[id].title);
            formTypeHelper.val(actions[id]['slug']);
            break;
    }
    console.log(formTypeHelper);
    
    for(var i=0; i < titles.length; i++){
        $('#translationform-'+[i]+'-title').val(titles[i]);
    }
});

JS;


$this->registerJs(
    $newScript
);

?>
