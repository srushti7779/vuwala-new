<?php

/* @var $this yii\web\View */
/* @var $model \app\modules\admin\forms\UserForm */
/* @var $roles array */

$this->title = "Update {$model->fullName}";
$this->params['breadcrumbs'][] = ['label' => 'Vendors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fullName, 'url' => ['update', 'id' => $model->id]];
$this->params['heading'] = 'Vendors';
$this->params['subheading'] = $model->fullName;
?>
<div class="vendor-update">
	
	<?= $this->render('update_form_vendor', [
		'model' => $model,
	]) ?>

</div>
aa