<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FrontEndAssets extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		'themes/assets/vendor/bootstrap/css/bootstrap.min.css',
		'themes/assets/vendor/icofont/icofont.min.css',
		'themes/assets/vendor/boxicons/css/boxicons.min.css',
		'themes/assets/vendor/owl.carousel/assets/owl.carousel.min.css',
		'themes/assets/vendor/venobox/venobox.css',
		'themes/assets/vendor/aos/aos.css',
		'themes/assets/css/style.css',
		'https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i'

	 ];
	public $js = [ 
		'themes/assets/vendor/jquery/jquery.min.js',
		'themes/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
		'themes/assets/vendor/jquery.easing/jquery.easing.min.js',
		'themes/assets/vendor/php-email-form/validate.js',
		'themes/assets/vendor/owl.carousel/owl.carousel.min.js',
		'theme/assets/vendor/venobox/venobox.min.js',
		'themes/assets/vendor/aos/aos.js',
		//'https://maps.googleapis.com/maps/api/js?key=AIzaSyDnd9JwZvXty-1gHZihMoFhJtCXmHfeRQg',
		'themes/assets/js/main.js',
	];
	public $jsOptions = [ 
			//'position' => \yii\web\View::POS_HEAD 
			'position' => \yii\web\View::POS_END
	];
	public $depends = [ 
			'yii\web\YiiAsset',
			'yii\web\JqueryAsset',
	];
}
