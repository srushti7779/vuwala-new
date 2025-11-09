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
class FrontendAsset extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css',
		'themes/frontend/assets/css/style1.css',
		'themes/frontend/assets/css/responsive.css',
		'themes/frontend/assets/css/mega-menu.css',
		'themes/frontend/assets/css/slick.css',
		'themes/frontend/assets/css/slick-theme.css',
		//'themes/frontend/assets/css/myaccount.css',
		'https://fonts.googleapis.com/css?family=Manjari|Permanent+Marker&display=swap',
		'https://fonts.googleapis.com/css?family=Noto+Sans+JP|Poppins&display=swap'
	 ];
	public $js = [ 
		'themes/frontend/assets/js/jquery_min.js',
		'themes/frontend/assets/js/bootstrap_min.js',
		
	];
	public $jsOptions = [ 
			'position' => \yii\web\View::POS_HEAD 
	];
	public $depends = [ 
			//'yii\web\YiiAsset',
			//'yii\web\JqueryAsset',
	];
}
