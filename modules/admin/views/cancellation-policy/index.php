<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\CancellationPolicySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Cancellation Policies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cancellation-policy-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Cancellation Policy'), ['create'], ['class' => 'btn btn-success']) ?>
         <?= Html::a(Yii::t('app', 'Advance Search'), '#', ['class' => 'btn btn-info search-button']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'hours_before',
            'refundable_amount_percentage',
            'status',
            // 'create_user_id',
            //'update_user_id',
            //'updated_by',
            //'updated_on',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
