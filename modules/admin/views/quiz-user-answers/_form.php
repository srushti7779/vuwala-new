<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\QuizUserAnswers */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="quiz-user-answers-form">

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

 <div class='col-lg-6'>    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\User::find()->orderBy('id')->asArray()->all(), 'id', 'username'),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'quiz_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Quizzes::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Quizzes')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'question_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\QuizQuestions::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Quiz questions')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'answer_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\QuizAnswers::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Quiz answers')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_correct')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'answered_on')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        'saveFormat' => 'php:Y-m-d H:i:s',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Answered On'),
                'autoclose' => true,
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

    <?php ActiveForm::end(); ?>

</div>