<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductOrdersHasDiscounts */

$this->title = Yii::t('app', 'Create Product Orders Has Discounts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Orders Has Discounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-orders-has-discounts-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
