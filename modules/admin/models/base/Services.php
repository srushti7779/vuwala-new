<?php


namespace app\modules\admin\models\base;

use app\modules\admin\models\Service;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "services".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property integer $sub_category_id
 * @property string $service_name
 * @property string $slug
 * @property string $image
 * @property string $description
 * @property string $small_description
 * @property double $original_price
 * @property double $standard_price
 * @property double $discount_price
 * @property integer $max_per_day_services
 * @property double $price
 * @property integer $duration
 * @property integer $home_visit
 * @property integer $walk_in
 * @property integer $type
 * @property integer $benefits
 * @property integer $precautions_recommendation
 * @property integer $why_choose_service
 * @property integer $why_choose_category
 * @property integer $additional_notes
 * @property integer $techinique_points
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\CartItems[] $cartItems
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\SubCategory $subCategory
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 */
class Services extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'cartItems',
            'createUser',
            'updateUser',
            'subCategory',
            'vendorDetails',
            'productServices'
        ];
    }
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETE = 3;
    const STATUS_ADMIN_WAITING_FOR_APPROVAL = 4;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const TYPE_WALK_IN = 1;

    const TYPE_HOME_VISIT = 2;


    const SERVICE_FOR_MALE = 1;

    const SERVICE_FOR_FEMALE = 2;

    const SERVICE_FOR_UNISEX = 3;


    public function getServiceTypeOptions()
    {
        return [
            self::SERVICE_FOR_MALE => 'Male',
            self::SERVICE_FOR_FEMALE => 'Female',
            self::SERVICE_FOR_UNISEX => 'Unisex',
        ];
    }

    
    public function getTypeOptionsBadges()
    {
        if ($this->type == self::TYPE_WALK_IN) {
            return '<span class="badge badge-success">Walk In</span>';
        } elseif ($this->type == self::TYPE_HOME_VISIT) {
            return '<span class="badge badge-danger">Home Visit</span>';
        } 
    }

    /**
     * Get badges for service types based on the current service type.
     *
     * @return string
     */
    public function getServiceTypeBadges()
    {
        switch ($this->service_type) {
            case self::SERVICE_FOR_MALE:
                return '<span class="badge badge-primary">Male</span>';
            case self::SERVICE_FOR_FEMALE:
                return '<span class="badge badge-pink">Female</span>';
            case self::SERVICE_FOR_UNISEX:
                return '<span class="badge badge-info">Unisex</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }


    public function rules()
    {
        return [
            [['vendor_details_id', 'sub_category_id', 'service_name', 'image', 'original_price', 'standard_price', 'discount_price', 'duration', 'service_for'], 'required'],
            [['vendor_details_id', 'sub_category_id', 'max_per_day_services', 'duration', 'type', 'status', 'create_user_id', 'update_user_id', 'service_for'], 'integer'],
            [['description','benefits','precautions_recommendation','why_choose_service','why_choose_category','additional_notes','techinique_points','slug'], 'string'],
            [['original_price', 'standard_price', 'discount_price', 'price'], 'number'],
            [['created_on', 'updated_on','is_parent_service'], 'safe'],
            [['service_name'], 'string', 'max' => 255],
            [['slug', 'image', 'small_description'], 'string', 'max' => 512],
            [['home_visit', 'walk_in'], 'string', 'max' => 1],
            [['home_visit', 'walk_in'], 'validateHomeVisitOrWalkIn'], 
            ['image', 'required', 'on' => 'create']

        ];
    }


    public function validateHomeVisitOrWalkIn($attribute, $params, $validator)
    {
        if (empty($this->home_visit) && empty($this->walk_in)) {
            $this->addError($attribute, Yii::t('app', 'Either Home Visit or Walk-In must be selected.'));
        }
    }



        public function getProductServices()
    {
        return $this->hasMany(\app\modules\admin\models\ProductServices::className(), ['service_id' => 'id']);
    }





    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'services';
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
            return '<span class="badge badge-default">In Active</span>';
        } elseif ($this->status == self::STATUS_DELETE) {
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
            'sub_category_id' => Yii::t('app', 'Sub Category ID'),
            'service_name' => Yii::t('app', 'Service Name'),
            'slug' => Yii::t('app', 'Slug'),
            'image' => Yii::t('app', 'Image'),
            'description' => Yii::t('app', 'Description'),
            'small_description' => Yii::t('app', 'Small Description'),
            'original_price' => Yii::t('app', 'Original Price'),
            'standard_price' => Yii::t('app', 'Standard Price'),
            'discount_price' => Yii::t('app', 'Discount Price'),
            'max_per_day_services' => Yii::t('app', 'Max Per Day Services'),
            'price' => Yii::t('app', 'Price'),
            'duration' => Yii::t('app', 'Duration'),
            'home_visit' => Yii::t('app', 'Home Visit'),
            'walk_in' => Yii::t('app', 'Walk In'),
            'type' => Yii::t('app', 'Type'),
            'benefits' => Yii::t('app', 'Benefits'),
            'why_choose_service' => Yii::t('app', 'Why Choose Service'),
            'why_choose_category' => Yii::t('app', 'Why Choose Category'),
            'precautions_recommendation' => Yii::t('app', 'Precautions And Recommendations'),
            'additional_notes' => Yii::t('app', 'Additional Notes'),
            'techinique_points' => Yii::t('app', 'Technical Points'),
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
    public function getCartItems()
    {
        return $this->hasMany(\app\modules\admin\models\CartItems::className(), ['service_item_id' => 'id']);
    }
public function getServiceHasCoupons()
{
    return $this->hasMany(ServiceHasCoupons::class, ['service_id' => 'id']);
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
    public function getSubCategory()
    {
        return $this->hasOne(\app\modules\admin\models\SubCategory::className(), ['id' => 'sub_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
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
     * @return \app\modules\admin\models\ServicesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ServicesQuery(get_called_class());
    }
    public function asJson($user_id = '')
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;

        $data['service_name'] =  $this->service_name;
        $data['store_service_type_id'] =  $this->store_service_type_id;


        $data['image'] =  $this->image;
        $data['multi_selection'] =  $this->multi_selection??false;


        $data['description'] =  preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->description))));

        $data['small_description'] =  $this->small_description;

        $data['original_price'] =  $this->original_price;


        $data['discount_price'] =  $this->discount_price;

        $data['max_per_day_services'] =  $this->max_per_day_services;
        $data['is_sessions_required'] = $this->is_sessions_required;

        $data['duration'] =  $this->duration . ' minutes';
        $data['duration_int'] =  $this->duration;
        $data['price'] =  $this->price;
        $data['from_price'] =  $this->from_price;
        $data['to_price'] =  $this->to_price;
        $data['is_price_range'] =  $this->is_price_range;

        $data['service_for'] =  $this->service_for; 
        $data['type'] =  $this->type;
 
        $data['home_visit'] =  $this->home_visit;

        $data['walk_in'] =  $this->walk_in;

        $data['is_product_required'] =  $this->is_product_required;

        if(!empty($this->parent_id)){
            $parent_service = Services::findOne($this->parent_id);
            $data['parent_service_name'] = $parent_service->service_name ?? '';

        }else{
               $data['parent_service_name'] = '';
        }

        $data['benefits'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->benefits))));
        $data['precautions_recommendation'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->precautions_recommendation))));
        $data['why_choose_service'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->why_choose_service))));
        $data['why_choose_category'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->why_choose_category))));
        $data['additional_notes'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->additional_notes))));
        $data['techinique_points'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->techinique_points))));
        $services = Services::find()
        ->where([
            'parent_id' => $this->id,
            'status' => Services::STATUS_ACTIVE,
        ])
        ->andWhere([
            'or',
            ['is_parent_service' => null],
            ['is_parent_service' => '']
        ])
        ->all();
        if(!empty($services)){
        $data['is_parent_service'] =  $this->is_parent_service;
        }else{
            $data['is_parent_service'] =  null;
        }
        if($data['is_parent_service']==1){
        if(!empty($services)){
                foreach($services as $servicesChild){
                    $data['child_services'][] = $servicesChild->asJsonChild($user_id);
                }
            }else{
                $data['child_services'] =null;
            }

        }


        $data['status'] =  $this->status;

        // check whether user added or not
        // Initialize defaults
        $data['is_added'] = false;
        $data['itemQty'] = 0;
        $data['cart_item_id'] = '';

