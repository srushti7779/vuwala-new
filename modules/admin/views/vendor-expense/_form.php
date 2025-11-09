<?php

use app\modules\admin\models\base\VendorExpense;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\VendorExpense */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="vendor-expense-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'expense_type_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorExpenseType::find()->orderBy('id')->asArray()->all(), 'id', 'type'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor expenses types')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>
<?= $form->field($model, 'payment_mode')->dropDownList(
    VendorExpense::getPaymentModes(),
    ['prompt' => 'Select Payment Mode']
) ?>


    <?= $form->field($model, 'expense_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Expense Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true, 'placeholder' => 'Amount']) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'image_url')->textInput(['maxlength' => true, 'placeholder' => 'Image Url']) ?>

    <?= $form->field($model, 'status')->dropDownList(
        $model->getStateOptions(),
        ['prompt'=> 'Select Status']
        ) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
