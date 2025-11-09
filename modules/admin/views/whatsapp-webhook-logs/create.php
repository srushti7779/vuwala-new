<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappWebhookLogs */

$this->title = Yii::t('app', 'Create Whatsapp Webhook Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp Webhook Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whatsapp-webhook-logs-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
