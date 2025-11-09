<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \app\modules\admin\forms\UserForm */

$this->title  = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Vendor', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['heading']       = 'Vendor';
$this->params['subheading']    = 'Add New';
?>
<div class="user-create">

	<?= $this->render('_vendor_form', [
		'model' => $model,
	]) ?>

</div>
