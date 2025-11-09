<?php

use app\models\User;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $model User
 * @var $form yii\widgets\ActiveForm
 */
?>

<style>
    .user-form.card {
        border: none;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
        border-radius: 15px;
        overflow: hidden;
        background: #ffffff;
    }

    .user-form .card-body {
        padding: 2rem;
    }

    .user-form .card-title {
        font-size: 1.4rem;
        font-weight: 600;
        border-left: 4px solid #007bff;
        padding-left: 12px;
        margin-bottom: 1.5rem;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .user-form .card-title::before {
        content: "📝";
        font-size: 1.2rem;
    }

    .user-form .form-control {
        border-radius: 12px;
        padding: 0.7rem 1rem;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .user-form .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.15);
    }

    .user-form .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.25rem;
    }

    .user-form .form-check-label {
        font-size: 1rem;
        margin-left: 0.5rem;
        font-weight: 500;
    }

    .user-form .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        border: none;
        padding: 0.7rem 2rem;
        font-size: 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }

    .user-form .btn-primary:hover {
        background: linear-gradient(135deg, #0056b3, #003d80);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    .user-form hr {
        margin: 2rem 0;
        border-top: 1px dashed #ccc;
    }

    .user-form .card-footer {
        background-color: #f1f1f1;
        border-top: 1px solid #e0e0e0;
        padding: 1.2rem 2rem;
        border-radius: 0 0 15px 15px;
    }

    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        padding: 6px 10px;
        height: 40px;
        border: 1px solid #ccc;
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
    }
</style>
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-header text-white rounded-top-4" style="background: linear-gradient(135deg, #3a0275ff, #05327eff);">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-id-badge me-5"></i> Vendor Creation
        </h5>
    </div>

    <div class="user-form card">
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'layout'               => 'horizontal',
                 'enableAjaxValidation' => false,
            ]); ?>


            <!-- Basic Information -->
            <!-- <h5 class="card-title">Basic Information</h5> -->

            <?php echo $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
            <?php echo $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
            <?php echo $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => 'Username']) ?>
            <?php echo $form->field($model, 'contact_no')->textInput(['placeholder' => 'Contact No']) ?>
            <?php echo $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
            <!--<?php echo $form->field($model, 'user_role')->hiddenInput(User::getStatusVendorStoreTypes()) ?> -->

            <?php echo $form->field($model, 'password')->passwordInput() ?>
            <?php echo $form->field($model, 'passwordRepeat')->passwordInput() ?>

               <!-- Vendor Configuration -->
            <hr>
            <h5 class="card-title" style="color:navy">Vendor Configuration</h5>

            <?php

            echo $form->field($model, 'vendor_store_type')->widget(Select2::classname(), [
                'data'          => [
                    User::VENDOR_STORE_TYPE_SINGLE => 'Single Store Vendor',
                    User::VENDOR_STORE_TYPE_MULTI  => 'Multi Store Vendor',
                ],
                'options'       => [
                    'placeholder' => 'Select Store Type',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);
            ?>
           



            <div class="form-group row">
                <div class="col-sm-2"></div> <!-- left padding -->
                <?php echo $form->field($model, 'main_vendor', [
                    'template' => "{input}{label}\n{error}",
                ])->checkbox([
                    'class'        => 'form-check-input main-vendor-checkbox',
                    'labelOptions' => ['class' => 'form-check-label'],
                    'value'        => 1,
                    'uncheck'      => 0,
                ]) ?>

             <?php 
                    echo $form->field($model, 'allow_onboarding', [
                        'template' => "{input}{label}\n{error}",
                    ])->checkbox([
                        'class'        => 'form-check-input allow-onboarding-checkbox',
                        'labelOptions' => ['class' => 'form-check-label'],
                        'value'        => 1,
                        'uncheck'      => 0,
                    ]) ?>

                    <?php 
                    echo $form->field($model, 'allow_order_approval', [
                        'template' => "{input}{label}\n{error}",
                    ])->checkbox([
                        'class'        => 'form-check-input allow-order-approval-checkbox',
                        'labelOptions' => ['class' => 'form-check-label'],
                        'value'        => 1,
                        'uncheck'      => 0,
                    ]) ?>



            </div>

            <!-- System Settings -->
            <hr>
            <h5 class="card-title" style="color:navy">System Settings</h5>

            <?php echo $form->field($model, 'user_role')->hiddenInput(User::getRoles())->label(false) ?>
            <?php

            echo $form->field($model, 'status')
                ->label('Select Status')
                ->widget(Select2::classname(), [
                    'data'          => User::getStatusesList(),
                    'options'       => [
                        'placeholder' => 'Select Status',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]);
            ?>


            <div class="card-footer text-right">
                <?php echo Html::submitButton($model->isNewRecord ? 'Create Vendor' : 'Update Vendor', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
<?php
$vendorId = $model->id ?? null; // null if creating

$this->registerJs(<<<JS
function handleCheckbox(selector, successMsg, cancelMsg, confirmEnableMsg, confirmCancelMsg) {
    $(document).on("change", selector, function (e) {
        let checkbox = $(this);

        if (checkbox.is(":checked")) {
            // ✅ Confirmation for enabling
            Swal.fire({
                title: confirmEnableMsg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Enable',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', successMsg, 'success');

                    // If vendor already exists -> run AJAX
                    if ($vendorId) {
                        $.ajax({
                            url: '/admin/users/update-onboarding-status',
                            type: 'POST',
                            data: {
                                id: $vendorId,
                                option: checkbox.attr('name'),
                                value: 1,
                                _csrf: yii.getCsrfToken()
                            },
                            success: function (res) {
                                console.log(res.message);
                            }
                        });
                    }

                } else {
                    checkbox.prop("checked", false);
                    Swal.fire('Cancelled', cancelMsg, 'error');
                }
            });

        } else {
            // ❌ Confirmation for disabling/cancelling
            Swal.fire({
                title: confirmCancelMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Cancelled', cancelMsg, 'error');

                    // If vendor already exists -> run AJAX
                    if ($vendorId) {
                        $.ajax({
                            url: '/admin/users/update-onboarding-status',
                            type: 'POST',
                            data: {
                                id: $vendorId,
                                option: checkbox.attr('name'),
                                value: 0,
                                _csrf: yii.getCsrfToken()
                            },
                            success: function (res) {
                                console.log(res.message);
                            }
                        });
                    }

                } else {
                    checkbox.prop("checked", true);
                }
            });
        }
    });
}

// ✅ Apply dynamically for both checkboxes
handleCheckbox(
    ".main-vendor-checkbox",
    "Main Vendor enabled successfully!",
    "Main Vendor cancelled successfully!",
    "Are you sure you want to enable Main Vendor?",
    "Are you sure you want to cancel Main Vendor?"
);

handleCheckbox(
    ".allow-onboarding-checkbox",
    "Onboarding enabled successfully!",
    "Onboarding cancelled successfully!",
    "Are you sure you want to enable Onboarding?",
    "Are you sure you want to cancel Onboarding?"
);
JS
);
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
