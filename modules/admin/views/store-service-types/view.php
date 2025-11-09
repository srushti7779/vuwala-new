<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoreServiceTypes */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Service Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-service-types-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Store Service Types').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'store.id',
            'label' => Yii::t('app', 'Store'),
        ],
        [
            'attribute' => 'serviceType.id',
            'label' => Yii::t('app', 'Service Type'),
        ],
        [
            'attribute' => 'mainCategory.title',
            'label' => Yii::t('app', 'Main Category'),
        ],
        'type',
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
        <h4>ServiceType<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnServiceType = [
        ['attribute' => 'id', 'visible' => false],
        [
            'attribute' => 'mainCategory.title',
            'label' => Yii::t('app', 'Main Category'),
        ],
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
        <h4>MainCategory<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnMainCategory = [
        ['attribute' => 'id', 'visible' => false],
        'title',
        'image',
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
        'model' => $model->mainCategory,
        'attributes' => $gridColumnMainCategory    ]);
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
        'city_id',
        'business_name',
        'description',
        [
            'attribute' => 'mainCategory.title',
            'label' => Yii::t('app', 'Main Category'),
        ],
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
        'status',
        'service_type_home_visit',
        'service_type_walk_in',
        'is_verified',
        'qr_scan_discount_percentage',
        'no_of_branches',
        'no_of_sitting',
        'no_of_staff',
    ];
    echo DetailView::widget([
        'model' => $model->store,
        'attributes' => $gridColumnVendorDetails    ]);
    ?>
    </div>
    </div>
</div>

