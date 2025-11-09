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

                        'label' => 'Shop',

                        'icon' => 'fas fa-store-alt',

                        'url' => '#',

                        'items' => [



                            [

                                'label' => 'Shops',

                                'url' => ['/admin/vendor-details'],

                                'options' => ['class' => 'bold'],

                            ],


                            [

                                'label' => 'Pending Onboarding Vendors',

                                'url' => ['/admin/vendor-details/pending-vendors-onboarding'],

                                'options' => ['class' => 'bold'],

                            ],

                            [

                                'label' => 'Staff',

                                'url' => ['/admin/staff'],

                                'options' => ['class' => 'bold'],

                            ],



                            [

                                'label' => 'shop gallery',

                                'url' => ['/admin/business-images'],

                                'options' => ['class' => 'bold'],

                            ],



                            [

                                'label' => 'shop timing',

                                'url' => ['/admin/store-timings'],

                                'options' => ['class' => 'bold'],

                            ],





                            [

                                'label' => 'vendor earnings',

                                'url' => ['/admin/vendor-earnings'],

                                'options' => ['class' => 'bold'],

                            ],







                            [

                                'label' => 'vendor payout',

                                'url' => ['/admin/vendor-payout'],

                                'options' => ['class' => 'bold'],

                            ],




                            [

                                'label' => 'bank details',

                                'url' => ['/admin/bank-details'],

                                'options' => ['class' => 'bold'],

                            ],







                        ],

                    ],







          



                 












               


         

            







            






          






                ],

            ]) ?>

	