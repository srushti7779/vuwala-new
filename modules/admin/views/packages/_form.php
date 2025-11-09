<div class="packages-form">

    <?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

 $form = ActiveForm::begin([
        'id' => 'combo-package-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group']],
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <div class="row">
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

        <!-- Vendor Details & Title -->
        <div class="col-md-6">
            <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
                'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(), 'id', 'business_name'),
                'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
                'pluginOptions' => ['allowClear' => true],
            ]); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'title')->textInput(['placeholder' => 'Title']) ?>
        </div>

        <!-- Price & Discount Price -->
        <div class="col-md-6">
            <?= $form->field($model, 'price')->textInput(['placeholder' => 'Price']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'discount_price')->textInput(['placeholder' => 'Discount Price']) ?>
        </div>

        <!-- Duration & Status -->
        <div class="col-md-6">
            <?= $form->field($model, 'time')->textInput(['placeholder' => 'Duration in minutes']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList(
                $model->getStateOptions(),
                ['prompt'=> 'Select Status']
            ) ?>
        </div>

        <!-- Services (full width) -->
        <div class="col-md-12">
            <?= $form->field($model, 'services_ids')->widget(\kartik\widgets\Select2::classname(), [
                'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\Services::find()->all(), 'id', 'service_name'),
                'options' => ['placeholder' => 'Select Services...', 'multiple' => true],
                'pluginOptions' => ['allowClear' => true],
            ]); ?>
        </div>
    </div>
    
 <div class='col-lg-6'>    <?= $form->field($model, 'is_home_visit')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_walk_in')->checkbox()  ?> </div>

 <?= $form->field($model, 'service_for')->dropDownList([
    1 => 'Male',
    2 => 'Female',
    3 => 'Unisex',
], ['prompt' => 'Select Service for...']) ?>


    <!-- Combo Services Tab -->
    <?php
    // $forms = [
    //     [
    //         'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'ComboServices')),
    //         'content' => $this->render('_formComboServices', [
    //             'row' => \yii\helpers\ArrayHelper::toArray($model->comboServices),
    //         ]),
    //     ],
    // ];
    // echo kartik\tabs\TabsX::widget([
    //     'items' => $forms,
    //     'position' => kartik\tabs\TabsX::POS_ABOVE,
    //     'encodeLabels' => false,
    //     'pluginOptions' => [
    //         'bordered' => true,
    //         'sideways' => true,
    //         'enableCache' => false,
    //     ],
    // ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
