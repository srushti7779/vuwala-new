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
            'label' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'url' => ['/admin/dashboard'],
            'active' => 'dashboard' === Yii::$app->controller->id,
        ],
        [
            'label' => 'Vendor Profile',
            'icon' => 'fas fa-cog',
            'url' => '#',
            'items' => [
                [
                    'label' => 'My Profile',
                    'icon' => 'fas fa-user-circle',
                    'url' => ['/admin/profile/index'],
                    'active' => Yii::$app->controller->id === 'profile',
                ],
            ],
        ],
        [
            'label' => 'Shop',
            'icon' => 'fas fa-store-alt',
            'url' => '#',
            'items' => [
                [
                    'label' => 'Shop Locations',
                    'url' => ['/admin/vendor-details/shop-locations'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Shops',
                    'url' => ['/admin/vendor-details'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Staff',
                    'url' => ['/admin/staff'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Shop Timing',
                    'url' => ['/admin/store-timings'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Vendor Earnings',
                    'url' => ['/admin/vendor-earnings'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Vendor Payout',
                    'url' => ['/admin/vendor-payout'],
                    'options' => ['class' => 'bold'],
                ],
            ],
        ],
        [
            'label' => 'Order Management',
            'icon' => 'fas fa-shopping-cart',
            'url' => '#',
            'items' => [
                [
                    'label' => 'All Orders',
                    'url' => ['/admin/orders'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'New Orders',
                    'url' => ['/admin/orders/new-orders'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Create Orders',
                    'icon' => 'fas fa-shopping-cart',
                    'url' => ['/admin/orders/create-by-vendor'],
                    'active' => Yii::$app->controller->id === 'orders' && Yii::$app->controller->action->id === 'create-by-vendor',
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label' => 'Accepted Orders',
                    'url' => ['/admin/orders/accepted-orders'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Service Started',
                    'url' => ['/admin/orders/service-started'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Expired Orders',
                    'url' => ['/admin/orders/expired-orders'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Service Completed',
                    'url' => ['/admin/orders/service-completed'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Cart',
                    'url' => ['/admin/cart'],
                    'options' => ['class' => 'bold'],
                ],
                [
                    'label' => 'Transactions',
                    'url' => ['/admin/wallet'],
                    'options' => ['class' => 'bold'],
                ],
            ],
        ],
        [
            'label' => 'Reel Management',
            'icon' => 'fas fa-video',
            'url' => '#',
            'items' => [
                [
                    'label' => 'Reels',
                    'url' => ['/admin/reels'],
                    'options' => ['class' => 'bold'],
                ],
            ],
        ],
        [
            'label' => 'Coupon Management',
            'icon' => 'fas fa-tag',
            'url' => '#',
            'items' => [
                [
                    'label' => 'Coupon',
                    'url' => ['/admin/coupon'],
                    'options' => ['class' => 'bold'],
                ],
            ],
        ],
        [
            'label'  => 'Support Tickets',
            'icon'   => 'fas fa-headset',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['SupportTickets', 'OrderComplaintsTicket']),
            'items'  => [
                [
                    'label'   => 'Support Tickets',
                    'icon'    => 'fas fa-headset',
                    'url'     => ['/admin/support-tickets'],
                    'active'  => Yii::$app->controller->id === 'support-tickets',
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'OrderComplaintsTicket',
                    'icon'    => 'fas fa-headset',
                    'url'     => ['/admin/order-complaints'],
                    'active'  => Yii::$app->controller->id === 'support-tickets',
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],

        ],
        [
            'label'  => 'Brands',
            'icon'   => 'fas fa-headset',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['Vendor Brand']),
            'items' => [
                 [
                    'label' => 'Vendor Brands',
                    'url' => ['/admin/vendor-brands'],
                    'options' => ['class' => 'bold'],
                ],

            ]
        ],
       
    ],
]) ?>
