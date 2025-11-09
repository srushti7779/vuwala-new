<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\search\ServicesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-services-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'id'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'sub_category_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\SubCategory::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Sub category')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'service_name')->textInput(['maxlength' => true, 'placeholder' => 'Service Name']) ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'placeholder' => 'Slug']) ?>

    <?php /* echo $form->field($model, 'image')->textInput(['maxlength' => true, 'placeholder' => 'Image']) */ ?>

    <?php /* echo $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(),[
                'editorOptions' => [
                    'preset' => 'full',
                    'inline' => false, 
                ],
            ]) */ ?>

    <?php /* echo $form->field($model, 'small_description')->textInput(['maxlength' => true, 'placeholder' => 'Small Description']) */ ?>

    <?php /* echo $form->field($model, 'original_price')->textInput(['placeholder' => 'Original Price']) */ ?>

    <?php /* echo $form->field($model, 'standard_price')->textInput(['placeholder' => 'Standard Price']) */ ?>

    <?php /* echo $form->field($model, 'discount_price')->textInput(['placeholder' => 'Discount Price']) */ ?>

    <?php /* echo $form->field($model, 'max_per_day_services')->textInput(['placeholder' => 'Max Per Day Services']) */ ?>

    <?php /* echo $form->field($model, 'price')->textInput(['placeholder' => 'Price']) */ ?>

    <?php /* echo $form->field($model, 'duration')->textInput(['placeholder' => 'Duration']) */ ?>

    <?php /* echo $form->field($model, 'home_visit')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'walk_in')->checkbox() */ ?>

    <?php /* echo $form->field($model, 'type')->textInput(['placeholder' => 'Type']) */ ?>

    <?php /* echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) */ ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
