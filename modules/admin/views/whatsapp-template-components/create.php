<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\WhatsappTemplateComponents */

$this->title = Yii::t('app', 'Create Whatsapp Template Components');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Whatsapp Template Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whatsapp-template-components-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
