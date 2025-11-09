<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ServiceOrdersProductOrders */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Orders Product Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-orders-product-orders-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Service Orders Product Orders').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'serviceOrder.id',
            'label' => Yii::t('app', 'Service Order'),
        ],
        [
            'attribute' => 'productOrder.id',
            'label' => Yii::t('app', 'Product Order'),
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
        <h4>Orders<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnOrders = [
        ['attribute' => 'id', 'visible' => false],
        'user_id',
        'vendor_details_id',
        'main_category_id',
        'json_details',
        'qty',
        'trans_type',
        'payment_type',
        'sub_total',
        'gst_number',
        'tip_amt',
        'cgst',
        'sgst',
        'tax',
        'processing_charges',
        'service_charge',
        'service_charge_tax_amt',
        'service_charge_w_tax',
        'Subtotal_tax',
        'taxable_total',
        'total_w_tax',
        'payable_amount',
        'balance_amount',
        'is_deleted',
        'cancel_reason',
        'cancel_description',
        'schedule_date',
        'schedule_time',
        'service_instruction',
        'voucher_code',
        'referral_discount_percentage',
        'referral_discount_amount',
        'voucher_amount',
        'voucher_type',
        'payment_mode',
        'payment_status',
        'fill_payment_status',
        'ip_ress',
        'service_address',
        'otp',
        'is_verify',
        'package_order_exist',
        'is_next_visit',
        'parent_order_id',
        'rating_flag',
        'service_type',
        'completed',
        'order_type',
        'service_payment_type',
        'next_visit_required',
        'platform_source',
        'platform',
        'status_step',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->serviceOrder,
        'attributes' => $gridColumnOrders    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>ProductOrders<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnProductOrders = [
        ['attribute' => 'id', 'visible' => false],
        'user_id',
        'vendor_details_id',
        'sub_total',
        'tax_percentage',
        'tax_amount',
        'total_with_tax',
        'payment_status',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->productOrder,
        'attributes' => $gridColumnProductOrders    ]);
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
        'unique_user_id',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'email_is_verified',
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
        'show_referral_tab',
        'signup_type',
        'business_name',
        'gst_number',
        'vendor_store_type',
        'allow_onboarding',
        'main_vendor',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
        'update_profile_count',
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
        'unique_user_id',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'email_is_verified',
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
        'show_referral_tab',
        'signup_type',
        'business_name',
        'gst_number',
        'vendor_store_type',
        'allow_onboarding',
        'main_vendor',
        'is_deleted',
        'info_delete',
        'created_at',
        'updated_at',
        'update_profile_count',
    ];
    echo DetailView::widget([
        'model' => $model->updateUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
    </div>
</div>

