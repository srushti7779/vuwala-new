<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkMessageLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Bulk Message Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-bulk-message-log-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Aisensy Bulk Message Log').' '. Html::encode($this->title) ?></h2>
        </div>
        <div class="col-sm-3" style="margin-top: 15px">
            
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                          <?php  if(\Yii::$app->user->identity->user_role==User::ROLE_ADMIN || \Yii::$app->user->identity->user_role==User::ROLE_SUBADMIN){ ?>
             <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ])
            ?>  
             <?php  } ?>
        </div>
    </div>
    </div>
    </div>
    <div class="card">
       <div class="card-body">

    <div class="row">
<?php 
    $gridColumn = [
        ['attribute' => 'id', 'visible' => false],
        [
            'attribute' => 'campaign.id',
            'label' => Yii::t('app', 'Campaign'),
        ],
        [
            'attribute' => 'template.name',
            'label' => Yii::t('app', 'Template'),
        ],
        'contact_number',
        'message_id',
        'status',
        'skip_reason',
        'sent_datetime',
        'delivered_datetime',
        'read_datetime',
        'error_message',
        'response_data',
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumn
    ]);
?>
</div>
</div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>AisensyBulkCampaignLog<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnAisensyBulkCampaignLog = [
        ['attribute' => 'id', 'visible' => false],
        'campaign_name',
        [
            'attribute' => 'template.name',
            'label' => Yii::t('app', 'Template'),
        ],
        'total_contacts',
        'sent_count',
        'delivered_count',
        'failed_count',
        'skipped_count',
        'campaign_status',
        'started_at',
        'completed_at',
    ];
    echo DetailView::widget([
        'model' => $model->campaign,
        'attributes' => $gridColumnAisensyBulkCampaignLog    ]);
    ?>
    </div>
    </div>
    <div class="card">
       <div class="card-body">
    <div class="row">
        <h4>AisensyTemplates<?= ' '. Html::encode($this->title) ?></h4>
    </div>
    <?php 
    $gridColumnAisensyTemplates = [
        ['attribute' => 'id', 'visible' => false],
        'external_id',
        'name',
        'category',
        'language',
        'status',
        'quality_score',
        'rejected_reason',
        'footer_text',
        'body_text',
        'meta',
    ];
    echo DetailView::widget([
        'model' => $model->template,
        'attributes' => $gridColumnAisensyTemplates    ]);
    ?>
    </div>
    </div>
</div>

