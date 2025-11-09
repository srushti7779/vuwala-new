<?php

use app\modules\admin\models\base\VendorDetails;
use app\modules\admin\models\User;
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\admin\models\Notification;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">
    <nav class="navbar navbar-expand navbar-white navbar-light border-bottom">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>

                    <span class="badge badge-warning navbar-badge">
                        <?php
                        $userId = Yii::$app->user->id;
                        $vendor_details = VendorDetails::find()->where(["user_id" => $userId])->one();

                        $generalCount = 0;
                        $orderCount = 0;
                        $notifications = [];

                        if (User::isAdmin()) {
                            $generalCount = Notification::find()
                                ->where(['mark_read' => 0])
                                ->andWhere(['order_id' => null])
                                ->count();

                            $orderCount = Notification::find()
                                ->where(['mark_read' => 0])
                                ->andWhere(['IS NOT', 'order_id', null])
                                ->count();

                            $notifications = Notification::find()
                                ->where(['mark_read' => 0])
                                ->orderBy(['id' => SORT_DESC])
                                ->limit(5)
                                ->all();
                        } elseif (User::isVendor() && $vendor_details) {
                            $generalCount = Notification::find()
                                ->where([
                                    'mark_read' => 0,
                                    'vendor_details_id' => $vendor_details->id,
                                    'order_id' => null
                                ])
                                ->count();

                            $orderCount = Notification::find()
                                ->where([
                                    'mark_read' => 0,
                                    'vendor_details_id' => $vendor_details->id,
                                ])
                                ->andWhere(['IS NOT', 'order_id', null])
                                ->count();

                            $notifications = Notification::find()
                                ->where(['mark_read' => 0, 'vendor_details_id' => $vendor_details->id])
                                ->orderBy(['id' => SORT_DESC])
                                ->limit(5)
                                ->all();
                        }

                        $totalCount = $generalCount + $orderCount;
                        echo $totalCount;
                        ?>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        <?= $totalCount ?> Notifications 
                        (<?= $generalCount ?> General / <?= $orderCount ?> Order)
                    </span>

                    <?php if ($totalCount > 0): ?>
                        <?php foreach ($notifications as $noty): ?>
                            <?php
                                $url = $noty->order_id
                                    ? Url::to(['/admin/orders/view', 'id' => $noty->order_id])
                                    : Url::to(['/admin/notification/view', 'id' => $noty->id, 'mark' => 1]);
                            ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= $url ?>" class="dropdown-item">
                                <i class="<?= Html::encode($noty->icon) ?> mr-2"></i> <?= Html::encode($noty->title) ?>
                                <span class="float-right text-muted text-sm"><?= Html::encode($noty->created_date) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dropdown-item text-center">No Notifications</div>
                    <?php endif; ?>

                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/admin/notification/index']) ?>" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>
        </ul>
    </nav>
</header>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdn.rawgit.com/admsev/jquery-play-sound/master/jquery.playSound.js"></script>

<script>
    function getNotification() {
        $.ajax({
            type: "GET",
            url: "<?= Url::toRoute(['notification/get-notification']) ?>",
            cache: false,
            success: function(data) {
                console.log(data);
                if (data.count > 0) {
                    $.playSound("http://www.soundjay.com/misc/sounds/bell-ringing-01.mp3");
                    $('.noty-count').html(data.count);
                    $.each(data.detail, function(key, val) {
                        var html = getNotyHtml(val);
                        $('.notification-data').append(html);
                        getgrowl(val);
                    });
                }
            }
        });
    }

    function getgrowl(msg) {
        $.ajax({
            type: "POST",
            url: "<?= Url::toRoute(['notification/growl']) ?>",
            cache: false,
            data: { msg: msg },
            success: function(data) {
                $.notify(data.growl);
            },
            error: function(xhr, status, error) {
                alert(error);
            }
        });
    }

    function getNotyHtml(data) {
        var html = '';
        html += '<li><a href="' + data.url + '"> <i class="' + data.icon + '"></i> ' + data.title + '</a></li>';
        return html;
    }
</script>
