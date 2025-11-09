<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\CouponsApplied */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coupons Applieds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="coupons-applied-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Coupons Applied').' '. Html::encode($this->title) ?></h2>
        </div>
        <div class="col-sm-3" style="margin-top: 15px">
            
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                          <?php  if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN){ ?>
             <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ])
            ?>  
             <?php  } ?>
        </div>
    </div>
    </div>
    </div>
    <div class="card">
       <div class="card-body">

    <div class="row">
<?php 
    $gridColumn = [
        ['attribute' => 'id', 'visible' => false],
        [
            'attribute' => 'order.id',
            'label' => Yii::t('app', 'Order'),
        ],
        [
            'attribute' => 'cart.id',
            'label' => Yii::t('app', 'Cart'),
        ],
        [
            'attribute' => 'coupon.name',
            'label' => Yii::t('app', 'Coupon'),
        ],
        'status',
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumn
    ]);
?>
</div>
</div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Cart<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnCart = [
        ['attribute' => 'id', 'visible' => false],
        'user_id',
        'vendor_details_id',
        'quantity',
        'amount',
        'tip',
        'wallet',
        'service_instructions',
        'details',
        'cgst',
        'sgst',
        'coupon_code',
        'coupon_discount',
        'coupon_applied_id',
        'service_fees',
        'other_charges',
        'status',
        'service_address',
        'service_time',
        'service_date',
        'type_id',
    ];
    echo DetailView::widget([
        'model' => $model->cart,
        'attributes' => $gridColumnCart    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>User<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUser = [
        ['attribute' => 'id', 'visible' => false],
        'username',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'first_name',
        'last_name',
        'lat',
        'lng',
        'contact_no',
        'alternative_contact',
        'date_of_birth',
        'gender',
        'description',
        'address',
        'location',
        'profile_image',
        'user_role',
        'oauth_client_user_id',
        'oauth_client',
        'access_token',
        'device_token',
        'device_type',
        'status',
        'online_status',
        'account_type',
        'referral_code',
        'referral_id',
        'signup_type',
        'business_name',
        'gst_number',
        'is_tiffin_box',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
    ];
    echo DetailView::widget([
        'model' => $model->createUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>User<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUser = [
        ['attribute' => 'id', 'visible' => false],
        'username',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'first_name',
        'last_name',
        'lat',
        'lng',
        'contact_no',
        'alternative_contact',
        'date_of_birth',
        'gender',
        'description',
        'address',
        'location',
        'profile_image',
        'user_role',
        'oauth_client_user_id',
        'oauth_client',
        'access_token',
        'device_token',
        'device_type',
        'status',
        'online_status',
        'account_type',
        'referral_code',
        'referral_id',
        'signup_type',
        'business_name',
        'gst_number',
        'is_tiffin_box',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
    ];
    echo DetailView::widget([
        'model' => $model->updateUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Coupon<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnCoupon = [
        ['attribute' => 'id', 'visible' => false],
        'name',
        'description',
        'code',
        'discount',
        'max_discount',
        'min_cart',
        'max_use',
        'max_use_of_coupon',
        'start_date',
        'end_date',
        'is_global',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->coupon,
        'attributes' => $gridColumnCoupon    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Orders<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnOrders = [
        ['attribute' => 'id', 'visible' => false],
        'shop_id',
        'vendor_details_id',
        'json_details',
        'qty',
        'trans_type',
        'payment_type',
        'sub_total',
        'tip_amt',
        'tax',
        'processing_charges',
        'service_charge',
        'taxable_total',
        'total_w_tax',
        'status',
        'cancel_reason',
        'cancel_description',
        'schedule_date',
        'schedule_time',
        'service_instruction',
        'voucher_code',
        'voucher_amount',
        'voucher_type',
        'payment_status',
        'ip_ress',
        'service_address',
        'otp',
        'cgst',
        'sgst',
        'is_verify',
        'service_type',
    ];
    echo DetailView::widget([
        'model' => $model->order,
        'attributes' => $gridColumnOrders    ]);
    ?>
    </div>
    </div>
</div>

