<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyTemplates */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'AisensyTemplateComponents', 
        'relID' => 'aisensy-template-components', 
        'value' => \yii\helpers\Json::encode($model->aisensyTemplateComponents),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'AisensyTemplateLinks', 
        'relID' => 'aisensy-template-links', 
        'value' => \yii\helpers\Json::encode($model->aisensyTemplateLinks),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="aisensy-templates-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'external_id')->textInput(['maxlength' => true, 'placeholder' => 'External'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Name'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'category')->textInput(['maxlength' => true, 'placeholder' => 'Category'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'language')->textInput(['maxlength' => true, 'placeholder' => 'Language'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'quality_score')->textInput(['placeholder' => 'Quality Score'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'rejected_reason')->textInput(['maxlength' => true, 'placeholder' => 'Rejected Reason'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'footer_text')->textInput(['maxlength' => true, 'placeholder' => 'Footer Text'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'body_text')->textarea(['rows' => 6])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'meta')->textInput(['placeholder' => 'Meta'])  ?> </div>

 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'AisensyTemplateComponents')),
            'content' => $this->render('_formAisensyTemplateComponents', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->aisensyTemplateComponents),
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'AisensyTemplateLinks')),
            'content' => $this->render('_formAisensyTemplateLinks', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->aisensyTemplateLinks),
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