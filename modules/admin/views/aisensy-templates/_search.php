<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\AisensyTemplatesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-aisensy-templates-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'external_id')->textInput(['maxlength' => true, 'placeholder' => 'External']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Name']) ?>

    <?= $form->field($model, 'category')->textInput(['maxlength' => true, 'placeholder' => 'Category']) ?>

    <?= $form->field($model, 'language')->textInput(['maxlength' => true, 'placeholder' => 'Language']) ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'quality_score')->textInput(['placeholder' => 'Quality Score']) */ ?>

    <?php /* echo $form->field($model, 'rejected_reason')->textInput(['maxlength' => true, 'placeholder' => 'Rejected Reason']) */ ?>

    <?php /* echo $form->field($model, 'footer_text')->textInput(['maxlength' => true, 'placeholder' => 'Footer Text']) */ ?>

    <?php /* echo $form->field($model, 'body_text')->textarea(['rows' => 6]) */ ?>

    <?php /* echo $form->field($model, 'meta')->textInput(['placeholder' => 'Meta']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
