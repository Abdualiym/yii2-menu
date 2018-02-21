<?php

use yii\helpers\Html;
use yii\grid\GridView;
use domain\modules\menu\entities\Menu;
use abdualiym\languageClass\Language;

/* @var $this yii\web\View */
/* @var $searchModel domain\modules\menu\entities\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список меню';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">
    <p>
        <?= Html::a('Создать меню', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="box box-default">
        <div class="box-header with-border">Заголовки по всем языкам</div>
        <div class="box-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
                    [
                        'label' => 'Позиция',
                        'value' => function (Menu $model) {
                            return
                                Html::a('<span class="glyphicon glyphicon-arrow-up"></span>', ['move-up', 'id' => $model->id]) .
                                Html::a('<span class="glyphicon glyphicon-arrow-down"></span>', ['move-down', 'id' => $model->id]);
                        },
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'text-align: center'],
                    ],
                    [
                        'attribute' => 'title',
                        'label' => 'Заголовок',
                        'content' => function ($model) {
                            foreach ($model->translate as $translation){
                                if($translation['lang_id'] == (Language::getLangByPrefix('ru'))['id']){
                                    $translate = $translation;
                                }
                            }
                            $indent = ($model->depth >= 1 ? str_repeat('— ', $model->depth) . ' ' : '');
                            return $indent . Html::a(Html::encode($translate->title), ['view', 'id' => $model->id]);
                        },
                        'format' => 'raw',
                    ],

                    [
                        'attribute' => 'parent',
                        'label' => 'Родительское меню',
                        'content' => function ($model) {
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
                        'attribute' => 'status',
                        'filter' => [Menu::VISIBLE => 'Активные', Menu::HIDDEN => 'Не активные'],
                        'value' => function ($model) {
                            return $model->status == 1 ? 'Активный' : 'Не активный';
                        }
                    ],
                    [
                        'attribute' => 'type',
                        'filter' => Menu::getMenuTypes(),
                        'value' => function($model){
                                $types = Menu::getMenuTypes();
                                return $types[$model->type];
                        }
                    ],

                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
        </div>
    </div>
</div>
