<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\StaffSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-staff-search">

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

    <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true, 'placeholder' => 'Profile Image']) ?>

    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true, 'placeholder' => 'Mobile No']) ?>

    <?php /* echo $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => 'Full Name']) */ ?>

    <?php /* echo $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email']) */ ?>

    <?php /* echo $form->field($model, 'gender')->textInput(['placeholder' => 'Gender']) */ ?>

    <?php /* echo $form->field($model, 'dob')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Dob'),
                'autoclose' => true
            ]
        ],
    ]); */ ?>

    <?php /* echo $form->field($model, 'experience')->textInput(['maxlength' => true, 'placeholder' => 'Experience']) */ ?>

    <?php /* echo $form->field($model, 'specialization')->textInput(['maxlength' => true, 'placeholder' => 'Specialization']) */ ?>

    <?php /* echo $form->field($model, 'role')->textInput(['maxlength' => true, 'placeholder' => 'Role']) */ ?>

    <?php /* echo $form->field($model, 'current_status')->textInput(['placeholder' => 'Current Status']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
