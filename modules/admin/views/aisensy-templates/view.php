<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyTemplates */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-templates-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-8">
            <h2><i class="fas fa-comments"></i> <?= Html::encode($this->title) ?></h2>
            <p class="text-muted">
                <i class="fas fa-language"></i> <?= Html::encode($model->language) ?> | 
                <i class="fas fa-tag"></i> <?= Html::encode($model->category) ?> | 
                <i class="fas fa-circle <?= $model->status == 1 ? 'text-success' : 'text-warning' ?>"></i> 
                <?= $model->status == 1 ? 'Active' : 'Inactive' ?>
            </p>
        </div>
        <div class="col-sm-4 text-right" style="margin-top: 15px">
            <?= Html::button('<i class="fas fa-paper-plane"></i> Test Template', [
                'class' => 'btn btn-success btn-lg', 
                'id' => 'test-template-btn', 
                'data-template-id' => $model->id
            ]) ?>
            <?= Html::a('<i class="fas fa-broadcast-tower"></i> Bulk Messages', ['bulk-message', 'id' => $model->id], ['class' => 'btn btn-primary btn-lg']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
    </div>
    </div>
    <div class="card">
       <div class="card-body">

    <div class="row">
        <div class="col-md-12">
            <div class="template-preview">
                <?php if (!empty($model->body_text)): ?>
                    <div class="alert alert-light border">
                        <h5><i class="fas fa-align-left"></i> Template Body</h5>
                        <div class="template-body-text">
                            <?= nl2br(Html::encode($model->body_text)) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($model->footer_text)): ?>
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-footer"></i> Footer Text</h6>
                        <small><?= Html::encode($model->footer_text) ?></small>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-info-circle text-primary"></i> Template Info</h6>
                                <p class="card-text">
                                    <strong>External ID:</strong> <?= Html::encode($model->external_id) ?><br>
                                    <strong>Language:</strong> <?= Html::encode($model->language) ?><br>
                                    <strong>Category:</strong> <?= Html::encode($model->category) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($model->quality_score)): ?>
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-star text-success"></i> Quality Score</h6>
                                    <p class="card-text">
                                        <?php 
                                        $qualityScore = json_decode($model->quality_score, true);
                                        if (is_array($qualityScore)) {
                                            foreach ($qualityScore as $key => $value) {
                                                echo "<strong>" . ucfirst($key) . ":</strong> " . Html::encode($value) . "<br>";
                                            }
                                        } else {
                                            echo Html::encode($model->quality_score);
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <h4><i class="fas fa-puzzle-piece"></i> Template Components</h4>
            <?php if($providerAisensyTemplateComponents->totalCount): ?>
                <div class="components-list">
                    <?php foreach ($providerAisensyTemplateComponents->allModels as $index => $component): ?>
                        <div class="card mb-3 component-card">
                            <div class="card-header bg-<?= getComponentColor($component->type) ?> text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-<?= getComponentIcon($component->type) ?>"></i>
                                    <?= strtoupper($component->type) ?>
                                    <?php if (!empty($component->format)): ?>
                                        <span class="badge badge-light ml-2"><?= $component->format ?></span>
                                    <?php endif; ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($component->text)): ?>
                                    <div class="mb-2">
                                        <strong>Text:</strong>
                                        <div class="component-text"><?= nl2br(Html::encode($component->text)) ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($component->buttons)): ?>
                                    <div class="mb-2">
                                        <strong>Buttons:</strong>
                                        <?php 
                                        $buttons = json_decode($component->buttons, true);
                                        if (is_array($buttons)): ?>
                                            <div class="buttons-preview mt-2">
                                                <?php foreach ($buttons as $button): ?>
                                                    <span class="badge badge-<?= $button['type'] === 'QUICK_REPLY' ? 'primary' : 'success' ?> mr-1 mb-1">
                                                        <i class="fas fa-<?= $button['type'] === 'QUICK_REPLY' ? 'reply' : ($button['type'] === 'URL' ? 'link' : 'phone') ?>"></i>
                                                        <?= Html::encode($button['text']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($component->example)): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <strong>Example:</strong> 
                                            <?php 
                                            $example = json_decode($component->example, true);
                                            if (is_array($example)) {
                                                echo Html::encode(json_encode($example, JSON_PRETTY_PRINT));
                                            } else {
                                                echo Html::encode($component->example);
                                            }
                                            ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No components found for this template.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

    <div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <?php if($providerAisensyTemplateLinks->totalCount): ?>
                <h4><i class="fas fa-link"></i> Template Links</h4>
                <div class="links-list">
                    <?php foreach ($providerAisensyTemplateLinks->allModels as $link): ?>
                        <div class="card mb-2 border-<?= $link->type === 'URL' ? 'info' : 'warning' ?>">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-<?= $link->type === 'URL' ? 'link' : 'phone' ?> text-<?= $link->type === 'URL' ? 'info' : 'warning' ?> mr-3"></i>
                                    <div>
                                        <strong><?= Html::encode($link->label) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= Html::encode($link->value) ?></small>
                                    </div>
                                    <span class="badge badge-<?= $link->type === 'URL' ? 'info' : 'warning' ?> ml-auto">
                                        <?= $link->type ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>

