<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Banner */
/* @var $providerSubCategory yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f1f1f1 !important;
    color: #333;
}
.table td img {
    max-width: 100px;
    height: auto;
}
.grid-view {
    overflow-x: auto;
}
.card-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
}
.card-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
}
.card {
    border-radius: 1rem;
    box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
}
.card-body {
    background-color: #fff;
    padding: 1.25rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 8px 20px;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    color: #fff;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.beautiful-btn i {
    margin-right: 6px;
}
.beautiful-btn.update {
    background: linear-gradient(to right, #36d1dc, #5b86e5);
}
.beautiful-btn.delete {
    background: linear-gradient(to right, #f85032, #e73827);
}
.beautiful-btn.recharge {
    background: linear-gradient(to right, #56ab2f, #a8e063);
}
.beautiful-btn.timing {
    background: linear-gradient(to right, #fbc02d, #ffeb3b);
    color: #000;
}
.beautiful-btn.logs {
    background: linear-gradient(to right, #0db423ff, #09ac42ff);
    color: #000;
}
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
CSS);
?>

<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
       <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-sliders-h me-2"></i>
                <?= Html::encode($model->title) ?>
            </h5>
        </div>
    <div class="card-body text-center">
        <div class="d-flex flex-wrap justify-content-center gap-4 py-3">
            <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
                'class' => 'btn beautiful-btn update'
            ]) ?>

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

<!-- Main Category Details -->
   <div class="card mb-4">
     <div class="card-header">
    <h5 class="mb-0">
        <i class="fas fa-sliders-h me-2"></i>
         <?= Html::encode($model->title) ?>
    </h5>
</div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' =>$providerSubCategory, 
            'pjax' => true,
            'responsive' => true,
            'hover' => true,
            'striped' => true,
            'bordered' => true,
            'panel' => false,
            'export' => false,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                ['attribute' => 'id', 'visible' => false],
                'title',
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->image, [
                            'style' => 'width: 100px; height: 100px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);'
                        ]);
                    },
                ],
                [
                    'attribute' => 'is_featured',
                    'format' => 'raw',
                    'value' => function($model) {
                        return $model->getFeatureOptionsBadges();
                    },
                ],
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

<!-- Sub Category GridView -->
<!-- <?php if (!empty($providerSubCategory) && $providerSubCategory->totalCount): ?>
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-th-list me-2"></i>Sub Categories</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $providerSubCategory,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-sub-category']],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => false,
                ],
                'export' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    ['attribute' => 'id', 'visible' => false],
                    'title',
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::img($model->image, ['style' => 'height: 100px']);
                        },
                    ],
                    'is_featured',
                    'status',
                    'sortOrder',
                    'type_id',
                ],
            ]) ?>
        </div>
    </div>
<?php endif; ?> -->
