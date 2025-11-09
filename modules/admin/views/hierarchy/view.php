<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Hierarchy */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Hierarchies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hierarchy-view">

    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Hierarchy').' '. Html::encode($this->title) ?></h2>
        </div>
        <div class="col-sm-3" style="margin-top: 15px">
            
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ])
            ?>
        </div>
    </div>

    <div class="row">
<?php 
    $gridColumn = [
        ['attribute' => 'id', 'visible' => false],
        [
            'attribute' => 'sku.id',
            'label' => Yii::t('app', 'Sku'),
        ],
        [
            'attribute' => 'units.id',
            'label' => Yii::t('app', 'Units'),
        ],
        'quantity',
        [
            'attribute' => 'ofUnits.id',
            'label' => Yii::t('app', 'Of Units'),
        ],
        'status',
        'created_on',
        'updated_on',
        [
            'attribute' => 'createUser.username',
            'label' => Yii::t('app', 'Create User'),
        ],
        [
            'attribute' => 'updateUser.username',
            'label' => Yii::t('app', 'Update User'),
        ],
    ];
    echo DetailView::widget([
        'model' =>$model->createUser,
        'attributes' => $gridColumn
    ]);
?>
    </div>
    <div class="row">
        <h4>Sku<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnSku = [
        ['attribute' => 'id', 'visible' => false],
        'vendor_details_id',
        'sku_code',
        'product_name',
        'brand_id',
        'ean_code',
        'category_id',
        'store_service_type_id',
        'product_type_id',
        'status',
        'created_on',
        'updated_on',
        [
            'attribute' => 'createUser.username',
            'label' => Yii::t('app', 'Create User'),
        ],
        [
            'attribute' => 'updateUser.username',
            'label' => Yii::t('app', 'Update User'),
        ],
    ];
    echo DetailView::widget([
        'model' => $model->sku,
        'attributes' => $gridColumnSku    ]);
    ?>
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
        'is_deleted',
        'info_delete',
        'update_profile_count',
        'update_user_id',
    ];
    echo DetailView::widget([
        'model' => $model->createUser,
        'attributes' => $gridColumnUser    ]);
    ?>
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
        'is_deleted',
        'info_delete',
        'update_profile_count',
        'create_user_id',
    ];
    echo DetailView::widget([
        'model' => $model->updateUser,
        'attributes' => $gridColumnUser    ]);
    ?>
    <div class="row">
        <h4>Units<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUnits = [
        ['attribute' => 'id', 'visible' => false],
        'unit_name',
        'status',
        'created_on',
        'updated_on',
        [
            'attribute' => 'createUser.username',
            'label' => Yii::t('app', 'Create User'),
        ],
        [
            'attribute' => 'updateUser.username',
            'label' => Yii::t('app', 'Update User'),
        ],
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumnUnits    ]);
    ?>
    <div class="row">
        <h4>Units<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnUnits = [
        ['attribute' => 'id', 'visible' => false],
        'unit_name',
        'status',
        'created_on',
        'updated_on',
        [
            'attribute' => 'createUser.username',
            'label' => Yii::t('app', 'Create User'),
        ],
        [
            'attribute' => 'updateUser.username',
            'label' => Yii::t('app', 'Update User'),
        ],
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumnUnits    ]);
    ?>
</div>
