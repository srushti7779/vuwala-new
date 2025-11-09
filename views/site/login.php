<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    body {
        background: linear-gradient(to right,rgb(18, 25, 166),rgb(2, 7, 94));
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 40px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(10px);
        color: #fff;
    }
    .login-card h1 {
        text-align: center;
        margin-bottom: 30px;
        font-weight: bold;
    }
    .form-control {
        background-color: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
    }
    .form-control:focus {
        background-color: rgba(255,255,255,0.2);
        color: #fff;
    }
    .custom-checkbox label,
    .control-label {
        color: #fff;
        font-weight: 500;
    }
    .btn-primary {
        background-color: #6c63ff;
        border: none;
        width: 100%;
        padding: 10px;
        font-weight: bold;
    }
    .btn-primary:hover {
        background-color: #594fd4;
    }
    .login-links {
        text-align: center;
        margin-top: 20px;
        color: #fff;
    }
    .login-links a {
        color: #fff;
        text-decoration: underline;
    }
");

?>

<div class="login-container">
    <div class="login-card">
        <h1><?= Html::encode($this->title) ?></h1>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            // 'options' => ['autocomplete' => 'off'],
        ]); ?>

        <?= $form->field($model, 'username')->textInput([
            'autofocus' => true,
            // 'autocomplete' => 'off',
        ]) ?>

        <?= $form->field($model, 'password')->passwordInput([
            // 'autocomplete' => 'new-password',
        ]) ?>

        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"custom-checkbox\">{input} {label}</div>\n{error}"
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <!-- <div class="login-links">
            <p>Don't have an account? <?= Html::a('Register', ['auth/register']) ?></p>
            <p>Forgot your password? <?= Html::a('Reset it', ['auth/password-request']) ?></p>
        </div> -->
    </div>
</div>
