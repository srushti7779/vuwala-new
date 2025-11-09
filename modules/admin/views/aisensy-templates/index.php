<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\AisensyTemplatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use app\models\User;
use app\modules\admin\models\base\Banner;
use app\modules\admin\models\AisensyWebhooks;

use kartik\grid\GridView;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Aisensy Templates');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);

// Register CSRF token for AJAX requests
$this->registerMetaTag(['name' => 'csrf-token', 'content' => Yii::$app->request->getCsrfToken()], 'csrf-token');

// Get webhook statistics
$webhookStats = [
    'sent' => AisensyWebhooks::find()->where(['status_value' => 'sent'])->count(),
    'delivered' => AisensyWebhooks::find()->where(['status_value' => 'delivered'])->count(),
    'read' => AisensyWebhooks::find()->where(['status_value' => 'read'])->count(),
    'failed' => AisensyWebhooks::find()->where(['status_value' => 'failed'])->count(),
    'total' => AisensyWebhooks::find()->count(),
    'unique_messages' => AisensyWebhooks::find()->select('message_id')->distinct()->count(),
    'unique_sent' => AisensyWebhooks::find()->select('message_id')->where(['status_value' => 'sent'])->distinct()->count()
];

// Get today's statistics
$todayStats = [
    'sent' => AisensyWebhooks::find()->where(['status_value' => 'sent'])->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->count(),
    'delivered' => AisensyWebhooks::find()->where(['status_value' => 'delivered'])->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->count(),
    'read' => AisensyWebhooks::find()->where(['status_value' => 'read'])->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->count(),
    'failed' => AisensyWebhooks::find()->where(['status_value' => 'failed'])->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->count(),
    'total' => AisensyWebhooks::find()->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->count(),
    'unique_messages' => AisensyWebhooks::find()->select('message_id')->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->distinct()->count(),
    'unique_sent' => AisensyWebhooks::find()->select('message_id')->where(['status_value' => 'sent'])->andWhere(['>=', 'created_on', date('Y-m-d 00:00:00')])->distinct()->count()
];

?>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card-body h3 {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
}

.card-body h4 {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
}

.card-body p {
    font-size: 0.9rem;
    margin: 0;
    opacity: 0.9;
}

