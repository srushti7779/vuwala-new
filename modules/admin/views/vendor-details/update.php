<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorDetails */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Vendor Details',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="vendor-details-update">
<div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

   <?= $this->render('_form', [
    'model' => $model,
    'mainCategoryList' => $mainCategoryList, 
]) ?>

</div>
</div>
</div>
