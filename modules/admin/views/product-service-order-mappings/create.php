<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductServiceOrderMappings */

$this->title = Yii::t('app', 'Create Product Service Order Mappings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Service Order Mappings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-service-order-mappings-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
