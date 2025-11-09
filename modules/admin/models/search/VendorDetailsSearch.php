<?php

namespace app\modules\admin\models\search;

use app\modules\admin\models\base\VendorMainCategoryData;
use app\modules\admin\models\CouponVendor;
use app\modules\admin\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\VendorDetails;
use app\modules\admin\models\SubCategory;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * app\modules\admin\models\search\VendorDetailsSearch represents the model behind the search form about `app\modules\admin\models\VendorDetails`.
 */
class VendorDetailsSearch extends VendorDetails
{

    public $contact_no;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'city_id', 'main_category_id', 'commission_type', 'service_radius', 'gender_type', 'status', 'create_user_id', 'update_user_id', 'is_premium', 'is_featured'], 'integer'],
            [['gender_type'], 'default', 'value' => null],
            [['business_name', 'description', 'website_link', 'gst_number', 'address', 'logo','location_name', 'shop_licence_no', 'offer_tag', 'is_top_shop', 'service_type_home_visit', 'service_type_walk_in', 'created_on', 'updated_on', 'account_number', 'ifsc_code', 'contact_no', 'is_verified'], 'safe'],
            [['latitude', 'longitude', 'avg_rating', 'min_order_amount', 'commission', 'min_service_fee', 'discount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
public function search($params)
{
    $query = VendorDetails::find()->joinWith('user');

    // If logged-in user is a vendor, show only their records
    if (User::isVendor()) {
    $query->andWhere(['or',
        ['vendor_details.user_id' => Yii::$app->user->identity->id],
        ['vendor_details.main_vendor_user_id' => Yii::$app->user->identity->id]
    ]);
}

    // $query->andWhere(['!=', 'vendor_details.status', VendorDetails::STATUS_DELETE]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'defaultOrder' => [
                'created_on' => SORT_DESC,
            ],
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }
    $query->andFilterWhere([
        'vendor_details.id' => $this->id,
        'vendor_details.user_id' => $this->user_id,
        'city_id' => $this->city_id,
        'main_category_id' => $this->main_category_id,
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,
        'avg_rating' => $this->avg_rating,
        'min_order_amount' => $this->min_order_amount,
        'commission_type' => $this->commission_type,
        'commission' => $this->commission,
        'service_radius' => $this->service_radius,
        'min_service_fee' => $this->min_service_fee,
        'discount' => $this->discount,
        'gender_type' => $this->gender_type,
        'is_premium' => $this->is_premium,
        'is_featured' => $this->is_featured,
        'vendor_details.status' => $this->status,
        'vendor_details.created_on' => $this->created_on,
        'vendor_details.updated_on' => $this->updated_on,
        'vendor_details.create_user_id' => $this->create_user_id,
        'vendor_details.update_user_id' => $this->update_user_id,
    ]);

    $query->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'user.contact_no', $this->contact_no])
        ->andFilterWhere(['like', 'location_name', $this->location_name])
        ->andFilterWhere(['like', 'is_verified', $this->is_verified])
        ->andFilterWhere(['like', 'website_link', $this->website_link])
        ->andFilterWhere(['like', 'gst_number', $this->gst_number])
        ->andFilterWhere(['like', 'account_number', $this->account_number])
        ->andFilterWhere(['like', 'ifsc_code', $this->ifsc_code])
        ->andFilterWhere(['like', 'vendor_details.address', $this->address])
        ->andFilterWhere(['like', 'logo', $this->logo])
        ->andFilterWhere(['like', 'shop_licence_no', $this->shop_licence_no])
        ->andFilterWhere(['like', 'offer_tag', $this->offer_tag])
        ->andFilterWhere(['like', 'is_top_shop', $this->is_top_shop])
        ->andFilterWhere(['like', 'service_type_home_visit', $this->service_type_home_visit])
        ->andFilterWhere(['like', 'service_type_walk_in', $this->service_type_walk_in]);

    return $dataProvider;
}
public function vendorsearch($params)
{
    $query = VendorDetails::find()->joinWith('user');
    $query->andWhere(['main_vendor_user_id' => Yii::$app->user->id]);


    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'defaultOrder' => [
                'created_on' => SORT_DESC,
            ],
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    // Filtering conditions
    $query->andFilterWhere([
        'vendor_details.id' => $this->id,
        'vendor_details.user_id' => $this->user_id,
        'city_id' => $this->city_id,
        'main_category_id' => $this->main_category_id,
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,
        'avg_rating' => $this->avg_rating,
        'min_order_amount' => $this->min_order_amount,
        'commission_type' => $this->commission_type,
        'commission' => $this->commission,
        'service_radius' => $this->service_radius,
        'min_service_fee' => $this->min_service_fee,
        'discount' => $this->discount,
        'gender_type' => $this->gender_type,
        'is_premium' => $this->is_premium,
        'is_featured' => $this->is_featured,
        'vendor_details.status' => $this->status,
        'vendor_details.created_on' => $this->created_on,
        'vendor_details.updated_on' => $this->updated_on,
        'vendor_details.create_user_id' => $this->create_user_id,
        'vendor_details.update_user_id' => $this->update_user_id,
    ]);

    $query->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'user.contact_no', $this->contact_no])
        ->andFilterWhere(['like', 'location_name', $this->location_name])
        ->andFilterWhere(['like', 'is_verified', $this->is_verified])
        ->andFilterWhere(['like', 'website_link', $this->website_link])
        ->andFilterWhere(['like', 'gst_number', $this->gst_number])
        ->andFilterWhere(['like', 'account_number', $this->account_number])
        ->andFilterWhere(['like', 'ifsc_code', $this->ifsc_code])
        ->andFilterWhere(['like', 'vendor_details.address', $this->address])
        ->andFilterWhere(['like', 'logo', $this->logo])
        ->andFilterWhere(['like', 'shop_licence_no', $this->shop_licence_no])
        ->andFilterWhere(['like', 'offer_tag', $this->offer_tag])
        ->andFilterWhere(['like', 'is_top_shop', $this->is_top_shop])
        ->andFilterWhere(['like', 'service_type_home_visit', $this->service_type_home_visit])
        ->andFilterWhere(['like', 'service_type_walk_in', $this->service_type_walk_in]);

    return $dataProvider;
}




    public function pendingVendorsOnboardingSearch($params)
    {

        $query = VendorDetails::find()->where(['vendor_details.status' => VENDORDETAILS::STATUS_VERIFICATION_PENDING]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_on' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'city_id' => $this->city_id,
            'main_category_id' => $this->main_category_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'avg_rating' => $this->avg_rating,
            'min_order_amount' => $this->min_order_amount,
            'commission_type' => $this->commission_type,
            'commission' => $this->commission,
            'service_radius' => $this->service_radius,
            'min_service_fee' => $this->min_service_fee,
            'discount' => $this->discount,
            'gender_type' => $this->gender_type,
            'is_premium' => $this->is_premium,
            'is_featured' => $this->is_featured,
            'vendor_details.status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'business_name', $this->business_name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'website_link', $this->website_link])
            ->andFilterWhere(['like', 'gst_number', $this->gst_number])
            ->andFilterWhere(['like', 'account_number', $this->account_number])
            ->andFilterWhere(['like', 'ifsc_code', $this->ifsc_code])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'logo', $this->logo])
            ->andFilterWhere(['like', 'shop_licence_no', $this->shop_licence_no])
            ->andFilterWhere(['like', 'offer_tag', $this->offer_tag])
            ->andFilterWhere(['like', 'is_top_shop', $this->is_top_shop])
            ->andFilterWhere(['like', 'service_type_home_visit', $this->service_type_home_visit])
            ->andFilterWhere(['like', 'service_type_walk_in', $this->service_type_walk_in]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with managersearch query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function managersearch($params)
    {
        $query = VendorDetails::find()
            ->where(['city_id' => \Yii::$app->user->identity->city_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_on' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'city_id' => $this->city_id,
            'main_category_id' => $this->main_category_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'avg_rating' => $this->avg_rating,
            'min_order_amount' => $this->min_order_amount,
            'commission_type' => $this->commission_type,
            'commission' => $this->commission,
            'service_radius' => $this->service_radius,
            'min_service_fee' => $this->min_service_fee,
            'discount' => $this->discount,
            'gender_type' => $this->gender_type,
            'is_premium' => $this->is_premium,
            'is_featured' => $this->is_featured,
            'vendor_details.status' => $this->status,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'business_name', $this->business_name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'website_link', $this->website_link])
            ->andFilterWhere(['like', 'gst_number', $this->gst_number])
            ->andFilterWhere(['like', 'account_number', $this->account_number])
            ->andFilterWhere(['like', 'ifsc_code', $this->ifsc_code])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'logo', $this->logo])
            ->andFilterWhere(['like', 'shop_licence_no', $this->shop_licence_no])
            ->andFilterWhere(['like', 'offer_tag', $this->offer_tag])
            ->andFilterWhere(['like', 'is_top_shop', $this->is_top_shop])
            ->andFilterWhere(['like', 'service_type_home_visit', $this->service_type_home_visit])
            ->andFilterWhere(['like', 'service_type_walk_in', $this->service_type_walk_in]);

        if (isset($this->created_on) && $this->created_on != '') {

            //you dont need the if function if yourse sure you have a not null date
            $date_explode = explode(" - ", $this->created_on);
            //   var_dump($date_explode);exit;
            $date1 = trim($date_explode[0]);
            $date2 = trim($date_explode[1]);
            $query->andFilterWhere(['between', 'created_on', $date1, $date2]);
            // var_dump($query->createCommand()->getRawSql());exit;
        }
        if (isset($this->updated_on) && $this->updated_on != '') {

            //you dont need the if function if yourse sure you have a not null date
            $date_explode = explode(" - ", $this->updated_on);
            //   var_dump($date_explode);exit;
            $date1 = trim($date_explode[0]);
            $date2 = trim($date_explode[1]);
            $query->andFilterWhere(['between', 'updated_on', $date1, $date2]);
            //  var_dump($query->createCommand()->getRawSql());exit;
        }

        return $dataProvider;
    }





    //backup code 


    public function getNearByShopsBasedOnServiceTypes($params, $post = '')
    {
        $latitude = !empty($post['latitude']) ? $post['latitude'] : null;
        $longitude = !empty($post['longitude']) ? $post['longitude'] : null;
        $main_category_id = !empty($post['main_category_id']) ? $post['main_category_id'] : null;
        $service_type_id = !empty($post['service_type_id']) ? $post['service_type_id'] : null;
        $service_type_home_visit = !empty($post['service_type_home_visit']) ? $post['service_type_home_visit'] : null;
        $service_type_walk_in = !empty($post['service_type_walk_in']) ? $post['service_type_walk_in'] : null;
        $search = !empty($post['search']) ? $post['search'] : null;
        $gender_type = !empty($post['gender_type']) ? $post['gender_type'] : null;
        $distance = !empty($post['distance']) ? $post['distance'] : 100;
        $is_featured = isset($post['is_featured']) ? $post['is_featured'] : null;
        $is_premium = isset($post['is_premium']) ? $post['is_premium'] : null;
        $is_top_rated = !empty($post['is_top_rated']) ? $post['is_top_rated'] : null;
        $is_popular = !empty($post['is_popular']) ? $post['is_popular'] : null;
        $discount = !empty($post['discount']) ? $post['discount'] : null;


        if (empty($latitude) || empty($longitude)) {
            throw new \yii\web\BadRequestHttpException('Latitude and Longitude are required.');
        }

        $query = VendorDetails::find()
            ->select([
                'vendor_details.*',
                "(CASE WHEN :latitude = latitude AND :longitude = longitude THEN 0 
            ELSE (6371 * acos(cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(latitude)))) END) AS distance"
            ])
            ->addParams([
                ':latitude' => $latitude,
                ':longitude' => $longitude
            ])
            ->having('distance < :distance')
            ->addParams([':distance' => $distance])
            ->where(['vendor_details.status' => VendorDetails::STATUS_ACTIVE])
            ->orderBy(['distance' => SORT_ASC]);


        if (!empty($service_type_id)) {

            $vendor_main_category_data = VendorMainCategoryData::find()
                ->select('main_category_id')
                ->where(['main_category_id' => $main_category_id])
                ->column();


         


            if (!empty($vendor_main_category_data)) {
                // Step 1: Get relevant sub-category records
                $subCategories = SubCategory::find()
                    ->select(['id', 'store_service_type_id', 'vendor_details_id'])
                    ->where(['service_type_id' => $service_type_id])
                    ->andWhere(['IN', 'main_category_id', $vendor_main_category_data])
                    ->asArray()
                    ->all();

                // Step 2: Extract relevant columns
                $subCategoryIds = ArrayHelper::getColumn($subCategories, 'id');
                $storeServiceTypeIds = ArrayHelper::getColumn($subCategories, 'store_service_type_id');
                $vendorDetailsIds = ArrayHelper::getColumn($subCategories, 'vendor_details_id');

                // Step 3: Query Services table based on those filters
                $vendorIds = Services::find()
                    ->select('vendor_details_id')
                    ->where([
                        'status' => Services::STATUS_ACTIVE,
                    ])
                    ->andWhere(['IN', 'sub_category_id', $subCategoryIds])
                    ->andWhere(['IN', 'vendor_details_id', $vendorDetailsIds])
                    // You can add more filtering if needed
                    ->distinct()
                    ->column();
                $query->andWhere(['in', 'vendor_details.id', $vendorIds]);
            }
        } elseif (!empty($main_category_id)) {

            $query->joinWith('vendorMainCategoryDatas as vmcd')
                ->andWhere(['vmcd.main_category_id' => $main_category_id]);
        }
    $currentDate = date('Y-m-d H:i:s'); 


     if (!empty($discount)) {
    $query->joinWith(['couponVendors.coupon as cv'])
        ->andWhere([
            'cv.status' => CouponVendor::STATUS_ACTIVE
        ])
        ->andWhere(['<=', 'cv.start_date', $currentDate])
        ->andWhere([
            'or',
            ['>=', 'cv.end_date', $currentDate],
            ['cv.end_date' => null]
        ]);
}


        if (!empty($service_type_home_visit)) {
            $query->andWhere(['service_type_home_visit' => $service_type_home_visit]);
        }

        if (!empty($service_type_walk_in)) {
            $query->andWhere(['service_type_walk_in' => $service_type_walk_in]);
        }

        if (!empty($gender_type)) {

            if ($gender_type == VendorDetails::GENDER_MALE) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_UNISEX]]);
            } elseif ($gender_type == VendorDetails::GENDER_FEMALE) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
            } elseif ($gender_type == VendorDetails::GENDER_UNISEX) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
            }
        }

        if (!empty($is_featured) && $is_featured !== null) {
            $query->andWhere(['is_featured' => $is_featured]);
        }

        if ($is_premium !== null) {
            $query->andWhere(['is_premium' => $is_premium]);
        }

        if (!empty($search)) {
            $query->andWhere(['like', 'vendor_details.business_name', $search]);
        }

        if (!empty($is_top_rated)) {
            $query->orderBy(['vendor_details.avg_rating' => SORT_DESC]);
        }

        if (!empty($is_popular)) {
            $query->leftJoin('orders', 'orders.vendor_details_id = vendor_details.id')
                ->groupBy('vendor_details.id')
                ->addSelect(['COUNT(orders.id) as order_count'])
                ->orderBy(['order_count' => SORT_DESC, 'distance' => SORT_ASC]);
        }

        // echo $query->createCommand()->getRawSql();
        // exit; 

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => !empty($post['per_page']) ? (int)$post['per_page'] : 10,
                'page' => isset($post['page']) ? ((int)$post['page'] - 1) : 0,
            ],
        ]);

        $this->load($params);

        if ($this->validate()) {
            $query->andFilterWhere([
                'vendor_details.user_id' => $this->user_id,
                'vendor_details.main_category_id' => $this->main_category_id,
                'vendor_details.latitude' => $this->latitude,
                'vendor_details.longitude' => $this->longitude,
                'vendor_details.avg_rating' => $this->avg_rating,
                'vendor_details.min_order_amount' => $this->min_order_amount,
                'vendor_details.commission_type' => $this->commission_type,
                'vendor_details.commission' => $this->commission,
                'vendor_details.service_radius' => $this->service_radius,
                'vendor_details.min_service_fee' => $this->min_service_fee,
                'vendor_details.discount' => $this->discount,
                'vendor_details.gender_type' => $this->gender_type,
                'vendor_details.created_on' => $this->created_on,
                'vendor_details.updated_on' => $this->updated_on,
                'vendor_details.create_user_id' => $this->create_user_id,
                'vendor_details.update_user_id' => $this->update_user_id,
            ]);

            $query->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
                ->andFilterWhere(['like', 'vendor_details.website_link', $this->website_link])
                ->andFilterWhere(['like', 'vendor_details.gst_number', $this->gst_number])
                ->andFilterWhere(['like', 'vendor_details.account_number', $this->account_number])
                ->andFilterWhere(['like', 'vendor_details.ifsc_code', $this->ifsc_code])
                ->andFilterWhere(['like', 'vendor_details.address', $this->address])
                ->andFilterWhere(['like', 'vendor_details.logo', $this->logo])
                ->andFilterWhere(['like', 'vendor_details.shop_licence_no', $this->shop_licence_no])
                ->andFilterWhere(['like', 'vendor_details.offer_tag', $this->offer_tag])
                ->andFilterWhere(['like', 'vendor_details.is_top_shop', $this->is_top_shop]);
        }

        return $dataProvider;
    }






    public function getNearByShopsWithActiveOffers($params, $post = '')
{
    $latitude = !empty($post['latitude']) ? $post['latitude'] : null;
    $longitude = !empty($post['longitude']) ? $post['longitude'] : null;
    $main_category_id = !empty($post['main_category_id']) ? $post['main_category_id'] : null;
    $service_type_id = !empty($post['service_type_id']) ? $post['service_type_id'] : null;
    $service_type_home_visit = !empty($post['service_type_home_visit']) ? $post['service_type_home_visit'] : null;
    $service_type_walk_in = !empty($post['service_type_walk_in']) ? $post['service_type_walk_in'] : null;
    $search = !empty($post['search']) ? $post['search'] : null;
    $gender_type = !empty($post['gender_type']) ? $post['gender_type'] : null;
    $distance = !empty($post['distance']) ? $post['distance'] : 100;
    $is_featured = isset($post['is_featured']) ? $post['is_featured'] : null;
    $is_premium = isset($post['is_premium']) ? $post['is_premium'] : null;
    $is_top_rated = !empty($post['is_top_rated']) ? $post['is_top_rated'] : null;
    $is_popular = !empty($post['is_popular']) ? $post['is_popular'] : null;

    if (empty($latitude) || empty($longitude)) {
        throw new \yii\web\BadRequestHttpException('Latitude and Longitude are required.');
    }

    $currentDate = date('Y-m-d H:i:s');

    $query = VendorDetails::find()
        ->select([
            'vendor_details.*',
            "(CASE WHEN :latitude = latitude AND :longitude = longitude THEN 0 
                ELSE (6371 * acos(cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(latitude)))) END) AS distance"
        ])
        ->addParams([
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ])
        ->having('distance < :distance')
        ->addParams([':distance' => $distance])
        ->where(['vendor_details.status' => VendorDetails::STATUS_ACTIVE])
        ->orderBy(['distance' => SORT_ASC]);

    // Always require active coupons (offers)
    $query->joinWith(['couponVendors.coupon as cv'])
        ->andWhere([
            'cv.status' => CouponVendor::STATUS_ACTIVE
        ])
        ->andWhere(['<=', 'cv.start_date', $currentDate])
        ->andWhere([
            'or',
            ['>=', 'cv.end_date', $currentDate],
            ['cv.end_date' => null]
        ]);

    // Service type filter (if needed)
    if (!empty($service_type_id)) {
        $vendor_main_category_data = VendorMainCategoryData::find()
            ->select('main_category_id')
            ->where(['main_category_id' => $main_category_id])
            ->column();

        if (!empty($vendor_main_category_data)) {
            $subCategories = SubCategory::find()
                ->select(['id', 'store_service_type_id', 'vendor_details_id'])
                ->where(['service_type_id' => $service_type_id])
                ->andWhere(['IN', 'main_category_id', $vendor_main_category_data])
                ->asArray()
                ->all();

            $subCategoryIds = \yii\helpers\ArrayHelper::getColumn($subCategories, 'id');
            $vendorDetailsIds = \yii\helpers\ArrayHelper::getColumn($subCategories, 'vendor_details_id');

            $vendorIds = Services::find()
                ->select('vendor_details_id')
                ->where([
                    'status' => Services::STATUS_ACTIVE,
                ])
                ->andWhere(['IN', 'sub_category_id', $subCategoryIds])
                ->andWhere(['IN', 'vendor_details_id', $vendorDetailsIds])
                ->distinct()
                ->column();
            $query->andWhere(['in', 'vendor_details.id', $vendorIds]);
        }
    } elseif (!empty($main_category_id)) {
        $query->joinWith('vendorMainCategoryDatas as vmcd')
            ->andWhere(['vmcd.main_category_id' => $main_category_id]);
    }

    if (!empty($service_type_home_visit)) {
        $query->andWhere(['service_type_home_visit' => $service_type_home_visit]);
    }

    if (!empty($service_type_walk_in)) {
        $query->andWhere(['service_type_walk_in' => $service_type_walk_in]);
    }

    if (!empty($gender_type)) {
        if ($gender_type == VendorDetails::GENDER_MALE) {
            $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_UNISEX]]);
        } elseif ($gender_type == VendorDetails::GENDER_FEMALE) {
            $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
        } elseif ($gender_type == VendorDetails::GENDER_UNISEX) {
            $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
        }
    }

    if (!empty($is_featured) && $is_featured !== null) {
        $query->andWhere(['is_featured' => $is_featured]);
    }

    if ($is_premium !== null) {
        $query->andWhere(['is_premium' => $is_premium]);
    }

    if (!empty($search)) {
        $query->andWhere(['like', 'vendor_details.business_name', $search]);
    }

    if (!empty($is_top_rated)) {
        $query->orderBy(['vendor_details.avg_rating' => SORT_DESC]);
    }

    if (!empty($is_popular)) {
        $query->leftJoin('orders', 'orders.vendor_details_id = vendor_details.id')
            ->groupBy('vendor_details.id')
            ->addSelect(['COUNT(orders.id) as order_count'])
            ->orderBy(['order_count' => SORT_DESC, 'distance' => SORT_ASC]);
    }

    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => !empty($post['per_page']) ? (int)$post['per_page'] : 10,
            'page' => isset($post['page']) ? ((int)$post['page'] - 1) : 0,
        ],
    ]);

    $this->load($params);

    if ($this->validate()) {
        $query->andFilterWhere([
            'vendor_details.user_id' => $this->user_id,
            'vendor_details.main_category_id' => $this->main_category_id,
            'vendor_details.latitude' => $this->latitude,
            'vendor_details.longitude' => $this->longitude,
            'vendor_details.avg_rating' => $this->avg_rating,
            'vendor_details.min_order_amount' => $this->min_order_amount,
            'vendor_details.commission_type' => $this->commission_type,
            'vendor_details.commission' => $this->commission,
            'vendor_details.service_radius' => $this->service_radius,
            'vendor_details.min_service_fee' => $this->min_service_fee,
            'vendor_details.discount' => $this->discount,
            'vendor_details.gender_type' => $this->gender_type,
            'vendor_details.created_on' => $this->created_on,
            'vendor_details.updated_on' => $this->updated_on,
            'vendor_details.create_user_id' => $this->create_user_id,
            'vendor_details.update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
            ->andFilterWhere(['like', 'vendor_details.website_link', $this->website_link])
            ->andFilterWhere(['like', 'vendor_details.gst_number', $this->gst_number])
            ->andFilterWhere(['like', 'vendor_details.account_number', $this->account_number])
            ->andFilterWhere(['like', 'vendor_details.ifsc_code', $this->ifsc_code])
            ->andFilterWhere(['like', 'vendor_details.address', $this->address])
            ->andFilterWhere(['like', 'vendor_details.logo', $this->logo])
            ->andFilterWhere(['like', 'vendor_details.shop_licence_no', $this->shop_licence_no])
            ->andFilterWhere(['like', 'vendor_details.offer_tag', $this->offer_tag])
            ->andFilterWhere(['like', 'vendor_details.is_top_shop', $this->is_top_shop]);
    }

    // The dataProvider will be empty if no active offers are found
    return $dataProvider;
}


    //new code 








    public function getNearByShops($params, $post = '')
    {

        // Initialize variables with defaults
        $latitude = !empty($post['latitude']) ? $post['latitude'] : null;
        $longitude = !empty($post['longitude']) ? $post['longitude'] : null;
        // $sub_category_id = !empty($post['sub_category_id']) ? $post['sub_category_id'] : null;
        $main_category_id = !empty($post['main_category_id']) ? $post['main_category_id'] : null;
        $service_type_id = !empty($post['service_type_id']) ? $post['service_type_id'] : null;
        $service_type_home_visit = !empty($post['service_type_home_visit']) ? $post['service_type_home_visit'] : null;
        $service_type_walk_in = !empty($post['service_type_walk_in']) ? $post['service_type_walk_in'] : null;
        $search = !empty($post['search']) ? $post['search'] : null;
        $gender_type = !empty($post['gender_type']) ? $post['gender_type'] : null;
        $is_top_rated = !empty($post['is_top_rated']) ? $post['is_top_rated'] : null;
        $is_popular = !empty($post['is_popular']) ? $post['is_popular'] : null;
        $distance = !empty($post['distance']) ? $post['distance'] : 10; // Default radius is 10 km

        // Validate latitude and longitude
        if (empty($latitude) || empty($longitude)) {
            throw new \yii\web\BadRequestHttpException('Latitude and Longitude are required.');
        }

        // Ensure subcategory selection
        if (empty($main_category_id) || empty($service_type_id)) {
            throw new \yii\web\BadRequestHttpException('main category and service type are required.');
        }

        // Initialize the query for VendorDetails
        $query = VendorDetails::find()
            ->select([
                'vendor_details.*',
                "(CASE WHEN :latitude = latitude AND :longitude = longitude THEN 0 
            ELSE (6371 * acos(cos(radians(:latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(latitude)))) END) AS distance"
            ])
            ->addParams([
                ':latitude' => $latitude,
                ':longitude' => $longitude
            ])
            ->having('distance < :distance')
            ->addParams([':distance' => $distance])
            ->where(['vendor_details.status' => VendorDetails::STATUS_ACTIVE])
            ->orderBy(['distance' => SORT_ASC]);

        // Filter by sub_category_id

        if (!empty($service_type_id)) {
            $subCategoryIds = [];
            $storeServiceTypeIds = [];
            $vendorDetailsIdsFromSubCategory = [];

            $subCategories = SubCategory::find()
                ->select(['id', 'store_service_type_id', 'vendor_details_id'])
                ->where([
                    'service_type_id' => $service_type_id,
                    'main_category_id' => $main_category_id
                ])
                ->asArray()
                ->all();



            foreach ($subCategories as $sub) {
                $subCategoryIds[] = $sub['id'];
                $storeServiceTypeIds[] = $sub['store_service_type_id'];
                $vendorDetailsIdsFromSubCategory[] = $sub['vendor_details_id'];
            }

            $vendorIds = Services::find()
                ->alias('s')
                ->select('s.vendor_details_id')
                ->distinct()
                ->where([
                    's.status' => Services::STATUS_ACTIVE
                ])
                ->andWhere(['IN', 's.sub_category_id', $subCategoryIds])
                ->andWhere(['IN', 's.store_service_type_id', $storeServiceTypeIds])
                ->andWhere(['IN', 's.vendor_details_id', $vendorDetailsIdsFromSubCategory])
                ->column();




            $query->andWhere(['in', 'vendor_details.id', $vendorIds]);
        } elseif (!empty($main_category_id)) {
            $query->andWhere(['main_category_id' => $main_category_id]);
        }

        // Apply additional filters
        if (!empty($service_type_home_visit)) {
            $query->andWhere(['service_type_home_visit' => $service_type_home_visit]);
        }

        if (!empty($service_type_walk_in)) {
            $query->andWhere(['service_type_walk_in' => $service_type_walk_in]);
        }

        if (!empty($gender_type)) {
            // $query->andWhere(['gender_type' => $gender_type]);

            if ($gender_type == VendorDetails::GENDER_MALE) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_UNISEX]]);
            } elseif ($gender_type == VendorDetails::GENDER_FEMALE) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
            } elseif ($gender_type == VendorDetails::GENDER_UNISEX) {
                $query->andWhere(['in', 'gender_type', [VendorDetails::GENDER_MALE, VendorDetails::GENDER_FEMALE, VendorDetails::GENDER_UNISEX]]);
            }
        }

        if (!empty($search)) {
            $query->andWhere(['like', 'vendor_details.business_name', $search]);
        }

        if (!empty($is_top_rated)) {
            $query->orderBy(['vendor_details.avg_rating' => SORT_DESC]);
        }

        if (!empty($is_popular)) {
            $query->leftJoin('orders', 'orders.vendor_details_id = vendor_details.id')
                ->groupBy('vendor_details.id')
                ->addSelect(['COUNT(orders.id) as order_count'])
                ->orderBy(['order_count' => SORT_DESC, 'distance' => SORT_ASC]);
        }


        // echo $query->createCommand()->getRawSql(); 
        // exit();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => false],
        ]);

        $this->load($params);

        // Apply additional filtering from user input if validation passes
        if ($this->validate()) {
            $query->andFilterWhere([
                'vendor_details.user_id' => $this->user_id,
                'vendor_details.main_category_id' => $this->main_category_id,
                'vendor_details.latitude' => $this->latitude,
                'vendor_details.longitude' => $this->longitude,
                'vendor_details.avg_rating' => $this->avg_rating,
                'vendor_details.min_order_amount' => $this->min_order_amount,
                'vendor_details.commission_type' => $this->commission_type,
                'vendor_details.commission' => $this->commission,
                'vendor_details.service_radius' => $this->service_radius,
                'vendor_details.min_service_fee' => $this->min_service_fee,
                'vendor_details.discount' => $this->discount,
                'vendor_details.gender_type' => $this->gender_type,
                'vendor_details.created_on' => $this->created_on,
                'vendor_details.updated_on' => $this->updated_on,
                'vendor_details.create_user_id' => $this->create_user_id,
                'vendor_details.update_user_id' => $this->update_user_id,
            ]);

            // Apply text-based filters
            $query->andFilterWhere(['like', 'vendor_details.business_name', $this->business_name])
                ->andFilterWhere(['like', 'vendor_details.website_link', $this->website_link])
                ->andFilterWhere(['like', 'vendor_details.gst_number', $this->gst_number])
                ->andFilterWhere(['like', 'vendor_details.account_number', $this->account_number])
                ->andFilterWhere(['like', 'vendor_details.ifsc_code', $this->ifsc_code])
                ->andFilterWhere(['like', 'vendor_details.address', $this->address])
                ->andFilterWhere(['like', 'vendor_details.logo', $this->logo])
                ->andFilterWhere(['like', 'vendor_details.shop_licence_no', $this->shop_licence_no])
                ->andFilterWhere(['like', 'vendor_details.offer_tag', $this->offer_tag])
                ->andFilterWhere(['like', 'vendor_details.is_top_shop', $this->is_top_shop]);
        }


        return $dataProvider;
    }
}
