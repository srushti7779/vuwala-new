<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Subscriptions */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Subscriptions'), 'url' => ['index']];
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
<div class="subscriptions-view">
<div class="card">
    <div class="card-header">
     <h4><?= Yii::t('app', 'Subscriptions').' '. Html::encode($this->title) ?></h4>
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
  <div class="card shadow-lg border-0 rounded-4 mb-5">
    <div class="card-header bg-gradient text-white rounded-top-4" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
        <h4 class="mb-0"><i class="fas fa-tags me-2"></i><?= Yii::t('app', 'Subscriptions') . ' ' . Html::encode($this->title) ?></h4>
    </div>
 

    <div class="card-body p-4">
        <div class="col-12">
    <?php
         $provider = new ArrayDataProvider([
                'allModels' => [$model],
                'pagination' => false,
                
            ]);

    
    ?>
             
            <?= GridView::widget([
                'dataProvider' => $provider,
                'summary' => false,
                'responsive'=> false,
                'tableOptions' => ['class' => 'table table-striped table-hover table-bordered mb-0'],
                'rowOptions' => ['class' => 'align-middle'],
                'headerRowOptions' => ['class' => 'table-primary text-center align-middle'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'title',
                        'contentOptions' => ['class' => 'fw-semibold'],
                    ],

                    [
                        'attribute' => 'description',
                        'format' => 'ntext',
                        'value' => function ($model) {
                            return \yii\helpers\StringHelper::truncate(strip_tags(html_entity_decode($model->description)), 80);
                        }
                    ],

                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::img($model->image, [
                                'alt' => 'Image',
                                'class' => 'rounded shadow-sm',
                                'style' => 'width: 60px; height: 60px; object-fit: cover;'
                            ]);
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
       


    <div class="card">
        <div class="card-body">
            <div class="row">
                <?php
                if ($providerVendorSubscriptions->totalCount) {
                    $gridColumnVendorSubscriptions = [
                        ['class' => 'yii\grid\SerialColumn'],
                        ['attribute' => 'id', 'visible' => false],
                        [
                            'attribute' => 'vendorDetails.business_name',
                            'label' => Yii::t('app', 'Vendor Details')
                        ],
                        'amount',
                        'duration',
                        'start_date',
                        'end_date',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return $model->getStateOptionsBadges();
                            },


                        ],
                    ]; 
                    echo Gridview::widget([
                        'dataProvider' => $providerVendorSubscriptions,
                        'pjax' => true,
                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-vendor-subscriptions']],
                        'panel' => [
                            'type' => GridView::TYPE_PRIMARY,
                            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Vendor Subscriptions')),
                        ],
                        'export' => false,
                        'columns' => $gridColumnVendorSubscriptions
                    ]);
                }

                ?>
            </div>
        </div>
    </div>

</div>