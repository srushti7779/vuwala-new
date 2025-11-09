<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Banner */
/* @var $form yii\widgets\ActiveForm */

\mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos'=> \yii\web\View::POS_END, 
    'viewParams' => [
        'class' => 'BannerTimings', 
        'relID' => 'banner-timings', 
        'value' => \yii\helpers\Json::encode($model->bannerTimings),
        'isNewRecord' => ($model->isNewRecord) ? 1 : 0
    ]
]);
?>

<div class="banner-form">

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

 <?= $form->field($model, 'main_category_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\modules\admin\models\MainCategory::find()->orderBy('id')->asArray()->all(), 'id', 'title'),
        'options' => [
            'placeholder' => Yii::t('app', 'Choose Main Category'),
            'id' => 'main-category-id',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
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

 <div class='col-lg-6'>    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title'])  ?> </div>

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

 <div class='col-lg-6'>    <?= $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(),[
                'editorOptions' => [
                    'preset' => 'full',
                    'inline' => false, 
                ],
            ])  ?> </div>





 <div class='col-lg-6'>    <?= $form->field($model, 'position')->textInput(['placeholder' => 'Position'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'type_id')->textInput(['placeholder' => 'Type'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'sort_order')->textInput(['placeholder' => 'Sort Order'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'start_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Start Date'),
                'autoclose' => true
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'end_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose End Date'),
                'autoclose' => true
            ]
        ],
    ]);  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'views_count')->textInput(['placeholder' => 'Views Count'])  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_top_banner')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_pop_up_banner')->checkbox()  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'status')->dropDownList($model->getStateOptions())  ?> </div>

 <div class='col-lg-6'>    <?= $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions())  ?> </div>

 </div> <?php if($model->isNewRecord){ ?>    <?php
    $forms = [
        // [
        //     'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'BannerTimings')),
        //     'content' => $this->render('_formBannerTimings', [
        //         'row' => \yii\helpers\ArrayHelper::toArray($model->bannerTimings),
        //     ]),
        // ],
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




<script>
    $(document).ready(function() {
        // Sort order check functionality
        $("#banner-sort").change(function() {
            var sortValue = $(this).val();
            if (sortValue) {
                $.ajax({
                    url: 'check-sort',
                    type: 'post',
                    data: {
                        sort: sortValue
                    },
                    success: function(data) {
                        if (data.status === 'taken') {
                            alert('Sort Order already taken. Please choose another one.');
                            $("#banner-sort").val('');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });

        // Dynamic vendor dropdown based on main category selection
        $('#main-category-id').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: 'vendors-by-category', // Adjust the URL based on your routing rules  
                    type: 'post',
                    data: {
                        main_category_id: categoryId
                    },
                    success: function(data) {
                        var vendors = JSON.parse(data);
                        var vendorSelect = $('#vendor-id');
                        vendorSelect.html('<option></option>'); // Clear existing options
                        $.each(vendors, function(key, value) {
                            vendorSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                $('#vendor-id').html('<option></option>'); // Clear vendor dropdown if no category selected
            }
        });
    });
</script>