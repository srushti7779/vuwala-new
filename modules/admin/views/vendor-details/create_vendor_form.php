<?php

    use app\modules\admin\models\MainCategory;
    use app\modules\admin\models\WebSetting;
    use kartik\file\FileInput;
    use kartik\form\ActiveForm;
    use kartik\widgets\Select2;
    use mihaildev\ckeditor\CKEditor;
    use pigolab\locationpicker\CoordinatesPicker;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\web\JsExpression;

?>
<style>
.vendor-details-form {
    background-color: #fdfdfd;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
}

.form-group label {
    font-weight: 500;
}

input.form-control, textarea.form-control, select.form-control {
    border-radius: 6px;
}

button.btn {
    border-radius: 25px;
}

</style>

<?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
<?php if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?php echo $type ?> alert-dismissible fade show" role="alert">
            <?php echo Yii::$app->session->getFlash($type) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php
    // AUTO REDIRECT on success
    if (Yii::$app->session->hasFlash('success')) {
        $redirectUrl = Url::to(['/admin/profile/index']);
        $this->registerJs("
        setTimeout(function() {
            window.location.href = '{$redirectUrl}';
        }, 1500);
    ");
    }

    $userRole = Yii::$app->user->identity->role ?? null;
?>

<div class="vendor-details-form container-fluid p-4 bg-white shadow rounded">

    <?php $form = ActiveForm::begin(['id' => 'vendor-create-form']); ?>

    <!-- Section: Vendor User Details -->
    <h4 class="section-title mb-4 text-primary">Vendor User Details</h4>
    <div class="row g-3">
        <?php if (empty($skipUser) || ! $skipUser): ?>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'first_name')?></div>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'last_name')?></div>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'username')?></div>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'email')?></div>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'contact_no')?></div>
            <div class="col-md-6"><?php echo $form->field($vendorUser, 'password_hash')->passwordInput()?></div>
        <?php endif; ?>
    </div>

    <!-- Section: Business Details -->
    <h4 class="section-title mt-5 mb-4 text-primary">Business Information</h4>
    <?php echo $form->errorSummary($storeModel, ['class' => 'alert alert-danger'])?>
    <?php echo $form->field($storeModel, 'id')->hiddenInput()->label(false)?>

    <div class="row g-3">
        <div class="col-md-6"><?php echo $form->field($storeModel, 'business_name')?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'website_link')?></div>
        <div class="col-md-12">
            <?php echo $form->field($storeModel, 'description')->widget(CKEditor::className(), [
    'editorOptions' => ['preset' => 'full'],
])?>
        </div>

    <div class="col-md-12">
   <?= $form->field($storeModel, 'main_category_ids')->widget(\kartik\select2\Select2::classname(), [
    'data' => $mainCategoryList,
    'options' => [
        'placeholder' => 'Choose Main Categories',
        'multiple' => true
    ],
    'pluginOptions' => [
        'allowClear' => true
    ],
]); ?>

