<?php

use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Banner */

$this->title = $model->service_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Custom CSS for clean design
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
        <h5><i class="fas fa-sliders-h me-2"></i> <?= Html::encode($model->service_name) ?></h5>
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

<!-- Service Details Grid -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-tools me-2"></i> Service Details - <?= Html::encode($model->service_name) ?></h5>
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
                [
                    'attribute' => 'subCategory.title',
                    'label' => Yii::t('app', 'Sub Category'),
                ],
                'service_name',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'white-space: normal; word-break: break-word; max-width: 600px;']
                ],
                [
                    'attribute' => 'small_description',
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'white-space: normal; word-break: break-word; max-width: 400px;']
                ],
                'original_price',
                'standard_price',
                'discount_price',
                'max_per_day_services',
                'price',
                'duration',
                [
                    'attribute' => 'home_visit',
                    'value' => $model->home_visit ? 'Yes' : 'No'
                ],
                [
                    'attribute' => 'walk_in',
                    'value' => $model->walk_in ? 'Yes' : 'No'
                ],
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return (!empty($model->image) && filter_var($model->image, FILTER_VALIDATE_URL))
                            ? Html::img($model->image, [
                                'style' => 'width: 80px; height: 80px; object-fit: cover; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);'
                            ])
                            : 'N/A';
                    },
                ],
                [
                    'attribute' => 'type',
                    'format' => 'raw',
                    'value' => $model->getTypeOptionsBadges(),
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


<!-- Cart Items Grid -->
<?php if ($providerCartItems->totalCount): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-shopping-cart me-2"></i> Cart Items</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $providerCartItems,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-cart-items']],
                'summary' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    ['attribute' => 'id', 'visible' => false],
                    ['attribute' => 'cart.id', 'label' => Yii::t('app', 'Cart')],
                    ['attribute' => 'user.username', 'label' => Yii::t('app', 'User')],
                    'quantity',
                    'amount',
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

<!-- Sub Category DetailView -->


<!-- Vendor Details DetailView -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-store me-2"></i> Vendor Details - <?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->vendorDetails,
            'options' => ['class' => 'table table-bordered table-striped table-hover'],
            'attributes' => [
                ['attribute' => 'id', 'visible' => false],
                'user_id',
                'business_name',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'white-space: normal; word-break: break-word; max-width: 400px;']
                ],
                'main_category_id',
                'website_link',
                'gst_number',
                'latitude',
                'longitude',
                'address',
                [
                    'attribute' => 'logo',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (!empty($model->logo) && filter_var($model->logo, FILTER_VALIDATE_URL)) {
                            return Html::img($model->logo, [
                                'style' => 'width: 100px; height: 100px; object-fit: contain; border-radius: 8px;'
                            ]);
                        }
                        return 'N/A';
                    },
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
                [
                    'attribute' => 'is_top_shop',
                    'value' => function ($model) {
                        return $model->is_top_shop ? 'Yes' : 'No';
                    }
                ],
                'gender_type',
                'status',
                [
                    'attribute' => 'service_type_home_visit',
                    'value' => function ($model) {
                        return $model->service_type_home_visit ? 'Yes' : 'No';
                    }
                ],
                [
                    'attribute' => 'service_type_walk_in',
                    'value' => function ($model) {
                        return $model->service_type_walk_in ? 'Yes' : 'No';
                    }
                ],
            ],
        ]) ?>
    </div>
</div>