.bg-info { background-color: #17a2b8 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-primary { background-color: #007bff !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-warning { background-color: #ffc107 !important; }

@media (max-width: 768px) {
    .card-body h3 {
        font-size: 1.8rem;
    }
    .card-body h4 {
        font-size: 1.5rem;
    }
    .card-body p {
        font-size: 0.8rem;
    }
}
</style>

<div class="aisensy-templates-index">
<div class="card">
       <div class="card-body">
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <!-- Webhook Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-3"><i class="fas fa-chart-bar"></i> WhatsApp Message Statistics</h4>
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload();">
                <i class="fas fa-sync-alt"></i> Refresh Stats
            </button>
            <small class="text-muted d-block mt-1">Last updated: <?= date('Y-m-d H:i:s') ?></small>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3 class="card-title"><?= number_format($webhookStats['sent']) ?></h3>
                    <p class="card-text"><i class="fas fa-paper-plane"></i> Sent</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="card-title"><?= number_format($webhookStats['delivered']) ?></h3>
                    <p class="card-text"><i class="fas fa-check-circle"></i> Delivered</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3 class="card-title"><?= number_format($webhookStats['read']) ?></h3>
                    <p class="card-text"><i class="fas fa-eye"></i> Read</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h3 class="card-title"><?= number_format($webhookStats['failed']) ?></h3>
                    <p class="card-text"><i class="fas fa-exclamation-triangle"></i> Failed</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h3 class="card-title"><?= number_format($webhookStats['unique_messages']) ?></h3>
                    <p class="card-text"><i class="fas fa-list"></i> Unique Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <h3 class="card-title"><?= $webhookStats['unique_sent'] > 0 ? number_format(($webhookStats['delivered'] / $webhookStats['unique_sent']) * 100, 1) : 0 ?>%</h3>
                    <p class="card-text"><i class="fas fa-percentage"></i> Delivery Rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="mb-3"><i class="fas fa-calendar-day"></i> Today's Statistics (<?= date('Y-m-d') ?>)</h5>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-info">
                <div class="card-body">
                    <h4 class="card-title text-info"><?= number_format($todayStats['sent']) ?></h4>
                    <p class="card-text text-muted"><i class="fas fa-paper-plane"></i> Sent Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-success">
                <div class="card-body">
                    <h4 class="card-title text-success"><?= number_format($todayStats['delivered']) ?></h4>
                    <p class="card-text text-muted"><i class="fas fa-check-circle"></i> Delivered Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-primary">
                <div class="card-body">
                    <h4 class="card-title text-primary"><?= number_format($todayStats['read']) ?></h4>
                    <p class="card-text text-muted"><i class="fas fa-eye"></i> Read Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-danger">
                <div class="card-body">
                    <h4 class="card-title text-danger"><?= number_format($todayStats['failed']) ?></h4>
                    <p class="card-text text-muted"><i class="fas fa-exclamation-triangle"></i> Failed Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-secondary">
                <div class="card-body">
                    <h4 class="card-title text-secondary"><?= number_format($todayStats['unique_messages']) ?></h4>
                    <p class="card-text text-muted"><i class="fas fa-list"></i> Unique Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card text-center bg-light border-warning">
                <div class="card-body">
                    <h4 class="card-title text-warning"><?= $todayStats['unique_sent'] > 0 ? number_format(($todayStats['delivered'] / $todayStats['unique_sent']) * 100, 1) : 0 ?>%</h4>
                    <p class="card-text text-muted"><i class="fas fa-percentage"></i> Today's Rate</p>
                </div>
            </div>
        </div>
    </div>

    <p>
    <?php  if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN){ ?>
        <?= Html::button('<i class="fas fa-download"></i> ' . Yii::t('app', 'Import Templates'), ['class' => 'btn btn-primary', 'id' => 'import-templates-btn']) ?>
        <?php  } ?>
    </p>
        </div>
    </div>
    <div class="card">
       <div class="card-body">
<?php 
    $gridColumn = [
        ['class' => 'yii\grid\SerialColumn'],
   
        ['attribute' => 'id', 'visible' => false],
   
        'external_id',
   
        'name',
   
        'category',
   
        'language',
   
        [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model){                   
                    return $model->getStateOptionsBadges();                   
                },
               
               
            ],
   
   
   
   
        'body_text:ntext',
   
    
        [
            'class' => 'kartik\grid\ActionColumn',
             'template' => '{view} {bulk} {update} {delete}',
             'buttons' => [
            'view'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a( '<span class="fas fa-eye" aria-hidden="true"></span>', $url, ['title' => 'View Template']);
                } 
                },
            'bulk'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a( '<span class="fas fa-broadcast-tower text-primary" aria-hidden="true"></span>', 
                        ['bulk-message', 'id' => $model->id], 
                        ['title' => 'Bulk Messages']);
                } 
                },
            'update'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN || \Yii::$app->user->identity->user_role == User::ROLE_MANAGER) {
                    return Html::a( '<span class="fas fa-pencil-alt" aria-hidden="true"></span>', $url);

                } 
                },
            'delete'=> function($url,$model) {
            if (\Yii::$app->user->identity->user_role == User::ROLE_ADMIN || \Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN) {
                    return Html::a( '<span class="fas fa-trash-alt" aria-hidden="true"></span>', 'javascript:void(0)',[
                        'class' => 'delete-template-btn',
                        'data-id' => $model->id,
                        'data-name' => $model->name,
                        'title' => 'Delete Template'
                                ]);
                } 
                },


        ]
            
           

        ],
    ];   
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumn,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-aisensy-templates']],
        'panel' => [
            
            'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
        ],
        'export' => false,
        // your toolbar can include the additional full export menu
        'toolbar' => [
            '{export}',
            ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumn,
                'target' => ExportMenu::TARGET_BLANK,
                'fontAwesome' => true,
                'dropdownOptions' => [
                    'label' => 'Full',
                    'class' => 'btn btn-default',
                    'itemsBefore' => [
                        '<li class="dropdown-header">Export All Data</li>',
                    ],
                ],
                'exportConfig' => [
                    ExportMenu::FORMAT_PDF => false
                ]
            ]) ,
        ],
    ]); ?>
