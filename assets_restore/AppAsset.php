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
class AppAsset extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [ 
			'themes/css/angular-material.min.css' 
	];
	public $js = [ 
			'themes/js/jquery.confirm.min.js',
			'themes/js/angular.min.js',
			'themes/js/angular-animate.min.js',
			'themes/js/angular-aria.min.js',
			'themes/js/angular-messages.min.js',
			'themes/js/angular-material.min.js' 
	];
	public $jsOptions = [ 
			'position' => \yii\web\View::POS_HEAD 
	];
	public $depends = [ 
			'yii\web\YiiAsset',
			'yii\web\JqueryAsset',
			'yii\bootstrap\BootstrapAsset',
			'yii\bootstrap\BootstrapPluginAsset' 
	];
}
