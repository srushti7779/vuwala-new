<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\data\ArrayDataProvider;
use kartik\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Banner */
/* @var $providerQuizQuestions yii\data\ActiveDataProvider */
/* @var $providerQuizUserAnswers yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Custom CSS
$this->registerCss(<<<CSS
.table th {
    font-weight: 600;
    background-color: #f1f1f1 !important;
    color: #333;
}
.table td img {
    max-width: 100px;
    height: auto;
}
.grid-view {
    overflow-x: auto;
}
.card-header {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
}
.card-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
}
.card {
    border-radius: 1rem;
    box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
}
.card-body {
    background-color: #fff;
    padding: 1.25rem;
}
.beautiful-btn {
    font-size: 14px;
    padding: 8px 20px;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    color: #fff;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease-in-out;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.beautiful-btn i {
    margin-right: 6px;
}
.beautiful-btn.update { background: linear-gradient(to right, #36d1dc, #5b86e5); }
.beautiful-btn.delete { background: linear-gradient(to right, #f85032, #e73827); }
.beautiful-btn.recharge { background: linear-gradient(to right, #56ab2f, #a8e063); }
.beautiful-btn.timing { background: linear-gradient(to right, #fbc02d, #ffeb3b); color: #000; }
.beautiful-btn.logs { background: linear-gradient(to right, #0db423ff, #09ac42ff); color: #000; }
.beautiful-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(5, 53, 150, 0.15);
}
CSS);
?>

<!-- Action Buttons -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-sliders-h me-2"></i>Quiz Actions</h5>
    </div>
    <div class="card-body text-center">
        <?= Html::a('<i class="fas fa-pen"></i> Update', ['update', 'id' => $model->id], [
            'class' => 'btn beautiful-btn update'
        ]) ?>

        <?php if (Yii::$app->user->identity->user_role == User::ROLE_ADMIN || Yii::$app->user->identity->user_role == User::ROLE_SUBADMIN): ?>
            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn beautiful-btn delete',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </div>
</div>

<!-- Quiz Detail -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2"></i>Quiz Details</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'title',
                 [
                    'attribute' => 'description',
                    'format'=> 'raw',
                    'value' => function($model){
                        return $model->description;
                    }
                ],
                'status',
            ],
        ]) ?>
    </div>
</div>

<!-- Quiz Questions -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-list-alt me-2"></i>Quiz Questions</h5>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $providerQuizQuestions,
            'pjax' => true,
            'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-quiz-questions']],
            'panel' => false,
            'export' => false,
            'emptyText' => '<div class="text-muted">No quiz questions found.</div>',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'question_text:ntext',
                'question_type',
                'status',
            ],
        ]) ?>
    </div>
</div>

<!-- User Answers -->
<?php if ($providerQuizUserAnswers->totalCount): ?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-users me-2"></i>User Answers</h5>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $providerQuizUserAnswers,
            'pjax' => true,
            'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-quiz-user-answers']],
            'panel' => false,
            'export' => false,
            'layout' => '{items}{pager}',
            'tableOptions' => ['class' => 'table table-sm table-hover table-bordered table-striped'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                ['attribute' => 'user.username', 'label' => Yii::t('app', 'User')],
                [
                    'attribute' => 'question.question_text',
                    'label' => Yii::t('app', 'Question'),
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'max-width: 300px; white-space: normal;'],
                ],
                [
                    'attribute' => 'answer.answer_text',
                    'label' => Yii::t('app', 'Answer'),
                    'format' => 'ntext',
                    'contentOptions' => ['style' => 'max-width: 300px; white-space: normal;'],
                ],
                [
                    'attribute' => 'is_correct',
                    'format' => 'raw',
                    'value' => function($model) {
                        return $model->is_correct
                            ? '<span class="badge bg-success">Correct</span>'
                            : '<span class="badge bg-danger">Wrong</span>';
                    },
                ],
                ['attribute' => 'answered_on', 'format' => ['datetime', 'php:d-m-Y H:i']],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function($model) {
                        return $model->status
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>';
                    },
                ],
            ],
        ]) ?>
    </div>
</div>
<?php endif; ?>

<!-- Created By User -->
<?php if ($model->createUser): ?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user-plus me-2"></i>Created By</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->createUser,
            'attributes' => [
                'username',
                'email',
                'first_name',
                'last_name',
                'contact_no',
                'gender',
                'address',
                'status',
                'created_at:datetime',
            ],
        ]) ?>
    </div>
</div>
<?php endif; ?>

<!-- Updated By User -->
<?php if ($model->updateUser): ?>
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user-edit me-2"></i>Last Updated By</h5>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->updateUser,
            'attributes' => [
                'username',
                'email',
                'first_name',
                'last_name',
                'contact_no',
                'gender',
                'address',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
<?php endif; ?>
