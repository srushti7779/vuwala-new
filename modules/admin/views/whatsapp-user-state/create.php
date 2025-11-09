<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappUserState */

$this->title = Yii::t('app', 'Create Whatsapp User State');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp User States'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whatsapp-user-state-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
