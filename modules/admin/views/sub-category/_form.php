<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\SubCategory */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="sub-category-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true, // shows tooltip styled validation error feedback
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']], // spacing field groups
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>


    <?= $form->field($model, 'main_category_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => [
            'placeholder' => Yii::t('app', 'Choose Main Category'),
            'id' => 'main-category-id', // Set an ID for jQuery selection
        ],
        'pluginOptions' => ['allowClear' => true],
    ]); ?>

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


    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>



 
    
    <?php 
    echo $form->field($model, 'image')->widget(FileInput::classname(), [
        'options' => ['multiple' => false, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => [
                $model->image,
            ],
            'initialPreviewAsData' => true,

            'overwriteInitial' => true,

            'showUpload' => false,
        ],
    ]);

    ?>


    <?= $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions()) ?>


    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>

    <?= $form->field($model, 'sortOrder')->textInput(['placeholder' => 'SortOrder']) ?>

    <?= $form->field($model, 'type_id')->textInput(['placeholder' => 'Type']) ?>

    <?php if ($model->isNewRecord) {

    ?><?php } ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
    $('#main-category-id').on('change', function() {
        var categoryId = $(this).val();
        $.ajax({
            url: 'get-vendors-by-category', // URL to your controller action
            type: 'GET',
            data: { category_id: categoryId },
            success: function(data) {
                var vendorDropdown = $('#vendor-details-id');
                vendorDropdown.empty().append('<option value="">Choose Vendor</option>'); // Reset dropdown

                if (data.length > 0) {
                    $.each(data, function(index, vendor) {
                        vendorDropdown.append('<option value="'+ vendor.id +'">'+ vendor.text +'</option>');
                    });
                }
            }
        });
    });
JS);
?>