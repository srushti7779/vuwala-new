<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use app\models\User;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reels'), 'url' => ['index']];
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
        <h5><i class="fas fa-sliders-h me-2"></i> <?= Html::encode($model->title) ?></h5>
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

<!-- Reel Details -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2"></i> Reel Information</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                ['attribute' => 'id', 'visible' => false],
                [
                    'attribute' => 'vendorDetails.id',
                    'label' => Yii::t('app', 'Vendor Details'),
                ],
                [
                    'attribute' => 'video',
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->video ? '<video width="150" height="100" controls><source src="' . $model->video . '" type="video/mp4"></video>' : null;
                    },
                ],
                [
                    'attribute' => 'thumbnail',
                    'format' => 'html',
                    'value' => function($model) {
                        return Html::img($model->thumbnail, ['style' => 'width:100px; height:100px;']);
                    },
                ],
                'title',
                'description:ntext',
                'like_count',
                'view_count',
                'share_count',
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function($model) { return $model->getStateOptionsBadges(); },
                ],
            ],
        ]) ?>
    </div>
</div>

<!-- GridView Sections -->
<?php
$grids = [
    'Reel Share Counts' => ['provider' => $providerReelShareCounts, 'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],
        ['attribute' => 'user.username', 'label' => Yii::t('app', 'User')],
        'platform',
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model) { return $model->getStateOptionsBadges(); },
        ],
    ]],
    'Reel Tags' => ['provider' => $providerReelTags, 'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],
        'tag',
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model) { return $model->getStateOptionsBadges(); },
        ],
    ]],
    'Reels Likes' => ['provider' => $providerReelsLikes, 'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],
        ['attribute' => 'user.username', 'label' => Yii::t('app', 'User')],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model) { return $model->getStateOptionsBadges(); },
        ],
    ]],
    'Reels View Counts' => ['provider' => $providerReelsViewCounts, 'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        ['attribute' => 'id', 'visible' => false],
        ['attribute' => 'user.username', 'label' => Yii::t('app', 'User')],
        'ip_address',
    ]],
];

foreach ($grids as $title => $config):
    if ($config['provider']->totalCount):
?>
<div class="card">
    <div class="card-header"><h5><i class="fas fa-database me-2"></i> <?= Html::encode(Yii::t('app', $title)) ?></h5></div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $config['provider'],
            'pjax' => true,
            'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-' . strtolower(str_replace(' ', '-', $title))]],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
                'heading' => false,
                'footer' => false,
            ],
            'export' => false,
            'columns' => $config['columns']
        ]) ?>
    </div>
</div>
<?php endif; endforeach; ?>