// Check whether user is logged in
if (!empty($user_id)) {

    // Check if the main service is in the cart
    $cartItems = CartItems::find()
        ->where(['service_item_id' => $this->id])
        ->andWhere(['user_id' => $user_id])
        ->andWhere(['is_package_service'=>0])
        ->one();

    if (!empty($cartItems)) {
        $data['is_added'] = true;
        $data['itemQty'] = $cartItems->quantity;
        $data['cart_item_id'] = $cartItems->id;
    } else {
        // If not, check for child services in cart
        $services_id = Services::find()
            ->select(['id'])
            ->where(['parent_id' => $this->id])
            ->andWhere(['status' => Services::STATUS_ACTIVE]) // fixed typo
            ->column();

        if (!empty($services_id)) {
            $childCartItem = CartItems::find()
                ->where(['service_item_id' => $services_id])
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['is_package_service'=>0])
                ->one();

            if (!empty($childCartItem)) {
                $data['is_added'] = true;
                $data['itemQty'] = $childCartItem->quantity;
                $data['cart_item_id'] = $childCartItem->id;
            }
        }
    }
}

   
    return $data;
    }
















    public function asJsonChild($user_id = '')
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;

        $data['service_name'] =  $this->service_name;

        $data['image'] =  $this->image;

        $data['description'] =  preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->description))));

        $data['small_description'] =  $this->small_description;

        $data['original_price'] =  $this->original_price;
        $data['is_sessions_required'] = $this->is_sessions_required;


        $data['discount_price'] =  $this->discount_price;

        $data['max_per_day_services'] =  $this->max_per_day_services;
        $data['is_product_required'] =  $this->is_product_required;

        $data['duration'] =  $this->duration . ' minutes';
        $data['duration_int'] =  $this->duration;
        $data['price'] =  $this->price;
        $data['service_for'] =  $this->service_for; 
        $data['type'] =  $this->type;
 
        $data['home_visit'] =  $this->home_visit;

        $data['walk_in'] =  $this->walk_in;

        $data['benefits'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->benefits))));
        $data['precautions_recommendation'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->precautions_recommendation))));
        $data['why_choose_service'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->why_choose_service))));
        $data['why_choose_category'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->why_choose_category))));
        $data['additional_notes'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->additional_notes))));
        $data['techinique_points'] = preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->techinique_points))));
 


        $data['status'] =  $this->status;

        // check whether user added or not
        if (!empty($user_id)) {
            $cartItems = CartItems::find()->where(['service_item_id' => $this->id])
                ->andWhere(['user_id' => $user_id])
                ->andWhere(['is_package_service'=>0])
                ->one();

            if (!empty($cartItems)) {
                $data['is_added'] = true;
                $data['itemQty'] = $cartItems['quantity'];
                $data['cart_item_id'] = $cartItems->id;
            } else {
                $data['is_added'] = false;
                $data['itemQty'] = 0;
                $data['cart_item_id'] = '';
            }
        } else {
            $data['is_added'] = false;
            $data['itemQty'] = 0;
            $data['cart_item_id'] = '';

        }
        return $data;
    }



 public function asJsonChildForCouponList()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;

        $data['service_name'] =  $this->service_name;

        $data['image'] =  $this->image;

        $data['description'] =  preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->description))));

        $data['small_description'] =  $this->small_description;

        $data['original_price'] =  $this->original_price;


        $data['discount_price'] =  $this->discount_price;

        $data['max_per_day_services'] =  $this->max_per_day_services;
        $data['is_product_required'] =  $this->is_product_required;

        $data['duration'] =  $this->duration . ' minutes';
        $data['duration_int'] =  $this->duration;
        $data['price'] =  $this->price;
        $data['service_for'] =  $this->service_for; 
        $data['type'] =  $this->type;
 
        $data['home_visit'] =  $this->home_visit;

        $data['walk_in'] =  $this->walk_in;

  

        $data['status'] =  $this->status;

 
        return $data;
    }







    public function asJsonByOrder()
    {
        $data = [];
        $data['service_id'] =  $this->id;
        $data['service_name'] =  $this->service_name;
        $data['vendor_details_id'] =  $this->vendor_details_id;
        $data['sub_category_id'] =  $this->sub_category_id;
        $data['is_product_required'] =  $this->is_product_required;
        $data['is_sessions_required'] = $this->is_sessions_required;

        return $data;
    }









        public function asJsonForCombo($user_id = '')
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;

        $data['service_name'] =  $this->service_name;

        $data['image'] =  $this->image;

        $data['description'] =  preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->description))));

        $data['small_description'] =  $this->small_description;

        $data['original_price'] =  $this->original_price;


        $data['discount_price'] =  $this->discount_price;
        $data['is_product_required'] =  $this->is_product_required;

        $data['max_per_day_services'] =  $this->max_per_day_services;

        $data['duration'] =  $this->duration . ' minutes';
        $data['duration_int'] =  $this->duration;
        $data['price'] =  $this->price;
        $data['service_for'] =  $this->service_for; 
        $data['type'] =  $this->type;
 
        $data['home_visit'] =  $this->home_visit;

        $data['walk_in'] =  $this->walk_in;



        $data['status'] =  $this->status;


             $services = Services::find()
        ->where([
            'parent_id' => $this->id,
            'status' => Services::STATUS_ACTIVE,
        ])
        ->andWhere([
            'or',
            ['is_parent_service' => null],
            ['is_parent_service' => '']
        ])
        ->all();
        if(!empty($services)){
        $data['is_parent_service'] =  $this->is_parent_service;
        }else{
            $data['is_parent_service'] =  null;
        }
        if($data['is_parent_service']==1){
        if(!empty($services)){
                foreach($services as $servicesChild){
                    $data['child_services'][] = $servicesChild->asJsonChild($user_id);
                }
            }else{
                $data['child_services'] =null;
            }

        }


   
        return $data;
    }


















