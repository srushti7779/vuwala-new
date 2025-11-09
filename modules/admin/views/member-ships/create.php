<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\MemberShips */

$this->title = Yii::t('app', 'Create Member Ships');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Member Ships'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-ships-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
