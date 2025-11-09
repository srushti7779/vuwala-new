<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ServiceOrdersProductOrders */

$this->title = Yii::t('app', 'Create Service Orders Product Orders');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Orders Product Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-orders-product-orders-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
