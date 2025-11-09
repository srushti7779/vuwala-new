<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\OrdersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-orders-search">

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

    <?= $form->field($model, 'json_details')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'qty')->textInput(['placeholder' => 'Qty']) ?>

    <?php /* echo $form->field($model, 'trans_type')->textInput(['maxlength' => true, 'placeholder' => 'Trans Type']) */ ?>

    <?php /* echo $form->field($model, 'payment_type')->textInput(['maxlength' => true, 'placeholder' => 'Payment Type']) */ ?>

    <?php /* echo $form->field($model, 'sub_total')->textInput(['placeholder' => 'Sub Total']) */ ?>

    <?php /* echo $form->field($model, 'tip_amt')->textInput(['placeholder' => 'Tip Amt']) */ ?>

    <?php /* echo $form->field($model, 'tax')->textInput(['placeholder' => 'Tax']) */ ?>

    <?php /* echo $form->field($model, 'processing_charges')->textInput(['placeholder' => 'Processing Charges']) */ ?>

    <?php /* echo $form->field($model, 'service_charge')->textInput(['placeholder' => 'Service Charge']) */ ?>

    <?php /* echo $form->field($model, 'taxable_total')->textInput(['placeholder' => 'Taxable Total']) */ ?>

    <?php /* echo $form->field($model, 'total_w_tax')->textInput(['placeholder' => 'Total W Tax']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'cancel_reason')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'cancel_description')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'schedule_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Schedule Date'),
                'autoclose' => true
            ]
        ],
    ]); */ ?>

    <?php /* echo $form->field($model, 'schedule_time')->textInput(['maxlength' => true, 'placeholder' => 'Schedule Time']) */ ?>

    <?php /* echo $form->field($model, 'service_instruction')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'voucher_code')->textInput(['maxlength' => true, 'placeholder' => 'Voucher Code']) */ ?>

    <?php /* echo $form->field($model, 'voucher_amount')->textInput(['placeholder' => 'Voucher Amount']) */ ?>

    <?php /* echo $form->field($model, 'voucher_type')->textInput(['maxlength' => true, 'placeholder' => 'Voucher Type']) */ ?>

    <?php /* echo $form->field($model, 'payment_status')->textInput(['placeholder' => 'Payment Status']) */ ?>

    <?php /* echo $form->field($model, 'ip_ress')->textInput(['maxlength' => true, 'placeholder' => 'Ip Ress']) */ ?>

    <?php /* echo $form->field($model, 'service_address')->textInput(['placeholder' => 'Service Address']) */ ?>

    <?php /* echo $form->field($model, 'otp')->textInput(['placeholder' => 'Otp']) */ ?>

    <?php /* echo $form->field($model, 'cgst')->textInput(['placeholder' => 'Cgst']) */ ?>

    <?php /* echo $form->field($model, 'sgst')->textInput(['placeholder' => 'Sgst']) */ ?>

    <?php /* echo $form->field($model, 'is_verify')->textInput(['placeholder' => 'Is Verify']) */ ?>

    <?php /* echo $form->field($model, 'service_type')->textInput(['placeholder' => 'Service Type']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
