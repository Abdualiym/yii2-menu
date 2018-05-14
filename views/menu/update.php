<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model abdualiym\menu\entities\Menu */
/* @var $menu abdualiym\menu\entities\Menu */

$this->title = $menu->translations[0]['title'];
$this->params['breadcrumbs'][] = ['label' => 'Меню', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $menu->translations[0]['title'], 'url' => ['view', 'id' => $menu->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="menu-update">

    <?= $this->render('_form', [
        'model' => $model,
        'menu' => $menu,
    ]) ?>

</div>
