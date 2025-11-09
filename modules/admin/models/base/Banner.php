<?php


namespace app\modules\admin\models\base;

use app\modules\admin\models\BannerRecharges;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "banner".
 *
 * @property integer $id
 * @property integer $main_category_id
 * @property integer $vendor_details_id
 * @property string $title
 * @property string $image
 * @property string $description
 * @property integer $position
 * @property integer $type_id
 * @property integer $sort_order
 * @property string $start_date
 * @property string $end_date
 * @property integer $views_count
 * @property integer $is_top_banner
 * @property integer $is_pop_up_banner
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $is_featured
 *
 * @property \app\modules\admin\models\MainCategory $mainCategory
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\BannerTimings[] $bannerTimings
 * @property \app\modules\admin\models\BannerTimings[] $bannerChargeLogs
 * @property \app\modules\admin\models\BannerTimings[] $bannerRecharges
 */
class Banner extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'mainCategory',
            'vendorDetails',
            'bannerLogs',
            'bannerTimings',
            'bannerChargeLogs'
        ];
    }

const STATUS_ACTIVE = 1;

const STATUS_INACTIVE = 2;

const STATUS_DELETE = 3;

const STATUS_PENDING = 4;

const STATUS_PAUSED = 5;

const BANNER_TYPE_PROMOTIONAL  = 1;
const BANNER_TYPE_SERVICE = 2;
const BANNER_TYPE_PRODUCT = 3;
const BANNER_TYPE_ANNOUNCEMENT = 4;


