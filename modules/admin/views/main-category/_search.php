<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\MainCategorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-main-category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>

    <?= $form->field($model, 'image')->textInput(['maxlength' => true, 'placeholder' => 'Image']) ?>

    <?= $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions()) ?>

    <?= $form->field($model, 'offer_percentage')->textInput(['placeholder' => 'Offer Percentage']) ?>

    <?php /* echo $form->field($model, 'is_required_documents')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <?php /* echo $form->field($model, 'show_home')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'sortOrder')->textInput(['placeholder' => 'SortOrder']) */ ?>

    <?php /* echo $form->field($model, 'position')->textInput(['placeholder' => 'Position']) */ ?>

    <?php /* echo $form->field($model, 'type_id')->textInput(['placeholder' => 'Type']) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
