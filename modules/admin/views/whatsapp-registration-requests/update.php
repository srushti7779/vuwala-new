<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappRegistrationRequests */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Whatsapp Registration Requests',
]) . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp Registration Requests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="whatsapp-registration-requests-update">
<div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
</div>
</div>