</div>

<!-- Test Template Modal -->
<div class="modal fade" id="testTemplateModal" tabindex="-1" role="dialog" aria-labelledby="testTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testTemplateModalLabel">
                    <i class="fas fa-paper-plane"></i> Test Template: <?= Html::encode($model->name) ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="testTemplateForm" enctype="multipart/form-data">
                    <input type="hidden" name="template_id" value="<?= $model->id ?>">
                    
                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="phone_number"><i class="fas fa-phone"></i> Phone Number *</label>
                        <input type="text" class="form-control" name="phone_number" id="phone_number" 
                               placeholder="e.g., 916281684605" required>
                        <small class="form-text text-muted">Enter phone number with country code (without +)</small>
                    </div>

                    <?php
                    // Get template components for dynamic form generation
                    $components = \app\modules\admin\models\AisensyTemplateComponents::find()
                        ->where(['template_id' => $model->id])
                        ->orderBy('component_index')
                        ->all();

                    foreach ($components as $component): ?>
                        
                        <?php if (strtoupper($component->type) === 'HEADER'): ?>
                            <div class="form-group">
                                <label><i class="fas fa-heading"></i> Header</label>
                                <?php if ($component->format === 'IMAGE'): ?>
                                    <div class="media-upload-section">
                                        <label for="header_media_file">Upload Header Image</label>
                                        <input type="file" class="form-control-file" name="header_media_file" 
                                               accept="image/*" data-media-type="image">
                                        <input type="hidden" name="header_media_id" id="header_media_id">
                                        <input type="hidden" name="header_media_filename" id="header_media_filename">
                                        <input type="hidden" name="header_media_caption" id="header_media_caption">
                                        <div class="upload-status mt-2"></div>
                                        <small class="form-text text-muted">Max file size: 5MB</small>
                                    </div>
                                <?php elseif ($component->format === 'VIDEO'): ?>
                                    <div class="media-upload-section">
                                        <label for="header_media_file">Upload Header Video</label>
                                        <input type="file" class="form-control-file" name="header_media_file" 
                                               accept="video/*" data-media-type="video">
                                        <input type="hidden" name="header_media_id" id="header_media_id">
                                        <input type="hidden" name="header_media_filename" id="header_media_filename">
                                        <input type="hidden" name="header_media_caption" id="header_media_caption">
                                        <div class="upload-status mt-2"></div>
                                        <small class="form-text text-muted">Max file size: 16MB</small>
                                    </div>
                                <?php elseif ($component->format === 'DOCUMENT'): ?>
                                    <div class="media-upload-section">
                                        <label for="header_media_file">Upload Header Document</label>
                                        <input type="file" class="form-control-file" name="header_media_file" 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" data-media-type="document">
                                        <input type="hidden" name="header_media_id" id="header_media_id">
                                        <input type="hidden" name="header_media_filename" id="header_media_filename">
                                        <input type="hidden" name="header_media_caption" id="header_media_caption">
                                        <div class="upload-status mt-2"></div>
                                        <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT</small>
                                    </div>
                                <?php else: ?>
                                    <input type="text" class="form-control" name="header_text" 
                                           placeholder="Header text">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (strtoupper($component->type) === 'BODY' && !empty($component->text)): ?>
                            <div class="form-group">
                                <label><i class="fas fa-align-left"></i> Body Parameters</label>
                                <div class="alert alert-info">
                                    <strong>Template Body:</strong><br>
                                    <?= nl2br(Html::encode($component->text)) ?>
                                </div>
                                
                                <?php
                                // Extract parameters from body text
                                preg_match_all('/\{\{(\d+)\}\}/', $component->text, $matches);
                                if (!empty($matches[1])): 
                                    foreach ($matches[1] as $index): ?>
                                        <div class="form-group">
                                            <label for="body_param_<?= $index ?>">Parameter {{<?= $index ?>}}</label>
                                            <input type="text" class="form-control" 
                                                   name="body_param_<?= $index ?>" 
                                                   id="body_param_<?= $index ?>"
                                                   placeholder="Enter value for parameter <?= $index ?>">
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (strtoupper($component->type) === 'BUTTONS' && !empty($component->buttons)): ?>
                            <?php 
                            $buttons = json_decode($component->buttons, true);
                            if (is_array($buttons)): ?>
                                <div class="form-group">
                                    <label><i class="fas fa-mouse-pointer"></i> Button Parameters</label>
                                    <?php foreach ($buttons as $buttonIndex => $button): ?>
                                        <?php if ($button['type'] === 'URL' && isset($button['url']) && strpos($button['url'], '{{') !== false): ?>
                                            <div class="form-group">
                                                <label for="button_param_<?= $buttonIndex ?>">
                                                    Button "<?= Html::encode($button['text']) ?>" URL Parameter
                                                </label>
                                                <input type="text" class="form-control" 
                                                       name="button_param_<?= $buttonIndex ?>" 
                                                       id="button_param_<?= $buttonIndex ?>"
                                                       placeholder="Enter URL parameter value">
                                                <small class="form-text text-muted">
                                                    URL Template: <?= Html::encode($button['url']) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endforeach; ?>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" id="sendTestMessageBtn">
                    <i class="fas fa-paper-plane"></i> Send Test Message
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions for component styling
function getComponentColor($type) {
    switch (strtoupper($type)) {
        case "HEADER": return "primary";
        case "BODY": return "success";
        case "FOOTER": return "secondary";
        case "BUTTONS": return "warning";
        default: return "info";
    }
}

