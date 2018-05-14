<?php

/**
 * @var $menu
 * @var $text
 * @var $category
 * @var $menuTypes
 * @var $langList
 */

use yii\helpers\Html;
use yii\helpers\StringHelper;

$pages = $menu->pagesList($text);
$articles = $menu->articlesList($text);
$categories = $menu->categoriesList($category);
$actions = $menu->actionsList(Yii::$app->params['actions']);
?>

<div class="box-group" id="accordion">
    <?php $i = 0;
    foreach ($menuTypes as $key => $type):
        $data = $menu->countByType($key, $articles, $pages, $categories,$actions);
    ?>
        <div class="panel box box-primary">
            <div class="box-header with-border">
                <div class="box-tools pull-right">
                    <?= empty($data)
                        ? ''
                        : Html::tag('span', $data[0],
                            [
                                'data-toggle' => 'tooltip',
                                'data-original-title' => 'total pages',
                                'class' => 'badge bg-yellow',
                            ])
                    ?>
                </div>
                <h4 class="box-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#<?= $key ?>">
                        <?= $type ?>
                    </a>
                </h4>
            </div>
            <div id="<?= $key ?>" class="panel-collapse collapse <?= $i == 0 ? 'in' : ''; ?>">
                <div class="box-body">
                    <?php if ($key == 'link'): ?>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-link"></i></span>
                            <input type="text" class="form-control" value="http://">
                        </div>
                        <br>
                        <?php foreach ($langList as $lang): ?>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-align-center"></i></span>
                                <input type="text" id="<?= $lang['id'] ?>" class="form-control linkInputs"
                                       placeholder="Текст ссылки (<?= $lang['title'] ?>)">
                            </div>
                            <br>
                        <?php endforeach; ?>
                        <button type="submit" data-button="<?= $key ?>" class="btn-block btn btn-primary btn-flat">
                            Добавить в меню
                        </button>
                    <?php else: ?>
                        <ul class="todo-list" style="max-height: 300px;">
                            <?php if (isset($data)): ?>

                                <?php foreach ($data[1] as $k => $page): ?>
                                    <li>
                                        <label style="font-size: 12px;">
                                            <input data-id="<?= $k ?>" type="radio" name="<?= $key ?>">
                                            <?= StringHelper::truncate($page['title'][2], 28) ?>
                                        </label>
                                        <div class="pull-right">
                                            <h6><b>
                                                    <?= isset($page['date']) ? Yii::$app
                                                        ->formatter
                                                        ->asRelativeTime($page['date'])
                                                        : '';
                                                    ?>
                                                </b></h6>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </ul>
                        <br>
                        <button type="submit" data-button="<?= $key ?>" class="btn-block btn btn-primary btn-flat">
                            Добавить в меню
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php $i++; endforeach; ?>
</div>