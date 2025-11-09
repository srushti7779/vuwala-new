<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\CartSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-cart-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\User::find()->orderBy('id')->asArray()->all(), 'id', 'username'),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'quantity')->textInput(['placeholder' => 'Quantity']) ?>

    <?= $form->field($model, 'amount')->textInput(['placeholder' => 'Amount']) ?>

    <?php /* echo $form->field($model, 'tip')->textInput(['placeholder' => 'Tip']) */ ?>

    <?php /* echo $form->field($model, 'wallet')->textInput(['placeholder' => 'Wallet']) */ ?>

    <?php /* echo $form->field($model, 'service_instructions')->textInput(['maxlength' => true, 'placeholder' => 'Service Instructions']) */ ?>

    <?php /* echo $form->field($model, 'details')->textInput(['maxlength' => true, 'placeholder' => 'Details']) */ ?>

    <?php /* echo $form->field($model, 'cgst')->textInput(['placeholder' => 'Cgst']) */ ?>

    <?php /* echo $form->field($model, 'sgst')->textInput(['placeholder' => 'Sgst']) */ ?>

    <?php /* echo $form->field($model, 'coupon_code')->textInput(['maxlength' => true, 'placeholder' => 'Coupon Code']) */ ?>

    <?php /* echo $form->field($model, 'coupon_discount')->textInput(['placeholder' => 'Coupon Discount']) */ ?>

    <?php /* echo $form->field($model, 'coupon_applied_id')->textInput(['placeholder' => 'Coupon Applied']) */ ?>

    <?php /* echo $form->field($model, 'service_fees')->textInput(['placeholder' => 'Service Fees']) */ ?>

    <?php /* echo $form->field($model, 'other_charges')->textInput(['placeholder' => 'Other Charges']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'service_address')->textInput(['placeholder' => 'Service Address']) */ ?>

    <?php /* echo $form->field($model, 'service_time')->textInput(['maxlength' => true, 'placeholder' => 'Service Time']) */ ?>

    <?php /* echo $form->field($model, 'service_date')->textInput(['maxlength' => true, 'placeholder' => 'Service Date']) */ ?>

    <?php /* echo $form->field($model, 'type_id')->textInput(['placeholder' => 'Type']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
