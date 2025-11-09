<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkCampaignLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Bulk Campaign Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-bulk-campaign-log-view">
<div class="card">
       <div class="card-body">
    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Aisensy Bulk Campaign Log').' '. Html::encode($this->title) ?></h2>
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
    <div class="card">
       <div class="card-body">
    <div class="row">
<?php
if($providerAisensyBulkMessageLog->totalCount){
    $gridColumnAisensyBulkMessageLog = [
        ['class' => 'yii\grid\SerialColumn'],
            ['attribute' => 'id', 'visible' => false],
                        [
                'attribute' => 'template.name',
                'label' => Yii::t('app', 'Template')
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
    echo Gridview::widget([
        'dataProvider' => $providerAisensyBulkMessageLog,
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-aisensy-bulk-message-log']],
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<span class="glyphicon glyphicon-book"></span> ' . Html::encode(Yii::t('app', 'Aisensy Bulk Message Log')),
        ],
        'export' => false,
        'columns' => $gridColumnAisensyBulkMessageLog
    ]);
}

?>
</div>
</div>
</div>

</div>

