<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappConversationFlows */

$this->title = Yii::t('app', 'Create Whatsapp Conversation Flows');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp Conversation Flows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whatsapp-conversation-flows-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
