<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkCampaignLog */

$this->title = Yii::t('app', 'Create Aisensy Bulk Campaign Log');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Bulk Campaign Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-bulk-campaign-log-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
