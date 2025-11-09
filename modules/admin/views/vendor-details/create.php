<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorDetails */

$this->title = Yii::t('app', 'Create Vendor Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendor Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-details-create">
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
