<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "memberships".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $membership_name
 * @property string $color
 * @property integer $discount
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createdUser
 * @property \app\modules\admin\models\User $updatedUser
 * @property \app\modules\admin\models\StoresUsersMemberships[] $storesUsersMemberships
 */
class MemberShips extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'vendorDetails',
            'createdUser',
            'updatedUser',
            'storesUsersMemberships'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_details_id', 'membership_name', 'color', 'discount'], 'required'],
            [['vendor_details_id', 'discount', 'status'], 'integer'],
            [['created_on', 'updated_on'], 'safe'],
            [['membership_name', 'color'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'memberships';
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
            'membership_name' => Yii::t('app', 'Membership Name'),
            'color' => Yii::t('app', 'Color'),
            'discount' => Yii::t('app', 'Discount'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),

        ];
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
    public function getCreatedUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'create_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedUser()
    {
        return $this->hasOne(\app\modules\admin\models\User::className(), ['id' => 'update_user_id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoresUsersMemberships()
    {
        return $this->hasMany(\app\modules\admin\models\StoresUsersMemberships::className(), ['membership_id' => 'id']);
    }

    public static function getMemberships($vendor_details_id)
    {
        $list = [];
          $memberships = MemberShips::find()
                ->where(['vendor_details_id' => $vendor_details_id])
                ->orderBy(['id' => SORT_DESC])
                ->all();

                if(!empty($memberships)) {
                  foreach($memberships as $membership) {
                    $list[] = $membership->asJson();
                  }
                }
                return $list;
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
     * @return \app\modules\admin\models\MembershipsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\MembershipsQuery(get_called_class());
    }

    public function getMembers($member_ships_id)
    {
       $StoresUsersMembershipsCount = StoresUsersMemberships::find()->where(['membership_id' => $member_ships_id])->count();
       return $StoresUsersMembershipsCount;
    }


    public function asJson()
{
    $data = [];

    $data['member_ships_id'] = $this->id;
    $data['vendor_details_id'] = $this->vendor_details_id;
    $data['membership_name'] = $this->membership_name;
    $data['color'] = $this->color;
    $data['discount'] = $this->discount;
    $data['is_vip_plan'] = $this->is_vip_plan;
    $data['membership_validity'] = $this->membership_validity;
    $data['actual_price'] = $this->actual_price;
    $data['discount_price'] = $this->discount_price;
    $data['members'] = $this->getMembers($this->id);
    $data['status'] = $this->status;
    $data['created_on'] = $this->created_on;
    $data['updated_on'] = $this->updated_on;



    return $data;
}


    public function asJsonUserMembership()
{
    $data = [];
    $data['member_ships_id'] = $this->id;
    $data['vendor_details_id'] = $this->vendor_details_id;
    $data['membership_name'] = $this->membership_name;
    $data['color'] = $this->color;
    $data['discount'] = $this->discount;
    return $data;
}



}


