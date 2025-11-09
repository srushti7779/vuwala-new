<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Hierarchy */

$this->title = Yii::t('app', 'Create Hierarchy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Hierarchies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hierarchy-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
