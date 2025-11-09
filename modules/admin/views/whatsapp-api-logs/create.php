<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappApiLogs */

$this->title = Yii::t('app', 'Create Whatsapp Api Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp Api Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whatsapp-api-logs-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
