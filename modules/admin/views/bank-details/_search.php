<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\BankDetailsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-bank-details-search">

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

    <?= $form->field($model, 'bank_account_holder_name')->textInput(['maxlength' => true, 'placeholder' => 'Bank Account Holder Name']) ?>

    <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true, 'placeholder' => 'Bank Name']) ?>

    <?= $form->field($model, 'bank_account_number')->textInput(['maxlength' => true, 'placeholder' => 'Bank Account Number']) ?>

    <?php /* echo $form->field($model, 'ifsc')->textInput(['maxlength' => true, 'placeholder' => 'Ifsc']) */ ?>

    <?php /* echo $form->field($model, 'branch')->textInput(['maxlength' => true, 'placeholder' => 'Branch']) */ ?>

    <?php /* echo $form->field($model, 'branch_address')->textInput(['maxlength' => true, 'placeholder' => 'Branch Address']) */ ?>

    <?php /* echo $form->field($model, 'upi')->textInput(['maxlength' => true, 'placeholder' => 'Upi']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
