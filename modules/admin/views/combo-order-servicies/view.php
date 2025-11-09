<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ComboOrderServicies */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Combo Order Servicies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="combo-order-servicies-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Combo Order Servicies').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'comboPackage.title',
            'label' => Yii::t('app', 'Combo Package'),
        ],
        [
            'attribute' => 'service.id',
            'label' => Yii::t('app', 'Service'),
        ],
        'price',
        'total_price',
        'qty',
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
        'status',
        'is_deleted',
        'cancel_reason',
        'cancel_description',
        'schedule_date',
        'schedule_time',
        'service_instruction',
        'voucher_code',
        'voucher_amount',
        'voucher_type',
        'payment_mode',
        'payment_status',
        'fill_payment_status',
        'ip_ress',
        'service_address',
        'otp',
        'is_verify',
        'is_next_visit',
        'service_type',
        'next_visit_required',
    ];
    echo DetailView::widget([
        'model' => $model->order,
        'attributes' => $gridColumnOrders    ]);
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
        'unique_user_id',
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
        <h4>ComboPackages<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnComboPackages = [
        ['attribute' => 'id', 'visible' => false],
        'vendor_details_id',
        'title',
        'price',
        'time',
        'is_home_visit',
        'is_walk_in',
        'service_for',
        'description',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->comboPackage,
        'attributes' => $gridColumnComboPackages    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Services<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnServices = [
        ['attribute' => 'id', 'visible' => false],
        'vendor_details_id',
        'sub_category_id',
        'store_service_type_id',
        'service_name',
        'slug',
        'image',
        'description',
        'small_description',
        'original_price',
        'standard_price',
        'discount_price',
        'max_per_day_services',
        'price',
        'duration',
        'home_visit',
        'walk_in',
        'type',
        'service_for',
        'benefits',
        'precautions_recommendation',
        'why_choose_service',
        'why_choose_category',
        'additional_notes',
        'techinique_points',
        'is_parent_service',
        'parent_id',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->service,
        'attributes' => $gridColumnServices    ]);
    ?>
    </div>
    </div>
</div>

