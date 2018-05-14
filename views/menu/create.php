<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model abdualiym\menu\entities\Menu */

$this->title = 'Добавить меню';
$this->params['breadcrumbs'][] = ['label' => 'Меню', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
