<?php

use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\Url;

$this->registerCss(<<<CSS
.beautiful-navbar {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-text {
    font-weight: 600;
    font-size: 1.25rem;
    color: #fff;
}

.navbar-nav .nav-link {
    color: #f0f0f0;
    transition: all 0.3s ease-in-out;
    font-weight: 500;
    border-radius: 6px;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    background: linear-gradient(135deg, #5a67d8, #6b46c1);
    color: #fff;
}
CSS);

NavBar::begin([
    'brandLabel' => Html::img('@web/images/logo.png', [
        'style' => 'height:40px; margin-right:10px; border-radius:8px;'
    ]) . '<span class="brand-text">' . Yii::$app->name . '</span>',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar navbar-expand-md navbar-dark beautiful-navbar shadow-lg',
    ],
]);

$menuItems = [];

if (!Yii::$app->user->isGuest && Yii::$app->user->identity) {
    if (Yii::$app->user->identity->isAdmin()) {
        $menuItems[] = [
            'label' => '<i class="fas fa-cogs mr-1"></i> Admin Panel',
            'url' => Url::to(['/admin']),
            'linkOptions' => ['class' => 'nav-link px-3']
        ];
    }
    if (Yii::$app->user->identity->isVendor()) {
        $menuItems[] = [
            'label' => '<i class="fas fa-store mr-1"></i> Vendor Panel',
            'url' => Url::toRoute(['/admin/vendor-details']),
            'linkOptions' => ['class' => 'nav-link px-3']
        ];
    }

    $menuItems[] = '<li class="nav-item">'
        . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
        . Html::submitButton(
            '<i class="fas fa-sign-out-alt mr-1"></i> Logout (' . Html::encode(Yii::$app->user->identity->email) . ')',
            ['class' => 'btn btn-gradient px-3 py-1']
        )
        . Html::endForm()
        . '</li>';
} else {
    $menuItems[] = [
        'label' => '<i class="fas fa-sign-in-alt mr-1"></i> Login',
        'url' => ['/site/login'],
        'linkOptions' => ['class' => 'nav-link px-3']
    ];
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto align-items-center'],
    'encodeLabels' => false,
    'items' => $menuItems,
]);

NavBar::end();
