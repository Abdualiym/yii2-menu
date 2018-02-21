<?php

use yii\helpers\Url;
$parent_left = 0;
$parent_right = 0;
$parent_isset = 0; // 1 if parent not empty

$menu = array();
$default_menu_key = 0;
?>
<ul>
    <?php foreach ($children as $key => $child) {

        if ($child['depth'] == 1) {

                $default_menu_key = $key;
                $class = 'active';
                $parent_left = $child['lft'];
                $parent_right = $child['rgt'];
                if (($child['rgt'] - $child['lft']) > 1) $parent_isset = 1;



            if ($child['type'] == 'link') {
                $url = Url::to($child['type_helper']);
            } else {
                $url = Url::to(['/site/slug-render', 'slug' => $child['type_helper']]);
            }

            $menu[] = array($class, $url, $child['translate'][0]['title'], $child['lft'], $child['rgt']);

        }
    }

    foreach ($menu as $element) { ?>
        <li class="<?php echo $element[0]; ?>"><a href="<?php echo $element[1]; ?>"><?php echo $element[2]; ?></a>
            <ul>
                <?php foreach ($children as $child2){
                    if($child2['depth'] == 2 && $child2['lft']  > $element[3] && $child2['lft']  < $element[4]){
                    ?>
                    <li><a href="

                        <?php if ($child2['type'] == 'link'): ?>

                        <?= Url::to($child2['type_helper']) ?>

                        <?php else: ?>

                        <?= Url::to(['/site/slug-render', 'slug' => $child2['type_helper']]) ?>

                        <?php endif; ?>

                    "><?php echo $child2['translate'][0]['title'] ?></a></li>
                <?php }} ?>
            </ul>
        </li>
    <?php } ?>
</ul>