</div>



        <div class="col-md-6"><?php echo $form->field($storeModel, 'gst_number')?></div>

        <?php if ($userRole === 'admin'): ?>
            <div class="col-md-6"><?php echo $form->field($storeModel, 'is_gst_number_verified')->checkbox()?></div>
            <div class="col-md-6"><?php echo $form->field($storeModel, 'qr_scan_discount_percentage')->textInput(['type' => 'number', 'step' => '0.01'])?></div>
            <div class="col-md-6"><?php echo $form->field($storeModel, 'min_service_fee')?></div>
            <div class="col-md-6"><?php echo $form->field($storeModel, 'convenience_fee')?></div>
            <div class="col-md-4"><?php echo $form->field($storeModel, 'discount')?></div>
            <div class="col-md-4"><?php echo $form->field($storeModel, 'is_premium')->dropDownList($storeModel->getOptionsPremium())?></div>
            <div class="col-md-4"><?php echo $form->field($storeModel, 'is_featured')->dropDownList($storeModel->getFeatureOptions())?></div>
            <div class="col-md-4"><?php echo $form->field($storeModel, 'is_verified')->checkbox()?></div>
        <?php endif; ?>

        <div class="col-md-6"><?php echo $form->field($storeModel, 'account_number')?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'ifsc_code')?></div>
    </div>

    <!-- Section: Location Picker -->
    <h4 class="section-title mt-5 mb-4 text-primary">Location</h4>
    <div class="row">
        <div class="col-12 mb-3">
            <?php
                $map_key       = (new WebSetting())->getSettingBykey('google_map_api_key');
                $latId         = Html::getInputId($storeModel, 'latitude');
                $lngId         = Html::getInputId($storeModel, 'longitude');
                $radiusId      = Html::getInputId($storeModel, 'service_radius');
                $addressId     = Html::getInputId($storeModel, 'address');
                $localityId    = Html::getInputId($storeModel, 'locality');
                $sublocalityId = Html::getInputId($storeModel, 'sublocality');
                $adminAreaId   = Html::getInputId($storeModel, 'administrative_area');
                $postalCodeId  = Html::getInputId($storeModel, 'postal_code');
                $countryId     = Html::getInputId($storeModel, 'country');

                echo $form->field($storeModel, 'coordinates')->widget(CoordinatesPicker::className(), [
                    'key'             => $map_key,
                    'options'         => ['style' => 'width: 100%; height: 400px'],
                    'enableSearchBox' => true,
                    'clientOptions'   => [
                        'location'     => [
                            'latitude'  => $storeModel->latitude ?: '17.446366',
                            'longitude' => $storeModel->longitude ?: '78.392414',
                        ],
                        'inputBinding' => [
                            'latitudeInput'  => new JsExpression("$('#{$latId}')"),
                            'longitudeInput' => new JsExpression("$('#{$lngId}')"),
                            'radiusInput'    => new JsExpression("$('#{$radiusId}')"),
                        ],
                    ],
                ]);
            ?>
        </div>

        <div class="col-md-4"><?php echo $form->field($storeModel, 'latitude')->textInput(['id' => $latId])?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'longitude')->textInput(['id' => $lngId])?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'service_radius')->textInput(['id' => $radiusId])?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'address')->textarea(['rows' => 2, 'id' => $addressId])?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'locality')->textInput(['id' => $localityId])?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'sublocality')->textInput(['id' => $sublocalityId])?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'postal_code')->textInput(['id' => $postalCodeId])?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'administrative_area')->textInput(['id' => $adminAreaId])?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'country')->textInput(['id' => $countryId])?></div>
    </div>

    <!-- Section: Other Information -->
    <h4 class="section-title mt-5 mb-4 text-primary">Other Information</h4>
    <div class="row g-3">
        <div class="col-md-6"><?php echo $form->field($storeModel, 'logo')->widget(FileInput::classname(), [
    'options'       => ['accept' => 'image/*'],
    'pluginOptions' => [
        'initialPreview'       => [$storeModel->logo],
        'initialPreviewAsData' => true,
        'overwriteInitial'     => true,
        'showUpload'           => false,
    ],
])?></div>
        <div class="col-md-6"><?php echo $form->field($storeModel, 'shop_licence_no')?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'gender_type')->dropDownList(['' => 'Select Gender'] + $storeModel->getGenderOptions())?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'status')->dropDownList($storeModel->getStateOptions())?></div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4"><?php echo $form->field($storeModel, 'service_type_home_visit')->checkbox()?></div>
        <div class="col-md-4"><?php echo $form->field($storeModel, 'service_type_walk_in')->checkbox()?></div>
    </div>

    <div class="form-group mt-5 text-center">
        <?php echo Html::submitButton($storeModel->isNewRecord ? 'Create Vendor' : 'Update Vendor', ['class' => 'btn btn-lg btn-success px-5'])?>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<?php
    $apiKey = (new WebSetting())->getSettingBykey('app_google_map_api');

    $js = <<<JS
function fetchAddressFromCoords(lat, lng) {
    const url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lng + '&key={$apiKey}';
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'OK' && data.results && data.results.length) {
                const result = data.results[0];
                const components = result.address_components;

                $('#{$addressId}').val(result.formatted_address);

                components.forEach(component => {
                    const types = component.types;
                    if (types.indexOf('locality') !== -1) {
                        $('#{$localityId}').val(component.long_name);
                    }
                    if (types.indexOf('sublocality') !== -1 || types.indexOf('sublocality_level_1') !== -1) {
                        $('#{$sublocalityId}').val(component.long_name);
                    }
                    if (types.indexOf('administrative_area_level_1') !== -1) {
                        $('#{$adminAreaId}').val(component.long_name);
                    }
                    if (types.indexOf('postal_code') !== -1) {
                        $('#{$postalCodeId}').val(component.long_name);
                    }
                    if (types.indexOf('country') !== -1) {
                        $('#{$countryId}').val(component.long_name);
                    }
                });
            }
        })
        .catch(err => console.error('Geocoding error:', err));
}

$('#{$latId}, #{$lngId}').on('change', function() {
    const lat = $('#{$latId}').val();
    const lng = $('#{$lngId}').val();
    if (lat && lng) {
        fetchAddressFromCoords(lat, lng);
    }
});
JS;

    $this->registerJs($js);
?>
