<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;
use app\modules\admin\models\WhatsappTemplateComponents;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappTemplates */
/* @var $providerWhatsappTemplateComponents \yii\data\ArrayDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'WhatsApp Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Fetch required components for the form (BODY and HEADER with is_required = 1)
$requiredComponents = WhatsappTemplateComponents::find()
    ->where(['template_id' => $model->id, 'status' => 1, 'is_required' => 1])
    ->andWhere(['in', 'type', ['BODY', 'HEADER']])
    ->orderBy(['param_order' => SORT_ASC])
    ->all();

// Register enhanced CSS for bulk messaging
$this->registerCss("
    #send-test-message-form .form-control {
        max-width: 300px;
    }
    #bulk-whatsapp-form .form-control {
        max-width: 400px;
    }
    #bulk-response {
        padding: 0;
        border-radius: 8px;
    }
    #bulk-response.success {
        background-color: transparent;
        border: none;
        color: inherit;
    }
    #bulk-response.error {
        background-color: transparent;
        border: none;
        color: inherit;
    }
    #bulk-response.info {
        background-color: #e3f2fd;
        border: 1px solid #90caf9;
        color: #1565c0;
        padding: 15px;
        border-radius: 8px;
    }
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    .bulk-stats {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 20px;
        margin: 15px 0;
    }
    .bulk-stats .col-md-3 {
        padding: 10px;
    }
    .bulk-stats .h4 {
        font-weight: 600;
        margin-bottom: 5px;
    }
    .progress-indicator {
        border-left: 4px solid #007bff;
        padding-left: 15px;
    }
    .bulk-parameter-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
    }
    .bulk-parameter-section h6 {
        color: #495057;
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    .bulk-parameter-section .form-group {
        margin-bottom: 12px;
    }
    .bulk-parameter-section label {
        color: #495057;
        font-weight: 500;
    }");
?> 
<div class="whatsapp-templates-view">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><?= Html::encode($this->title) ?></h2>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><?= Yii::t('app', 'Template Details') ?></h4>
                <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
                    <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                            'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php } ?>
            </div>
            <?php
            $gridColumn = [
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Template Name'),
                    'value' => function ($model) {
                        return Html::encode($model->name);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'language_code',
                    'label' => Yii::t('app', 'Language'),
                    'value' => function ($model) {
                        return Html::encode($model->language_code);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'description',
                    'label' => Yii::t('app', 'Description'),
                    'value' => function ($model) {
                        return nl2br(Html::encode($model->description));
                    },
                    'format' => 'raw',
                ],
            ];
            echo DetailView::widget([
                'model' => $model,
                'attributes' => $gridColumn,
                'options' => ['class' => 'table table-bordered table-striped detail-view'],
            ]);
            ?>
        </div>
    </div>

    <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0"><?= Yii::t('app', 'Send Test Message') ?></h4>
            </div>
            <div class="card-body">
                <form id="send-test-message-form">
                    <div class="form-group mb-3">
                        <label for="phone_number"><?= Yii::t('app', 'Phone Number') ?> (e.g., 1234567890)</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number in international format" required>
                    </div>
                    <?php foreach ($requiredComponents as $component) { ?>
                        <?php
                        $inputType = 'text';
                        $placeholder = 'Enter value for ' . Html::encode($component->variable_name ?: 'param_' . $component->param_order);
                        // Determine input type based on variable_name or default_value
                        if ($component->type === 'HEADER' && $component->subtype === 'IMAGE') {
                            $inputType = 'url';
                            $placeholder = 'Enter image URL';
                        } elseif ($component->type === 'BODY' && (stripos($component->variable_name, 'url') !== false || preg_match('/^https?:\/\//', $component->default_value))) {
                            $inputType = 'url';
                            $placeholder = 'Enter URL';
                        }
                        ?>
                        <div class="form-group mb-3">
                            <label for="param_<?= $component->id ?>"><?= Yii::t('app', $component->type) ?> Parameter <?= $component->param_order ?> (<?= Html::encode($component->variable_name ?: 'param_' . $component->param_order) ?>)</label>
                            <input type="<?= $inputType ?>" class="form-control" id="param_<?= $component->id ?>" name="parameters[<?= $component->id ?>]" placeholder="<?= $placeholder ?>" value="<?= Html::encode($component->default_value ?: '') ?>" required>
                        </div>
                    <?php } ?>
                    <button type="submit" class="btn btn-success btn-sm" id="send-test-message-btn"><?= Yii::t('app', 'Send Test Message') ?></button>
                </form>
            </div>
        </div>
    <?php } ?>

    <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) { ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><?= Yii::t('app', 'Bulk WhatsApp Messaging') ?></h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted"><?= Yii::t('app', 'Upload Excel/CSV file with contact numbers and enter template parameters below') ?></p>
                    
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <div class="d-flex">
                            <div class="mr-2">
                                <i class="fa fa-info-circle fa-lg text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-2">📋 How Bulk Messaging Works</h6>
                                <ul class="mb-2 small">
                                    <li><strong>Excel/CSV:</strong> Contains mobile numbers + media URLs (if template has HEADER images/videos/docs)</li>
                                    <li><strong>BODY Parameters:</strong> Enter manually in form below (same for all contacts)</li>
                                    <li><strong>HEADER Media:</strong> Include URLs in Excel columns or uses template defaults</li>
                                    <li><strong>Anti-Spam:</strong> Humanized timing with 2-3.5s delays + batch breaks</li>
                                </ul>
                                <small class="text-muted">⏱️ Processing time varies based on contact count and includes spam prevention delays.</small>
                            </div>
                        </div>
                    </div>
                    
                    <?= Html::a(Yii::t('app', 'Download Contact Format'), '#', [
                        'class' => 'btn btn-sm btn-outline-info',
                        'id' => 'download-contact-example-btn'
                    ]) ?>
                </div>

                <form id="bulk-whatsapp-form" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="contact_file"><?= Yii::t('app', 'Select Contact File') ?></label>
                        <input type="file" class="form-control" id="contact_file" name="contact_file" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted"><?= Yii::t('app', 'File should contain only mobile numbers') ?></small>
                    </div>
                    
                    <?php 
                    // Get ALL parameters for bulk messaging (BODY and HEADER)
                    $allParameters = WhatsappTemplateComponents::find()
                        ->where(['template_id' => $model->id, 'status' => 1])
                        ->andWhere(['in', 'type', ['BODY', 'HEADER']])
                        ->orderBy(['type' => SORT_ASC, 'param_order' => SORT_ASC])
                        ->all();
                    ?>
                    
                    <?php if (!empty($allParameters)) { ?>
                        <div class="bulk-parameter-section">
                            <h6 class="text-primary mb-3"><?= Yii::t('app', 'Template Parameters (will be used for all contacts)') ?></h6>
                            <p class="text-muted small mb-3">Enter all parameter values below. These will be applied to every contact in your Excel file.</p>
                            
                            <?php foreach ($allParameters as $component) { ?>
                                <div class="form-group mb-2">
                                    <?php
                                    $inputType = 'text';
                                    $placeholder = Html::encode($component->default_value ?: 'Enter value for ' . ($component->variable_name ?: 'param_' . $component->param_order));
                                    
                                    // Determine input type based on component type and subtype
                                    if ($component->type === 'HEADER' && in_array($component->subtype, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                                        $inputType = 'url';
                                        $placeholder = 'Enter ' . strtolower($component->subtype) . ' URL';
                                    } elseif (stripos($component->variable_name, 'url') !== false || preg_match('/^https?:\/\//', $component->default_value)) {
                                        $inputType = 'url';
                                        $placeholder = 'Enter URL';
                                    }
                                    
                                    $paramLabel = $component->type . ' Parameter ' . $component->param_order;
                                    if ($component->variable_name) {
                                        $paramLabel .= ' (' . Html::encode($component->variable_name) . ')';
                                    }
                                    if ($component->type === 'HEADER' && $component->subtype) {
                                        $paramLabel .= ' [' . $component->subtype . ']';
                                    }
                                    ?>
                                    <label for="bulk_param_<?= $component->id ?>" class="font-weight-bold">
                                        <?= $paramLabel ?>
                                        <?php if ($component->is_required) { ?>
                                            <span class="text-danger">*</span>
                                        <?php } ?>
                                    </label>
                                    <input 
                                        type="<?= $inputType ?>" 
                                        class="form-control" 
                                        id="bulk_param_<?= $component->id ?>" 
                                        name="bulk_parameters[<?= $component->id ?>]" 
                                        placeholder="<?= $placeholder ?>" 
                                        value="<?= Html::encode($component->default_value ?: '') ?>"
                                        <?= $component->is_required ? 'required' : '' ?>>
                                    <small class="text-muted">
                                        <?php if ($component->is_required) { ?>
                                            <span class="text-danger">Required</span> - 
                                        <?php } ?>
                                        Default: <?= Html::encode($component->default_value ?: 'No default value') ?>
                                    </small>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    
                    <div class="form-group mb-3">
                        <div class="border p-3 bg-light">
                            <p class="text-muted mb-2"><?= Yii::t('app', 'Your Excel file should contain:') ?></p>
                            <ul class="mb-2">
                                <li><strong>mobile_number</strong> - <?= Yii::t('app', 'Contact phone number (one per row)') ?></li>
                            </ul>
                            <div class="alert alert-info py-2 px-3 mb-2">
                                <small><i class="fa fa-info-circle"></i> <strong>Simple Format:</strong> 
                                Excel file needs only mobile numbers. All other parameters (images, videos, text) are entered above and will be used for all contacts.</small>
                            </div>
                            <p class="text-info mt-2 mb-0"><small><?= Yii::t('app', 'All parameters entered above will be used for all contacts') ?></small></p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning btn-sm" id="send-bulk-whatsapp-btn">
                        <i class="fa fa-paper-plane"></i> <?= Yii::t('app', 'Send Bulk WhatsApp Messages') ?>
                    </button>
                </form>
                
                <div id="bulk-response" class="mt-3"></div>
            </div>
        </div>
    <?php } ?>

    <?php if ($providerWhatsappTemplateComponents->totalCount) { ?>
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">
                    <a data-toggle="collapse" href="#componentsCollapse" role="button" aria-expanded="true" aria-controls="componentsCollapse">
                        <?= Yii::t('app', 'Template Components') ?>
                    </a>
                </h4>
            </div>
            <div class="collapse show" id="componentsCollapse">
                <div class="card-body">
                    <?php
                    $gridColumnWhatsappTemplateComponents = [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'type',
                            'label' => Yii::t('app', 'Type'),
                            'value' => function ($model) {
                                return Html::encode(strtoupper($model->type));
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'subtype',
                            'label' => Yii::t('app', 'Subtype'),
                            'value' => function ($model) {
                                return Html::encode($model->subtype ?: '-');
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'param_order',
                            'label' => Yii::t('app', 'Order'),
                        ],
                        [
                            'attribute' => 'default_value',
                            'label' => Yii::t('app', 'Default Value'),
                            'value' => function ($model) {
                                return nl2br(Html::encode($model->default_value));
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'variable_name',
                            'label' => Yii::t('app', 'Variable Name'),
                            'value' => function ($model) {
                                return Html::encode($model->variable_name ?: '-');
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'is_required',
                            'label' => Yii::t('app', 'Required'),
                            'value' => function ($model) {
                                return $model->is_required ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
                            },
                        ],
                    ];
                    echo GridView::widget([
                        'dataProvider' => $providerWhatsappTemplateComponents,
                        'pjax' => true,
                        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-whatsapp-template-components']],
                        'columns' => $gridColumnWhatsappTemplateComponents,
                        'panel' => [
                            'type' => GridView::TYPE_DEFAULT,
                            'heading' => false,
                        ],
                        'export' => false,
                        'tableOptions' => ['class' => 'table table-hover table-striped'],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php
$this->registerCss("
    .whatsapp-templates-view .card {
        border-radius: 8px;
    }
    .whatsapp-templates-view .card-header {
        font-weight: 500;
    }
    .whatsapp-templates-view .detail-view th {
        width: 30%;
        background-color: #f8f9fa;
    }
    .whatsapp-templates-view .table td, .whatsapp-templates-view .table th {
        padding: 12px;
    }
    .whatsapp-templates-view .card-header a {
        color: white;
        text-decoration: none;
    }
    .whatsapp-templates-view .card-header a:hover {
        text-decoration: underline;
    }
    #send-test-message-form .form-control {
        max-width: 300px;
    }
    #excel-upload-form .form-control {
        max-width: 400px;
    }
    #upload-response {
        padding: 10px;
        border-radius: 4px;
    }
    #upload-response.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    #upload-response.error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
");

$this->registerJs("
    $('#send-test-message-form').on('submit', function(e) {
        e.preventDefault();
        var phoneNumber = $('#phone_number').val();
        if (!phoneNumber.match(/^[0-9]{10,15}$/)) {
            swal('Error!', 'Please enter a valid phone number (10-15 digits, no special characters).', 'error');
            return;
        }
        var parameters = {};
        $('input[name^=\"parameters[\"]').each(function() {
            var paramId = $(this).attr('name').match(/\\d+/)[0];
            parameters[paramId] = $(this).val();
        });
        $.ajax({
            type: 'POST',
            url: '" . \yii\helpers\Url::to(['whatsapp-templates/send-test-message', 'id' => $model->id]) . "',
            data: { phone_number: phoneNumber, parameters: parameters },
            success: function(response) {
                if (response.success) {
                    swal('Success!', response.message, 'success');
                } else {
                    swal('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                swal('Error!', 'Failed to send test message: ' + xhr.responseText, 'error');
            }
        });
    });

    // Bulk WhatsApp messaging functionality
    $('#bulk-whatsapp-form').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#contact_file')[0];
        var uploadBtn = $('#send-bulk-whatsapp-btn');
        var responseDiv = $('#bulk-response');
        
        if (!fileInput.files.length) {
            responseDiv.removeClass('success info').addClass('error')
                .html('<strong>❌ Error:</strong> Please select a contact file.');
            return;
        }
        
        var file = fileInput.files[0];
        var allowedExtensions = ['xlsx', 'xls', 'csv'];
        var fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (allowedExtensions.indexOf(fileExtension) === -1) {
            responseDiv.removeClass('success info').addClass('error')
                .html('<strong>❌ Error:</strong> Please select a valid file (.xlsx, .xls, or .csv).');
            return;
        }
        
        // Validate bulk parameters
        var bulkParameters = {};
        var missingParams = [];
        $('input[name^=\"bulk_parameters[\"]').each(function() {
            var paramValue = $(this).val().trim();
            var paramLabel = $(this).prev('label').text();
            if (!paramValue) {
                missingParams.push(paramLabel);
            } else {
                var paramId = $(this).attr('name').match(/\\d+/)[0];
                bulkParameters[paramId] = paramValue;
            }
        });
        
        if (missingParams.length > 0) {
            responseDiv.removeClass('success info').addClass('error')
                .html('<strong>❌ Error:</strong> Please fill in all required parameters: ' + missingParams.join(', '));
            return;
        }
        
        // Show loading state with enhanced messaging
        uploadBtn.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i> Sending Messages...');
        responseDiv.removeClass('success error').addClass('info').html(
            '<div class=\"d-flex align-items-center\">' +
            '<div class=\"spinner-border spinner-border-sm text-primary mr-2\" role=\"status\"></div>' +
            '<div>' +
            '<strong>🚀 Processing:</strong> Reading contact file and sending WhatsApp messages...<br>' +
            '<small class=\"text-muted\">⏱️ This process includes humanized delays to avoid spam detection. Please wait...</small>' +
            '</div>' +
            '</div>'
        );
        
        var formData = new FormData();
        formData.append('contact_file', file);
        formData.append('template_id', '" . $model->id . "');
        
        // Add bulk parameters to form data
        $.each(bulkParameters, function(paramId, paramValue) {
            formData.append('bulk_parameters[' + paramId + ']', paramValue);
        });
        
        var startTime = new Date().getTime();
        
        $.ajax({
            type: 'POST',
            url: '" . \yii\helpers\Url::to(['whatsapp-templates/send-bulk-messages']) . "',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 600000, // 10 minutes timeout for large batches
            success: function(response) {
                var endTime = new Date().getTime();
                var duration = ((endTime - startTime) / 1000).toFixed(1);
                
                if (response.success) {
                    var html = '<div class=\"alert alert-success border-0 shadow-sm\">';
                    html += '<h5 class=\"alert-heading mb-3\">✅ Bulk Messaging Completed Successfully!</h5>';
                    
                    // Summary stats
                    html += '<div class=\"row mb-3\">';
                    html += '<div class=\"col-md-3 text-center\">';
                    html += '<div class=\"h4 text-success mb-0\">' + (response.successCount || 0) + '</div>';
                    html += '<small class=\"text-muted\">✅ Sent</small>';
                    html += '</div>';
                    html += '<div class=\"col-md-3 text-center\">';
                    html += '<div class=\"h4 text-danger mb-0\">' + (response.errorCount || 0) + '</div>';
                    html += '<small class=\"text-muted\">❌ Failed</small>';
                    html += '</div>';
                    html += '<div class=\"col-md-3 text-center\">';
                    html += '<div class=\"h4 text-warning mb-0\">' + (response.skippedCount || 0) + '</div>';
                    html += '<small class=\"text-muted\">⏭️ Skipped</small>';
                    html += '</div>';
                    html += '<div class=\"col-md-3 text-center\">';
                    html += '<div class=\"h4 text-info mb-0\">' + duration + 's</div>';
                    html += '<small class=\"text-muted\">⏱️ Duration</small>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Session info
                    if (response.sessionId) {
                        html += '<div class=\"mb-2\"><small class=\"text-muted\">🔍 Session ID: ' + response.sessionId + '</small></div>';
                    }
                    
                    // Message details
                    if (response.message) {
                        html += '<div class=\"small\">' + response.message.replace(/\\n/g, '<br>') + '</div>';
                    }
                    
                    html += '</div>';
                    
                    responseDiv.removeClass('error info').addClass('success').html(html);
                    $('#contact_file').val('');
                } else {
                    responseDiv.removeClass('success info').addClass('error')
                        .html('<div class=\"alert alert-danger\"><strong>❌ Error:</strong> ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Failed to send bulk messages. Please try again.';
                
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. The process may still be running in the background. Please check your WhatsApp messages or try again with a smaller batch.';
                } else {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        if (xhr.status === 0) {
                            errorMsg = 'Network error. Please check your internet connection.';
                        } else if (xhr.status >= 500) {
                            errorMsg = 'Server error. Please try again later or contact support.';
                        }
                    }
                }
                
                responseDiv.removeClass('success info').addClass('error')
                    .html('<div class=\"alert alert-danger\"><strong>❌ Error:</strong> ' + errorMsg + '</div>');
            },
            complete: function() {
                uploadBtn.prop('disabled', false).html('<i class=\"fa fa-paper-plane\"></i> Send Bulk WhatsApp Messages');
            }
        });
    });

    // Excel Upload functionality
    $('#excel-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#excel_file')[0];
        var uploadBtn = $('#upload-excel-btn');
        var responseDiv = $('#upload-response');
        
        if (!fileInput.files.length) {
            responseDiv.removeClass('success').addClass('error')
                .html('<strong>Error:</strong> Please select a file.');
            return;
        }
        
        var file = fileInput.files[0];
        var allowedExtensions = ['xlsx', 'xls', 'csv'];
        var fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (allowedExtensions.indexOf(fileExtension) === -1) {
            responseDiv.removeClass('success').addClass('error')
                .html('<strong>Error:</strong> Please select a valid file (.xlsx, .xls, or .csv).');
            return;
        }
        
        // Show loading state
        uploadBtn.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i> Uploading...');
        responseDiv.removeClass('success error').html('');
        
        var formData = new FormData();
        formData.append('excel_file', file);
        formData.append('template_id', '" . $model->id . "');
        
        $.ajax({
            type: 'POST',
            url: '" . \yii\helpers\Url::to(['whatsapp-templates/upload-excel']) . "',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    responseDiv.removeClass('error').addClass('success')
                        .html('<strong>Success:</strong> ' + response.message);
                    $('#excel_file').val('');
                    // Optionally reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    responseDiv.removeClass('success').addClass('error')
                        .html('<strong>Error:</strong> ' + response.message);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Upload failed. Please try again.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch (e) {
                    // Use default error message
                }
                responseDiv.removeClass('success').addClass('error')
                    .html('<strong>Error:</strong> ' + errorMsg);
            },
            complete: function() {
                uploadBtn.prop('disabled', false).html('<i class=\"fa fa-upload\"></i> Upload Excel');
            }
        });
    });

    // Download example Excel format
    $('#download-example-btn').on('click', function(e) {
        e.preventDefault();
        window.open('" . \yii\helpers\Url::to(['whatsapp-templates/download-example-excel']) . "', '_blank');
    });

    // Download contact example format
    $('#download-contact-example-btn').on('click', function(e) {
        e.preventDefault();
        window.open('" . \yii\helpers\Url::to(['whatsapp-templates/download-contact-example', 'id' => $model->id]) . "', '_blank');
    });
");
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">