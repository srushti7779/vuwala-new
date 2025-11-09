<div class="vendor-has-menus-form">
  <?php
    use kartik\form\ActiveForm;
    use kartik\helpers\Html;

    $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>

   <?= $form->field($model, 'vendor_id')->widget(\kartik\select2\Select2::classname(), [
    'data' => \yii\helpers\ArrayHelper::map(
        \app\modules\admin\models\VendorDetails::find()->orderBy('id')->asArray()->all(),
        'id',
        'business_name'
    ),
    'options' => [
        'placeholder' => Yii::t('app', 'Choose Vendor details'),
        'id' => 'vendor-select'
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]); ?>

    <div class="form-group">
        <label class="control-label mt-4"><?= Yii::t('app', 'Menus') ?></label>
        <div id="menu-checkboxes" class="row">
            <?php 
           $allMenus = \app\modules\admin\models\Menus::find()->all();

foreach ($allMenus as $menu) {
    $checked = in_array($menu->id, $existingMenus ?? []);
    echo '<div class="col-md-3 col-sm-4 col-6 mb-3">';
    echo '<div class="custom-control custom-checkbox-card">';
    echo Html::checkbox('menu_ids[]', $checked, [
        'value' => $menu->id,
        'id' => 'menu-' . $menu->id,
        'class' => 'custom-control-input menu-checkbox',
        'data-menu-id' => $menu->id
    ]);
    echo Html::label('<span class="checkbox-card-content">' .
        ($menu->label ?: $menu->id) .
        '</span>', 'menu-' . $menu->id, ['class' => 'custom-control-label']);
    echo '</div>';

    // ✅ render already assigned permissions
    $assignedPerms = $existingPermissions[$menu->id] ?? [];
    $menuPerms = \app\modules\admin\models\MenuPermissions::find()->where(['menu_id' => $menu->id])->all();

    echo '<div class="permissions-container" id="permissions-' . $menu->id . '" style="margin-left:15px;' .
         ($checked ? '' : 'display:none;') . '">';
    foreach ($menuPerms as $perm) {
        $permChecked = in_array($perm->id, $assignedPerms);
        echo '<div class="form-check">';
        echo Html::checkbox("permissions[{$menu->id}][]", $permChecked, [
            'value' => $perm->id,
            'id' => "perm-{$perm->id}",
            'class' => 'form-check-input permission-checkbox'
        ]);
        echo Html::label($perm->small_description ?: $perm->permission_name, "perm-{$perm->id}", [
            'class' => 'form-check-label'
        ]);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
}

            ?>
        </div>
    </div>
    <!-- <div class="form-group">
        <div class="custom-control custom-switch">
            <?= $form->field($model, 'status', [
                'template' => '{input} {label}',
                'options' => ['class' => 'custom-control custom-switch'],
                'labelOptions' => ['class' => 'custom-control-label']
            ])->checkbox([
                'class' => 'custom-control-input',
                'template' => '<div class="custom-control custom-switch">{input} {label}</div>'
            ], false); ?>
        </div>
    </div> -->

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), [
            'class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg',
            'style' => 'padding: 8px 24px; border-radius: 24px;'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .vendor-has-menus-form {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.08);
    }
    
    .custom-checkbox-card {
        position: relative;
        display: block;
        min-height: auto;
        padding-left: 0;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .custom-control-label {
        position: relative;
        margin-bottom: 0;
        padding: 16px 20px 16px 40px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        height: 100%;
    }
    
    .custom-control-label::before {
        content: "";
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #adb5bd;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .custom-control-label::after {
        content: "";
        position: absolute;
        left: 19px;
        top: 50%;
        transform: translateY(-50%) scale(0);
        width: 10px;
        height: 10px;
        background-color: white;
        clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
        transition: all 0.2s ease;
    }
    
    .custom-control-input:checked ~ .custom-control-label {
        border-color: #5e72e4;
        background-color: rgba(94, 114, 228, 0.05);
    }
    
    .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateY(-50%) scale(1);
    }
    
    .custom-control-input:focus ~ .custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25);
    }
    
    .checkbox-card-content {
        display: inline-block;
        font-weight: 500;
        color: #525f7f;
    }
    
    .custom-switch .custom-control-label::before {
        height: 24px;
        width: 48px;
    }
    
    .custom-switch .custom-control-label::after {
        width: 20px;
        height: 20px;
        left: -42px;
        top: 2px;
    }
    
    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(24px);
    }
    
    @media (max-width: 767.98px) {
        #menu-checkboxes .col-6 {
            padding-left: 8px;
            padding-right: 8px;
        }
        
        .custom-control-label {
            padding: 12px 15px 12px 35px;
            font-size: 14px;
        }
    }
</style>

<?php
// JavaScript for dynamic behavior
$this->registerJs('
    $("#vendor-select").on("change", function() {
        var vendorId = $(this).val();
        if (vendorId) {
            $.get("' . Yii::$app->urlManager->createUrl(['admin/vendor-has-menus/get-vendor-menus']) . '", {vendorId: vendorId}, function(data) {
                $(".menu-checkbox").prop("checked", false).change(); // Uncheck all first
                $.each(data.menus || [], function(index, menuId) {
                    $("#menu-" + menuId).prop("checked", true).change();
                });
                $.each(data.permissions || [], function(menuId, permIds) {
                    permIds.forEach(function(permId) {
                        $("#perm-" + permId).prop("checked", true);
                    });
                });
            });
        } else {
            $(".menu-checkbox").prop("checked", false).change();
            $(".permission-checkbox").prop("checked", false);
        }
    });

    $(document).on("change", ".menu-checkbox", function() {
        var menuId = $(this).data("menu-id");
        var container = $("#permissions-" + menuId);

        if ($(this).is(":checked")) {
            $(this).next(".custom-control-label").css({
                "transform": "translateY(-2px)",
                "box-shadow": "0 4px 12px rgba(94, 114, 228, 0.2)"
            }).animate({
                "transform": "translateY(0)",
                "box-shadow": "0 2px 6px rgba(94, 114, 228, 0.1)"
            }, 200);

            $.ajax({
                url: "' . Yii::$app->urlManager->createUrl(['admin/vendor-has-menus/get-permissions']) . '",
                data: {menu_id: menuId},
                type: "GET",
                success: function(permissions) {
                    container.html("");
                    if (permissions.length > 0) {
                        permissions.forEach(function(perm) {
                            container.append(
                                \'<div class="form-check">\n\' +
                                \'<input type="checkbox" name="permissions[\' + menuId + \'][]" value="\' + perm.id + \'" id="perm-\' + perm.id + \'" class="form-check-input permission-checkbox">\n\' +
                                \'<label for="perm-\' + perm.id + \'" class="form-check-label">\' + (perm.small_description || perm.permission_name) + \'</label>\n\' +
                                \'</div>\'
                            );
                        });
                    } else {
                        container.append(\'<p class="text-muted">No permissions available.</p>\');
                    }
                    container.show();
                },
                error: function() {
                    alert("Error loading permissions.");
                }
            });
        } else {
            container.hide().html("");
            $(this).next(".custom-control-label").css({
                "transform": "translateY(0)",
                "box-shadow": "none"
            });
        }
    });
');
?>