</div>
</div> 
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
$(document).on('change','select[id^=status_list_]',function(){
var id=$(this).attr('data-id');
var val=$(this).val();

$.ajax({
	  type: "POST",
	 
      url: "/estetica_back_end/gii/default/status-change",
     
 
      data: {id:id,val:val},
	  success: function(data){
		  swal("Good job!", "Status Successfully Changed!", "success");
	  }
	});
});

// Import Templates functionality
$(document).on('click', '#import-templates-btn', function(){
    var button = $(this);
    
    swal({
        title: "Import Templates",
        text: "Are you sure you want to import templates from AiSensy? This will fetch all approved templates and update existing ones.",
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancel",
                value: false,
                visible: true
            },
            confirm: {
                text: "Import",
                value: true,
                visible: true
            }
        }
    }).then((willImport) => {
        if (willImport) {
            // Disable button and show loading
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
            
            $.ajax({
                type: "POST",
                url: "<?= yii\helpers\Url::to(['import-templates']) ?>",
                dataType: 'json',
                success: function(response) {
                    button.prop('disabled', false).html('<i class="fas fa-download"></i> Import Templates');
                    
                    if (response.success) {
                        var messageText = response.message;
                        if (response.errors && response.errors.length > 0) {
                            messageText += "\n\nWarnings:\n" + response.errors.join("\n");
                        }
                        
                        swal({
                            title: "Import Successful!",
                            text: messageText,
                            icon: "success",
                            content: {
                                element: "div",
                                attributes: {
                                    innerHTML: messageText.replace(/\n/g, "<br>")
                                }
                            }
                        }).then(() => {
                            // Reload the page to show new templates
                            location.reload();
                        });
                    } else {
                        swal({
                            title: "Import Failed",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    button.prop('disabled', false).html('<i class="fas fa-download"></i> Import Templates');
                    swal({
                        title: "Import Failed",
                        text: "An error occurred during import: " + error,
                        icon: "error"
                    });
                }
            });
        }
    });
});

// Delete Template functionality
$(document).on('click', '.delete-template-btn', function(){
    var button = $(this);
    var templateId = button.data('id');
    var templateName = button.data('name') || 'this template';
    
    swal({
        title: "Delete Template",
        text: "Are you sure you want to delete '" + templateName + "'? This will attempt to delete it from both the local database and AiSensy API.",
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancel",
                value: false,
                visible: true
            },
            confirm: {
                text: "Delete",
                value: true,
                visible: true,
                className: "btn-danger"
            }
        }
    }).then((willDelete) => {
        if (willDelete) {
            // Show loading state
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                type: "POST",
                url: "<?= yii\helpers\Url::to(['delete-template']) ?>",
                data: {
                    id: templateId,
                    _csrf: $('meta[name=csrf-token]').attr("content")
                },
                dataType: 'json',
                success: function(response) {
                    button.prop('disabled', false).html('<span class="fas fa-trash-alt" aria-hidden="true"></span>');
                    
                    if (response.success) {
                        var icon = "success";
                        var title = "Template Deleted!";
                        
                        if (response.apiDeleted === false && response.apiErrors) {
                            icon = "warning";
                            title = "Partially Deleted";
                        }
                        
                        swal({
                            title: title,
                            text: response.message,
                            icon: icon
                        }).then(() => {
                            // Reload the page to reflect changes
                            location.reload();
                        });
                    } else {
                        swal({
                            title: "Deletion Failed",
                            text: response.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    button.prop('disabled', false).html('<span class="fas fa-trash-alt" aria-hidden="true"></span>');
                    swal({
                        title: "Deletion Failed",
                        text: "An error occurred during deletion: " + error,
                        icon: "error"
                    });
                }
            });
        }
    });
});

</script>