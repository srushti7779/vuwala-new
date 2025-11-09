<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Sku */

$this->title = Yii::t('app', 'Create Sku');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Skus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
