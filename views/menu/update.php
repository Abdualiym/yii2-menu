<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model domain\modules\menu\entities\Menu */

$this->title = 'Update Menu: ' . $menu->translate[0]['title'];
$this->params['breadcrumbs'][] = ['label' => 'Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $menu->translate[0]['title'], 'url' => ['view', 'id' => $menu->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="menu-update">

    <?= $this->render('_form', [
        'model' => $model,
        'menu' => $menu,
    ]) ?>

</div>
