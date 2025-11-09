<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "combo_packages".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $title
 * @property double $price
 * @property string $time
 * @property integer $is_home_visit
 * @property integer $is_walk_in
 * @property integer $service_for
 * @property string $description
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\ComboServices[] $comboServices
 */
class ComboPackages extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;
       public $services_ids = []; 


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'createUser',
            'updateUser',
            'vendorDetails',
            'comboServices'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
       public function rules()
    {
        return [
            [['vendor_details_id', 'title', 'price', 'time'], 'required'],
            [['vendor_details_id',  'service_for', 'status', 'create_user_id', 'update_user_id'], 'integer'],
            [['price'], 'number'],
           [['title'], 'match', 'pattern' => '/^[a-zA-Z ]+$/', 'message' => 'Title can contain only letters and spaces.'],
            [['description'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['time'], 'string', 'max' => 20],
            [['is_home_visit', 'is_walk_in'], 'string', 'max' => 1],
            [['services_ids'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'combo_packages';
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
            'vendor_details_id' => Yii::t('app', 'Vendor Details ID'),
            'title' => Yii::t('app', 'Title'),
            'price' => Yii::t('app', 'Price'),
            'time' => Yii::t('app', 'Time'),
            'is_home_visit' => Yii::t('app', 'Is Home Visit'),
            'is_walk_in' => Yii::t('app', 'Is Walk In'),
            'service_for' => Yii::t('app', 'Service For'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
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
  public function getComboServices()
{
    return $this->hasMany(ComboServices::class, ['combo_package_id' => 'id'])
                ->with('services'); 
}
public function getServices()
{
    // via the junction table
    return $this->hasMany(\app\modules\admin\models\Services::class, ['id' => 'services_id'])
                ->via('comboServices');
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
                'value' => date('Y-m-d H:i:s'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'create_user_id',
                'updatedByAttribute' => 'update_user_id',
            ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\ComboPackagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ComboPackagesQuery(get_called_class());
    }
public function asJson($user_id=''){
    $data = [] ; 
            $data['combo_package_id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['title'] =  $this->title;
        
                $data['price'] =  $this->price;

                $data['discount_price'] =  $this->discount_price;

        
                $data['time'] =  $this->time;
        
                $data['is_home_visit'] =  $this->is_home_visit;
        
                $data['is_walk_in'] =  $this->is_walk_in;
        
                $data['service_for'] =  $this->service_for;
        
                $data['description'] =  $this->description;

                if(!empty( $this->comboServices)){
                    foreach( $this->comboServices as $comboService){
                $data['comboServices'][] = $comboService->services->service_name??'';

                    }
                }else{
                    $data['comboServices']=[];
                }

                $combo_packages_cart = ComboPackagesCart::findOne(['user_id'=>$user_id,'combo_package_id'=>$this->id]);
                if(!empty($combo_packages_cart)){
                $data['combo_packages_cart_added'] = true;
                $data['combo_packages_cart_id'] = $combo_packages_cart->id;

                }else{
                $data['combo_packages_cart_added'] = false;
                $data['combo_packages_cart_id'] = '';

                }
                return $data;
}











public function asJsonVender(){
    $data = [] ; 
            $data['combo_package_id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['title'] =  $this->title;
        
                $data['price'] =  $this->price;

                $data['discount_price'] =  $this->discount_price??$this->price;

        
                $data['time'] =  $this->time;
        
                $data['is_home_visit'] =  $this->is_home_visit;
        
                $data['is_walk_in'] =  $this->is_walk_in;
        
                $data['service_for'] =  $this->service_for;
        
                $data['description'] =  $this->description;

            if(!empty( $this->comboServices)){
                    foreach( $this->comboServices as $comboService){
                $data['comboServices'][] = $comboService->services->asJsonForComboData()??'';

                    }
                }else{
                    $data['comboServices']=[];
                }

        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}

}


