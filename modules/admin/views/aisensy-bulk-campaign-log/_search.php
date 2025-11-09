<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\AisensyBulkCampaignLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-aisensy-bulk-campaign-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'campaign_name')->textInput(['maxlength' => true, 'placeholder' => 'Campaign Name']) ?>

    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\AisensyTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Aisensy templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'total_contacts')->textInput(['placeholder' => 'Total Contacts']) ?>

    <?= $form->field($model, 'sent_count')->textInput(['placeholder' => 'Sent Count']) ?>

    <?php /* echo $form->field($model, 'delivered_count')->textInput(['placeholder' => 'Delivered Count']) */ ?>

    <?php /* echo $form->field($model, 'failed_count')->textInput(['placeholder' => 'Failed Count']) */ ?>

    <?php /* echo $form->field($model, 'skipped_count')->textInput(['placeholder' => 'Skipped Count']) */ ?>

    <?php /* echo $form->field($model, 'campaign_status')->dropDownList([ 'pending' => 'Pending', 'running' => 'Running', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled', ], ['prompt' => '']) */ ?>

    <?php /* echo $form->field($model, 'started_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Started At'),
                'autoclose' => true,
            ]
        ],
    ]); */ ?>

    <?php /* echo $form->field($model, 'completed_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Completed At'),
                'autoclose' => true,
            ]
        ],
    ]); */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
