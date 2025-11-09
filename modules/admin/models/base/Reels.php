<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\UploadedFile;

/**
 * This is the base model class for table "reels".
 *
 * @property integer $id
 * @property integer $vendor_details_id
 * @property string $video
 * @property string $thumbnail
 * @property string $title
 * @property string $description
 * @property integer $like_count
 * @property integer $view_count
 * @property integer $share_count 
 * @property integer $status
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $created_on
 * @property integer $updated_on
 *
 * @property \app\modules\admin\models\ReelShareCounts[] $reelShareCounts
 * @property \app\modules\admin\models\ReelTags[] $reelTags
 * @property \app\modules\admin\models\VendorDetails $vendorDetails
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\ReelsLikes[] $reelsLikes
 * @property \app\modules\admin\models\ReelsViewCounts[] $reelsViewCounts
 */
class Reels extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
     * This function helps \mootensai\relation\RelationTrait runs faster
     * @return array relation names of this model
     */
    public function relationNames()
    {
        return [
            'reelShareCounts',
            'reelTags',
            'vendorDetails',
            'createUser',
            'updateUser',
            'reelsLikes',
            'reelsViewCounts'
        ];
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETE = 3;

    const IS_FEATURED = 1;
    const IS_NOT_FEATURED = 0;

    /**
     * @inheritdoc
     */ public function rules()
    {
        return [
            [['vendor_details_id', 'title'], 'required'],
            [['vendor_details_id', 'like_count', 'view_count', 'share_count', 'status', 'create_user_id', 'update_user_id', 'created_on', 'updated_on'], 'integer'],
            [['description'], 'string'],
            [['thumbnail'], 'file', 'extensions' => 'jpg, jpeg, png, gif', 'skipOnEmpty' => true],
            [
                ['video'],
                'file',
                'extensions' => 'mp4, mov, avi',
                'maxSize' => 20971520,
                'skipOnEmpty' => true,
                'tooBig' => 'The video must not exceed 20MB in size.'
            ],

            [['title'], 'string', 'max' => 512],
        ];
    }








    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reels';
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
            'video' => Yii::t('app', 'Video'),
            'thumbnail' => Yii::t('app', 'Thumbnail'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'like_count' => Yii::t('app', 'Like Count'),
            'view_count' => Yii::t('app', 'View Count'),
            'share_count' => Yii::t('app', 'Share Count'),
            'status' => Yii::t('app', 'Status'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'update_user_id' => Yii::t('app', 'Update User ID'),
            'created_on' => Yii::t('app', 'Created On'),
            'updated_on' => Yii::t('app', 'Updated On'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReelShareCounts()
    {
        return $this->hasMany(\app\modules\admin\models\ReelShareCounts::className(), ['real_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReelTags()
    {
        return $this->hasMany(\app\modules\admin\models\ReelTags::className(), ['reel_id' => 'id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getReelsLikes()
    {
        return $this->hasMany(\app\modules\admin\models\ReelsLikes::className(), ['reel_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReelsViewCounts()
    {
        return $this->hasMany(\app\modules\admin\models\ReelsViewCounts::className(), ['real_id' => 'id']);
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
     * @return \app\modules\admin\models\ReelsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\ReelsQuery(get_called_class());
    }
    public function asJson($userId = null)
    {
        $data = [];
        $data['id'] = $this->id;
        $data['vendor_details_id'] = $this->vendor_details_id;
        $data['store_name'] = $this->vendorDetails->business_name;
        $data['store_logo'] = $this->vendorDetails->logo;
        $data['video'] = $this->video;
        $data['thumbnail'] = $this->thumbnail;
        $data['title'] = $this->title;
        $data['description'] = strip_tags($this->description);
        $data['like_count'] = $this->like_count;
        $data['view_count'] = $this->view_count;
        $data['share_count'] = $this->share_count;
        $data['status'] = $this->status;
        $data['reelTags'] = $this->reelTags;
        $data['create_user_id'] = $this->create_user_id;
        $posted_by_first_name = $this->createUser ? $this->createUser->first_name : null;
        $posted_by_last_name = $this->createUser ? $this->createUser->last_name : null;
        $posted_by = $posted_by_first_name . ' ' . $posted_by_last_name;

        $data['posted_by'] = $posted_by;
        $data['update_user_id'] = $this->update_user_id;
        $data['created_on'] = $this->created_on;
        $data['updated_on'] = $this->updated_on;

        // Use the relation to check if the user has liked the reel
        $data['is_liked'] = $this->getReelsLikes()->where(['user_id' => $userId])->exists();

        return $data;
    }
}
