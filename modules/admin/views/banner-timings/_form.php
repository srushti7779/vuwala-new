<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BannerTimings */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="banner-timings-form">

    <?php $form = ActiveForm::begin([
        'id' => 'banner-timings-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'formConfig' => ['showErrors' => true],
        'fieldConfig' => [
            'options' => ['class' => 'form-group col-md-6'],
        ],
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <div class="row">

        <!-- Hidden Fields -->
        <div class="col-md-6" style="display:none">
            <?= $form->field($model, 'id')->textInput() ?>
        </div>

        <div class="col-md-6" style="display:none">
            <?= $form->field($model, 'banner_id')->hiddenInput()->label(false); ?>
        </div>

        <!-- Start Time -->
        <div class="col-md-6">
            <?= $form->field($model, 'start_time')->widget(\kartik\datecontrol\DateControl::class, [
                'type' => \kartik\datecontrol\DateControl::FORMAT_TIME,
                'saveFormat' => 'php:H:i:s',
                'ajaxConversion' => true,
                'options' => [
                    'pluginOptions' => [
                        'placeholder' => Yii::t('app', 'Choose Start Time'),
                        'autoclose' => true
                    ]
                ]
            ]); ?>
        </div>

        <!-- End Time -->
        <div class="col-md-6">
            <?= $form->field($model, 'end_time')->widget(\kartik\datecontrol\DateControl::class, [
                'type' => \kartik\datecontrol\DateControl::FORMAT_TIME,
                'saveFormat' => 'php:H:i:s',
                'ajaxConversion' => true,
                'options' => [
                    'pluginOptions' => [
                        'placeholder' => Yii::t('app', 'Choose End Time'),
                        'autoclose' => true
                    ]
                ]
            ]); ?>
        </div>

        <!-- Status -->
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList(
                $model->getStateOptions(),
                ['prompt' => Yii::t('app', 'Select Status')]
            ); ?>
        </div>

    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
