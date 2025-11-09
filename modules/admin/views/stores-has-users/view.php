<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoresHasUsers */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stores Has Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stores-has-users-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Stores Has Users').' '. Html::encode($this->title) ?></h2>
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
        [
            'attribute' => 'guestUser.username',
            'label' => Yii::t('app', 'Guest User'),
        ],
        [
            'attribute' => 'vendorUser.username',
            'label' => Yii::t('app', 'Vendor User'),
        ],
        'is_vip',
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
if($providerGuestUserDeposits->totalCount){
    $gridColumnGuestUserDeposits = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
            [
                'attribute' => 'guestUser.username',
                'label' => Yii::t('app', 'Guest User')
            ],
                        'amount',
            'deposit_date_and_time',
            'payment_mode',
            'status',
            [
                'attribute' => 'createdUser.username',
                'label' => Yii::t('app', 'Created User')
            ],
            [
                'attribute' => 'updatedUser.username',
                'label' => Yii::t('app', 'Updated User')
            ],
    ];
    echo Gridview::widget([
        'dataProvider' => $providerGuestUserDeposits,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-guest-user-deposits']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Guest User Deposits')),
        ],
        'export' => false,
        'columns' => $gridColumnGuestUserDeposits
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
        'model' => $model->guestUser,
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
        'description',
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
        'model' => $model->vendorUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerStoresUsersMemberships->totalCount){
    $gridColumnStoresUsersMemberships = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
                        [
                'attribute' => 'membership.id',
                'label' => Yii::t('app', 'Membership')
            ],
            'status',
            [
                'attribute' => 'createdUser.username',
                'label' => Yii::t('app', 'Created User')
            ],
            [
                'attribute' => 'updatedUser.username',
                'label' => Yii::t('app', 'Updated User')
            ],
    ];
    echo Gridview::widget([
        'dataProvider' => $providerStoresUsersMemberships,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-stores-users-memberships']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Stores Users Memberships')),
        ],
        'export' => false,
        'columns' => $gridColumnStoresUsersMemberships
    ]);
}

?>
</div>
</div>
</div>

</div>

