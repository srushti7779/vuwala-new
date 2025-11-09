<?php

use app\models\User;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use app\modules\admin\models\City;
use app\modules\admin\models\Courses;
use app\modules\admin\models\Subjects;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model \app\modules\admin\forms\UserForm
 * @var $form yii\widgets\ActiveForm
 */
?>

<div class="user-form card">
	<div class="card-body">


		<?php if ($model->isNewRecord) {
			$form = ActiveForm::begin([
				'enableAjaxValidation' => true,
			]);
		} else {
			$form = ActiveForm::begin([
				'enableAjaxValidation' => false,
			]);
		} ?>

		<div class="row">

			<div class='col-lg-6'><?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?></div>

			<div class='col-lg-6'><?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?></div>

			<div class='col-lg-6'><?= $form->field($model, 'username', ['enableAjaxValidation' => true]); ?></div>

			<div class='col-lg-6'><?= $form->field($model, 'email')->textInput(['maxlength' => true, 'enableAjaxValidation' => true]) ?></div>

			<div class='col-lg-6'><?= $form->field($model, 'password')->passwordInput() ?></div>

			<div class='col-lg-6'><?= $form->field($model, 'passwordRepeat')->passwordInput() ?></div>

			<?php if ($model->isNewRecord) { ?>
				<div class='col-lg-6'> <?= $form->field($userCourse, 'course_id')->widget(\kartik\widgets\Select2::classname(), [
											'data' => \yii\helpers\ArrayHelper::map(
												\app\modules\admin\models\Courses::find()
													->joinWith('plan') // Assuming there's a relation named 'plan' in the Courses model
													->where(['courses.status' => Courses::STATUS_ACTIVE])
													->orderBy('courses.id')
													->asArray()
													->all(),
												'id',

												function ($course) {
													return $course['name'] . ' - (' . $course['plan']['name'] . ')';
												}
											),
											'options' => ['placeholder' => Yii::t('app', 'Choose Courses'), 'id' => 'course-id'],
											'pluginOptions' => [
												'allowClear' => true
											],
										]); ?></div>

				<div class='col-lg-6'> <?= $form->field($userCourse, 'subject_id')->widget(DepDrop::classname(), [
											'data' => \yii\helpers\ArrayHelper::map(
												Subjects::find()->where(['status' => Subjects::STATUS_ACTIVE])->orWhere(['status' => "1"])->asArray()->all(),
												'id',
												'name' . 'plan_id'
											),
											'type' => DepDrop::TYPE_SELECT2,
											'options' => [
												'placeholder' => Yii::t('app', 'Choose Subjects'), 'id' => 'category-id',
												'multiple' => true,

											],
											'pluginOptions' => [
												'depends' => ['course-id'],
												'placeholder' => 'Select...',
												'url' => Url::to(['subject-topics/get-subject']),
											],
										]);
										?> </div><?php } ?>
			<div class='col-lg-6'> <?= $form->field($model, 'permission')->dropDownList($model->getPermissionStatus())->label('Permissions')  ?> </div>

			<div class='col-lg-6'><?= $form->field($model, 'status')->dropDownList(User::getStatusesList()) ?></div>

		</div>







		<div class="card-footer text-right">
			<?= Html::submitButton($model->isNewRecord ? 'Save' : 'Update', ['class' => 'btn btn-primary']) ?>
		</div>

		<?php ActiveForm::end(); ?>
	</div>
</div>