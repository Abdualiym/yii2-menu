<?php

use yii\helpers\Html;
use yii\grid\GridView;
use abdualiym\menu\entities\Menu;
use abdualiym\languageClass\Language;
use abdualiym\text\helpers\TextHelper;

/* @var $this yii\web\View */
/* @var $searchModel abdualiym\menu\entities\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список меню';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-plus']) . ' Добавить меню',
        ['create'],
        ['class' => 'btn btn-primary btn-flat'])
    ?>
</p>
<div class="box box-default">


        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "<div class=\"box-header with-border\">"
                        ."<div class=\"box-title\"><h1>Меню</h1></div>"
                        ."<div class=\"box-tools pull-right\">{pager}</div></div>"
                        ."<div class=\"box-body\">{items}</div>",
            'columns' => [
                [
                    'label' => 'Позиция',
                    'value' => function (Menu $model) {
                        return
                            Html::a('<span class="glyphicon glyphicon-arrow-up"></span>',
                                ['move-up', 'id' => $model->id]
                            ) .
                            Html::a('<span class="glyphicon glyphicon-arrow-down"></span>',
                                ['move-down', 'id' => $model->id]
                            );
                    },
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'text-align: center'],
                ],
                [
                    'attribute' => 'title',
                    'label' => 'Заголовок',
                    'content' => function ($model) {
                        foreach ($model->translations as $tr) {

                            if ($tr['lang_id'] == (Language::getLangByPrefix('ru'))['id']) {
                                $translation = $tr;
                            }
                        }
                        $indent = ($model->depth >= 1 ? str_repeat('— ', $model->depth) . ' ' : '');
                        return $indent . Html::a(Html::encode($translation->title), ['view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],

                [
                    'attribute' => 'parent',
                    'label' => 'Родительское меню',
                    'content' => function ($model) {
                        $parent = $model->getParent()->with('translations')->one();

                        if ($parent) {
                            foreach ($parent->translations as $translation) {
                                if ($translation->lang_id == (Language::getLangByPrefix('ru'))['id']) {
                                    $translations = $translation;
                                }
                            }
                            $parent = $parent->id !== 1
                                ?
                                Html::a(
                                    Html::encode($translations->title),
                                    [
                                        'view',
                                        'id' => $parent->id],
                                    [
                                        'class' => 'label label-info'
                                    ]
                                )
                                : Html::tag('span', 'Верхнее меню', [
                                    'class' => 'label label-default'
                                ]);
                        } else {
                            $parent = Html::tag('span', 'Основное', [
                                'class' => 'label label-default'
                            ]);
                        }
                        return $parent;
                    },
                    'format' => 'raw'

                ],
                [
                    'attribute' => 'status',
                    'label' => Yii::t('text', 'Status'),
                    'value' => function (Menu $model) {
                        return TextHelper::statusLabel($model->status);
                    },
                    'format' => 'html',
                    'filter' => [1 => 'Активный', 0 => 'Черновик']
                ],
                [
                    'attribute' => 'type',
                    'filter' => Menu::getMenuTypes(),
                    'value' => function ($model) {
                        $types = Menu::getMenuTypes();
                        return Html::tag('span', $types[$model->type], [
                            'class' => 'label label-primary'
                        ]);
                    },
                    'format' => 'raw'
                ],
            ],
        ]); ?>
</div>
