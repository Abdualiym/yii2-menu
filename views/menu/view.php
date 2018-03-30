<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use abdualiym\languageClass\Language;
use abdualiym\menu\entities\Menu;
use abdualiym\text\entities\CategoryTranslation;

/* @var $this yii\web\View */
/* @var $model abdualiym\menu\entities\Menu */

$this->title = 'Название: ' . $model->translations[0]->title;
$this->params['breadcrumbs'][] = ['label' => 'Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header">
                <p>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-flat btn-primary']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-flat btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>
            </div>
            <div class="box-body">
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
                                $parent = $model->getParent()->with('translations')->one();

                                if ($parent) {
                                    foreach ($parent->translations as $translation) {
                                        if ($translation->lang_id == (Language::getLangByPrefix('ru'))['id']) {
                                            $translations = $translation;
                                        }
                                    }
                                    $parent = Html::a(Html::encode($translations->title), ['view', 'id' => $parent->id]);
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
                            'value' => function ($model) {
                                $types = Menu::getMenuTypes();
                                return $types[$model->type];
                            }
                        ],
                        [
                            'attribute' => 'type_helper',
                            'label' => 'Перейти',
                            'value' => function ($model) {
                                switch ($model->type) {
                                    case 'link':
                                        return Html::a('Перейти по ссылке ', $model->type_helper);
                                    case 'category':
                                        $cats = CategoryTranslation::find()
                                            ->where(['parent_id' => $model->type_helper])
                                            ->asArray()
                                            ->all();
                                        foreach (Language::langList(
                                            Yii::$app->params['languages'],
                                            true) as $lang) {
                                            $arr[] = Html::a($lang['title'],
                                                Yii::$app->params['frontendHostInfo'] . '/' . $lang['prefix']
                                                . \abdualiym\menu\components\MenuSlugHelper::getSlug(
                                                    $cats[0]['slug'],
                                                    $model->type, $model->type_helper, $lang),['target'=>'_blank']);
                                        }

                                        return implode('<br/>', $arr);
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
