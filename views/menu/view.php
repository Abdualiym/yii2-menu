<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use abdualiym\languageClass\Language;
use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\CategoryTranslation;

/* @var $this yii\web\View */
/* @var $model app\modules\Menu\entities\Menu */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'status',
                'filter' => [Menu::VISIBLE => 'Активные', Menu::HIDDEN => 'Не активные'],
                'value' => function ($model) {
                    return $model->status == 1 ? 'Активный' : 'Не активный';
                }
            ],
            [
                'attribute' => 'parent',
                'label' => 'Родительское меню',
                'value' => function ($model) {
                    $parent = $model->getParent()->with('translate')->one();

                    if ($parent) {
                        foreach ($parent->translate as $translation){
                            if($translation->lang_id == (Language::getLangByPrefix('ru'))['id']){
                                $translate = $translation;
                            }
                        }
                        $parent = Html::a(Html::encode($translate->title), ['view', 'id' => $parent->id]);
                    } else {
                        $parent = 'Основное';
                    }
                    return $parent;
                },
                'format' => 'raw'

            ],
            [
                'attribute' => 'type',
                'filter' => Menu::getMenuTypes(),
                'value' => function($model){
                    $types = Menu::getMenuTypes();
                    return $types[$model->type];
                }
            ],
            [
                'attribute' => 'type_helper',
                'label' => 'Перейти',
                'value' => function($model){
                    switch ($model->type){
                        case 'link':
                            return Html::a('Перейти по ссылке ',$model->type_helper);
                        case 'category':
                            $cats = CategoryTranslation::find()
                                ->where(['parent_id' => $model->type_helper])
                                ->asArray()
                                ->all();
                            foreach (Language::langList(
                                Yii::$app->params['languages'],
                                true) as $lang){
                                $arr[] = Html::a($lang['title'],
                                    Yii::$app->params['frontendHostInfo'].'/'.$lang['prefix']
                                    .Menu::getSlug(
                                        $cats[0]['slug'],
                                        $model->type,$model->type_helper,$lang));
                            }

                            return implode('<br/>',$arr);
                    }
                },
                'format' => 'html'
            ],
            'type_helper:html',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute' => 'created_by',
                'label' => 'Создал',
                'value' => function($model){
                    return (\backend\entities\User::findOne($model->created_by))->username;
                }
            ],
            [
                'attribute' => 'updated_by',
                'label' => 'Обнавил',
                'value' => function($model){
                    return (\backend\entities\User::findOne($model->created_by))->username;
                }
            ],
        ],
    ]) ?>

</div>
