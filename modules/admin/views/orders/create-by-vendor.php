<?php

use app\modules\admin\models\Orders;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\modules\admin\models\Services;
use app\modules\admin\models\StoreTimings;
use app\modules\admin\models\VendorDetails;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

$selectedServices = (array)$selectedServices;
?>

<div class="orders-form">
      <div class="card-header">
     <h2>
        <i class="fas fa-info-circle me-2" style="color: navy;"></i>
        <span style="color: navy;">
            <?= $model->isNewRecord ? 'Create Order' : 'Update Order' ?>
        </span>
    </h2>


    </div>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'enableClientValidation' => true,
        'id' => 'order-form',
    ]); ?>

<!-- Type Radio Buttons (inline) -->
<?= $form->field($model, 'type')->radioList([
    Services::TYPE_WALK_IN => 'Walk-In',
    Services::TYPE_HOME_VISIT => 'Home Visit',
], [
    'item' => function($index, $label, $name, $checked, $value) {
        $checkedAttr = $checked ? 'checked' : '';
        return "
            <label class='radio-inline mr-4'>
                <input type='radio' name='{$name}' value='{$value}' {$checkedAttr} onchange='document.getElementById(\"order-form\").submit();'>
                {$label}
            </label>
        ";
    },
])->label('Service Type') ?>

   <!-- Gender Radio Buttons (inline) -->
<?= $form->field($model, 'gender')->radioList([
    Services::SERVICE_FOR_MALE => 'Male',
    Services::SERVICE_FOR_FEMALE => 'Female',
    Services::SERVICE_FOR_UNISEX => 'Unisex',
], [
    'item' => function($index, $label, $name, $checked, $value) {
        $checkedAttr = $checked ? 'checked' : '';
        return "
            <label class='radio-inline mr-4'>
                <input type='radio' name='{$name}' value='{$value}' {$checkedAttr} onchange='document.getElementById(\"order-form\").submit();'>
                {$label}
            </label>
        ";
    },
])->label('Gender') ?>


    <?= $form->errorSummary($model); ?>

    <!-- Hidden Fields -->
    <?= $form->field($model, 'id', ['template' => '{input}'])->hiddenInput()->label(false); ?>

    <!-- User Dropdown -->
    <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(
            \app\modules\admin\models\User::find()
                ->where(['user_role' => 'user'])
                ->orderBy('id')->asArray()->all(),
            'id', 'username'
        ),
        'options' => ['placeholder' => Yii::t('app', 'Choose User')],
        'pluginOptions' => ['allowClear' => true],
    ]); ?>

    <!-- Services List -->
    <?php if (!empty($services)): ?>
        <div class="form-group">
            <label class="form-label font-weight-bold mb-2">
                <?= Yii::t('app', 'Select Services') ?> <small>(Multiple Allowed)</small>
            </label>

            <!-- Select All Option -->
            <div class="mb-2">
                <label class="font-weight-normal">
                    <input type="checkbox" id="select-all-services"> <strong>Select All</strong>
                </label>
            </div>

            <!-- Scrollable Services -->
            <div class="service-list-scrollable" style="max-height: 300px; overflow-y: auto;">
                <div class="service-list">
                    <?php foreach ($services as $service): ?>
                        <label class="service-card d-block border p-2 mb-2 rounded">
                            <input type="checkbox" class="service-checkbox mr-2" name="Orders[services][]" 
                                value="<?= $service['id'] ?>"
                                <?= in_array($service['id'], $selectedServices) ? 'checked' : '' ?>>

                            <div class="service-info d-inline-block w-75">
                                <strong><?= Html::encode($service['service_name']) ?></strong>

                                <?= $service['type'] == Services::TYPE_WALK_IN 
                                    ? '<span class="badge badge-success ml-2">Walk-In</span>' 
                                    : '<span class="badge badge-danger ml-2">Home Visit</span>' ?>

                                <?php
                                if ($service['service_for'] == Services::SERVICE_FOR_MALE) {
                                    echo '<span class="badge badge-primary ml-2">Male</span>';
                                } elseif ($service['service_for'] == Services::SERVICE_FOR_FEMALE) {
                                    echo '<span class="badge badge-pink ml-2">Female</span>';
                                } else {
                                    echo '<span class="badge badge-info ml-2">Unisex</span>';
                                }
                                ?>

                                <div class="tags mt-1">
                                    <span class="badge badge-light"><?= Html::encode($service['duration']) ?> min</span>
                                    <span class="badge badge-secondary">₹<?= Html::encode($service['price']) ?></span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <?= Yii::t('app', 'No services available for the selected type and gender.') ?>
        </div>
    <?php endif; ?>
     <?= $form->field($model, 'schedule_date')->widget(\kartik\date\DatePicker::classname(), [
        'options' => ['placeholder' => Yii::t('app', 'Choose Schedule Date')],
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
    ]); ?>

<?php
$vendorId = $model->vendor_details_id;

