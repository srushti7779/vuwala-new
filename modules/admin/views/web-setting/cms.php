<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\widgets\FileInput;
// use yii\widgets\ActiveForm;
use app\models\Setting;
use app\modules\admin\models\WebSetting;

// var_dump($paypal_setting);exit;
/* @var $this yii\web\View */
/* @var $model app\models\Setting */

$this->title = Yii::t('app', 'CMS');
$this->params['header'] = Yii::t('app', 'Settings');
$this->params['description'] = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Settings'), 'url' => ['cms']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.file-loading:before {
display:none}
</style>
<div class="setting-create">
<div class="card">
<div class="card-body"> 
<div class="row">
  <div class="col-md-12">
    <div class="nav-tabs-custom">
    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#web_setting" role="tab" aria-controls="nav-home" aria-selected="true">Website Setting</a>
  
	<a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#amount_settings" role="tab" aria-controls="nav-contact" aria-selected="false">Amount Setting</a>
	<!-- <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#social_login" role="tab" aria-controls="nav-contact" aria-selected="false">Social Login Setting</a> -->
	<a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#url_setting" role="tab" aria-controls="nav-contact" aria-selected="false">Url Setting</a>
    <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#firebase_setting" role="tab" aria-controls="nav-contact" aria-selected="false">Firebase Setting</a>
    <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#notification_setting" role="tab" aria-controls="nav-contact" aria-selected="false">Notification Setting</a>
  
						
	</div>
      <!--<ul class="nav nav-tabs">
        <li class="active"><a href="#web_setting" data-toggle="tab" aria-expanded="false">Website Setting</a></li>
        <li class=""><a href="#razor_pay" data-toggle="tab" aria-expanded="false">RazorPay</a></li>
        <li class=""><a href="#notification" data-toggle="tab" aria-expanded="false">Notifications</a></li>
        <li class=""><a href="#amount_settings" data-toggle="tab" aria-expanded="true">Amount Setting</a></li>
       
      </ul>-->
      <div class="tab-content">
     
        
        <div class="tab-pane active" id="web_setting">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <?php foreach ($web_settings as $setting){?>
                <div class="col-md-6">
                    <?= $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false) ?>
                    <label><?= $setting->name ?></label>
                    <?= $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false) ?>
                </div>
            <?php }?>
           
                <div class="col-md-6">
                <div class="file-loading">
                <label><?php //$webimage_setting->name ?></label>
                    <?php /*echo $form->field($webimage_setting, 'value')->widget(FileInput::classname(),[
                            'name' => 'attachment_53',
                            
                            'pluginOptions' => [
                                'showCaption' => false,
                                'showRemove' => false,
                                'showUpload' => false,
                                
                                'browseClass' => 'btn btn-primary btn-block',
                                'browseIcon' => '<i class="fa fa-camera"></i> ',
                                'browseLabel' =>  'Select Photo'
                            ],
                            'options' => ['accept' => 'image/*']
                        ])->label(false);*/ ?>
                </div>
                </div>
            
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="tab-pane" id="notification_setting">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <?php foreach ($notification as $setting){?>
                <div class="col-md-6">
                    <?= $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false) ?>
                    <label><?= $setting->name ?></label>
                    <?= $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false) ?>
                </div>
            <?php }?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>
       
        <div class="tab-pane" id="url_setting">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">
                <?php foreach ($url_settings as $setting){?>
                <div class="col-md-6">
                    <?= $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false) ?>
                    <label><?= $setting->name ?></label>
                    <?= $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false) ?>
                </div>
            <?php }?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>
      
       
        <div class="tab-pane" id="amount_settings">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row">


            <?php foreach ($amount_settings as $setting){?>
                    <?php if($setting->setting_key=='commission_type'){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'COMMISSION_FIXED', '2' => 'COMMISSION_PERCENT'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }
                    else if($setting->setting_key=='cashback_type'){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'COMMISSION_FIXED', '2' => 'COMMISSION_PERCENT'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }

                    else if($setting->setting_key=='enable_signup_bonus'){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'Yes', '0' => 'No'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }
                    else if($setting->setting_key=='enable_social_media'){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'Enable', '0' => 'Disable'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }
                    
                    
                    else{
                        echo '<div class="col-md-6">';
                        echo '<div id="'.$setting->setting_key.'">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false);
                        echo '<label>'.$setting->name.'</label>';
                        echo $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div></div>';

                    }
                     ?>
            <?php }?>
                <?php /*foreach ($amount_settings as $setting){?>
                <div class="col-md-6">
                    <?= $form->field($setting, 'id')->hiddenInput(['maxlength' => true])->label(false) ?>
                    <label><?= $setting->name ?></label>
                    <?= $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->id,'data-id'=>$setting->id])->label(false) ?>
                </div>
            <?php }*/ ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>
        
       
       

       
<!-- social login -->
        <div class="tab-pane" id="firebase_setting">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?> 
            <div class="row">
                <?php foreach ($firebase_setting as $setting){?>
                    <?php if($setting->setting_key=='enable_fb_login' || $setting->setting_key=='enable_google_login'){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'Yes', '0' => 'No'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }else{
                        echo '<div class="col-md-6">';
                        echo '<div id="'.$setting->setting_key.'">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false);
                        echo '<label>'.$setting->name.'</label>';
                        echo $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div></div>';

                    }
                     ?>
            <?php }?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>
        
        <div class="tab-pane" id="secret_id">
        <div class="cms-form">
            <?php $form = ActiveForm::begin(); ?> 
            <div class="row">
                <?php foreach ($secret_id as $setting){?>
                    <?php if($setting->setting_key=='enable_otp_verification' || $setting->setting_key=='enable_google_login' || $setting->setting_key=='enable_time_slot' ){
                        echo '<div class=" col-md-6">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => false])->label(false);
                        echo "<label>".$setting->name."</label>";
                        echo  $form->field($setting, 'value')->dropDownList(['1' => 'Yes', '0' => 'No'], ['prompt'=>'Select Option','class'=>'get_selected_value form-control','id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div>';
                    }else{
                        echo '<div class="col-md-6">';
                        echo '<div id="'.$setting->setting_key.'">';
                        echo $form->field($setting, 'setting_id')->hiddenInput(['maxlength' => true])->label(false);
                        echo '<label>'.$setting->name.'</label>';
                        echo $form->field($setting, 'value')->textInput(['id'=>'cms_'.$setting->setting_id,'data-id'=>$setting->setting_id])->label(false);
                        echo '</div></div>';

                    }
                     ?>
            <?php }?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>

       

      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
$(document).on('change','input[id^=cms_]',function(){
var id=$(this).attr('data-id');
var value=$(this).val();
$.ajax({
	  type: "GET",
	  url: "<?= Url::toRoute(['web-setting/save-cms'])?>",
	  data: {id:id,value:value},
	  cache: false,
	  success: function(data){
        swal("Good job!", "Settings Saved!", "success");
	  }
	});
});
</script>
 <script>
 $(document).on("change", 'select[id^=cms_]', function(){ 
var id=$(this).attr('data-id');
var value=$(this).val();
$.ajax({
	  type: "GET",
	  url: "<?= Url::toRoute(['web-setting/save-cms'])?>",
	  data: {id:id,value:value},
	  cache: false,
	  success: function(data){
        swal("Good job!", "Settings Saved!", "success");
	  }
	});
});
 </script>











