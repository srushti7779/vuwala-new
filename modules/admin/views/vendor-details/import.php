<?php
    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ActiveForm;

    $this->title                   = 'Import Vendor Details';
    $this->params['breadcrumbs'][] = $this->title;
?>

<h1><?php echo Html::encode($this->title)?></h1>

<div class="vendor-details-import">
    <!-- Display flash messages -->
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?php echo Yii::$app->session->getFlash('error')?>
        </div>
    <?php endif; ?>
<?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('success')?>
        </div>
    <?php endif; ?>

    <!-- Import form -->
    <?php $form = ActiveForm::begin([
            'action'  => ['process-import'],                   // Target action for file processing
            'options' => ['enctype' => 'multipart/form-data'], // Required for file uploads
    ]); ?>

    <?php echo Html::fileInput('file', null, ['class' => 'form-control'])?>
  <div class="mb-2">
   <?php echo Html::a(Yii::t('app', 'Download Example Format'), ['/admin/vendor-details/download-example'], [
    'class' => 'btn btn-sm btn-outline-info'
]) ?>

</div>


    <div class="form-group">
        <?php echo Html::submitButton('Import', ['class' => 'btn btn-success'])?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
