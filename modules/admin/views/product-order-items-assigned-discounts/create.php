<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductOrderItemsAssignedDiscounts */

$this->title = Yii::t('app', 'Create Product Order Items Assigned Discounts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Order Items Assigned Discounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-order-items-assigned-discounts-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
