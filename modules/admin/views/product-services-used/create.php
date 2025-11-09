<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\ProductServicesUsed */

$this->title = Yii::t('app', 'Create Product Services Used');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Product Services Useds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-services-used-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
