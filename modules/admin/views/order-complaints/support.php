<?php

use app\modules\admin\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\OrderComplaints */

$this->title = 'Submit Support Complaint';
$this->params['breadcrumbs'][] = ['label' => 'Order Complaints', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="order-complaints-support">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-exclamation-circle"></i> Submit Complaint</h5>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
              <?= $form->field($model, 'response')->textarea(['rows' => 6]) ?>
                <?php if(User::isAdmin()) : ?>

                <?= $form->field($model, 'status')->dropDownList($model->getStatusList(), ['prompt' => 'Select Status']) ?>

                <?php else: ?>
                    <?= $form->field($model, 'status')->hiddenInput(['value'=> 0])->label(false) ?>

                <?php endif; ?>
             

                

            <!-- Hidden fields if necessary -->
            <?= $form->field($model, 'order_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'store_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