function getComponentIcon($type) {
    switch (strtoupper($type)) {
        case "HEADER": return "heading";
        case "BODY": return "align-left";
        case "FOOTER": return "angle-down";
        case "BUTTONS": return "mouse-pointer";
        default: return "puzzle-piece";
    }
}
?>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<style>
/* Template View Styling */
.template-preview {
    margin-bottom: 20px;
}

.template-body-text {
    font-size: 16px;
    line-height: 1.6;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-top: 10px;
}

.component-card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
    transition: transform 0.2s;
}

.component-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.component-text {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #28a745;
    margin-top: 5px;
    font-family: 'Courier New', monospace;
}

.buttons-preview {
    margin-top: 8px;
}

.buttons-preview .badge {
    font-size: 0.8em;
    padding: 5px 10px;
}

.links-list .card {
    transition: all 0.2s;
}

.links-list .card:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Modal Styling */
.media-upload-section {
    border: 2px dashed #ddd;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin: 10px 0;
    transition: all 0.3s;
}

.media-upload-section:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
    transform: scale(1.02);
}

.upload-status.success {
    color: #28a745;
    font-weight: bold;
}

.upload-status.error {
    color: #dc3545;
    font-weight: bold;
}

.upload-status.uploading {
    color: #007bff;
}

/* Card Enhancements */
.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    font-weight: 600;
    border-bottom: none;
}

.btn-lg {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
}

/* Status Indicators */
.fas.fa-circle {
    font-size: 8px;
    margin: 0 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .col-sm-4 {
        text-align: center !important;
        margin-top: 20px;
    }
    
    .btn-lg {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }
}
</style> 

