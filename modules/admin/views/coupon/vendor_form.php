<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\models\User;
use app\modules\admin\models\base\Coupon;
use app\modules\admin\models\base\ServiceHasCoupons;
use app\modules\admin\models\base\Services;
use yii\helpers\ArrayHelper;

$role = Yii::$app->user->identity->user_role;

\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos' => \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'CouponVendor',
        'relID' => 'coupon-vendor',
        'value' => \yii\helpers\Json::encode($model->couponVendors),
        'isNewRecord' => $model->isNewRecord ? 1 : 0
    ]
]);

\mootensai\components\JsBlock::widget([
    'viewFile' => '_script',
    'pos' => \yii\web\View::POS_END,
    'viewParams' => [
        'class' => 'CouponsApplied',
        'relID' => 'coupons-applied',
        'value' => \yii\helpers\Json::encode($model->couponsApplieds),
        'isNewRecord' => $model->isNewRecord ? 1 : 0
    ]
]);

$dayOptions = Coupon::dayList();
?>

<div class="coupon-form">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']],
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?= $form->errorSummary($model); ?>
    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

    <?php if ($role == User::ROLE_ADMIN || $role == User::ROLE_SUBADMIN): ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Name']) ?>
        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Code']) ?>
    <?php elseif ($role == User::ROLE_VENDOR): ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Vendor Coupon']) ?>
        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Auto-generated or fixed']) ?>
    <?php endif; ?>

    <?= $form->field($model, 'description')->textArea(['maxlength' => true, 'placeholder' => 'Description']) ?>
    <?= $form->field($model, 'discount')->textInput(['maxlength' => true, 'placeholder' => 'Discount']) ?>
    <?= $form->field($model, 'max_discount')->textInput(['maxlength' => true, 'placeholder' => 'Max Discount']) ?>

    <?= $form->field($model, 'discount_type')->dropDownList(
        [
            Coupon::DISCOUNT_TYPE_PERCENTAGE => 'PERCENTAGE',
            Coupon::DISCOUNT_TYPE_FIXED => 'FIXED',
        ],
        ['prompt' => 'Select Discount Type']
    ) ?>

    <?= $form->field($model, 'coupon_type')->dropDownList(
        [
            Coupon::COUPON_TYPE_HAPPY_HOUR => 'Happy Hour',
            Coupon::COUPON_TYPE_NORMAL => 'Normal',
        ],
        ['prompt' => 'Select Coupon Type', 'id' => 'coupon-type']
    ) ?>

    <?= $form->field($model, 'offer_type')->dropDownList(
        [
            Coupon::OFFER_TYPE_ALL_SERVICES => 'All Services',
            Coupon::OFFER_TYPE_SPECIFIC_SERVICES => 'Specific Services',
        ],
        ['prompt' => 'Select Offer Type', 'id' => 'coupon-offer_type']
    ) ?>

    <?= $form->field($model, 'daily_redeem_limit')->textInput(['placeholder' => 'Daily Redeem Limit']) ?>
    <?= $form->field($model, 'start_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose Start Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>

    <?= $form->field($model, 'end_date')->widget(\kartik\datecontrol\DateControl::classname(), [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
        'saveFormat' => 'php:Y-m-d',
        'ajaxConversion' => true,
        'options' => [
            'pluginOptions' => [
                'placeholder' => Yii::t('app', 'Choose End Date'),
                'autoclose' => true
            ]
        ],
    ]); ?>
    <!-- Services Section -->
    <div id="services-section" style="display: none; margin-top:20px;">
        <h5>Select Services</h5>
        <?= $form->field($model, 'service_ids')->widget(\kartik\select2\Select2::classname(), [
            'data' => ArrayHelper::map(Services::find()->all(), 'id', 'service_name'),  // ✅ Load from services table
            'value' => ArrayHelper::getColumn(
                ServiceHasCoupons::find()
                    ->select('service_id')
                    ->where(['coupon_id' => $model->id])
                    ->asArray()
                    ->all(),
                'service_id'
            ),
            'options' => [
                'multiple' => true,
                'placeholder' => 'Select services...'
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]) ?>
    </div>

    <!-- Time Slots Section -->
    <!-- Time Slots Section -->
    <?php
    // Generate time options every 30 minutes in AM/PM format
    function getTimeOptions($start = '06:00', $end = '23:30')
    {
        $times = [];
        $current = strtotime($start);
        $endTime = strtotime($end);
        while ($current <= $endTime) {
            $times[date('h:i A', $current)] = date('h:i A', $current);
            $current = strtotime('+30 minutes', $current);
        }
        return $times;
    }

    $timeOptions = getTimeOptions();
    ?>

    <div id="time-slots-section" style="display: none; margin-top:20px;">
        <h5>Time Slots</h5>
        <div id="time-slots-wrapper">
            <div class="time-slot-row form-inline mb-2">
                <select name="timeSlots[0][day]" class="form-control mr-2 day-select">
                    <option value="">Select Day</option>
                    <?php foreach ($dayOptions as $day): ?>
                        <option value="<?= $day ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="timeSlots[0][start_time]" class="form-control mr-2 start-time">
                    <option value="">Start Time</option>
                </select>

                <select name="timeSlots[0][end_time]" class="form-control mr-2 end-time">
                    <option value="">End Time</option>
                </select>

                <button type="button" class="btn btn-danger remove-slot">X</button>
            </div>
        </div>
        <button type="button" id="add-slot" class="btn btn-success mt-2">+ Add Slot</button>
    </div>




    <?= $form->field($model, 'min_cart')->textInput(['placeholder' => 'Min Cart']) ?>

    <?php if ($role == User::ROLE_ADMIN || $role == User::ROLE_SUBADMIN || $role == User::ROLE_VENDOR): ?>
        <?= $form->field($model, 'is_global')->checkbox() ?>
        <?= $form->field($model, 'status')->dropDownList(
            $model->getStateOptions(),
            ['prompt' => 'Select Status']
        ) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- Toggle & Add Slot Script -->
<script>
    const daySlots = <?= json_encode($daySlots ?? []) ?>;
    document.addEventListener('DOMContentLoaded', function() {
        let slotIndex = 1;
        const couponType = document.getElementById('coupon-type');
        const offerType = document.getElementById('coupon-offer_type');
        const timeSlotsSection = document.getElementById('time-slots-section');
        const servicesSection = document.getElementById('services-section');

        function toggleSections() {
            timeSlotsSection.style.display = (couponType.value == "<?= Coupon::COUPON_TYPE_HAPPY_HOUR ?>") ? 'block' : 'none';
            servicesSection.style.display = (offerType.value == "<?= Coupon::OFFER_TYPE_SPECIFIC_SERVICES ?>") ? 'block' : 'none';
        }

        couponType.addEventListener('change', toggleSections);
        offerType.addEventListener('change', toggleSections);
        toggleSections();

        // Populate slots when day is selected
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('day-select')) {
                let day = e.target.value;
                let row = e.target.closest('.time-slot-row');
                let startSelect = row.querySelector('.start-time');
                let endSelect = row.querySelector('.end-time');

                startSelect.innerHTML = '<option value="">Start Time</option>';
                endSelect.innerHTML = '<option value="">End Time</option>';

                if (day && daySlots[day]) {
                    daySlots[day].forEach(function(slot) {
                        let option = `<option value="${slot}">${slot}</option>`;
                        startSelect.innerHTML += option;
                        endSelect.innerHTML += option;
                    });
                } else {
                    startSelect.innerHTML = '<option value="">No Slots</option>';
                    endSelect.innerHTML = '<option value="">No Slots</option>';
                }
            }
        });

        // Add new slot
        document.getElementById('add-slot').addEventListener('click', function() {
            let wrapper = document.getElementById('time-slots-wrapper');
            let newRow = document.createElement('div');
            newRow.className = "time-slot-row form-inline mb-2";

            newRow.innerHTML = `
            <select name="timeSlots[${slotIndex}][day]" class="form-control mr-2 day-select">
                <option value="">Select Day</option>
                <?php foreach ($dayOptions as $day): ?>
                    <option value="<?= $day ?>"><?= $day ?></option>
                <?php endforeach; ?>
            </select>

            <select name="timeSlots[${slotIndex}][start_time]" class="form-control mr-2 start-time">
                <option value="">Start Time</option>
            </select>

            <select name="timeSlots[${slotIndex}][end_time]" class="form-control mr-2 end-time">
                <option value="">End Time</option>
            </select>

            <button type="button" class="btn btn-danger remove-slot">X</button>
        `;

            wrapper.appendChild(newRow);
            slotIndex++;
        });

        // Remove slot
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-slot')) {
                e.target.closest('.time-slot-row').remove();
            }
        });
    });
</script>