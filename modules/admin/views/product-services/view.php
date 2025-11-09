<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductServices */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Services'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-services-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Product Services').' '. Html::encode($this->title) ?></h2>
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
            'attribute' => 'service.id',
            'label' => Yii::t('app', 'Service'),
        ],
        [
            'attribute' => 'product.id',
            'label' => Yii::t('app', 'Product'),
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
        'from_price',
        'to_price',
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
        'is_price_range',
        'multi_selection',
        'status',
    ];
    echo DetailView::widget([
        'model' => $model->service,
        'attributes' => $gridColumnServices    ]);
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