if (!$vendorId) {
    $vendor = VendorDetails::find()
        ->select(['id'])
        ->where(['user_id' => Yii::$app->user->id])
        ->asArray()
        ->one();
    $vendorId = $vendor['id'] ?? 0;
}

$scheduleDate = $model->schedule_date ?? date('Y-m-d');
$dayId = date('N', strtotime($scheduleDate));
$storeTimings = StoreTimings::find()
    ->where([
        'vendor_details_id' => $vendorId,
        'day_id' => $dayId,
        'status' => 1
    ])
    ->all();
           $bookedSlots = Orders::find()
            ->select(['TIME(schedule_time) as time'])
            ->where([
                'vendor_details_id' => $vendorId,
                'schedule_date' => $scheduleDate
            ])
            ->column();

    $bookedSlots = array_map(function($time) {
        return date('H:i', strtotime($time));
    }, $bookedSlots);
    $selectedSlot = $model->schedule_time;
?>

<div class="form-group">
    <label><b>Schedule Time <span style="color: red">*</span></b></label>
    <div class="time-slot-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php if (!empty($storeTimings)): ?>
            <?php foreach ($storeTimings as $timing): ?>
                <?php
                    $start = strtotime($timing->start_time);
                    $end = strtotime($timing->close_time);
                    $interval = 30 * 60;

                    for ($time = $start; $time < $end; $time += $interval):
                        $displayTime = date('h:i A', $time);
                        $rawTime = date('H:i:s', $time);
                        $checkTime = date('H:i', $time);
                        $isBooked = in_array($checkTime, $bookedSlots);
                        $isSelected = ($selectedSlot === $rawTime);
                ?>
                <div 
                    class="time-slot <?= $isBooked ? 'booked' : '' ?> <?= $isSelected ? 'selected' : '' ?>" 
                    data-time="<?= $rawTime ?>" 
                    style="
                        padding: 10px 15px;
                        border: 1px solid #ccc;
                        border-radius: 8px;
                        cursor: <?= $isBooked ? 'not-allowed' : 'pointer' ?>;
                        background-color: <?= $isSelected ? '#6f42c1' : '#fff' ?>;
                        color: <?= $isSelected ? '#fff' : '#000' ?>;
                        user-select: none;
                    ">
                    <?= $displayTime ?>
                </div>
                <?php endfor; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: red;">Vendor has no availability set for this date.</p>
        <?php endif; ?>
    </div>

    <?= $form->field($model, 'schedule_time')->hiddenInput(['id' => 'selected-time'])->label(false) ?>
</div>

<?php
$this->registerJs(<<<JS
    $('.time-slot').click(function() {
        if ($(this).hasClass('booked')) return;

        $('.time-slot').removeClass('selected').css({'background-color': '#fff', 'color': '#000'});
        $(this).addClass('selected').css({'background-color': '#6f42c1', 'color': '#fff'});
        $('#selected-time').val($(this).data('time'));
    });
JS);
?>
    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- Select All JS -->
<script>
document.getElementById('select-all-services').addEventListener('change', function(e) {
    let checkboxes = document.querySelectorAll('.service-checkbox');
    checkboxes.forEach(cb => cb.checked = e.target.checked);
});
</script>


<!-- ✅ Custom CSS -->
<style>
.orders-form {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin-top: 20px;
}

.card-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
    margin-bottom: 20px;
}

.card-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 24px;
    color: #343a40;
}

/* Services Section */
.service-list-scrollable {
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 12px;
    background: #fdfdfd;
    margin-bottom: 20px;
}

.service-list-scrollable::-webkit-scrollbar {
    width: 6px;
}

.service-list-scrollable::-webkit-scrollbar-thumb {
    background-color: #ced4da;
    border-radius: 6px;
}

.service-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    background-color: #fff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.service-card:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.service-card input[type="checkbox"] {
    margin-right: 16px;
    transform: scale(1.3);
    accent-color: #198754;
}

.service-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-grow: 1;
    flex-wrap: wrap;
}

.service-info strong {
    font-size: 16px;
    color: #212529;
}

.service-info .tags {
    display: flex;
    gap: 10px;
    margin-top: 5px;
}

.service-info .tags span {
    background-color: #e9ecef;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 13px;
    color: #495057;
}

/* Form controls */
.form-group label,
.form-label {
    font-weight: 600;
    color: #343a40;
}

.select2-container .select2-selection--single {
    height: 38px;
    border-radius: 8px;
    border-color: #ced4da;
}

.select2-selection__rendered {
    padding-left: 12px;
    padding-top: 5px;
    color: #212529;
}

input.form-control,
textarea.form-control,
.select2-container--default .select2-selection--single {
    border-radius: 8px !important;
    border-color: #ced4da;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input.form-control:focus,
textarea.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.btn-success, .btn-primary {
    padding: 10px 25px;
    font-size: 16px;
    border-radius: 8px;
}

.btn-success:hover,
.btn-primary:hover {
    opacity: 0.9;
}
</style>

