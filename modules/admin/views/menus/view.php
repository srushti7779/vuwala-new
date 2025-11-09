<?php

use app\modules\admin\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Menus */

$this->title = "Menu Details — " . Html::encode($model->label);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #212529;
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
    font-weight: 600;
    font-size: 1rem;
    padding: 0.85rem 1.25rem;
    border-bottom: none;
}
.card-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.card {
    border-radius: 1rem;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}
.card-body {
    background-color: #fff;
    padding: 1.5rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 10px 22px;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    color: #fff;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
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
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(5, 53, 150, 0.18);
}
.section-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 15px;
    color: #333;
    border-left: 4px solid #2575fc;
    padding-left: 10px;
}
CSS);
?>

<div class="menus-view">

    <!-- Action Buttons Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header">
            <h5>
                <i class="fas fa-bars"></i>
                Menu ID: <?= Html::encode($model->id) ?> — 
               
            </h5>
        </div>
        <div class="card-body text-center">
            <div class="d-flex flex-wrap justify-content-center gap-4 py-2">
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

    <!-- Menu Details Card -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-info-circle"></i> Menu Details</h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    ['attribute' => 'id', 'visible' => false],
                    'label',
                    'route',
                    'icon',
                    'parent_id',
                    'sort_order',
                    [
                        'attribute' => 'status',
                        'value' => $model->status ? 'Active' : 'Inactive',
                        'contentOptions' => ['class' => $model->status ? 'text-success fw-bold' : 'text-danger fw-bold'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- Permissions Grid Card -->
    <?php if ($providerMenuPermissions->totalCount): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-shield"></i> Menu Permissions</h5>
            </div>
            <div class="card-body">
                <?= GridView::widget([
                    'dataProvider' => $providerMenuPermissions,
                    'pjax' => true,
                    'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-menu-permissions']],
                    'panel' => [
                        'type' => GridView::TYPE_PRIMARY,
                        'heading' => false,
                        'before' => false,
                        'after' => false,
                        'footer' => false,
                    ],
                    'export' => false,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        ['attribute' => 'id', 'visible' => false],
                        'permission_name',
                        'small_description',
                        [
                            'attribute' => 'status',
                            'value' => function ($model) {
                                return $model->status ? 'Active' : 'Inactive';
                            },
                            'contentOptions' => function ($model) {
                                return ['class' => $model->status ? 'text-success fw-bold' : 'text-danger fw-bold'];
                            },
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

</div>
