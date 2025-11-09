<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Staff */
/* @var $form yii\widgets\ActiveForm */
$vendor_details_id = Yii::$app->request->get('vendor_details_id', null);
// print_r($vendor_details_id);

?>

<div class="staff-form">

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

  <?php

        $user = Yii::$app->user->identity;

        $query = VendorDetails::find()->where(['status' => VendorDetails::STATUS_ACTIVE]);

        if ($user->user_role === User::ROLE_VENDOR) {
            $query->andWhere(['user_id' => $user->id]);
        } elseif ($user->user_role === User::ROLE_ADMIN) {
        }

        $vendorList = ArrayHelper::map(
            $query->orderBy('id')->asArray()->all(),
            'id',
            'business_name'
        );
        ?>

        <?= $form->field($model, 'vendor_details_id')->widget(\kartik\widgets\Select2::classname(), [
            'data' => $vendorList,
            'options' => ['placeholder' => Yii::t('app', 'Choose Vendor details')],
            'pluginOptions' => [
                'allowClear' => false
            ],
        ]); 
        
        ?>




     <?php
    echo $form->field($model, 'profile_image')->widget(FileInput::classname(), [
        'options' => ['multiple' => false, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => [
                $model->profile_image,
            ],
            'initialPreviewAsData' => true,

            'overwriteInitial' => true,

            'showUpload' => false,
        ],
    ]);

    ?> 




    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true, 'placeholder' => 'Mobile No']) ?>

    <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => 'Full Name']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Email']) ?>



    <?= $form->field($model, 'gender')->dropDownList($model->getGenderOptions()) ?>



    <?= $form->field($model, 'dob')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Dob'),
                'autoclose' => true
            ]
        ],
    ]); ?>
 

<?= $form->field($model, 'experience')->textInput(['maxlength' => true, 'placeholder' => 'Experience']) ?>

<?= $form->field($model, 'specialization')->textInput(['maxlength' => true, 'placeholder' => 'Specialization']) ?>


    <?= $form->field($model, 'role')->dropDownList($model->getRoles()) ?>

    <?= $form->field($model, 'current_status')->dropDownList($model->getCurrentStatusOptions()) ?> 
    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

<?php if($model->isNewRecord){ ?><?php } ?>    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
  
    <?php ActiveForm::end(); ?>

</div> 
