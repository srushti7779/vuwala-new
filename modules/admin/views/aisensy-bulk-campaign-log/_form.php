<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkCampaignLog */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'AisensyBulkMessageLog', 
        'relID' => 'aisensy-bulk-message-log', 
        'value' => \yii\helpers\Json::encode($model->aisensyBulkMessageLogs),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="aisensy-bulk-campaign-log-form">

    <?php $form = ActiveForm::begin([
    'id' => 'login-form-inline',
    'type' => ActiveForm::TYPE_VERTICAL,
    'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
    'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
    'formConfig' => ['showErrors' => true],
    // set style for proper tooltips error display
    ]); ?>

    <?= $form->errorSummary($model); ?>
    <div class="row">
         <div class='col-lg-6 '>   <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

 </div> <div class='col-lg-6'>    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'campaign_name')->textInput(['maxlength' => true, 'placeholder' => 'Campaign Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'template_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\AisensyTemplates::find()->orderBy('id')->asArray()->all(), 'id', 'name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Aisensy templates')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'total_contacts')->textInput(['placeholder' => 'Total Contacts'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sent_count')->textInput(['placeholder' => 'Sent Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'delivered_count')->textInput(['placeholder' => 'Delivered Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'failed_count')->textInput(['placeholder' => 'Failed Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'skipped_count')->textInput(['placeholder' => 'Skipped Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'campaign_status')->dropDownList([ 'pending' => 'Pending', 'running' => 'Running', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled', ], ['prompt' => ''])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'started_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Started At'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'completed_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Completed At'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'AisensyBulkMessageLog')),
            'content' => $this->render('_formAisensyBulkMessageLog', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->aisensyBulkMessageLogs),
            ]),
        ],
    ];
    echo kartik\tabs\TabsX::widget([
        'items' => $forms,
        'position' => kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'pluginOptions' => [
            'bordered' => true,
            'sideways' => true,
            'enableCache' => false,
        ],
    ]);
    ?>
<?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>