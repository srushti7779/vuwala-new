<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "uploads".
 *
 * @property integer $id
 * @property string $entity_type
 * @property integer $entity_id
 * @property string $file_url
 * @property string $file_name
 * @property string $file_type
 * @property integer $file_size
 * @property string $extension
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 */
class Uploads extends \yii\db\ActiveRecord
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
            [['entity_id', 'file_size'], 'integer'],
            [['file_url'], 'required'],
            [['created_on', 'updated_on'], 'safe'],
            [['entity_type'], 'string', 'max' => 50],
            [['file_url'], 'string', 'max' => 512],
            [['file_name'], 'string', 'max' => 255],
            [['file_type'], 'string', 'max' => 100],
            [['extension'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uploads';
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
            'entity_type' => Yii::t('app', 'Entity Type'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'file_url' => Yii::t('app', 'File Url'),
            'file_name' => Yii::t('app', 'File Name'),
            'file_type' => Yii::t('app', 'File Type'),
            'file_size' => Yii::t('app', 'File Size'),
            'extension' => Yii::t('app', 'Extension'),
            'status' => Yii::t('app', 'Status'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
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
     * @return \app\modules\admin\models\UploadsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\UploadsQuery(get_called_class());
    }
public function asJson(){
    $data = [] ; 
            $data['id'] =  $this->id;
        
                $data['entity_type'] =  $this->entity_type;
        
                $data['entity_id'] =  $this->entity_id;
        
                $data['file_url'] =  $this->file_url;
        
                $data['file_name'] =  $this->file_name;
        
                $data['file_type'] =  $this->file_type;
        
                $data['file_size'] =  $this->file_size;
        
                $data['extension'] =  $this->extension;
        
                $data['status'] =  $this->status;
        
                $data['created_on'] =  $this->created_on;
        
                $data['updated_on'] =  $this->updated_on;
        
            return $data;
}


}


