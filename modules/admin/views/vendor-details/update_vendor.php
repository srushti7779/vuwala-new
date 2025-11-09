<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorDetails */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Vendor Details',
]) . ' ' . $storeModel->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' =>  $storeModel->id, 'url' => ['view', 'id' =>  $storeModel->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="vendor-details-update">
<div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

   <?= $this->render('update_vendor_form', [
    'model' => $storeModel,
    'mainCategoryList' => $mainCategoryList, 
]) ?>

</div>
</div>
</div>
