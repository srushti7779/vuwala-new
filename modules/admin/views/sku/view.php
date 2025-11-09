<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Sku */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Skus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Sku').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'vendorDetails.id',
            'label' => Yii::t('app', 'Vendor Details'),
        ],
        'sku_code',
        'product_name',
        [
            'attribute' => 'brand.id',
            'label' => Yii::t('app', 'Brand'),
        ],
        'ean_code',
        [
            'attribute' => 'category.title',
            'label' => Yii::t('app', 'Category'),
        ],
        [
            'attribute' => 'serviceType.id',
            'label' => Yii::t('app', 'Service Type'),
        ],
        [
            'attribute' => 'storeServiceType.id',
            'label' => Yii::t('app', 'Store Service Type'),
        ],
        [
            'attribute' => 'productType.id',
            'label' => Yii::t('app', 'Product Type'),
        ],
        [
            'attribute' => 'uom.id',
            'label' => Yii::t('app', 'Uom'),
        ],
        'tax_rate',
        're_order_level_for_alerts',
        [
            'attribute' => 'uomIdReOrderLevel.id',
            'label' => Yii::t('app', 'Uom Id Re Order Level'),
        ],
        'min_quantity_need',
        'description:ntext',
        'image',
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
<?php
if($providerProducts->totalCount){
    $gridColumnProducts = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'vendorDetails.id',
                'label' => Yii::t('app', 'Vendor Details')
            ],
                        'discount_allowed',
            'minimum_stock',
            [
                'attribute' => 'units.id',
                'label' => Yii::t('app', 'Units')
            ],
            [
                'attribute' => 'supplier.id',
                'label' => Yii::t('app', 'Supplier')
            ],
            'batch_number',
            'purchase_date',
            'mrp_price',
            'selling_price',
            'expire_date',
            'units_received',
            [
                'attribute' => 'receivedUnits.id',
                'label' => Yii::t('app', 'Received Units')
            ],
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
        'description:ntext',
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
        <h4>Units<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUnits = [
        ['attribute' => 'id', 'visible' => false],
        'unit_name',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->uomIdReOrderLevel,
        'attributes' => $gridColumnUnits    ]);
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
        'description:ntext',
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
        <h4>VendorDetails<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnVendorDetails = [
        ['attribute' => 'id', 'visible' => false],
        'user_id',
        'main_vendor_user_id',
        'vendor_brand_id',
        'uuid_myoperator',
        'extension_myoperator',
        'city_id',
        'business_name',
        'description:ntext',
        'main_category_id',
        'website_link',
        'gst_number',
        'is_gst_number_verified',
        'msme_number',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'bank_name',
        'bank_branch',
        'bank_state',
        'bank_city',
        'bank_address',
        'latitude',
        'longitude',
        'coordinates',
        'address',
        'logo',
        'shop_licence_no',
        'avg_rating',
        'min_order_amount',
        'commission_type',
        'commission',
        'offer_tag',
        'service_radius',
        'min_service_fee',
        'discount',
        'is_top_shop',
        'gender_type',
        'is_featured',
        'is_premium',
        'catalog_file',
        'allow_order_approval',
        'status',
        'service_type_home_visit',
        'service_type_walk_in',
        'is_verified',
        'qr_scan_discount_percentage',
        'no_of_branches',
        'no_of_sitting',
        'no_of_staff',
        'location_name',
        'street',
        'iso_country_code',
        'country',
        'postal_code',
        'administrative_area',
        'subadministrative_area',
        'locality',
        'sublocality',
        'thoroughfare',
        'subthoroughfare',
    ];
    echo DetailView::widget([
        'model' => $model->vendorDetails,
        'attributes' => $gridColumnVendorDetails    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>MainCategory<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnMainCategory = [
        ['attribute' => 'id', 'visible' => false],
        'title',
        'image',
        'icon',
        'is_featured',
        'offer_percentage',
        'is_required_documents',
        'status',
        'show_home',
        'sortOrder',
        'position',
        'type_id',
        'is_scheduled_next_visit',
    ];
    echo DetailView::widget([
        'model' => $model->category,
        'attributes' => $gridColumnMainCategory    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>StoreServiceTypes<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnStoreServiceTypes = [
        ['attribute' => 'id', 'visible' => false],
        'store_id',
        [
            'attribute' => 'serviceType.id',
            'label' => Yii::t('app', 'Service Type'),
        ],
        'main_category_id',
        'type',
        'image',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->storeServiceType,
        'attributes' => $gridColumnStoreServiceTypes    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>ProductTypes<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnProductTypes = [
        ['attribute' => 'id', 'visible' => false],
        'product_type_name',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->productType,
        'attributes' => $gridColumnProductTypes    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Brands<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnBrands = [
        ['attribute' => 'id', 'visible' => false],
        'brand_name',
        'image',
        'is_global',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->brand,
        'attributes' => $gridColumnBrands    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>ServiceType<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnServiceType = [
        ['attribute' => 'id', 'visible' => false],
        'main_category_id',
        'image',
        'type',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->serviceType,
        'attributes' => $gridColumnServiceType    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>Units<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUnits = [
        ['attribute' => 'id', 'visible' => false],
        'unit_name',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->uom,
        'attributes' => $gridColumnUnits    ]);
    ?>
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
                'attribute' => 'units.id',
                'label' => Yii::t('app', 'Units')
            ],
            'quantity',
            [
                'attribute' => 'ofUnits.id',
                'label' => Yii::t('app', 'Of Units')
            ],
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

</div>

