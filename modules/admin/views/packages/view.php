<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ComboPackages */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Custom CSS for neat design
$this->registerCss(<<<CSS
.card-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    font-weight: 500;
    font-size: 1rem;
    padding: 1rem;
}
.card {
    margin-bottom: 30px;
    border-radius: 1rem;
    box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05);
}
.card-body {
    padding: 1.5rem;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}
.table td img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
CSS);
?>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-box-open me-2"></i> <?= Html::encode($model->title) ?></h5>
    </div>
    <div class="card-body text-center">
        <div class="d-flex flex-wrap justify-content-center gap-4 py-3">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary px-4 rounded-pill'
            ]) ?>

            <?php if (in_array(Yii::$app->user->identity->user_role, [User::ROLE_ADMIN, User::ROLE_SUBADMIN])): ?>
                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger px-4 rounded-pill',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Package Details -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2"></i> Package Details - <?= Html::encode($model->title) ?></h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered table-striped table-hover'],
            'attributes' => [
                ['attribute' => 'id', 'visible' => false],
                [
                    'attribute' => 'vendorDetails.id',
                    'label' => Yii::t('app', 'Vendor'),
                ],
                'title',
                'price',
                'time',
                [
                    'attribute' => 'is_home_visit',
                    'value' => $model->is_home_visit ? 'Yes' : 'No'
                ],
                [
                    'attribute' => 'is_walk_in',
                    'value' => $model->is_walk_in ? 'Yes' : 'No'
                ],
                'service_for',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'white-space: normal; word-break: break-word; max-width: 600px;']
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => $model->getStateOptionsBadges(),
                ],
            ],
        ]) ?>
    </div>
</div>

<!-- Created User -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user me-2"></i> Created By</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->createUser,
            'options' => ['class' => 'table table-bordered table-striped table-hover'],
            'attributes' => [
                'username',
                'email',
                'first_name',
                'last_name',
                'contact_no',
                'address',
                [
                    'attribute' => 'profile_image',
                    'format' => 'raw',
                    'value' => function ($user) {
                        return (!empty($user->profile_image) && filter_var($user->profile_image, FILTER_VALIDATE_URL))
                            ? Html::img($user->profile_image, ['style' => 'width: 80px; height: 80px; object-fit: cover; border-radius: 8px;'])
                            : 'N/A';
                    },
                ],
                'user_role',
                'status',
            ],
        ]) ?>
    </div>
</div>

<!-- Vendor Details -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-store me-2"></i> Vendor Details</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->vendorDetails,
            'options' => ['class' => 'table table-bordered table-striped table-hover'],
            'attributes' => [
            'business_name',
               [
                'attribute' => 'description',
                'format' => 'text',
                'value' => function ($model) {
                    return strip_tags($model->description);
                }
            ],
                'website_link',
                'gst_number',
                'address',
                [
                    'attribute' => 'logo',
                    'format' => 'raw',
                    'value' => function ($vendor) {
                        return (!empty($vendor->logo) && filter_var($vendor->logo, FILTER_VALIDATE_URL))
                            ? Html::img($vendor->logo, ['style' => 'width: 100px; height: 100px; object-fit: contain; border-radius: 8px;'])
                            : 'N/A';
                    },
                ],
                'avg_rating',
                'status',
            ],
        ]) ?>
    </div>
</div>

<!-- Combo Services Grid -->
<?php if ($providerComboServices->totalCount): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-briefcase me-2"></i> Combo Services</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $providerComboServices,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-combo-services']],
                'summary' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    ['attribute' => 'id', 'visible' => false],
                    ['attribute' => 'vendorDetails.business_name', 'label' => Yii::t('app', 'Vendor')],
                    ['attribute' => 'services.service_name', 'label' => Yii::t('app', 'Service')],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model->getStateOptionsBadges();
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
<?php endif; ?>
