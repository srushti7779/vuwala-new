<?php

namespace app\modules\comingsoon\controllers;

use yii\web\Controller;

/**
 * Default controller for the `commingsoon` module
 */
class DefaultController extends Controller {
	/**
	 * Renders the index view for the module
	 *
	 * @return string
	 */
	public function actionIndex() {
		if (COMMING_SOON) {
			return $this->render ( 'index' );
		}
		return $this->redirect ( [ 
				'/' 
		] );
	}
}
