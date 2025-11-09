<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
?>

<div class="component-form">
    <?php foreach ($row as $i => $component): ?>
        <div class="row align-items-center">
            <div class="col-lg-3">
                <?= Html::dropDownList(
                    "WhatsappTemplateComponents[$index][type]",
                    $component['type'] ?? '',
                    [
                        'header' => 'Header',
                        'body' => 'Body',
                        'button' => 'Button',
                    ],
                    [
                        'class' => 'form-control',
                        'prompt' => 'Select Component Type',
                        'onchange' => 'toggleSubtypeField(this)',
                        'aria-label' => 'Component Type'
                    ]
                ) ?>
            </div>
            <div class="col-lg-3 subtype-field" style="display: <?= ($component['type'] ?? '') === 'header' ? 'block' : 'none' ?>">
                <?= Html::dropDownList(
                    "WhatsappTemplateComponents[$index][subtype]",
                    $component['subtype'] ?? '',
                    [
                        'TEXT' => 'Text',
                        'IMAGE' => 'Image',
                        'VIDEO' => 'Video',
                        'DOCUMENT' => 'Document',
                    ],
                    [
                        'class' => 'form-control',
                        'prompt' => 'Select Subtype',
                        'aria-label' => 'Header Subtype'
                    ]
                ) ?>
            </div>
            <div class="col-lg-3 default-value-field">
                <?php if (isset($component['subtype']) && in_array($component['subtype'], ['IMAGE', 'VIDEO', 'DOCUMENT'])): ?>
                    <?= Html::fileInput(
                        "WhatsappTemplateComponents[$index][default_value]",
                        null,
                        [
                            'class' => 'form-control',
                            'accept' => $component['subtype'] === 'IMAGE' ? 'image/*' : ($component['subtype'] === 'VIDEO' ? 'video/*' : '.pdf,.doc,.docx'),
                            'aria-label' => 'Upload Media'
                        ]
                    ) ?>
                <?php else: ?>
                    <?= Html::textInput(
                        "WhatsappTemplateComponents[$index][default_value]",
                        $component['default_value'] ?? '',
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Default Value (e.g., {{1}} for variables)',
                            'aria-label' => 'Default Value'
                        ]
                    ) ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-2">
                <?= Html::textInput(
                    "WhatsappTemplateComponents[$index][variable_name]",
                    $component['variable_name'] ?? 'param_' . ($index + 1),
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Variable Name',
                        'aria-label' => 'Variable Name'
                    ]
                ) ?>
            </div>
            <div class="col-lg-1">
                <button type="button" class="btn btn-danger remove-component" aria-label="Remove Component">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$js = <<<JS
function toggleSubtypeField(select) {
    var row = $(select).closest('.row');
    var subtypeField = row.find('.subtype-field');
    var defaultValueField = row.find('.default-value-field');
    
    if (select.value === 'header') {
        subtypeField.show();
    } else {
        subtypeField.hide();
        row.find('select[name$="[subtype]"]').val('');
    }

    // Update default value field based on subtype
    row.find('select[name$="[subtype]"]').on('change', function() {
        var subtype = this.value;
        var inputHtml = '';
        if (subtype === 'IMAGE' || subtype === 'VIDEO' || subtype === 'DOCUMENT') {
            var accept = subtype === 'IMAGE' ? 'image/*' : (subtype === 'VIDEO' ? 'video/*' : '.pdf,.doc,.docx');
            inputHtml = '<input type="file" name="WhatsappTemplateComponents[' + row.data('index') + '][default_value]" class="form-control" accept="' + accept + '" aria-label="Upload Media">';
        } else {
            inputHtml = '<input type="text" name="WhatsappTemplateComponents[' + row.data('index') + '][default_value]" class="form-control" placeholder="Default Value (e.g., {{1}} for variables)" aria-label="Default Value">';
        }
        defaultValueField.html(inputHtml);
    });
}
JS;
$this->registerJs($js);
?>