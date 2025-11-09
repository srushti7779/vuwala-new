<?php

use yii\helpers\Html;
use app\widgets\FlashAlert;
use lo\modules\noty\Wrapper;
use app\modules\admin\assets\AssetBundle;
use kingston\mdbootstrap\MDBootstrapAsset;
use kingston\mdbootstrap\MDBootstrapPluginAsset;
use app\modules\admin\models\WebSetting;
use yii\helpers\Url;


AssetBundle::register($this);
MDBootstrapAsset::register($this);
MDBootstrapPluginAsset::register($this);

$adminlteAssets = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
$setting = new WebSetting();

$icon = $setting->getSettingBykey('website_favicon');

$this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<link rel="icon" href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?>/uploads/<?php echo $icon;?>" type="image/x-icon">
	<link rel="stylesheet" href="<?= Url::base() ?>/web/css/lightBox/ekko-lightbox/ekko-lightbox.css">

	<?php $this->head() ?>
</head>
<style>
.btn-default.dropdown-toggle {
    background-color: red !important;
}
.content-wrapper {
	background: #ECEFF4; /* Lighter background for better contrast */
}

.card {
	background-color: #ffffff !important;
	box-shadow: 0 1px 6px 1px rgba(69, 65, 78, 0.1);
}

a {
	color: #bf616a; /* Accent color for links */
}

.nav-pills .nav-link:not(.active):hover {
	color: #f5e9dc;
}

.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
	color: black;
	background-color: #bd995e !important;
}

.bg-light, .bg-light a {
	color: #f8f9fa !important;
}

.bg-primary {
    background-color: #bd995e !important;
}
.btn-info {
    color: #fff;
    background-color: #dca81d !important;
}

.btn-success {
    color: #fff;
    background-color: #a24b08 !important;
}
.btn-primary {
    color: #fff;
    background-color: #af9b0bbf !important;
}

.btn-outline-secondary {
    color: #610909 !important;
    background-color: transparent !important;
    border: 2px solid #af9b0bbf !important;
}
.border-primary {
    border-color: #af9b0bbf !important;
}


.btn-default { 
    color: #fff;
    background-color: #7e5a09 !important;
}

</style>
<body class="hold-transition sidebar-mini layout-fixed">
<?php $this->beginBody() ?>

<div class="wrapper">
	<?= $this->render('../partials/header', ['adminlteAssets' => $adminlteAssets]); ?>
	<?= $this->render('../partials/nav', ['adminlteAssets' => $adminlteAssets]); ?>

	<div class="content-wrapper">
		<?= $this->render('../partials/content-header'); ?>

		<section class="content">
			<div class="container-fluid" id='flash'>
				<?= FlashAlert::widget() ?>
				<?= $content ?>
				<br>
			</div>
		</section>
	</div>

	<script>
		$('button[type="reset"]').click(function() {
			window.location.href = window.location.href.split('?')[0];
		});
	</script>
	<?= $this->render('../partials/footer'); ?>
</div>

<?php $this->endBody() ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= Url::base() ?>/web/css/lightBox/ekko-lightbox/ekko-lightbox.min.js"></script>
</body>
</html>
<?php $this->endPage() ?>