public function asJsonForComboData()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;
        $data['is_product_required'] =  $this->is_product_required;


        $data['service_name'] =  $this->service_name;
        return $data;
    }


    public function asJsonForProductAndServiceUom()
    {
        $data = [];
        $data['service_id'] =  $this->id;
        $data['service_name'] =  $this->service_name;
        $data['is_product_required'] =  $this->is_product_required;


        if(!empty($this->productServices)){
            foreach($this->productServices as $productService){
                $data['productServices'][] =$productService->asJson();
            }
        }else{
            $data['productServices'] = [];
        }

        return $data;
    }




        public function asJsonForCouponList()
    {
        $data = [];
        $data['id'] =  $this->id;

        $data['service_id'] =  $this->id;

        $data['vendor_details_id'] =  $this->vendor_details_id;

        $data['sub_category_id'] =  $this->sub_category_id;

        $data['service_name'] =  $this->service_name;
        $data['store_service_type_id'] =  $this->store_service_type_id;


        $data['image'] =  $this->image;
        $data['multi_selection'] =  $this->multi_selection??false;


        $data['description'] =  preg_replace('/\s+/', ' ', trim(html_entity_decode(strip_tags($this->description))));

        $data['small_description'] =  $this->small_description;

        $data['original_price'] =  $this->original_price;


        $data['discount_price'] =  $this->discount_price;

        $data['max_per_day_services'] =  $this->max_per_day_services;

        $data['duration'] =  $this->duration . ' minutes';
        $data['duration_int'] =  $this->duration;
        $data['price'] =  $this->price;
        $data['from_price'] =  $this->from_price;
        $data['to_price'] =  $this->to_price;
        $data['is_price_range'] =  $this->is_price_range;

        $data['service_for'] =  $this->service_for; 
        $data['type'] =  $this->type;
 
        $data['home_visit'] =  $this->home_visit;

        $data['walk_in'] =  $this->walk_in;

        $data['is_product_required'] =  $this->is_product_required;


     
     
        $services = Services::find()
        ->where([
            'parent_id' => $this->id,
            'status' => Services::STATUS_ACTIVE,
        ])
        ->andWhere([
            'or',
            ['is_parent_service' => null],
            ['is_parent_service' => '']
        ])
        ->all();
        if(!empty($services)){
        $data['is_parent_service'] =  $this->is_parent_service;
        }else{
            $data['is_parent_service'] =  null;
        }
        if($data['is_parent_service']==1){
        if(!empty($services)){
                foreach($services as $servicesChild){
                    $data['child_services'][] = $servicesChild->asJsonChildForCouponList();
                }
            }else{
                $data['child_services'] =null;
            }

        }


        $data['status'] =  $this->status;

    



   
    return $data;
    }



}

