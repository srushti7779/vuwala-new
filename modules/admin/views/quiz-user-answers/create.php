<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\QuizUserAnswers */

$this->title = Yii::t('app', 'Create Quiz User Answers');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Quiz User Answers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="quiz-user-answers-create">
    <div class="card">
       <div class="card-body">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
    </div>
    </div>
</div>
