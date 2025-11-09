<?php


namespace app\modules\admin\models\base;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the base model class for table "banner_timings".
 *
 * @property integer $id
 * @property integer $banner_id
 * @property string $start_time
 * @property string $end_time
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 * @property integer $create_user_id
 * @property integer $update_user_id
 *
 * @property \app\modules\admin\models\User $createUser
 * @property \app\modules\admin\models\User $updateUser
 * @property \app\modules\admin\models\Banner $banner
 */
class BannerTimings extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            'createUser',
            'updateUser',
            'banner'
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
        [['banner_id', 'start_time', 'end_time'], 'required'],
        [['banner_id', 'status', 'create_user_id', 'update_user_id'], 'integer'],
        [['start_time', 'end_time', 'created_on', 'updated_on'], 'safe'],
        ['end_time', 'validateEndTime'],
    ];
}

public function validateEndTime($attribute, $params)
{
    if (!empty($this->start_time) && !empty($this->end_time)) {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        if ($start >= $end) {
            $this->addError($attribute, 'End Time must be greater than Start Time.');
            return;
        }

        // AM to PM check
        $startPeriod = date('A', $start);
        $endPeriod = date('A', $end);

        if ($startPeriod === 'AM' && $endPeriod === 'AM') {
            $this->addError($attribute, 'If Start Time is in AM, End Time must be in PM.');
        }
    }
}



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_timings';
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
            'banner_id' => Yii::t('app', 'Banner ID'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
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
    public function getBanner()
    {
        return $this->hasOne(\app\modules\admin\models\Banner::className(), ['id' => 'banner_id']);
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
                'value' => new \yii\db\Expression('NOW()'),
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
     * @return \app\modules\admin\models\BannerTimingsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\admin\models\BannerTimingsQuery(get_called_class());
    }






public function asJsonOnlyTimings()
{
    $data = [];
    $data['id'] = $this->id;
    $data['banner_id'] = $this->banner_id;
    $data['start_time'] = $this->start_time; // format: "10:00:00"
    $data['end_time'] = $this->end_time;     // format: "17:30:00"
    return $data;
}







public function asJson($post = '')
{
    $data = [];
    $start_date = !empty($post['start_date']) ? $post['start_date'] : null;
    $end_date = !empty($post['end_date']) ? $post['end_date'] : null;

    $data['id'] = $this->id;
    $data['banner_id'] = $this->banner_id;
    $data['start_time'] = $this->start_time;
    $data['end_time'] = $this->end_time;

    $query = BannerChargeLogs::find()
        ->where(['banner_id' => $this->banner_id])
        ->andWhere(new \yii\db\Expression("TIME(performed_at) BETWEEN :start_time AND :end_time", [
            ':start_time' => $this->start_time,
            ':end_time' => $this->end_time,
        ]));

    if ($start_date && $end_date) {
        $query->andWhere(['between', new \yii\db\Expression("DATE(performed_at)"), $start_date, $end_date]);
    } elseif ($start_date) {
        $query->andWhere(['>=', new \yii\db\Expression("DATE(performed_at)"), $start_date]);
    } elseif ($end_date) {
        $query->andWhere(['<=', new \yii\db\Expression("DATE(performed_at)"), $end_date]);
    }

    $chargeLogs = $query->all();

    // Collect raw logs and calculate counts
    $clickCount = 0;
    $viewCount = 0;
    $logData = [];

    foreach ($chargeLogs as $log) {
        if ($log->action === 'click') {
            $clickCount++;
        } elseif ($log->action === 'view') {
            $viewCount++;
        }
      
    }

    $data['clicks'] = $clickCount;
    $data['views'] = $viewCount;


    return $data;
}







}


