<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductTypes */

$this->title = Yii::t('app', 'Create Product Types');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-types-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
