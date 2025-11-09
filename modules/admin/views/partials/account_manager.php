<?php

use app\modules\admin\widgets\Menu;
use yii\helpers\Html;

?>

<?= Menu::widget([
    'options' => [
        'class' => 'nav nav-pills nav-sidebar flex-column text-capitalize',
        'data-widget' => 'treeview',
        'role' => 'menu',
        'data-accordion' => 'false',
    ],
    'items' => [
        [
            'label' => 'Vendor Payments',
            'icon' => 'fas fa-store', // FontAwesome icon
            'url' => ['/admin/vendor-subscriptions/vendor-payments'],
        ],
      
    ],
]) ?>
