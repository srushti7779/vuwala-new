<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "business_documents".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $file
 * @property integer $document_type
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 */
class BusinessDocuments extends \yii\db\ActiveRecord
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
            'createUser',
            'updateUser'
        ];
    }

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 2;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;


    const MEDICAL_COUNCIL_REG_CERTIFICATE = 1;
    const CLINIC_REG_CERTIFICATE = 2;

 
    /**
     * @inheritdoc
     */
  public function rules()
{
    return [
        [['document_type'], 'required'],
        [['vendor_details_id', 'document_type', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['created_on', 'updated_on','vendor_details_id'], 'safe'],
        // Required for create scenario
        [['file'], 'required', 'on' => self::SCENARIO_DEFAULT],
        // File validation
        [['file'], 'file',
            'skipOnEmpty' => true,
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
            'maxSize' => 2 * 1024 * 1024, // 2 MB
            'tooBig' => 'The file is too large. Maximum allowed size is 2MB.',
        ],
    ];
}


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'business_documents';
    }

    public function getStateOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'In Active',

        ];
    }
    public function getStateOptionsBadges()
    {

        if ($this->status == self::STATUS_ACTIVE) {
            return '<span class="badge badge-success">Active</span>';
        } elseif ($this->status == self::STATUS_INACTIVE) {
            return '<span class="badge badge-default">In Active</span>';
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


    public function getDocumentTypeOptionsOptions()
    {
        return [
            self::MEDICAL_COUNCIL_REG_CERTIFICATE => 'Medical Council Registration Certificate',
            self::CLINIC_REG_CERTIFICATE => 'Clinic Registration Certificate',
        ];
    }
    public function getDocumentTypeOptionsBadges()
    {
        if ($this->document_type == self::MEDICAL_COUNCIL_REG_CERTIFICATE) {
            return '<span class="badge badge-success">Medical Council Registration Certificate</span>';
        } elseif ($this->document_type == self::CLINIC_REG_CERTIFICATE) {
            return '<span class="badge badge-default">Clinic Registration Certificate</span>';
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
            'file' => Yii::t('app', 'File'),
            'document_type' => Yii::t('app', 'Document Type'),
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
     * @return \app\modules\admin\models\BusinessDocumentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BusinessDocumentsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['vendor_details_id'] =  $this->vendor_details_id;
        
                $data['file'] =  $this->file;
        
                $data['document_type'] =  $this->document_type;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['update_user_id'] =  $this->update_user_id;
        
            return $data;
}


}


