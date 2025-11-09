<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoreTimings */

$this->title = Yii::t('app', 'Create Store Timings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Store Timings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="store-timings-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('store_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
