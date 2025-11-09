<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ReelsViewCounts */

$this->title = Yii::t('app', 'Create Reels View Counts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reels View Counts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reels-view-counts-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
