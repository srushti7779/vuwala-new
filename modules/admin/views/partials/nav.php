<?php

use app\modules\admin\widgets\Menu;
use yii\helpers\Html;
use app\models\User;
use app\modules\admin\models\WebSetting;
use yii\helpers\Url;

$setting = new WebSetting();
$app_name = $setting->getSettingBykey('app_name');

?>

<style>
    /* Custom Sidebar Styles */
    .main-sidebar {
        background: linear-gradient(to right, #693f167b, #785e0c), #2E3440 !important;
    }



    .brand-link {
        background: linear-gradient(to right, #693f167b, #785e0c), #3B4252 !important;
        color: #D8DEE9 !important;
        /* Light text color for better contrast */
    }

    .user-panel .image i {
        color: #610909 !important;
        font-size: 2rem;
    }

    .user-panel .info a {
        color: #f5e9dc !important;
        font-weight: bold;
    }

    .user-panel .info a:hover {
        text-decoration: underline;
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link {
        color: #ECEFF4;
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
        background-color: #bd995e;
        color: black;
        /* background-color: linear-gradient(to right, #693f167b, #785e0c), #2E3440 !important; */
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item.menu-open>.nav-link {
        background-color: #bd995e;
        color: black;
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link>i {
        color: #f5e9dc;
    }

    /* Adjust the font size and padding for better readability */
    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link {
        font-size: 15px;
        padding: 12px;
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
        background-color: #bd995e;
        color: #2E3440;
    }

    .nav-sidebar .nav-item .nav-treeview>.nav-item>.nav-link {
        background-color: #bd995e;
        color: black;
    }

    .nav-sidebar .nav-item .nav-treeview>.nav-item>.nav-link:hover {
        background-color: #bd995e;
        color: #88C0D0;
    }

    .d-block {
        color: #f5e9dc !important;
    }

    .btn-default.dropdown-toggle {
        background-color: lightgrey !important;
    }
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #2E3440;">

    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl; ?>" class="brand-link text-center" style="background-color: #3B4252;">
        <span class="brand-text font-weight-light" style="color: #D8DEE9;"><?= Html::encode($app_name); ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
    <div class="image">
        <i class="fas fa-user-tie img-circle elevation-2" style="color: #88C0D0; font-size: 1.5rem;"></i>
    </div>
    <div class="info flex-grow-1">
        <a href="#" class="d-block" style="color: #bd995e;"><?= Yii::$app->user->identity->username ?></a>
    </div>
    <div class="info text-right" style="margin-right: 10px;">
        <a href="<?= Url::base() . '/admin/users/update?id=' . Yii::$app->user->identity->id ?>" title="Edit Profile" style="color: #bd995e; margin-right: 10px;">
            <i class="fas fa-edit"></i>
        </a>
    </div>
    <div>
        <?= Html::a(
            '<i class="fa fa-power-off"></i>',
            ['/auth/logout'],
            [
                'title' => 'Sign Out',
                'data-method' => 'post',
                'style' => 'color: #88C0D0;',
            ]
        ); ?>
    </div>
</div>
       


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN) { ?>
                <?= Yii::$app->controller->renderPartial('/partials/admin_nav') ?>
            <?php } else if (\Yii::$app->user->identity->user_role == User::ROLE_VENDOR) { ?>
                <?= Yii::$app->controller->renderPartial('/partials/vendor_nav') ?>
            <?php } else if(\Yii::$app->user->identity->user_role == User::ROLE_ACCOUNT_MANAGER) { ?>
                <?= Yii::$app->controller->renderPartial('/partials/account_manager') ?>
            <?php }
            
            else if(\Yii::$app->user->identity->user_role == User::ROLE_QA){
                echo Yii::$app->controller->renderPartial('/partials/qa');

            }
            
            ?>
            
       
        </nav>

    </div>
    <!-- /.sidebar -->
</aside>