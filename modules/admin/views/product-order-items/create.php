<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductOrderItems */

$this->title = Yii::t('app', 'Create Product Order Items');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Order Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-order-items-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
