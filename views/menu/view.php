<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use abdualiym\languageClass\Language;
use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\CategoryTranslation;
use abdualiym\text\helpers\TextHelper;

/* @var $this yii\web\View */
/* @var $model abdualiym\menu\entities\Menu */
/* @var $content */

Yii::$app->formatter->locale = 'ru';

$this->title = $model->translations[0]->title;
$this->params['breadcrumbs'][] = ['label' => ' Меню', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="row">
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header">
                <p>

                    <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-pencil']) . ' Редактировать',
                        ['update', 'id' => $model->id],
                        ['class' => 'btn btn-flat btn-primary']) ?>

                    <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-trash']) . ' Удалить',
                        ['delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-flat btn-danger pull-right',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php if ($model->isActive()): ?>

                        <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-eye-slash']) .' '. Yii::t('app', 'Draft'),
                            ['draft', 'id' => $model->id],
                            ['class' => 'btn btn-flat btn-default pull-right', 'data-method' => 'post']) ?>

                    <?php else: ?>

                        <?= Html::a(Html::tag('i', '', ['class' => 'fa fa-eye']) .' '. Yii::t('app', 'Activate'),
                            ['activate', 'id' => $model->id],
                            ['class' => 'btn btn-flat btn-success', 'data-method' => 'post']) ?>

                    <?php endif; ?>

                </p>
                <hr>
                <h2><?= $model->translations[0]->title ?>
                    <small><?= Menu::getMenuTypes()[$model->type] ?></small>
                </h2>
            </div>

            <div class="box-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'status',
                            'label' => 'Статус',
                            'value' => function (Menu $model) {
                                return TextHelper::statusLabel($model->status);
                            },
                            'format' => 'html',
                            'filter' => [1 => 'Активный', 0 => 'Черновик']
                        ],
                        [
                            'attribute' => 'parent',
                            'label' => 'Родительское меню',
                            'value' => function ($model) {
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
                        [
                            'attribute' => 'created_at',
                            'label' => 'Дата создание',
                            'format' => 'datetime'
                        ],
                        [
                            'attribute' => 'updated_at',
                            'label' => 'Дата обновление',
                            'format' => 'datetime'
                        ],
                        [
                            'attribute' => 'created_by',
                            'label' => 'Создал',
                            'value' => function ($model) {
                                return (\backend\entities\User::findOne($model->created_by))->username;
                            }
                        ],
                        [
                            'attribute' => 'updated_by',
                            'label' => 'Обнавил',
                            'value' => function ($model) {
                                return (\backend\entities\User::findOne($model->created_by))->username;
                            }
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
