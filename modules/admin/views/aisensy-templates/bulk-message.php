<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyTemplates */
/* @var $components array */

$this->title = 'Bulk Messages - ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Bulk Messages';

// Register CSRF token for AJAX requests
$this->registerMetaTag(['name' => 'csrf-token', 'content' => Yii::$app->request->getCsrfToken()], 'csrf-token');
?>

<div class="aisensy-templates-bulk-message">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-8">
                    <h2><i class="fas fa-broadcast-tower"></i> Bulk Messages</h2>
                    <p class="text-muted">
                        <strong>Template:</strong> <?= Html::encode($model->name) ?> | 
                        <i class="fas fa-language"></i> <?= Html::encode($model->language) ?> | 
                        <i class="fas fa-tag"></i> <?= Html::encode($model->category) ?>
                    </p>
                </div>
                <div class="col-sm-4 text-right" style="margin-top: 15px">
                    <?= Html::a('<i class="fas fa-download"></i> Download Sample', ['download-sample-excel', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Back', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Preview -->
    <div class="card">
        <div class="card-body">
            <h4><i class="fas fa-eye"></i> Template Preview</h4>
            <?php if (!empty($model->body_text)): ?>
                <div class="alert alert-light border">
                    <h6><i class="fas fa-align-left"></i> Template Body</h6>
                    <div class="template-body-text">
                        <?= nl2br(Html::encode($model->body_text)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($components)): ?>
                <div class="row">
                    <?php foreach ($components as $component): ?>
                        <?php if (strtoupper($component->type) === 'HEADER' && in_array($component->format, ['IMAGE', 'VIDEO', 'DOCUMENT'])): ?>
                            <div class="col-md-4">
                                <div class="card border-primary mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-heading"></i> Header Media Required</h6>
                                        <p class="card-text text-primary">
                                            <strong>Format:</strong> <?= $component->format ?><br>
                                            <small>You'll need to upload a <?= strtolower($component->format) ?> for the header.</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bulk Upload Form -->
    <div class="card">
        <div class="card-body">
            <h4><i class="fas fa-upload"></i> Bulk Message Upload</h4>
            
            <form id="bulkMessageForm" enctype="multipart/form-data">
                <input type="hidden" name="template_id" value="<?= $model->id ?>" id="template_id_field">
                
                <!-- Step 1: Upload Excel File -->
                <div class="form-section"> 
                    <h5 class="text-primary"><i class="fas fa-file-excel"></i> Step 1: Upload Excel File</h5>
                    <div class="form-group">
                        <label for="excel_file">Excel/CSV File *</label>
                        <input type="file" class="form-control-file" name="excel_file" id="excel_file" 
                               accept=".csv,.xlsx,.xls" required>
                        <small class="form-text text-muted">
                            Upload CSV or Excel file with mobile numbers and dynamic parameters. 
                            <a href="<?= Url::to(['download-sample-excel', 'id' => $model->id]) ?>">
                                <i class="fas fa-download"></i> Download sample file
                            </a>
                        </small>
                        <div class="alert alert-info mt-2">
                            <h6><i class="fas fa-info-circle"></i> Mobile Number Format Requirements:</h6>
                            <ul class="mb-0">
                                <li><strong>Include country code without +</strong> (e.g., 916281684605 for India)</li>
                                <li><strong>In Excel:</strong> Format mobile number column as TEXT to prevent scientific notation</li>
                                <li><strong>Alternative:</strong> Prefix numbers with apostrophe (') to force text format</li>
                                <li><strong>Example:</strong> '916281684605 or ensure column is formatted as Text</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php
                // Check if template needs media upload
                $needsMedia = false;
                $mediaFormat = '';
                foreach ($components as $component) {
                    if (strtoupper($component->type) === 'HEADER' && in_array($component->format, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                        $needsMedia = true;
                        $mediaFormat = $component->format;
                        break;
                    }
                }
                ?>

                <?php if ($needsMedia): ?>
                    <!-- Step 2: Upload Media (if required) -->
                    <div class="form-section mt-4">
                        <h5 class="text-warning"><i class="fas fa-image"></i> Step 2: Upload Header Media</h5>
                        <div class="form-group">
                            <label for="header_media_file">Header <?= $mediaFormat ?> *</label>
                            <div class="media-upload-section">
                                <input type="file" class="form-control-file" name="header_media_file" id="header_media_file"
                                       <?php if ($mediaFormat === 'IMAGE'): ?>accept="image/*"<?php endif; ?>
                                       <?php if ($mediaFormat === 'VIDEO'): ?>accept="video/*"<?php endif; ?>
                                       <?php if ($mediaFormat === 'DOCUMENT'): ?>accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"<?php endif; ?>
                                       data-media-type="<?= strtolower($mediaFormat) ?>" required>
                                <input type="hidden" name="header_media_id" id="header_media_id">
                                <input type="hidden" name="header_media_filename" id="header_media_filename">
                                <input type="hidden" name="header_media_caption" id="header_media_caption">
                                <div class="upload-status mt-2"></div>
                                <small class="form-text text-muted">
                                    This <?= strtolower($mediaFormat) ?> will be sent as header to all recipients.<br>
                                    <strong>File Size Limits:</strong> 
                                    <?php if ($mediaFormat === 'IMAGE'): ?>
                                        Images up to 5MB
                                    <?php elseif ($mediaFormat === 'VIDEO'): ?>
                                        Videos up to 16MB
                                    <?php elseif ($mediaFormat === 'DOCUMENT'): ?>
                                        Documents up to 100MB
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                 <?php endif; ?>

                <!-- Step 3: Anti-Spam Settings -->
                <div class="form-section mt-4">
                    <h5 class="text-danger"><i class="fas fa-shield-alt"></i> Step 3: Anti-Spam Settings</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="batch_size">Batch Size</label>
                                <select class="form-control" name="batch_size" id="batch_size">
                                    <option value="50">50 messages</option>
                                    <option value="100" selected>100 messages</option>
                                    <option value="200">200 messages</option>
                                </select>
                                <small class="form-text text-muted">Maximum messages to send in one batch</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delay_seconds">Delay Between Messages</label>
                                <select class="form-control" name="delay_seconds" id="delay_seconds">
                                    <option value="0.5">0.5 seconds</option>
                                    <option value="1" selected>1 second</option>
                                    <option value="2">2 seconds</option>
                                    <option value="3">3 seconds</option>
                                </select>
                                <small class="form-text text-muted">Delay to prevent spam detection</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-section mt-4">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Notes:</h6>
                        <ul class="mb-0">
                            <li><strong>Mobile Number Format:</strong> Must be text format in Excel (not scientific notation like 9.16E+11)</li>
                            <li><strong>Country Code:</strong> Include country code without + symbol (e.g., 916281684605)</li>
                            <li><strong>Excel Tip:</strong> Format mobile number column as TEXT or prefix with apostrophe (')</li>
                            <li>Duplicate numbers will be automatically skipped</li>
                            <li>Processing will stop at batch limit to prevent spam</li>
                            <li>Large files may take time to process - please be patient</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg" id="sendBulkBtn">
                        <i class="fas fa-rocket"></i> Start Bulk Messaging
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="card" id="progressSection" style="display: none;">
        <div class="card-body">
            <h4><i class="fas fa-cogs"></i> Processing Status</h4>
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                     style="width: 0%" id="progressBar"></div>
            </div>
            <div id="statusMessage" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Preparing to send messages...
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="card" id="resultsSection" style="display: none;">
        <div class="card-body">
            <h4><i class="fas fa-chart-bar"></i> Results</h4>
            <div id="resultsContent"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<style>
.form-section {
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 20px;
}

.media-upload-section {
    border: 2px dashed #ddd;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    margin: 10px 0;
    transition: all 0.3s;
}

.media-upload-section:hover {
    border-color: #007bff;
    background-color: #f0f8ff;
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

.template-body-text {
    font-size: 16px;
    line-height: 1.6;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-top: 10px;
}

.progress {
    height: 25px;
}

.results-summary {
    padding: 15px;
    border-radius: 8px;
    margin: 10px 0;
}

.results-summary.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.results-summary.warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.results-summary.error {
    background: #f8d7da;
    border: 1px solid #f1aeb5;
    color: #721c24;
}
</style>

<script>
$(document).ready(function() {
    // Handle media file uploads (if needed)
    $('input[type="file"][data-media-type]').change(function() {
        var fileInput = $(this);
        var file = this.files[0];
        var statusDiv = fileInput.siblings('.upload-status');
        var hiddenInput = $('#header_media_id');
        var mediaType = fileInput.data('media-type');
        
        if (!file) return;
        
        // Validate file size based on WhatsApp limits
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
        
        // Show uploading status
        statusDiv.removeClass('success error').addClass('uploading')
                .html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        // Create FormData
        var formData = new FormData();
        formData.append('media_file', file);
        formData.append('_csrf', $('meta[name=csrf-token]').attr('content'));
        
        // Upload file
        $.ajax({
            url: '<?= Url::to(['upload-media']) ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    var displayName = response.filename || response.media_id;
                    statusDiv.removeClass('uploading error').addClass('success')
                            .html('<i class="fas fa-check"></i> Upload successful! File: ' + displayName);
                    hiddenInput.val(response.media_id);
                    
                    // Store filename and caption
                    if (response.filename) {
                        $('#header_media_filename').val(response.filename);
                    }
                    if (response.caption) {
                        $('#header_media_caption').val(response.caption);
                    }
                } else {
                    statusDiv.removeClass('uploading success').addClass('error')
                            .html('<i class="fas fa-times"></i> Upload failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                statusDiv.removeClass('uploading success').addClass('error')
                        .html('<i class="fas fa-times"></i> Upload error: ' + error);
            }
        });
    });

    // Handle bulk message form submission
    $('#bulkMessageForm').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#sendBulkBtn');
        
        console.log('=== FORM SUBMISSION STARTED ===');
        
        // Debug: Check template ID field
        var templateIdField = $('#template_id_field');
        console.log('Template ID field value:', templateIdField.val());
        console.log('Template ID field exists:', templateIdField.length > 0);
        
        // Debug: Check file input immediately
        var fileInput = document.getElementById('excel_file');
        console.log('File input element at start:', fileInput);
        console.log('Files at start:', fileInput.files);
        console.log('Number of files at start:', fileInput.files.length);
        
        // Validate Excel file
        var excelFile = $('#excel_file')[0].files[0];
        if (!excelFile) {
            console.error('No file found during validation');
            swal("Validation Error", "Please select an Excel/CSV file", "error");
            return;
        }
        
        // Debug file info
        console.log('Selected file during validation:', excelFile.name, 'Size:', excelFile.size, 'Type:', excelFile.type);
        
        // Validate media upload if required
        <?php if ($needsMedia): ?>
        var mediaId = $('#header_media_id').val();
        if (!mediaId) {
            swal("Media Required", "Please upload the header <?= strtolower($mediaFormat) ?> before proceeding", "error");
            return;
        }
        <?php endif; ?>
        
        swal({
            title: "Start Bulk Messaging?",
            text: "This will process your Excel file and send messages to all valid numbers. This action cannot be undone.",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancel",
                    value: false,
                    visible: true
                },
                confirm: {
                    text: "Start Processing",
                    value: true,
                    visible: true
                }
            }
        }).then((willProceed) => {
            if (willProceed) {
                // Show progress section
                $('#progressSection').show();
                $('#resultsSection').hide();
                
                // Store file reference BEFORE disabling anything
                var storedFile = document.getElementById('excel_file').files[0];
                console.log('Stored file before disabling form:', storedFile);
                
                // Disable form and show processing state (but keep file input enabled for FormData)
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                // Don't disable any form elements that might affect FormData
                // form.find('input:not([type="file"]), select').prop('disabled', true);
                
                // Prepare form data
                var formData = new FormData();
                
                // Add CSRF token
                formData.append('_csrf', $('meta[name=csrf-token]').attr('content'));
                
                // Add template_id
                var templateId = $('#template_id_field').val();
                if (templateId) {
                    formData.append('template_id', templateId);
                } else {
                    swal("Error", "Template ID is missing", "error");
                    submitBtn.prop('disabled', false).html('<i class="fas fa-rocket"></i> Start Bulk Messaging');
                    form.find('input, select').prop('disabled', false);
                    return;
                }
                
                // Add Excel file using stored reference
                if (storedFile) {
                    console.log('Using stored file:', {
                        name: storedFile.name,
                        size: storedFile.size,
                        type: storedFile.type,
                        lastModified: storedFile.lastModified
                    });
                    formData.append('excel_file', storedFile);
                    console.log('✓ Stored file appended to FormData');
                } else {
                    console.error('✗ Stored file is null or undefined');
                    swal("Error", "File reference was lost", "error");
                    submitBtn.prop('disabled', false).html('<i class="fas fa-rocket"></i> Start Bulk Messaging');
                    return;
                }
                
                // Add any header media if exists
                var headerMediaId = $('#header_media_id').val();
                if (headerMediaId) {
                    formData.append('header_media_id', headerMediaId);
                    
                    // Add filename and caption if available
                    var headerMediaFilename = $('#header_media_filename').val();
                    if (headerMediaFilename) {
                        formData.append('header_media_filename', headerMediaFilename);
                    }
                    
                    var headerMediaCaption = $('#header_media_caption').val();
                    if (headerMediaCaption) {
                        formData.append('header_media_caption', headerMediaCaption);
                    }
                }
                
                // Debug: Log form data
                console.log('Form data being sent:');
                for (var pair of formData.entries()) {
                    if (pair[1] instanceof File) {
                        console.log(pair[0] + ': File - ' + pair[1].name + ' (' + pair[1].size + ' bytes)');
                    } else {
                        console.log(pair[0] + ': ' + pair[1]);
                    }
                }
                
                // Specifically check if excel_file is in FormData
                var hasExcelFile = formData.has('excel_file');
                console.log('FormData has excel_file:', hasExcelFile);
                if (hasExcelFile) {
                    var excelFileFromForm = formData.get('excel_file');
                    console.log('Excel file from FormData:', excelFileFromForm);
                    if (excelFileFromForm instanceof File) {
                        console.log('✓ File is properly attached to FormData');
                    } else {
                        console.error('✗ excel_file in FormData is not a File object:', typeof excelFileFromForm);
                    }
                } else {
                    console.error('✗ excel_file NOT found in FormData');
                    swal("Error", "File was not properly attached to form data", "error");
                    submitBtn.prop('disabled', false).html('<i class="fas fa-rocket"></i> Start Bulk Messaging');
                    form.find('input, select').prop('disabled', false);
                    return;
                }
                
                // Update progress
                updateProgress(25, 'Reading Excel file...');
                
                setTimeout(function() {
                    updateProgress(50, 'Validating data...');
                    
                    // Send AJAX request with enhanced timeout handling
                    $.ajax({
                        url: '<?= Url::to(['process-bulk-upload']) ?>',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        timeout: 360000, // 6 minutes timeout
                        xhr: function() {
                            var xhr = new XMLHttpRequest();
                            var startTime = Date.now();
                            
                            // Monitor progress and show estimated time
                            var progressInterval = setInterval(function() {
                                var elapsed = Math.round((Date.now() - startTime) / 1000);
                                var progress = Math.min(90, 20 + (elapsed * 1.5)); // Gradual progress increase
                                updateProgress(progress, 'Processing messages... (' + elapsed + 's elapsed)');
                            }, 2000);
                            
                            // Clear interval when request completes
                            xhr.addEventListener('loadend', function() {
                                clearInterval(progressInterval);
                            });
                            
                            return xhr;
                        },
                        success: function(response) {
                            updateProgress(100, 'Processing complete!');
                            
                            setTimeout(function() {
                                showResults(response);
                                
                                // Re-enable form
                                submitBtn.prop('disabled', false).html('<i class="fas fa-rocket"></i> Start Bulk Messaging');
                                form.find('input, select').prop('disabled', false);
                            }, 1000);
                        },
                        error: function(xhr, status, error) {
                            updateProgress(100, 'Processing failed!');
                            
                            setTimeout(function() {
                                $('#progressSection').hide();
                                
                                var errorMessage = '';
                                if (status === 'timeout') {
                                    errorMessage = 'Request timed out. The bulk messaging process may still be running in the background. Please check your messages or try with a smaller batch.';
                                } else if (xhr.status === 0) {
                                    errorMessage = 'Network connection error. Please check your internet connection and try again.';
                                } else if (xhr.status >= 500) {
                                    errorMessage = 'Server error (' + xhr.status + '). Please try again or contact support if the problem persists.';
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else {
                                    errorMessage = 'An error occurred: ' + error + ' (Status: ' + status + ')';
                                }
                                
                                swal({
                                    title: "Processing Failed",
                                    text: errorMessage,
                                    icon: "error",
                                    button: "OK"
                                });
                                
                                // Re-enable form
                                submitBtn.prop('disabled', false).html('<i class="fas fa-rocket"></i> Start Bulk Messaging');
                                form.find('input, select').prop('disabled', false);
                            }, 1000);
                        }
                    });
                }, 1000);
            }
        });
    });
    
    function updateProgress(percent, message) {
        $('#progressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#statusMessage').html('<i class="fas fa-cogs"></i> ' + message);
    }
    
    function showResults(response) {
        $('#progressSection').hide();
        $('#resultsSection').show();
        
        var resultsHtml = '';
        
        if (response.success) {
            var successClass = response.failed_count > 0 ? 'warning' : 'success';
            var icon = response.failed_count > 0 ? 'exclamation-triangle' : 'check-circle';
            
            resultsHtml += '<div class="results-summary ' + successClass + '">';
            resultsHtml += '<h5><i class="fas fa-' + icon + '"></i> ' + response.message + '</h5>';
            resultsHtml += '<div class="row mt-3">';
            resultsHtml += '<div class="col-md-3"><strong>Total Processed:</strong> ' + response.total_processed + '</div>';
            resultsHtml += '<div class="col-md-3"><strong>Successful:</strong> ' + response.success_count + '</div>';
            resultsHtml += '<div class="col-md-3"><strong>Failed:</strong> ' + response.failed_count + '</div>';
            resultsHtml += '<div class="col-md-3"><strong>Success Rate:</strong> ' + Math.round((response.success_count / response.total_processed) * 100) + '%</div>';
            resultsHtml += '</div>';
            
            // Add performance metrics if available
            if (response.performance_metrics) {
                resultsHtml += '<div class="row mt-2 border-top pt-2">';
                resultsHtml += '<div class="col-md-12"><h6><i class="fas fa-clock"></i> Performance Metrics</h6></div>';
                resultsHtml += '<div class="col-md-3"><strong>Execution Time:</strong> ' + response.performance_metrics.execution_time + 's</div>';
                resultsHtml += '<div class="col-md-3"><strong>Avg per Message:</strong> ' + response.performance_metrics.avg_time_per_message + 's</div>';
                resultsHtml += '<div class="col-md-3"><strong>Chunks Processed:</strong> ' + response.performance_metrics.chunks_processed + '</div>';
                resultsHtml += '<div class="col-md-3"><strong>Messages/Chunk:</strong> ' + response.performance_metrics.messages_per_chunk + '</div>';
                resultsHtml += '</div>';
            }
            
            resultsHtml += '</div>';
            
            if (response.errors && response.errors.length > 0) {
                resultsHtml += '<div class="mt-3">';
                resultsHtml += '<h6><i class="fas fa-exclamation-circle"></i> Errors/Warnings:</h6>';
                resultsHtml += '<div class="alert alert-danger" style="max-height: 300px; overflow-y: auto;">';
                resultsHtml += '<ul class="mb-0">';
                response.errors.forEach(function(error) {
                    resultsHtml += '<li>' + error + '</li>';
                });
                resultsHtml += '</ul>';
                resultsHtml += '</div>';
                resultsHtml += '</div>';
            }
        } else {
            resultsHtml += '<div class="results-summary error">';
            resultsHtml += '<h5><i class="fas fa-times-circle"></i> Processing Failed</h5>';
            resultsHtml += '<p>' + response.message + '</p>';
            resultsHtml += '</div>';
        }
        
        $('#resultsContent').html(resultsHtml);
    }
});
</script>
