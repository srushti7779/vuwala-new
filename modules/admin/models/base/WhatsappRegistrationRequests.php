<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "whatsapp_registration_requests".
 *
 * @property integer $id
 * @property string $source
 * @property integer $src_id
 * @property string $username
 * @property string $email
 * @property string $contact_no
 * @property string $first_name
 * @property string $last_name
 * @property string $business_name
 * @property string $gst_number
 * @property integer $city_id
 * @property string $address
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property array $extra
 */
class WhatsappRegistrationRequests extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            ''
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
            [['source', 'src_id'], 'required'],
            [['src_id', 'city_id', 'status'], 'integer'],
            [['address', 'extra'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['source'], 'string', 'max' => 20],
            [['username', 'email', 'business_name', 'gst_number'], 'string', 'max' => 255],
            [['contact_no'], 'string', 'max' => 50],
            [['first_name', 'last_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'whatsapp_registration_requests';
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
            'source' => Yii::t('app', 'Source'),
            'src_id' => Yii::t('app', 'Src ID'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'contact_no' => Yii::t('app', 'Contact No'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'business_name' => Yii::t('app', 'Business Name'),
            'gst_number' => Yii::t('app', 'Gst Number'),
            'city_id' => Yii::t('app', 'City ID'),
            'address' => Yii::t('app', 'Address'),
            'status' => Yii::t('app', 'Status'),
            'extra' => Yii::t('app', 'Extra'),
        ];
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
     * @return \app\modules\admin\models\WhatsappRegistrationRequestsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WhatsappRegistrationRequestsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['source'] =  $this->source;
        
                $data['src_id'] =  $this->src_id;
        
                $data['username'] =  $this->username;
        
                $data['email'] =  $this->email;
        
                $data['contact_no'] =  $this->contact_no;
        
                $data['first_name'] =  $this->first_name;
        
                $data['last_name'] =  $this->last_name;
        
                $data['business_name'] =  $this->business_name;
        
                $data['gst_number'] =  $this->gst_number;
        
                $data['city_id'] =  $this->city_id;
        
                $data['address'] =  $this->address;
        
                $data['status'] =  $this->status;
        
                                $data['extra'] =  $this->extra;
        
            return $data;
}


}


