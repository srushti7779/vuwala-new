<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyBulkMessageLog */

$this->title = Yii::t('app', 'Create Aisensy Bulk Message Log');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Bulk Message Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-bulk-message-log-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
