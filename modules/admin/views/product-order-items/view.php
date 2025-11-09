<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductOrderItems */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Order Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-order-items-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Product Order Items').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'productOrder.id',
            'label' => Yii::t('app', 'Product Order'),
        ],
        [
            'attribute' => 'product.id',
            'label' => Yii::t('app', 'Product'),
        ],
        'quantity',
        'units',
        'mrp_price',
        'selling_price',
        'sub_total',
        'tax_percentage',
        'tax_amount',
        'total_with_tax',
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
        <h4>Products<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnProducts = [
        ['attribute' => 'id', 'visible' => false],
        'vendor_details_id',
        'sku_id',
        'discount_allowed',
        'minimum_stock',
        'units_id',
        'supplier_id',
        'batch_number',
        'ean_code',
        'purchase_date',
        'mrp_price',
        'selling_price',
        'purchased_price',
        'expire_date',
        'units_received',
        'received_units_id',
        'invoice_number',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->product,
        'attributes' => $gridColumnProducts    ]);
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
<?php
if($providerProductOrderItemsAssignedDiscounts->totalCount){
    $gridColumnProductOrderItemsAssignedDiscounts = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
                        'discount_percentage',
            'discount_amount',
            [
                'attribute' => 'coupon.name',
                'label' => Yii::t('app', 'Coupon')
            ],
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerProductOrderItemsAssignedDiscounts,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-product-order-items-assigned-discounts']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Product Order Items Assigned Discounts')),
        ],
        'export' => false,
        'columns' => $gridColumnProductOrderItemsAssignedDiscounts
    ]);
}

?>
</div>
</div>
</div>

</div>

