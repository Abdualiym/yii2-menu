<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model abdualiym\menu\entities\Menu */
/* @var $menu abdualiym\menu\entities\Menu */

$this->title = 'Update Menu: ' . $menu->translations[0]['title'];
$this->params['breadcrumbs'][] = ['label' => 'Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $menu->translations[0]['title'], 'url' => ['view', 'id' => $menu->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="menu-update">

    <?= $this->render('_form', [
        'model' => $model,
        'menu' => $menu,
    ]) ?>

</div>
