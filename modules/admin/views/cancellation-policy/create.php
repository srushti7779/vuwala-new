<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\CancellationPolicy */

$this->title = Yii::t('app', 'Create Cancellation Policy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cancellation Policies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cancellation-policy-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
