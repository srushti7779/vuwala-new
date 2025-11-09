<?php

use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ReelShareCounts */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reel Share Counts'), 'url' => ['index']];
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
<div class="card">
    <div class="card-header">
        <h5>
            <i class="fas fa-sliders-h me-2"></i> 
            Reel Share Counts 
            <span class="ms-2">#<?= Html::encode($model->id) ?></span>
        </h5>
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
<!--Share Count  section-->
  <div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2"></i> Share Count Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
             <div class="col-12">
            <?php
            $providerReelShareCounts = new ArrayDataProvider([
                'allModels'=>[$model],
                'pagination' =>false

            ]);
            
      

            echo GridView::widget([
                'dataProvider' => $providerReelShareCounts,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-share-counts']],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => false,
                    'footer' => false,
                ],
                'toolbar' => false,
                'export' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'real.title',
                        'label' => Yii::t('app', 'Reel'),
                    ],
                    [
                        'attribute' => 'user.username',
                        'label' => Yii::t('app', 'User'),
                    ],
                    'platform',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) { return $model->getStateOptionsBadges(); },
                    ],
                ],
            ]);
            ?>
        </div>
        </div>
    </div>
</div>
<!--- Reel Share Count Section-->
    <div class="card">
        

  <div class="card">
    <div class="card-header">
            <h4>
                <i class="fas fa-video me-2"></i> Reels <?= Html::encode($this->title) ?>
            </h4>
        </div>
    <div class="card-body">
        <div class="row">
            <?php
            $providerReels = new ArrayDataProvider([
                'allModels' => [$model->real], 
                'pagination' => false
            ]);

            echo GridView::widget([
                'dataProvider' => $providerReels,
                'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-reels']],
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => false,
                ],
                'hover' => true,
                'striped' => true,
                'bordered' => false,
                'export' => false,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'vendor_details_id',
                    [
                        'label' => Yii::t('app', 'Video'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->video
                                ? '<video width="150" height="100" controls>
                                    <source src="' . $model->video . '" type="video/mp4">
                                   </video>'
                                : '<span class="text-muted">No video</span>';
                        },
                    ],
                    [
                        'label' => Yii::t('app', 'Thumbnail'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->thumbnail
                                ? Html::img($model->thumbnail, [
                                    'style' => 'width:100px; height:100px; border-radius:8px; object-fit:cover;',
                                ])
                                : '<span class="text-muted">No thumbnail</span>';
                        },
                    ],
                    'title',
                    [
                        'attribute' => 'description',
                        'format' => 'ntext',
                    ],
                    'view_count',
                    'share_count',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) { return $model->getStateOptionsBadges(); },
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
