<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\User;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ServicePinCode */

$this->title = Yii::t('app', 'Service Pin Code') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Pin Codes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

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

/* GridView Enhancements */
.kv-panel-heading {
    background: linear-gradient(135deg, #36d1dc, #5b86e5);
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}
.kv-grid-table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    font-size: 14px;
}
.kv-grid-table td {
    font-size: 13px;
    color: #212529;
    padding: 0.75rem;
    vertical-align: middle;
}
.kv-grid-table tr:nth-child(even) {
    background-color: #f8fafd;
}
.kv-grid-table tr:hover {
    background-color: #e2f0ff;
    transition: background 0.3s ease;
}
CSS);
?>

<div class="service-pin-code-view">

   <div class="service-pin-code-view">
    <!-- Card with Action Buttons -->
    <div class="card">
        <div class="card-header">
            <h4><?= Yii::t('app', '') . ' ' . Html::encode($this->title) ?></h4>
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

    <!-- Service Pin Code Details -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
            <h5 class="mb-0"><i class="fas fa-map-pin me-2"></i><?= Yii::t('app', 'Service Pin Code Details') ?></h5>
        </div>
        <div class="card-body">
            <?php
            $dataProvider = new ArrayDataProvider([
                'allModels' => [$model],
                'pagination' => false,
            ]);

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'export' => false,
                'toolbar' => false,
                'bordered' => false,
                'striped' => true,
                'hover' => true,
                'responsive' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'city.name',
                        'label' => Yii::t('app', 'City'),
                    ],
                    'area_pin_code',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getStateOptionsBadges(); 
                        },
                    ],
                ],
                'tableOptions' => ['class' => 'table table-bordered table-sm mb-0'],
            ]);
            ?>
        </div>
    </div>

    <!-- Linked City Info -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #36d1dc, #5b86e5);">
            <h5 class="mb-0"><i class="fas fa-city me-2"></i><?= Yii::t('app', 'City Information') ?></h5>
        </div>
        <div class="card-body">
            <?php
            $cityProvider = new ArrayDataProvider([
                'allModels' => [$model->city],
                'pagination' => false,
            ]);

            echo GridView::widget([
                'dataProvider' => $cityProvider,
                'summary' => false,
                'export' => false,
                'toolbar' => false,
                'bordered' => false,
                'striped' => true,
                'hover' => true,
                'responsive' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'name',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getStateOptionsBadges();
                        },
                    ],
                ],
                'tableOptions' => ['class' => 'table table-bordered table-sm mb-0'],
            ]);
            ?>
        </div>
    </div>

</div>
