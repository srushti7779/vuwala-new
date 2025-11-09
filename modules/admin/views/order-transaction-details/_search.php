<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\OrderTransactionDetailsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-order-transaction-details-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'order_id')->textInput(['placeholder' => 'Order']) ?>

    <?= $form->field($model, 'razorpay_order_id')->textInput(['maxlength' => true, 'placeholder' => 'Razorpay Order']) ?>

    <?= $form->field($model, 'payment_id')->textInput(['maxlength' => true, 'placeholder' => 'Payment']) ?>

    <?= $form->field($model, 'order_type')->textInput(['placeholder' => 'Order Type']) ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
