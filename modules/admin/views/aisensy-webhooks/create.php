<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyWebhooks */

$this->title = Yii::t('app', 'Create Aisensy Webhooks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Webhooks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-webhooks-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
