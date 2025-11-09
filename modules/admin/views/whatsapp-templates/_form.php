<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\Url;
?>

<div class="whatsapp-templates-form">
    <?php $form = ActiveForm::begin([
        'id' => 'whatsapp-template-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-12']],
        'formConfig' => ['showErrors' => true],
        'options' => ['enctype' => 'multipart/form-data'] // For file uploads
    ]); ?>

    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

    <div class="row">
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
        <div class="col-lg-6">
            <?= $form->field($model, 'name', [
                'addon' => ['append' => ['content' => '<i class="fas fa-info-circle" title="Use lowercase letters, numbers, and underscores only"></i>']]
            ])->textInput([
                'maxlength' => true,
                'placeholder' => 'Template Name (lowercase, no spaces)',
                'oninput' => 'this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, "")',
                'aria-describedby' => 'name-help'
            ])->hint('Template name must be unique and contain only lowercase letters, numbers, and underscores.') ?>
        </div>
        <div class="col-lg-6">
            <?php
            $languageOptions = [
                'en_US' => 'English (US)',
                'en_GB' => 'English (UK)',
                'es_ES' => 'Spanish (Spain)',
                'es_MX' => 'Spanish (Mexico)',
                'fr_FR' => 'French',
                // Add more as per WhatsApp API supported languages
            ];
            ?>
            <?= $form->field($model, 'language_code')->dropDownList($languageOptions, [
                'prompt' => 'Select Language Code',
                'aria-describedby' => 'language-help'
            ])->hint('Select a language supported by WhatsApp.') ?>
        </div>
        <div class="col-lg-6">
            <?php
            $categoryOptions = Yii::$app->params['whatsapp_template_categories'] ?? [
                'TRANSACTIONAL' => 'Transactional',
                'MARKETING' => 'Marketing',
                'OTP' => 'OTP'
            ];
            ?>
            <?= $form->field($model, 'category')->dropDownList($categoryOptions, [
                'prompt' => 'Select Template Category',
                'aria-describedby' => 'category-help'
            ])->hint('Choose the category that matches your template’s purpose.') ?>
        </div>
        <div class="col-lg-12">
            <?= $form->field($model, 'description')->textarea([
                'rows' => 4,
                'placeholder' => 'Enter template body with variables, e.g., Hi {{1}}, welcome!',
                'aria-describedby' => 'description-help'
            ])->hint('Use {{1}}, {{2}}, etc., for dynamic variables.') ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'status')->dropDownList($model->getStateOptions(), [
                'prompt' => 'Select Status',
                'aria-describedby' => 'status-help'
            ])->hint('Active templates can be used; inactive ones cannot.') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <h3>Template Components</h3>
            <div id="components-container" style="margin-bottom: 15px;">
                <?php if (!empty($components)): ?>
                    <?php foreach ($components as $index => $component): ?>
                        <div class="component-row card card-body mb-2" data-index="<?= $index ?>">
                            <?= $this->render('_formWhatsappTemplateComponents', [
                                'row' => [$component],
                                'index' => $index,
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-component" class="btn btn-info mb-2">Add Component</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <h3>Template Preview</h3>
            <div id="template-preview" class="card card-body" style="min-height: 100px;">
                <p>Preview will update as you add components.</p>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$addComponentUrl = Url::to(['add-whatsapp-template-components']);
$js = <<<JS
$(document).ready(function() {
    let componentCount = $('.component-row').length;

    // Add component
    $('#add-component').click(function() {
        $.ajax({
            url: '$addComponentUrl',
            method: 'POST',
            data: { _action: 'add' },
            success: function(response) {
                $('#components-container').append('<div class="component-row card card-body mb-2" data-index="' + componentCount + '">' + response + '</div>');
                componentCount++;
                updatePreview();
            },
            error: function() {
                $('#components-container').append('<div class="alert alert-danger">Failed to load component form.</div>');
            }
        });
    });

    // Remove component
    $(document).on('click', '.remove-component', function() {
        $(this).closest('.component-row').remove();
        updatePreview();
    });

    // Update preview
    function updatePreview() {
        let preview = '';
        $('.component-row').each(function() {
            let type = $(this).find('select[name$="[type]"]').val();
            let subtype = $(this).find('select[name$="[subtype]"]').val();
            let defaultValue = $(this).find('input[name$="[default_value]"]').val();
            
            if (type === 'header') {
                preview += '<strong>' + (subtype === 'TEXT' ? defaultValue : '[Media: ' + subtype + ']') + '</strong><br>';
            } else if (type === 'body') {
                preview += '<p>' + defaultValue + '</p>';
            } else if (type === 'button') {
                preview += '<button class="btn btn-secondary btn-sm">' + defaultValue + '</button><br>';
            }
        });
        $('#template-preview').html(preview || '<p>Preview will update as you add components.</p>');
    }

    // Validate form before submission
    $('#whatsapp-template-form').on('beforeSubmit', function(e) {
        let valid = true;
        $('.component-row').each(function() {
            let type = $(this).find('select[name$="[type]"]').val();
            let defaultValue = $(this).find('input[name$="[default_value]"]').val();
            let subtype = $(this).find('select[name$="[subtype]"]').val();
            
            if (!type || !defaultValue) {
                valid = false;
                $(this).find('.form-group').addClass('has-error');
                $(this).append('<div class="alert alert-danger mt-2">Please fill in all required fields.</div>');
            }
            if (type === 'header' && !subtype) {
                valid = false;
                $(this).find('.subtype-field').addClass('has-error');
                $(this).append('<div class="alert alert-danger mt-2">Header requires a subtype.</div>');
            }
        });
        
        if (!valid) {
            $('#components-container').prepend('<div class="alert alert-danger">Please fix errors in components before submitting.</div>');
            return false;
        }
        return true;
    });

    // Update preview on input change
    $(document).on('change input', '.component-row select, .component-row input', updatePreview);
});
JS;
$this->registerJs($js);
?>

<style>
.component-row { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; }
#template-preview { background: #f8f9fa; padding: 15px; }
</style>