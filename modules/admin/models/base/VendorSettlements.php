<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "vendor_settlements".
 *
 * @property integer $id
 * @property integer $vendor_earnings_id
 * @property integer $status
 * @property integer $created_on
 * @property integer $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorEarnings $vendorEarnings
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class VendorSettlements extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
          'vendorEarnings',
            'createUser',
            'updateUser',
            'vendorPayout'
        ];
    }

    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;
 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_earnings_id'], 'required'],
            [['vendor_earnings_id', 'status', 'created_on', 'updated_on', 'create_user_id', 'update_user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vendor_settlements';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_APPROVED => 'Approved',

            self::STATUS_REJECTED => 'Rejected',
        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_APPROVED) {
            return '<span class="badge badge-success">Approved</span>';
        } elseif ($this->status == self::STATUS_REJECTED) {
            return '<span class="badge badge-danger">Rejected</span>';
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
            'vendor_earnings_id' => Yii::t('app', 'Vendor Earnings ID'),
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
    public function getVendorEarnings()
    {
        return $this->hasOne(\app\modules\admin\models\VendorEarnings::className(), ['id' => 'vendor_earnings_id']);
    }
     public function getVendorDetails()
    {
        return $this->hasOne(\app\modules\admin\models\VendorDetails::className(), ['id' => 'vendor_details_id']);
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
    public function getVendorPayout()
    {
        return $this->hasOne(\app\modules\admin\models\VendorPayout::className(), ['id' => 'vendor_payout_id']);
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
     * @return \app\modules\admin\models\VendorSettlementsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\VendorSettlementsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_earnings_id'] =  $this->vendor_earnings_id;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


