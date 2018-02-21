<?php

use yii\helpers\Url;

$pathInfo = Yii::$app->request->pathInfo;
$path = explode('/', $pathInfo);
$check = array_shift($path);

$parent_left = 0;
$parent_right = 0;
$parent_isset = 0; // 1 if parent not empty

$menu = array();
$default_menu_key = 0;

/* @var $children domain\modules\menu\widgets\Navigations */
?>
<nav class="main-nav">
    <ul>
        <?php foreach ($children as $key => $child) {

            if ($child['depth'] == 1) {
                if ($child['type_helper'] == $check || ($pathInfo == '' && $key == 0)) {
                    $default_menu_key = $key;
                    $class = 'active';
                    $parent_left = $child['lft'];
                    $parent_right = $child['rgt'];
                    if (($child['rgt'] - $child['lft']) > 1) $parent_isset = 1;

                } else {
                    $class = '';
                }

                if ($child['type'] == 'link') {
                    $url = Url::to($child['type_helper']);
                } else {
                    $url = Url::to(['/site/slug-render', 'slug' => $child['type_helper']]);
                }

                $menu[] = array($class, $url, $child['translate'][0]['title'], $child['lft'], $child['rgt']);

            }
        }

        if ($default_menu_key == 0) {
            $menu[0] = array('active', $menu[0][1], $menu[0][2], $menu[0][3], $menu[0][4]);
            $parent_isset = 1;
            $parent_left = $menu[0][3];
            $parent_right = $menu[0][4];
        }

        foreach ($menu as $element) { ?>
            <li class="<?php echo $element[0]; ?> has-submenu"><a href="<?php echo $element[1]; ?>"><?php echo $element[2]; ?></a>
                <?php

                if ($parent_isset == 1) {
                    echo '<ul class="submenu">';
                    foreach ($children as $key => $child) {

                        if ($child['depth'] == 2 && $child['lft'] > $parent_left && $child['rgt'] < $parent_right) { ?>
                            <li>
                                <a href="
                            <?php if ($child['type'] == 'link' && $child['type'] == 'action'): ?>
                                <?= Url::to($child['type_helper']) ?>
                            <?php else: ?>
                            <?= Url::to(['/site/slug-render', 'slug' => $child['type_helper']]) ?>
                            <?php endif; ?>
                        "><?= $child['translate'][0]['title'] ?></a>
                            </li>
                        <?php }
                    }

                    echo '</ul>';
                }
                ?>
            </li>
        <?php }


        ?>
        <li><a class="search-button" href="#"><i class="fa fa-search"></i> <?= Yii::t('app','Search') ?></a></li>

    </ul>
</nav>
