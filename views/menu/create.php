<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model domain\modules\menu\entities\Menu */

$this->title = 'Создать меню';
$this->params['breadcrumbs'][] = ['label' => 'Меню', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
