<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Units */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Units'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="units-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Units').' '. Html::encode($this->title) ?></h2>
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
        'unit_name',
        'category',
        'status',
        'uom_type',
        'factor',
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
<?php
if($providerProductServices->totalCount){
    $gridColumnProductServices = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'service.id',
                'label' => Yii::t('app', 'Service')
            ],
            [
                'attribute' => 'product.id',
                'label' => Yii::t('app', 'Product')
            ],
                        'quantity',
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerProductServices,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-product-services']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Product Services')),
        ],
        'export' => false,
        'columns' => $gridColumnProductServices
    ]);
}

?>
</div>
</div>
</div>

    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerProductServicesUsed->totalCount){
    $gridColumnProductServicesUsed = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'service.id',
                'label' => Yii::t('app', 'Service')
            ],
            [
                'attribute' => 'order.id',
                'label' => Yii::t('app', 'Order')
            ],
            [
                'attribute' => 'product.id',
                'label' => Yii::t('app', 'Product')
            ],
            'quantity',
                        'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerProductServicesUsed,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-product-services-used']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Product Services Used')),
        ],
        'export' => false,
        'columns' => $gridColumnProductServicesUsed
    ]);
}

?>
</div>
</div>
</div>

    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerProducts->totalCount){
    $gridColumnProducts = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'vendorDetails.id',
                'label' => Yii::t('app', 'Vendor Details')
            ],
            [
                'attribute' => 'sku.id',
                'label' => Yii::t('app', 'Sku')
            ],
            'discount_allowed',
            'minimum_stock',
                        [
                'attribute' => 'supplier.id',
                'label' => Yii::t('app', 'Supplier')
            ],
            'batch_number',
            'ean_code',
            'purchase_date',
            'mrp_price',
            'selling_price',
            'purchased_price',
            'expire_date',
            'units_received',
                        'invoice_number',
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerProducts,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-products']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Products')),
        ],
        'export' => false,
        'columns' => $gridColumnProducts
    ]);
}

?>
</div>
</div>
</div>

    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerSku->totalCount){
    $gridColumnSku = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'vendorDetails.id',
                'label' => Yii::t('app', 'Vendor Details')
            ],
            'sku_code',
            'product_name',
            [
                'attribute' => 'brand.id',
                'label' => Yii::t('app', 'Brand')
            ],
            'ean_code',
            [
                'attribute' => 'category.title',
                'label' => Yii::t('app', 'Category')
            ],
            [
                'attribute' => 'serviceType.id',
                'label' => Yii::t('app', 'Service Type')
            ],
            [
                'attribute' => 'storeServiceType.id',
                'label' => Yii::t('app', 'Store Service Type')
            ],
            [
                'attribute' => 'productType.id',
                'label' => Yii::t('app', 'Product Type')
            ],
            'tax_rate',
            're_order_level_for_alerts',
                        'min_quantity_need',
            'description:ntext',
            'image',
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerSku,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-sku']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Sku')),
        ],
        'export' => false,
        'columns' => $gridColumnSku
    ]);
}

?>
</div>
</div>
</div>

    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerUOMHierarchy->totalCount){
    $gridColumnUOMHierarchy = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'sku.id',
                'label' => Yii::t('app', 'Sku')
            ],
                        'quantity',
                        'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerUOMHierarchy,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-u-o-m-hierarchy']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Uom Hierarchy')),
        ],
        'export' => false,
        'columns' => $gridColumnUOMHierarchy
    ]);
}

?>
</div>
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
        'allow_order_approval',
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
        'allow_order_approval',
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
if($providerWastageProducts->totalCount){
    $gridColumnWastageProducts = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'vendorDetails.id',
                'label' => Yii::t('app', 'Vendor Details')
            ],
            [
                'attribute' => 'product.id',
                'label' => Yii::t('app', 'Product')
            ],
                        'quantity',
            'batch_number',
            [
                'attribute' => 'wastageType.id',
                'label' => Yii::t('app', 'Wastage Type')
            ],
            'reason_for_wastage:ntext',
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerWastageProducts,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-wastage-products']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Wastage Products')),
        ],
        'export' => false,
        'columns' => $gridColumnWastageProducts
    ]);
}

?>
</div>
</div>
</div>

</div>