<script>
$(document).ready(function() {
    // Register CSRF token for AJAX requests
    $('meta[name=csrf-token]').attr('content', '<?= Yii::$app->request->getCsrfToken() ?>');
    
    // Open test template modal
    $('#test-template-btn').click(function() {
        $('#testTemplateModal').modal('show');
    });

    // Handle media file uploads
    $('input[type="file"][data-media-type]').change(function() {
        var fileInput = $(this);
        var file = this.files[0];
        var statusDiv = fileInput.siblings('.upload-status');
        var hiddenInput = fileInput.closest('.form-group').find('input[name="header_media_id"]');
        var mediaType = fileInput.data('media-type');
        
        if (!file) return;
        
        // Validate file size based on WhatsApp limits: Images(5MB), Videos(16MB), Documents(100MB)
        var maxSize;
        if (mediaType === 'document') {
            maxSize = 100 * 1024 * 1024; // 100MB for documents
        } else if (mediaType === 'video') {
            maxSize = 16 * 1024 * 1024; // 16MB for videos
        } else {
            maxSize = 5 * 1024 * 1024; // 5MB for images
        }
        if (file.size > maxSize) {
            statusDiv.removeClass('uploading success').addClass('error')
                    .html('<i class="fas fa-times"></i> File too large. Max size: ' + (maxSize / 1024 / 1024) + 'MB');
            return;
        }
        
        // Validate file type for documents
        if (mediaType === 'document') {
            var allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
            var fileName = file.name.toLowerCase();
            var fileExtension = fileName.split('.').pop();
            
            if (!allowedExtensions.includes(fileExtension)) {
                statusDiv.removeClass('uploading success').addClass('error')
                        .html('<i class="fas fa-times"></i> Invalid file type. Please upload: ' + allowedExtensions.join(', ').toUpperCase());
                return;
            }
        }
        
        // Show uploading status
        statusDiv.removeClass('success error').addClass('uploading')
                .html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        // Create FormData
        var formData = new FormData();
        formData.append('media_file', file);
        formData.append('_csrf', $('meta[name=csrf-token]').attr('content'));
        
        // Upload file
        $.ajax({
            url: '<?= yii\helpers\Url::to(['upload-media']) ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    statusDiv.removeClass('uploading error').addClass('success')
                            .html('<i class="fas fa-check"></i> Upload successful! File: ' + (response.filename || 'Unknown'));
                    hiddenInput.val(response.media_id);
                    
                    // Store filename for documents, videos, and images
                    var filenameInput = fileInput.closest('.form-group').find('input[name="header_media_filename"]');
                    if (filenameInput.length && response.filename) {
                        filenameInput.val(response.filename);
                    }
                } else {
                    var errorMsg = response.message || 'Upload failed';
                    statusDiv.removeClass('uploading success').addClass('error')
                            .html('<i class="fas fa-times"></i> Upload failed: ' + errorMsg);
                    console.error('Upload failed:', response);
                }
            },
            error: function(xhr, status, error) {
                var errorDetail = error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorDetail = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorDetail = response.message;
                        }
                    } catch(e) {
                        // Keep original error if can't parse
                    }
                }
                statusDiv.removeClass('uploading success').addClass('error')
                        .html('<i class="fas fa-times"></i> Upload error: ' + errorDetail);
                console.error('Upload error:', xhr, status, error);
            }
        });
    });

    // Send test message
    $('#sendTestMessageBtn').click(function() {
        var button = $(this);
        var form = $('#testTemplateForm');
        
        // Validate phone number
        var phoneNumber = $('#phone_number').val().trim();
        if (!phoneNumber) {
            swal("Validation Error", "Please enter a phone number", "error");
            return;
        }
        
        // Validate phone number format (should be digits only with country code)
        if (!/^\d{10,15}$/.test(phoneNumber)) {
            swal("Validation Error", "Please enter a valid phone number with country code (digits only, 10-15 digits)", "error");
            return;
        }
        
        swal({
            title: "Send Test Message",
            text: "Are you sure you want to send a test message to " + phoneNumber + "?",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancel",
                    value: false,
                    visible: true
                },
                confirm: {
                    text: "Send",
                    value: true,
                    visible: true
                }
            }
        }).then((willSend) => {
            if (willSend) {
                // Disable button and show loading
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
                
                // Prepare form data
                var formData = form.serialize();
                formData += '&_csrf=' + encodeURIComponent($('meta[name=csrf-token]').attr('content'));
                
                $.ajax({
                    url: '<?= yii\helpers\Url::to(['send-test-message']) ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        button.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test Message');
                        
                        if (response.success) {
                            swal({
                                title: "Message Sent!",
                                text: response.message,
                                icon: "success"
                            }).then(() => {
                                $('#testTemplateModal').modal('hide');
                                // Reset form
                                form[0].reset();
                                $('.upload-status').removeClass('success error uploading').empty();
                                $('input[name="header_media_id"]').val('');
                                $('input[name="header_media_filename"]').val('');
                                $('input[name="header_media_caption"]').val('');
                            });
                        } else {
                            swal({
                                title: "Message Failed",
                                text: response.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        button.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test Message');
                        swal({
                            title: "Message Failed",
                            text: "An error occurred: " + error,
                            icon: "error"
                        });
                    }
                });
            }
        });
    });
});
</script> 

