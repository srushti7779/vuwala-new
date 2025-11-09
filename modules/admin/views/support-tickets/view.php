<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SupportTickets */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Support Tickets'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
// Custom CSS
$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #343a40;
}
.table td img, .table td video {
    max-width: 100px;
    height: auto;
}
.card-header {
    background: linear-gradient(90deg, #6a11cb, #2575fc);
    color: white;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.card-header h5 {
    margin: 0;
}
.card {
    border-radius: 1rem;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.card-body {
    background-color: #ffffff;
    padding: 1.5rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 8px 20px;
    border-radius: 30px;
    color: #fff;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.beautiful-btn.update {
    background: linear-gradient(to right, #36d1dc, #5b86e5);
}
.beautiful-btn.delete {
    background: linear-gradient(to right, #f85032, #e73827);
}
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
CSS);
?>
<div class="support-tickets-view">
<div class="card">
    <div class="card-header">
     <h4><?= Yii::t('app', 'Support Tickets').' '. Html::encode($this->title) ?></h4>
    </div>
    <div class="card-body text-center">
        <div class="d-flex flex-wrap justify-content-center gap-3 py-2">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn beautiful-btn update']) ?>
            <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn beautiful-btn delete',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

   <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Vendor Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                 <?php
            $provider = new ArrayDataProvider([
                'allModels' => [$model],
                'pagination' => false,
            ]);

            echo GridView::widget([
                'dataProvider' => $provider,
                'summary' => false,
                'panel' => false,
                'export' => false,
                'toolbar' => false,
                'columns' => [
                    [
                        'label' => Yii::t('app', 'Vendor Name'),
                        'value' => function ($model) {
                            return $model->vendorDetails->business_name ?? '-';
                        },
                    ],
                    [
                        'attribute' => 'subject',
                        'label' => Yii::t('app', 'Subject'),
                    ],
                    [
                        'attribute' => 'message',
                        'format' => 'ntext',
                        'label' => Yii::t('app', 'Message'),
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getStateOptionsBadges(); 
                        },
                    ],
                ],
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered mb-0'],
            ]);
            ?>
        </div>

            </div>
           
    </div>
</div>
<!--- Vendordetails---->
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Vendor Full Details</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->vendorDetails,
            'options' => ['class' => 'table table-bordered table-striped table-hover align-middle'],
            'attributes' => [
                'id',
                'user_id',
                'uuid_myoperator',
                'extension_myoperator',
                'city_id',
                'business_name',
                'description:ntext',
                'main_category_id',
                'website_link:url',
                'gst_number',
                'is_gst_number_verified:boolean',
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
                'address:ntext',
                [
                    'attribute' => 'logo',
                    'format' => 'html',
                    'value' => fn($model) => $model->logo ? Html::img($model->logo, ['style' => 'width:100px; height:100px;']) : null,
                ],
                'shop_licence_no',
                'avg_rating',
                'min_order_amount',
                'commission_type',
                'commission',
                'offer_tag',
                'service_radius',
                'min_service_fee',
                'discount',
                'is_top_shop:boolean',
                'gender_type',
                'is_featured:boolean',
                'is_premium:boolean',
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => fn($model) => $model->getStateOptionsBadges(),
                ],
                'service_type_home_visit:boolean',
                'service_type_walk_in:boolean',
                'is_verified:boolean',
                'created_on:datetime',
                'updated_on:datetime',
                'create_user_id',
                'update_user_id',
                'qr_scan_discount_percentage',
                'no_of_branches',
                'no_of_sitting',
                'no_of_staff',
            ],
        ]) ?>
    </div>
</div>
<!---User Details-->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>User Details <?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body">
        <?= \yii\widgets\DetailView::widget([
            'model' => $model->updateUser,
            'options' => ['class' => 'table table-bordered table-striped table-hover mb-0'],
            'attributes' => [
                ['attribute' => 'id', 'visible' => false],
                'username',
                'unique_user_id',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'email:email',
                'first_name',
                'last_name',
                'lat',
                'lng',
                'contact_no',
                'alternative_contact',
                'date_of_birth:date',
                'gender',
                'description:ntext',
                'address:ntext',
                'location',
                [
                    'attribute' => 'profile_image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->profile_image, ['alt'=>'Profile Image', 'width'=>'100', 'class'=>'img-thumbnail rounded']);
                    },
                ],
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
                'is_tiffin_box:boolean',
                'is_deleted:boolean',
                'info_delete:boolean',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
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
<?php
if($providerSupportTicketsHasFiles->totalCount){
    $gridColumnSupportTicketsHasFiles = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
                        'file',
            'status',
    ];
    echo Gridview::widget([
        'dataProvider' => $providerSupportTicketsHasFiles,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-support-tickets-has-files']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Support Tickets Has Files')),
        ],
        'export' => false,
        'columns' => $gridColumnSupportTicketsHasFiles
    ]);
}

?>
</div>
</div>
</div>

</div>

