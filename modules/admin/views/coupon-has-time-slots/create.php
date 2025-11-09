<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\CouponHasTimeSlots */

$this->title = Yii::t('app', 'Create Coupon Has Time Slots');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coupon Has Time Slots'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="coupon-has-time-slots-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
