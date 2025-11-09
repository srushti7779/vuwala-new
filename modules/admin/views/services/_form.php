<?php

use app\modules\admin\models\SubCategory;
use app\modules\admin\models\ServiceType;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Services */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos' => \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'CartItems',
        'relID' => 'cart-items',
        'value' => \yii\helpers\Json::encode($model->cartItems),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>




<div class="services-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
        'formConfig' => ['showErrors' => true],
        // set style for proper tooltips error display
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <!-- <?= $form->field($model, 'sub_category_id')->widget(\kartik\widgets\Select2::classname(), [
                'data' => \yii\helpers\ArrayHelper::map(SubCategory::find()->orderBy('id')
                    ->andWhere(['status' => SubCategory::STATUS_ACTIVE])
                    ->andWhere(['vendor_details_id' => null])
                    ->asArray()->all(), 'id', 'title'),
                'options' => ['placeholder' => Yii::t('app', 'Choose Sub category')],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?> -->


    <?= $form->field($model, 'sub_category_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(ServiceType::find()->orderBy('id')
            ->andWhere(['status' => ServiceType::STATUS_ACTIVE])
            // ->andWhere(['id' => 'sub_category_id'])
            ->asArray()->all(), 'id', 'type'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Sub category')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'service_name')->textInput(['maxlength' => true, 'placeholder' => 'Service Name']) ?>



    <?php
    echo $form->field($model, 'image')->textInput([
        'maxlength' => true,
        'placeholder' => 'Enter Image URL', // Placeholder to guide the user
    ]);
    ?>



    <?= $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>




    <?= $form->field($model, 'small_description')->textInput(['maxlength' => true, 'placeholder' => 'Small Description']) ?>

    <?= $form->field($model, 'original_price')->textInput(['placeholder' => 'Original Price']) ?>

    <?= $form->field($model, 'standard_price')->textInput(['placeholder' => 'Standard Price']) ?>

    <?= $form->field($model, 'discount_price')->textInput(['placeholder' => 'Discount Price']) ?>

    <?= $form->field($model, 'max_per_day_services')->textInput(['placeholder' => 'Max Per Day Services']) ?>


    <?= $form->field($model, 'duration')->textInput(['placeholder' => 'Duration']) ?>

    <?= $form->field($model, 'home_visit')->checkbox() ?>

    <?= $form->field($model, 'walk_in')->checkbox() ?>

    <?= $form->field($model, 'service_for')->dropDownList($model->getServiceTypeOptions()) ?>
    <?= $form->field($model, 'benefits')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>

    <?= $form->field($model, 'precautions_recommendation')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>


    <?= $form->field($model, 'why_choose_service')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>


    <?= $form->field($model, 'why_choose_category')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>


    <?= $form->field($model, 'additional_notes')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>


    <?= $form->field($model, 'techinique_points')->widget(\mihaildev\ckeditor\CKEditor::className(), [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>


    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?php if ($model->isNewRecord) { ?> <?php

                                        ?>
    <?php } ?> <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>