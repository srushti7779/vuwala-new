<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\StoresUsersMemberships */

$this->title = Yii::t('app', 'Create Stores Users MemberShips');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stores Users MemberShips'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stores-users-memberships-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
