<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorSubscriptions */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Subscriptions'), 'url' => ['index']];
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
<div class="vendor-subscriptions-view">
<div class="card">
    <div class="card-header">
     <h4><?= Yii::t('app', 'vendor-subscriptions').' '. Html::encode($this->title) ?></h4>
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
<!---- vendor subscriptions-->
   <div class="card shadow-lg border-0 rounded-4 mb-5">
    <div class="card-header bg-gradient text-white rounded-top-4" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
            <h4><?= Yii::t('app', 'vendor-subscriptions').' '. Html::encode($this->title) ?></h4>
     </div>

    <div class="card-body p-4">
        <div class="col-12">
            <div class="row">
                  <?php

            $dataProvider = new ArrayDataProvider([
                'allModels'=>[$model],
                'pagination'=>false

            ]);
        
            ?>
             <?= GridView::widget([
            'dataProvider' => $dataProvider, 
            'summary' => false,
            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover mb-0'],
            'headerRowOptions' => ['class' => 'table-primary text-center align-middle'],
            'rowOptions' => ['class' => 'align-middle'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'vendorDetails.id',
                    'label' => Yii::t('app', 'Vendor Details'),
                    'value' => function($model) {
                        return $model->vendorDetails->id ?? '-';
                    }
                ],
                [
                    'attribute' => 'subscription.title',
                    'label' => Yii::t('app', 'Subscription'),
                    'value' => function($model) {
                        return $model->subscription->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'amount',
                    'label' => Yii::t('app', 'Plan Amount'),
                    'format' => ['currency']
                ],
                'start_date:date',
                'end_date:date',
                'duration',

                [
                    'attribute' => 'bill_generation_date_time',
                    'label' => Yii::t('app', 'Bill Generated On'),
                    'format' => ['datetime']
                ],
                [
                    'attribute' => 'payment_received_datetime',
                    'label' => Yii::t('app', 'Payment Received On'),
                    'format' => ['datetime']
                ],
                [
                    'attribute' => 'sent_invoice',
                    'label' => Yii::t('app', 'Sent Invoice'),
                    'value' => function ($model) {
                        return $model->sent_invoice === 'Yes' ? 'Yes' : 'No';
                    }
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    }
                ],

            ],
        ]); ?>
    </div>
</div>

        </div>

            </div>
          
       



    <!-- Vendor Details Card -->
<div class="card shadow-lg border-0 rounded-4 mb-5">
    <div class="card-header bg-gradient text-white rounded-top-4"
         style="background: linear-gradient(135deg, #7b4397, #dc2430);">
        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Vendor Details <?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body p-4">
        <?= GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'allModels' => [$model->vendorDetails],
                'pagination' => false,
            ]),
            'summary' => false,
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered mb-0'],
            'rowOptions' => ['class' => 'align-middle'],
            'headerRowOptions' => ['class' => 'table-primary text-center'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'user_id',
                [
                    'attribute' => 'city_id',
                    'label' => 'City',
                    'value' => function ($model) {
                        return $model->city->name ?? '-';
                    }
                ],
                'business_name',
                [
                    'attribute' => 'main_category_id',
                    'label' => 'Main Category',
                    'value' => function ($model) {
                        return $model->mainCategory->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    }
                ],
            ]
        ]); ?>
    </div>
</div>

<!-- Subscriptions Card -->
<div class="card shadow-lg border-0 rounded-4 mb-5">
    <div class="card-header bg-gradient text-white rounded-top-4"
         style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Subscriptions <?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body p-4">
        <?= GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'allModels' => [$model->subscription],
                'pagination' => false,
            ]),
            'summary' => false,
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered mb-0'],
            'rowOptions' => ['class' => 'align-middle'],
            'headerRowOptions' => ['class' => 'table-primary text-center'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'title',
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'value' => function ($model) {
                        return strip_tags(html_entity_decode($model->description));
                    }
                ],
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->image, [
                            'alt' => 'Image',
                            'class' => 'rounded shadow-sm',
                            'style' => 'width: 100px; height: 100px; object-fit: cover;'
                        ]);
                    }
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStateOptionsBadges();
                    }
                ]
            ]
        ]); ?>
    </div>
</div>
