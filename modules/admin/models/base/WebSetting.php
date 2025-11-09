<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "web_setting".
 *
 * @property integer $setting_id
 * @property string $name
 * @property string $setting_key
 * @property string $value
 * @property integer $type_id
 * @property integer $status
 * @property string $created_date
 * @property string $updated_date
 * @property integer $create_user_id
 * @property integer $updated_user_id 
 */
class WebSetting extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */


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
            [['name', 'setting_key', 'value'], 'required'],
            [['value'], 'string'],
            [['type_id', 'status', 'create_user_id', 'updated_user_id'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['setting_key'], 'string', 'max' => 50]
          
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_setting';
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
     *
     * @return string
     * overwrite function optimisticLock
     * return string name of field are used to stored optimistic lock
     *
     */
    // public function optimisticLock() {
    //     return 'lock';
    // }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'setting_id' => Yii::t('app', 'Setting ID'),
            'name' => Yii::t('app', 'Name'),
            'setting_key' => Yii::t('app', 'Setting Key'),
            'value' => Yii::t('app', 'Value'),
            'type_id' => Yii::t('app', 'Type ID'),
            'status' => Yii::t('app', 'Status'),
            'created_date' => Yii::t('app', 'Created Date'),
            'updated_date' => Yii::t('app', 'Updated Date'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'updated_user_id' => Yii::t('app', 'Updated User ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            // 'timestamp' => [
            //     'class' => TimestampBehavior::className(),
            //     'createdAtAttribute' => 'created_on',
            //     'updatedAtAttribute' => 'updated_on',
            //     'value' => date('Y-m-d H:i:s'),
            // ],
            // 'blameable' => [
            //     'class' => BlameableBehavior::className(),
            //     'createdByAttribute' => 'created_by',
            //     'updatedByAttribute' => 'updated_by',
            // ],
        ];
    }



    /**
     * @inheritdoc
     * @return \app\modules\admin\models\WebSettingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\WebSettingQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['setting_id'] =  $this->setting_id;
        
                $data['name'] =  $this->name;
        
                $data['setting_key'] =  $this->setting_key;
        
                $data['value'] =  $this->value;
        
                $data['type_id'] =  $this->type_id;
        
                $data['status'] =  $this->status;
        
                $data['created_date'] =  $this->created_date;
        
                $data['updated_date'] =  $this->updated_date;
        
                $data['create_user_id'] =  $this->create_user_id;
        
                $data['updated_user_id'] =  $this->updated_user_id;
        
            return $data;
}


}


