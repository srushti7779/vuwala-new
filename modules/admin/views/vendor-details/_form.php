<?php

use app\modules\admin\models\User;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\VendorMainCategoryData;
use app\modules\admin\models\WebSetting;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;

?>

<div class="vendor-details-form container-fluid">

    <?php $form = ActiveForm::begin([
        'id' => 'vendor-details-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group col-md-6']], // responsive field layout
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?php echo $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

    <?php echo $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <div class="row">
        <?php
        // Fetch vendor user data
        $vendor_details = VendorDetails::find()->all();
        $user_id = [];
        foreach ($vendor_details as $vendor_details_data) {
            $user_id[] = $vendor_details_data->user_id;
        }
        ?>

     <?= $form->field($model, 'description')->textarea([
        'rows' => 6,
        'placeholder' => 'Enter description here...',
        'value' => strip_tags($model->description), // ✅ sanitize here
    ]) ?>

    <?php
        echo $form->field($model, 'main_category_ids')->widget(Select2::classname(), [
        'data' => $mainCategoryList,
        'value' => $model->main_category_ids, // Explicitly set the value
        'options' => [
            'placeholder' => Yii::t('app', 'Choose Main category'),
            'multiple' => true,
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => false,
        ],
    ]);
    ?>

        <?php echo $form->field($model, 'website_link')->textInput(['maxlength' => true, 'placeholder' => 'Website Link']) ?>
        <?php echo $form->field($model, 'gst_number')->textInput(['maxlength' => true, 'placeholder' => 'GST Number']) ?>
        <?php echo $form->field($model, 'is_gst_number_verified')->checkbox() ?>
        <?php echo $form->field($model, 'account_number')->textInput(['maxlength' => true, 'placeholder' => 'Account Number']) ?>
        <?php echo $form->field($model, 'ifsc_code')->textInput(['maxlength' => true, 'placeholder' => 'IFSC Code']) ?>
        <?php echo $form->field($model, 'qr_scan_discount_percentage')->textInput([
            'type' => 'number',
            'min' => 0,
            'step' => '0.01', // Allows decimal entry
            'placeholder' => 'Enter QR Scan Discount Percentage',
            'maxlength' => true,
        ]) ?>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php
            $setting = new WebSetting();
            $map_key = $setting->getSettingBykey('google_map_api_key');
            ?>
            <?php echo $form->field($model, 'coordinates')->widget('\pigolab\locationpicker\CoordinatesPicker', [
                'key' => $map_key,
                'options' => [
                    'style' => 'width: 100%; height: 400px', // map canvas width and height
                ],
                'enableSearchBox' => true,
                'searchBoxOptions' => [
                    'style' => 'width: 300px;',
                ],
                'mapOptions' => [
                    'rotateControl' => true,
                    'scaleControl' => false,
                    'streetViewControl' => true,
                    'mapTypeId' => new JsExpression('google.maps.MapTypeId.SATELLITE'),
                    'heading' => 90,
                    'tilt' => 45,
                    'mapTypeControl' => true,
                    'mapTypeControlOptions' => [
                        'style' => new JsExpression('google.maps.MapTypeControlStyle.HORIZONTAL_BAR'),
                        'position' => new JsExpression('google.maps.ControlPosition.TOP_CENTER'),
                    ],
                ],
                'clientOptions' => [
                    'location' => [
                        'latitude' => $model->latitude ?: '17.446366',
                        'longitude' => $model->longitude ?: '78.392414',
                    ],
                    'radius' => 3000,
                    'addressFormat' => 'street_number',
                    'onchanged' => new JsExpression('function(currentLocation, radius, isMarkerDropped) {
                        var addressComponents = $(this).locationpicker("map").location.addressComponents;
                        console.log(addressComponents);
                    }'),
                    'inputBinding' => [
                        'latitudeInput' => new JsExpression("$('#" . Html::getInputId($model, "latitude") . "')"),
                        'longitudeInput' => new JsExpression("$('#" . Html::getInputId($model, "longitude") . "')"),
                        'radiusInput' => new JsExpression("$('#" . Html::getInputId($model, "service_radius") . "')"),
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <div class="row">
        <?php echo $form->field($model, 'latitude')->textInput(['placeholder' => 'Latitude']) ?>
        <?php echo $form->field($model, 'longitude')->textInput(['placeholder' => 'Longitude']) ?>
        <?php echo $form->field($model, 'address')->textarea(['rows' => 4, 'placeholder' => 'Address']) ?>
        <?php echo $form->field($model, 'locality')->textInput(['id' => 'vendor-details-locality', 'placeholder' => 'Locality']) ?>
        <?php echo $form->field($model, 'sublocality')->textInput(['id' => 'vendor-details-sublocality', 'placeholder' => 'Sublocality']) ?>
        <?php echo $form->field($model, 'postal_code')->textInput(['id' => 'vendor-details-postal-code', 'placeholder' => 'Postal Code']) ?>
        <?php echo $form->field($model, 'administrative_area')->textInput(['id' => 'vendor-details-admin-area', 'placeholder' => 'State']) ?>
        <?php echo $form->field($model, 'country')->textInput(['id' => 'vendor-details-country', 'placeholder' => 'Country']) ?>
      <?php if (User::isAdmin()): ?>
        <?= $form->field($model, 'avg_rating')->textInput([
            'id' => 'vendor-details-avg_rating',
            'placeholder' => 'avg_rating',
            'type' => 'number',
            'min' => 0,
            'max' => 5,
            'step' => '0.1'
        ]) ?>
    <?php endif; ?>

        
    </div>

    <div class="row">
        <?php
        echo $form->field($model, 'logo')->widget(FileInput::classname(), [
            'options' => ['multiple' => false, 'accept' => 'image/*'],
            'pluginOptions' => [
                'previewFileType' => 'image',
                'initialPreview' => [
                    $model->logo,
                ],
                'initialPreviewAsData' => true,
                'overwriteInitial' => true,
                'showUpload' => false,
            ],
        ]);
        ?>
    </div>

    <div class="row">
        <?php echo $form->field($model, 'shop_licence_no')->textInput(['maxlength' => true, 'placeholder' => 'Shop License No']) ?>
        <?php echo $form->field($model, 'service_radius')->textInput(['placeholder' => 'Service Radius']) ?>
        <?php echo $form->field($model, 'min_service_fee')->textInput(['placeholder' => 'Convenience Fee']) ?>
        <?php echo $form->field($model, 'discount')->textInput(['placeholder' => 'Discount']) ?>
        <?php 
        echo $form->field($model, 'gender_type')->dropDownList(
            (['' => 'Select Gender'] + $model->getGenderOptions())
        );
        ?>
        <?php echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>
        <?php echo $form->field($model, 'is_premium')->dropDownList($model->getOptionsPremium()) ?>
        <?php echo $form->field($model, 'is_featured')->dropDownList($model->getFeatureOptions()) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'is_verified')->checkbox() ?>
        <?= $form->field($model, 'service_type_home_visit')->checkbox() ?>
        <?= $form->field($model, 'service_type_walk_in')->checkbox() ?>
    </div>
    
    <div class="form-group text-center">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
    <?php
    $apiKey = (new WebSetting())->getSettingBykey('app_google_map_api');
    
    $this->registerJs("
    function fetchAddressFromCoords(lat, lng) {
        const url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key=" . $apiKey . "';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'OK') {
                    const result = data.results[0];
                    const components = result.address_components;

                    $('#vendordetails-address').val(result.formatted_address);

                    components.forEach(component => {
                        const types = component.types;

                        if (types.includes('locality')) {
                            $('#vendor-details-locality').val(component.long_name);
                        }
                        if (types.includes('sublocality') || types.includes('sublocality_level_1')) {
                            $('#vendor-details-sublocality').val(component.long_name);
                        }
                        if (types.includes('administrative_area_level_1')) {
                            $('#vendor-details-admin-area').val(component.long_name);
                        }
                        if (types.includes('postal_code')) {
                            $('#vendor-details-postal-code').val(component.long_name);
                        }
                        if (types.includes('country')) {
                            $('#vendor-details-country').val(component.long_name);
                        }
                    });
                }
            })
            .catch(err => console.error('Geocoding error:', err));
    }

    $('#vendordetails-latitude, #vendordetails-longitude').on('change', function() {
        const lat = $('#vendordetails-latitude').val();
        const lng = $('#vendordetails-longitude').val();
        if (lat && lng) {
            fetchAddressFromCoords(lat, lng);
        }
    });
");
    ?>
</div>