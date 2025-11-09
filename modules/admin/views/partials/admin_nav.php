<?php

use app\modules\admin\widgets\Menu;
use yii\helpers\Html;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


<?php echo Menu::widget([
    'options' => [
        'class'          => 'nav nav-pills nav-sidebar flex-column text-capitalize',
        'data-widget'    => 'treeview',
        'role'           => 'menu',
        'data-accordion' => 'false',
    ],
    'items'   => [
        [
            'label'  => 'Dashboard',
            'icon'   => 'fas fa-tachometer-alt',
            'url'    => ['/admin/dashboard'],
            'active' => Yii::$app->controller->id === 'dashboard',
        ],
        [
            'label'  => 'Users',
            'icon'   => 'fas fa-users',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['users']),
            'items'  => [
                [
                    'label'   => 'All Users',
                    'url'     => ['/admin/users'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'temporary Users',
                    'url'     => ['/admin/temporary-users'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendors',
                    'url'     => ['/admin/users/vendor'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Account Managers',
                    'url'     => ['/admin/users/account-manager'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'QA Team',
                    'url'     => ['/admin/users/qa'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Marketing Team',
                    'url'     => ['/admin/users/marketing'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Categories',
            'icon'   => 'fas fa-list',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['main-category', 'service-type', 'sub-category', 'services']),
            'items'  => [
                [
                    'label'   => 'Main Categories',
                    'url'     => ['/admin/main-category'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Service Types',
                    'url'     => ['/admin/service-type'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Sub Categories',
                    'url'     => ['/admin/sub-category'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Services',
                    'url'     => ['/admin/services'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Shops',
            'icon'   => 'fas fa-store',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['vendor-details', 'staff', 'business-images', 'store-timings', 'vendor-earnings', 'vendor-payout', 'bank-details']),
            'items'  => [
                [
                    'label'   => 'Shop Locations',
                    'url'     => ['/admin/vendor-details/shop-locations'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'All Shops',
                    'url'     => ['/admin/vendor-details'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Pending Onboarding Vendors',
                    'url'     => ['/admin/vendor-details/pending-vendors-onboarding'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Staff',
                    'url'     => ['/admin/staff'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Shop Gallery',
                    'url'     => ['/admin/business-images'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Shop Timings',
                    'url'     => ['/admin/store-timings'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Store Break Timings',
                    'url'     => ['/admin/store-timings-has-brakes'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendor Earnings',
                    'url'     => ['/admin/vendor-earnings'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendor Payouts',
                    'url'     => ['/admin/vendor-payout'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Bank Details',
                    'url'     => ['/admin/bank-details'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Subscriptions',
            'icon'   => 'fas fa-credit-card',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['subscriptions', 'vendor-subscriptions']),
            'items'  => [
                [
                    'label'   => 'Subscription Plans',
                    'url'     => ['/admin/subscriptions'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendor Subscriptions',
                    'url'     => ['/admin/vendor-subscriptions'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendor Payments',
                    'url'     => ['/admin/vendor-subscriptions/vendor-payments'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Wallet',
            'icon'   => 'fas fa-wallet',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['wallet']),
            'items'  => [
                [
                    'label'  => 'Wallet',
                    'icon'   => 'fas fa-wallet',
                    'url'    => ['/admin/wallet'],
                    'active' => Yii::$app->controller->id === 'wallet',

                ],

            ],
        ],
        [
            'label'  => 'Bypass Numbers',
            'icon'   => 'fas fa-phone',
            'url'    => ['/admin/bypass-numbers'],
            'active' => Yii::$app->controller->id === 'bypass-numbers',
        ],
        [
            'label'  => 'Expenses',
            'icon'   => 'fas fa-receipt',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['Add Expenses', 'Add Expenses Types']),
            'items'  => [
                [
                    'label'  => 'Add Expenses',
                    'url'    => ['/admin/vendor-expenses'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'  => 'Add Expenses Types',
                    'url'    => ['/admin/vendor-expenses-types'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

            ],

        ],

        [
            'label'  => 'Orders',
            'icon'   => 'fas fa-shopping-cart',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['orders', 'cart']),
            'items'  => [
                [
                    'label'   => 'All Orders',
                    'url'     => ['/admin/orders'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'New Orders',
                    'url'     => ['/admin/orders/new-orders'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Accepted Orders',
                    'url'     => ['/admin/orders/accepted-orders'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Service Started',
                    'url'     => ['/admin/orders/service-started'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Expired Orders',
                    'url'     => ['/admin/orders/expired-orders'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Service Completed',
                    'url'     => ['/admin/orders/service-completed'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Cart',
                    'url'     => ['/admin/cart'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Reels',
            'icon'   => 'fas fa-video',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['reels', 'reel-share-counts', 'reels-likes', 'reels-view-counts', 'reel-tags']),
            'items'  => [
                [
                    'label'   => 'Reels',
                    'url'     => ['/admin/reels'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Share Counts',
                    'url'     => ['/admin/reel-share-counts'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Likes',
                    'url'     => ['/admin/reels-likes'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'View Counts',
                    'url'     => ['/admin/reels-view-counts'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Tags',
                    'url'     => ['/admin/reel-tags'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Coupons',
            'icon'   => 'fas fa-tag',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['coupon', 'coupon-vendor']),
            'items'  => [
                [
                    'label'   => 'Coupons',
                    'url'     => ['/admin/coupon'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Vendor Coupons',
                    'url'     => ['/admin/coupon-vendor'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Service Zones',
            'icon'   => 'fas fa-map-marker-alt',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['city', 'service-pin-code']),
            'items'  => [
                [
                    'label'   => 'Cities',
                    'url'     => ['/admin/city'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Service Pin Codes',
                    'url'     => ['/admin/service-pin-code'],
                    'options' => ['class' => 'font-weight-bold'],
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
            'label'  => 'Web Settings',
            'icon'   => 'fas fa-cog',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['web-setting']),
            'items'  => [
                [
                    'label'   => 'CMS Settings',
                    'url'     => ['/admin/web-setting/cms'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],
        [
            'label'  => 'Banners',
            'icon'   => 'fas fa-image',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['banner', 'banner-recharges', 'banner-charge-logs', 'banner-timings']),
            'items'  => [
                [
                    'label'   => 'Banners',
                    'url'     => ['/admin/banner'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

            ],
        ],
        [
            'label'  => 'Quizzes',
            'icon'   => 'fas fa-question-circle',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['quizzes', 'quiz-answers']),
            'items'  => [
                [
                    'label'   => 'Quizzes',
                    'url'     => ['/admin/quizzes'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

            ],
        ],
        [
            'label'  => 'Menus',
            'icon'   => 'fas fa-th-large',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['quizzes', 'quiz-answers']),
            'items'  => [
                [
                    'label'   => 'Menu',
                    'url'     => ['/admin/menus'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

                [
                    'label'   => 'vendor has menus',
                    'url'     => ['/admin/vendor-has-menus'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

                [
                    'label'   => 'roles',
                    'url'     => ['/admin/roles'],
                    'options' => ['class' => 'font-weight-bold'],
                ],

            ],
        ],

        [
            'label'  => 'Whatsapp',
            'icon'   => 'bi bi-whatsapp',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['whatsapp-conversation-flows', 'whatsapp-templates']),
            'items'  => [
                [
                    'label'   => 'Whatsapp Conversation',
                    'icon'    => 'bi bi-whatsapp',
                    'url'     => ['/admin/whatsapp-conversation-flows'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Whatsapp Templates',
                    'icon'    => 'bi bi-whatsapp',
                    'url'     => ['/admin/whatsapp-templates'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Whatsapp Aisensy Templates',
                    'icon'    => 'bi bi-whatsapp',
                    'url'     => ['/admin/aisensy-templates'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Aisensy Webhooks',
                    'icon'    => 'bi bi-whatsapp',
                    'url'     => ['/admin/aisensy-webhooks'],
                    'options' => ['class' => 'font-weight-bold'],
                ],


            ],
        ],
        [
            'label'  => 'MemberShips',
            'icon'   => 'bi bi-star',
            'url'    => ['/admin/member-ships'],
            'active' => Yii::$app->controller->id === 'member-ships',
        ],

        [
            'label'   => 'Cancellation Policy',
            'icon'    => 'bi bi-file-text',
            'url'     => ['/admin/cancellation-policy'],
            'options' => ['class' => 'font-weight-bold'],
        ],




        [
            'label'  => 'Products Management',
            'icon'   => 'bi bi-shop',
            'url'    => '#',
            'active' => in_array(Yii::$app->controller->id, ['Brands', 'Hierarchy']),
            'items'  => [
                [
                    'label'   => 'Brands',
                    'icon'    => 'bi bi-tags',
                    'url'     => ['/admin/brands'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'SKU',
                    'icon'    => 'bi bi-upc-scan',
                    'url'     => ['/admin/sku'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Products',
                    'icon'    => 'bi bi-box',
                    'url'     => ['/admin/products'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Categorys',
                    'icon'    => 'bi bi-box',
                    'url'     => ['/admin/product-categories'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Orders',
                    'icon'    => 'bi bi-receipt',
                    'url'     => ['/admin/product-orders'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Services',
                    'icon'    => 'bi bi-tools',
                    'url'     => ['/admin/product-services'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Order Items',
                    'icon'    => 'bi bi-list-check',
                    'url'     => ['/admin/product-order-items'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Order Discounts',
                    'icon'    => 'bi bi-percent',
                    'url'     => ['/admin/product-orders-has-discounts'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Order Items Assigned Discounts',
                    'icon'    => 'bi bi-ticket-perforated',
                    'url'     => ['/admin/product-order-items-assigned-discounts'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Product Types',
                    'icon'    => 'bi bi-grid',
                    'url'     => ['/admin/products'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Units',
                    'icon'    => 'bi bi-rulers',
                    'url'     => ['/admin/units'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
                [
                    'label'   => 'Hierarchy',
                    'icon'    => 'bi bi-diagram-3',
                    'url'     => ['/admin/hierarchy'],
                    'options' => ['class' => 'font-weight-bold'],
                ],
            ],
        ],

    ],

]) ?>