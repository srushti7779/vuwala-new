<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\BotSessions */

$this->title = Yii::t('app', 'Create Bot Sessions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Bot Sessions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bot-sessions-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
