
<?php 
use yii\helpers\Url;
?> 
<div>
    <p>Please use this link to reset your password :<a href="<?= Url::to(["/user/reset",'access_token'=> 
    $user->access_token], TRUE); ?>">Click here to reset your password</a></p>
</div>