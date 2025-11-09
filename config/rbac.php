<?php
use app\models\User;
use yii\web\ForbiddenHttpException;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

return [
    'as adminAccess' => [
        'class' => yii\filters\AccessControl::class,
        'rules' => [
            [
                'allow' => false,
                'controllers' => ['admin/*'],
                'matchCallback' => function ($rule, $action) {
                    $user = Yii::$app->user;
                    $role = $user->identity->user_role ?? null;

                    $isAllowed = User::isAdmin() || User::isVendor() || User::isSubAdmin() || User::isManager() || User::isAccountManager()|| User::isQa();

                    // ⛔ If not allowed
                    if (!$isAllowed) {
                        return true; // triggers denyCallback
                    }

                

                    return false; // do not deny
                },
					'denyCallback' => function ($rule, $action) {
    if (Yii::$app->controller->id === 'site' && $action->id === 'error') {
        // Prevent redirect loop if error action is forbidden
        return;
    }

    // Optional: flash message
    Yii::$app->session->setFlash('error', 'You are not allowed to access this page.');

    // Redirect to home or login instead of throwing exception
    return Yii::$app->response->redirect(['/site/index'])->send();
}


            ],
            [
                'allow' => true,
                'controllers' => ['*'],
            ],
        ],
    ],
];