const IS_FEATURED = 1;
const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
public function rules()
{
    return [
        // Required fields with custom messages
        [['title'], 'required', 'message' => 'Title is required.'],
        [['image'], 'required', 'message' => 'Image is required.'],
        [['main_category_id'], 'required', 'message' => 'Main Category is required.'],
        [['vendor_details_id'], 'required', 'message' => 'Vendor Details is required.'],
        [['position'], 'required', 'message' => 'Position is required.'],
        [['type_id'], 'required', 'message' => 'Type is required.'],
        [['status'], 'required', 'message' => 'Status is required.'],

        // Type and other validations
        [['main_category_id', 'vendor_details_id', 'position', 'type_id', 'sort_order', 'views_count', 'status', 'create_user_id', 'update_user_id', 'is_featured'], 'integer'],
        [['description'], 'string'],
        [['start_date', 'end_date', 'created_on', 'updated_on'], 'safe'],
        [['title', 'image'], 'string', 'max' => 255],
        [['is_top_banner', 'is_pop_up_banner'], 'string', 'max' => 1],
    ];
}


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_DELETE => 'Deleted',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-warning">In Active</span>';
        }elseif ($this->status == self::STATUS_DELETE) {
            return '<span class="badge badge-danger">Deleted</span>';
        }

    }

    public function getFeatureOptions()
    {
        return [

            self::IS_FEATURED => 'Is Featured',
            self::IS_NOT_FEATURED => 'Not Featured',
           
        ];
    }

    public function getFeatureOptionsBadges()
    {
        if ($this->is_featured == self::IS_FEATURED) {
            return '<span class="badge badge-success">Featured</span>';
        } elseif ($this->is_featured == self::IS_NOT_FEATURED) {
            return '<span class="badge badge-danger">Not Featured</span>';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'main_category_id' => Yii::t('app', 'Main Category ID'),
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'title' => Yii::t('app', 'Title'),
            'image' => Yii::t('app', 'Image'),
            'description' => Yii::t('app', 'Description'),
            'position' => Yii::t('app', 'Position'),
            'type_id' => Yii::t('app', 'Type ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
            'views_count' => Yii::t('app', 'Views Count'),
            'is_top_banner' => Yii::t('app', 'Is Top Banner'),
            'is_pop_up_banner' => Yii::t('app', 'Is Pop Up Banner'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
            'is_featured' => Yii::t('app', 'Is Featured'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainCategory()
    {
        return $this->hasOne(\app\modules\admin\models\MainCategory::className(), ['id' => 'main_category_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannerTimings()
    {
        return $this->hasMany(\app\modules\admin\models\BannerTimings::className(), ['banner_id' => 'id']);
    }


       public function getBannerLogs()
    {
        return $this->hasMany(\app\modules\admin\models\BannerLogs::className(), ['banner_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_on',
                'updatedAtAttribute' => 'updated_on',
                'value' => new \yii\db\Expression('NOW()'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }


        /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannerChargeLogs()
    {
        return $this->hasMany(\app\modules\admin\models\BannerChargeLogs::className(), ['banner_id' => 'id']);
    }
      public function getBannerRecharges()
    {
        return $this->hasMany(\app\modules\admin\models\BannerRecharges::className(), ['banner_id' => 'id']);
    }







    /**
     * @inheritdoc
     * @return \app\modules\admin\models\BannerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BannerQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['main_category_id'] =  $this->main_category_id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['title'] =  $this->title;
        
                $data['image'] =  $this->image;
        
                $data['description'] =  $this->description;
        
                $data['position'] =  $this->position;
        
                $data['type_id'] =  $this->type_id;
        
                $data['sort_order'] =  $this->sort_order;
        
                $data['start_date'] =  $this->start_date;
        
                $data['end_date'] =  $this->end_date;

                $banner_charge_logs_views = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'view'])->count();

                $banner_charge_logs_click = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'click'])->count();


                $data['views_count'] =  $banner_charge_logs_views;

                $data['click_count'] =  $banner_charge_logs_click;

                $data['is_top_banner'] =  $this->is_top_banner;
        
                $data['is_pop_up_banner'] =  $this->is_pop_up_banner;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
                $data['is_featured'] =  $this->is_featured;
        
            return $data;
}



public function getCTR()
{
    $views = BannerChargeLogs::find()
        ->where(['banner_id' => $this->id])
        ->andWhere(['action' => 'view'])
        ->count();

    $clicks = BannerChargeLogs::find()
        ->where(['banner_id' => $this->id])
        ->andWhere(['action' => 'click'])
        ->count();

    if ($views == 0) {
        return '0';
    }

    $ctr = ($clicks / $views) * 100;
    return round($ctr, 2) . '%';
}



public function asJsonVendor(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['main_category_id'] =  $this->main_category_id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['title'] =  $this->title;
        
                $data['image'] =  $this->image;
        
                $data['description'] =  $this->description;
        
                $data['position'] =  $this->position;
        
                $data['type_id'] =  $this->type_id;
        
                $data['sort_order'] =  $this->sort_order;
        
                $data['start_date'] =  $this->start_date;
        
                $data['end_date'] =  $this->end_date;
        
                $banner_charge_logs_views = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'view'])->count();

                $banner_charge_logs_click = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'click'])->count();


                $data['views_count'] =  $banner_charge_logs_views;

                $data['click_count'] =  $banner_charge_logs_click;

                $data['likes_count'] =  $this->views_count??0;
                
                $data['is_top_banner'] =  $this->is_top_banner;
        
                $data['is_pop_up_banner'] =  $this->is_pop_up_banner;

                $amount_paid = BannerRecharges::find()->where(['banner_id' => $this->id])->sum('amount');
                $data['amount_paid'] =  $amount_paid??0;
                $banner_last_recharge = BannerRecharges::find()->where(['banner_id' => $this->id])->orderBy(['id' => SORT_DESC])->one();
                $data['banner_last_recharge'] = $banner_last_recharge ? $banner_last_recharge->created_on : null;
                    if(!empty($this->bannerTimings)){
                    $data['banner_timings'] = array_map(function($timing) {
                        return $timing->asJsonOnlyTimings();
                    }, $this->bannerTimings);
                } else {
                    $data['banner_timings'] = [];   
                }
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
                $data['is_featured'] =  $this->is_featured;
        
            return $data;
}



public function asJsonVendorView($post=''){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['main_category_id'] =  $this->main_category_id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['title'] =  $this->title;
        
                $data['image'] =  $this->image;
        
                $data['description'] =  $this->description;
        
                $data['position'] =  $this->position;
        
                $data['type_id'] =  $this->type_id;
        
                $data['sort_order'] =  $this->sort_order;
        
                $data['start_date'] =  $this->start_date;
        
                $data['end_date'] =  $this->end_date;

                $data['ctr'] = $this->getCTR();
        
                $banner_charge_logs_views = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'view'])->count();

                $banner_charge_logs_click = BannerChargeLogs::find()->where(['banner_id'=>$this->id])->andWhere(['action'=>'click'])->count();


                $data['views_count'] =  $banner_charge_logs_views;

                $data['click_count'] =  $banner_charge_logs_click;
                
                $amount_paid = BannerRecharges::find()->where(['banner_id' => $this->id])->sum('amount');
                $data['amount_paid'] =  $amount_paid??0;
                $banner_last_recharge = BannerRecharges::find()->where(['banner_id' => $this->id])->orderBy(['id' => SORT_DESC])->one();
                $data['banner_last_recharge'] = $banner_last_recharge ? $banner_last_recharge->created_on : null;
                $banner_available_amount = BannerRecharges::find()->where(['banner_id' => $this->id])->sum('amount');




                if(!empty($this->bannerTimings)){
                    $data['banner_timings'] = array_map(function($timing) use ($post) {
                        return $timing->asJson($post);
                    }, $this->bannerTimings);
                } else {
                    $data['banner_timings'] = [];   
                }


                

                $data['is_top_banner'] =  $this->is_top_banner;
        
                $data['is_pop_up_banner'] =  $this->is_pop_up_banner;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
                $data['is_featured'] =  $this->is_featured;
        
            return $data;
}


}


