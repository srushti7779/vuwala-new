<?php


namespace app\modules\admin\models\base;

use app\modules\admin\models\Service;
use app\modules\admin\models\Services;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the base model class for table "sub_category".
 *
 * @property integer $id
 * @property integer $main_category_id
 * @property integer $vendor_details_id
 * @property string $title
 * @property string $slug
 * @property string $image
 * @property integer $is_featured
 * @property integer $status
 * @property integer $service_type_id
 * @property integer $sortOrder
 * @property integer $type_id
 * @property string $created_on
 * @property string $updated_on 
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $is_premium
 *
 * @property \app\modules\admin\models\MainCategory $mainCategory
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 */
class SubCategory extends \yii\db\ActiveRecord
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
            'createUser',
            'updateUser',
            'vendorDetails',
            'services',
            'storeServiceType',
        ];
    }

    const STATUS_INACTIVE = 2;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;




    const PREMIUM = 1;
    const NOT_PREMIUM = 2;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['main_category_id', 'title', 'image', 'is_featured','slug'], 'required'],
            [['main_category_id', 'vendor_details_id', 'is_featured','status', 'sortOrder', 'type_id', 'create_user_id', 'update_user_id','service_type_id'], 'integer'],
            [['slug'], 'string'],
            [['slug'], 'unique'],
            [['created_on', 'updated_on',], 'safe'], 
            [['title', 'image'], 'string', 'max' => 255] 
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sub_category';
    }

    public function getStateOptions()
    {
        return [

            self::STATUS_INACTIVE => 'In Active',
            self::STATUS_ACTIVE => 'Active',
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



    // public function getOptionsPremium()
    // {
    //     return [

    //         self::PREMIUM => 'Premium',
    //         self::NOT_PREMIUM => 'Not Premium',

    //     ];
    // }


    public function getFeatureOptionsBadges()
    {
        if ($this->is_featured == self::IS_FEATURED) {
            return '<span class="badge badge-success">Featured</span>';
        } elseif ($this->is_featured == self::IS_NOT_FEATURED) {
            return '<span class="badge badge-danger">Not Featured</span>';
        }
    }



    // public function getOptionsBadgesPremium()
    // {
    //     if ($this->is_premium == self::PREMIUM) {
    //         return '<span class="badge badge-success">Premium</span>';
    //     } elseif ($this->is_premium == self::NOT_PREMIUM) {
    //         return '<span class="badge badge-danger">Not Premium</span>';
    //     }
    // }


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
            'slug' => Yii::t('app', 'Slug'),
            'image' => Yii::t('app', 'Image'),
            'is_featured' => Yii::t('app', 'Is Featured'),
            // 'is_premium' => Yii::t('app', 'Is Premium'),
            'status' => Yii::t('app', 'Status'),
            'sortOrder' => Yii::t('app', 'Sort Order'),
            'type_id' => Yii::t('app', 'Type ID'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
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

    
    public function getStoreServiceType()
    {
        return $this->hasOne(\app\modules\admin\models\StoreServiceTypes::className(), ['id' => 'store_service_type_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(\app\modules\admin\models\Services::className(), ['sub_category_id' => 'id']);
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\SubCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\SubCategoryQuery(get_called_class());
    }
 



    public function asJson($vendor_details_id = '', $user_id = '')
{
    $data = [
        'sub_category_id' => $this->id,
        'main_category_id' => $this->main_category_id,
        'vendor_details_id' => $this->vendor_details_id,
        'vendor_details' => $this->vendorDetails,
        'title' => $this->title,
        'slug' => $this->slug,
        'image' => $this->image,
        'is_featured' => $this->is_featured,
        'service_type_id' => $this->service_type_id,
        'status' => $this->status,
        'sortOrder' => $this->sortOrder,
        'type_id' => $this->type_id,
        'created_on' => $this->created_on,
        'updated_on' => $this->updated_on,
        'create_user_id' => $this->create_user_id,
        'update_user_id' => $this->update_user_id,
    ];

    // Get services only once
    $servicesQuery = Services::find()
        ->select(['id', 'vendor_details_id', 'sub_category_id']) // Only needed fields
        ->where(['sub_category_id' => $this->id]);

    if (!empty($vendor_details_id)) {
        $servicesQuery->andWhere(['vendor_details_id' => $vendor_details_id]);
        $servicesQuery->andWhere(['status' => Services::STATUS_ACTIVE]);
    }

    $services = $servicesQuery->all();

    $data['services_count'] = count($services);

    if (!empty($services)) {
        $data['services'] = array_map(function ($service) use ($user_id) {
            return $service->asJson($user_id);
        }, $services);
    } else {
        $data['services'] = (object)[];
    }

    // Favorite check (optimized)
    $data['added_favorite'] = ShopLikes::find()
        ->where([
            'user_id' => $user_id,
            'vendor_details_id' => $this->vendor_details_id
        ])
        ->exists();

    return $data;
}




    public function asJsonSearch()
{
    $data = [
        'sub_category_id' => $this->id,
        'main_category_id' => $this->main_category_id,
        'vendor_details_id' => $this->vendor_details_id,
        'title' => $this->title,
        'slug' => $this->slug,
        'image' => $this->image,
        'is_featured' => $this->is_featured,
        'service_type_id' => $this->service_type_id,
        'status' => $this->status,
        'sortOrder' => $this->sortOrder,
        'type_id' => $this->type_id,
     
    ];

 

    return $data;
}



    public function asJsonVendor()
{
    $data = [
        'sub_category_id' => $this->id,
        'main_category_id' => $this->main_category_id,
        'vendor_details_id' => $this->vendor_details_id,
        'vendor_details' => $this->vendorDetails,
        'title' => $this->title,
        'slug' => $this->slug,
        'image' => $this->image,
        'store_service_type_id' => $this->store_service_type_id,
        'is_featured' => $this->is_featured,
        'service_type_id' => $this->service_type_id,
        'status' => $this->status,
        'sortOrder' => $this->sortOrder,
        'type_id' => $this->type_id
    ];

    // Get services only once
    $servicesQuery = Services::find()
        ->select(['id', 'vendor_details_id', 'sub_category_id']) // Only needed fields
        ->where(['sub_category_id' => $this->id])
        ->andWhere(['store_service_type_id' => $this->store_service_type_id])
        ->andWhere(['service_type_id' => $this->service_type_id]);

     $servicesQuery->andWhere(['vendor_details_id' => $this->vendor_details_id]);
    $servicesQuery->andWhere(['status' => Services::STATUS_ACTIVE]);

    $services = $servicesQuery->all();
    $data['services_count'] = count($services);

    $sub_category = SubCategory::find()
        ->where(['main_category_id' => $this->main_category_id])
        ->andWhere(['vendor_details_id' => $this->vendor_details_id])
        ->andWhere(['service_type_id' => $this->service_type_id])
        ->andWhere(['store_service_type_id'=> $this->store_service_type_id])
        ->all();

        if(!empty($sub_category)){
            foreach($sub_category as $sub){
                $data['sub_category'][] = $sub->asJson();
            }
        }else{
            $data['sub_category'] = (object)[];
        }
    

   

    return $data;
}



    public function asJsonVendorStoreService()
{
    $data = [
        'sub_category_id' => $this->id,
        'main_category_id' => $this->main_category_id,
        'service_type_id' => $this->service_type_id,
        'vendor_details_id' => $this->vendor_details_id,
        'store_service_type_id' => $this->store_service_type_id,
        'title' => $this->title,
        'slug' => $this->slug,
        'image' => $this->image,
        'is_featured' => $this->is_featured,
        'status' => $this->status,
    
    ];

 

    return $data;
}







public function customJson($user_id = '', $post = [])
{
    $service_type = !empty($post['service_type']) ? strtolower($post['service_type']) : null;
    $service_for = $post['service_for'] ?? null;
    $sort = !empty($post['sort']) ? strtolower($post['sort']) : null;
    $search = !empty($post['search']) ? strtolower($post['search']) : null;




    $data = [
        'sub_category_id' => $this->id,
        'main_category_id' => $this->main_category_id,
        'vendor_details_id' => $this->vendor_details_id,
        'title' => $this->title,
        'image' => $this->image,
    ];
    $data['store_service_type_id'] = $this->store_service_type_id;

    // Build service query
    $query = Services::find()
        ->where(['status' => Services::STATUS_ACTIVE])
        ->andWhere(['vendor_details_id'=> $this->vendor_details_id])
        ->andWhere(['sub_category_id'=> $this->id]);

 

        if (!empty($service_type)) {
                  $query->andWhere(['type' => $service_type]);

    }


        if (!empty($service_for)) {
          
                $query->andWhere(['service_for'=>$service_for]);
            }


       if (!empty($search)) {
    $query->andWhere(['like', 'services.service_name', "%$search%", false]);
            }


        if (!empty($sort) && in_array($sort, ['asc', 'desc'])) {
                $query->orderBy(['services.service_for' => ($sort === 'asc') ? SORT_ASC : SORT_DESC]);
            }

    // Include only parent services
    $query->andWhere([
        'or',
        ['parent_id' => null],
        ['parent_id' => '']
    ]);

    // Apply sort if requested
    if (!empty($sort) && in_array($sort, ['asc', 'desc'])) {
        $query->orderBy(['id' => $sort === 'asc' ? SORT_ASC : SORT_DESC]);
    }

    $services = $query->all();
    $data['services_count'] = (string)count($services);

    if (!empty($services)) {
        foreach ($services as $service_data) {
            $data['services'][] = $service_data->asJson($user_id);
        }
    } else {
        $data['services'] = (object)[];
    }

    return $data;
}


    


}
