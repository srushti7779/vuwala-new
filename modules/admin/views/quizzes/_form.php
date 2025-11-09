<?php
    use kartik\form\ActiveForm;
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $model app\modules\admin\models\Quizzes */
    /* @var $form yii\widgets\ActiveForm */

    \mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos' => \yii\web\View::POS_END,
        'viewParams' => [
            'class' => 'QuizQuestions',
            'relID' => 'quiz-questions',
            'value' => \yii\helpers\Json::encode($model->quizQuestions),
            'isNewRecord' => ($model->isNewRecord) ? 1 : 0,
        ],
    ]);
    \mootensai\components\JsBlock::widget(['viewFile' => '_script', 'pos' => \yii\web\View::POS_END,
        'viewParams' => [
            'class' => 'QuizUserAnswers',
            'relID' => 'quiz-user-answers',
            'value' => \yii\helpers\Json::encode($model->quizUserAnswers),
            'isNewRecord' => ($model->isNewRecord) ? 1 : 0,
        ],
    ]);
?>

<div class="quizzes-form">

    <?php $form = ActiveForm::begin([
        'id' => 'login-form-inline',
        'type' => ActiveForm::TYPE_VERTICAL,
        'tooltipStyleFeedback' => true,
        'fieldConfig' => ['options' => ['class' => 'form-group col-xs-6 col-sm-6 col-md-6 col-lg-12']],
        'formConfig' => ['showErrors' => true],
    ]); ?>

    <?php echo $form->errorSummary($model); ?>
    
    <div class="row">
        <div class='col-lg-6'>
            <?php echo $form->field($model, 'id', ['template' => '{input}'])->textInput(['style' => 'display:none']); ?>
        </div>
        
        <div class='col-lg-6'>
            <?php echo $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Title']) ?>
        </div>

        <div class='col-lg-6'>
            <?php echo $form->field($model, 'description')->widget(\mihaildev\ckeditor\CKEditor::className(), [
                'editorOptions' => [
                    'preset' => 'full',
                    'inline' => false,
                ],
            ]) ?>
        </div>

        <div class='col-lg-6'>
            <?php echo $form->field($model, 'status')->dropDownList($model->getStateOptions()) ?>
        </div>
    </div>

    <?php
    $forms = [
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'Quiz Questions')),
            'content' => $this->render('_formQuizQuestions', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->quizQuestions),
                'quizId' => $model->isNewRecord ? null : $model->id,
            ]),
        ],
        [
            'label' => '<i class="fa fa-book"></i> ' . Html::encode(Yii::t('app', 'Quiz Questions')),
            'content' => $this->render('_formQuizUserAnswers', [
                'row' => \yii\helpers\ArrayHelper::toArray($model->quizUserAnswers),
                'quizId' => $model->isNewRecord ? null : $model->id,
            ]),
        ],
    ];
    
    // Remove the condition for new records so tabs show for existing quizzes too
    echo kartik\tabs\TabsX::widget([
        'items' => $forms,
        'position' => kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'pluginOptions' => [
            'bordered' => true,
            'sideways' => true,
            'enableCache' => false,
        ],
    ]);
    ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- Modal for editing questions -->
<div class="modal fade" id="questionModal" tabindex="-1" role="dialog" aria-labelledby="questionModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="questionModalLabel">Edit Question</h4>
            </div>
            <div class="modal-body" id="questionModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveQuestionBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for handling question editing
$this->registerJs(<<<JS
// Function to open edit modal
function openEditQuestionModal(questionId) {
    $.get('/admin/quizzes/edit-question', {id: questionId}, function(data) {
        $('#questionModalBody').html(data);
        $('#questionModal').modal('show');
    });
}

// Save edited question
$('#saveQuestionBtn').on('click', function() {
    var formData = $('#question-form').serialize();
    
    $.post('/admin/quizzes/save-question', formData, function(response) {
        if (response.success) {
            $('#questionModal').modal('hide');
            // Refresh the questions tab or update specific question
            $.pjax.reload({container: '#quiz-questions-tab', timeout: false});
        } else {
            $('#questionModalBody').html(response.form);
        }
    });
});

// Handle delete question
function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        $.post('/admin/quizzes/delete-question', {id: questionId}, function(response) {
            if (response.success) {
                $.pjax.reload({container: '#quiz-questions-tab', timeout: false});
            }
        });
    }
}
JS
);
?>