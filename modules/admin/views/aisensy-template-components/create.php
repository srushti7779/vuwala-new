<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\AisensyTemplateComponents */

$this->title = Yii::t('app', 'Create Aisensy Template Components');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Aisensy Template Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aisensy-template-components-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
