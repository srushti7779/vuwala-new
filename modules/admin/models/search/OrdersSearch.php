<?php

namespace app\modules\admin\models\search;

use app\models\User;
use app\modules\admin\models\base\VendorDetails;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\models\Orders;

/**
 * app\modules\admin\models\search\OrdersSearch represents the model behind the search form about `app\modules\admin\models\Orders`.
 */
class OrdersSearch extends Orders
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'vendor_details_id', 'qty', 'status', 'payment_status', 'service_address', 'otp', 'is_verify', 'service_type', 'create_user_id', 'update_user_id'], 'integer'],
            [['json_details', 'trans_type', 'payment_type', 'cancel_reason', 'cancel_description', 'schedule_date', 'schedule_time', 'service_instruction', 'voucher_code', 'voucher_type', 'ip_ress', 'created_on', 'updated_on'], 'safe'],
            [['sub_total', 'tip_amt', 'tax', 'processing_charges', 'service_charge', 'taxable_total', 'total_w_tax', 'voucher_amount', 'cgst', 'sgst'], 'number'],
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
public function search($params, $vendor_details_id = '', $status = '')
{
    $query = Orders::find()
        ->where(['payment_status' => Orders::PAYMENT_DONE]);
  
       if(User::isVendor() && Yii::$app->user->identity->main_vendor == 1){
        // Get all vendor detail IDs for this main vendor
        $vendorIds = VendorDetails::find()
            ->select('id')
            ->where(['main_vendor_user_id' => Yii::$app->user->identity->id])
            ->column(); // This returns an array of IDs
        
        if (!empty($vendorIds)) {
            $query->andWhere(['vendor_details_id' => $vendorIds]); // This uses WHERE IN automatically
        } else {
            // If no vendor details found, return empty result
            $query->andWhere(['vendor_details_id' => -1]);
        }
    } elseif(User::isVendor()) {
        // For regular vendors (not main vendors)
        $vendorIds = VendorDetails::find()
            ->select('id')
            ->where(['user_id' => Yii::$app->user->identity->id])
            ->column();
        
        if (!empty($vendorIds)) {
            $query->andWhere(['vendor_details_id' => $vendorIds]);
        } else {
            $query->andWhere(['vendor_details_id' => -1]);
        }
    }

    if (!empty($vendor_details_id)) {
        $query->andWhere(['vendor_details_id' => $vendor_details_id]);
        // Debugging line, can be removed later
    }

    if (!empty($status)) {
        $query->andWhere(['status' => $status]);
    }

    // Exclude disabled (deleted) orders
    $query->andWhere([
        'or',
        ['is_deleted' => Orders::IS_DELETED_NO],
        ['is_deleted' => null],
        ['is_deleted' => '']
    ]);

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
        'id' => $this->id,
        'user_id' => $this->user_id,
        'vendor_details_id' => $this->vendor_details_id,
        'qty' => $this->qty,
        'sub_total' => $this->sub_total,
        'tip_amt' => $this->tip_amt,
        'tax' => $this->tax,
        'processing_charges' => $this->processing_charges,
        'service_charge' => $this->service_charge,
        'taxable_total' => $this->taxable_total,
        'total_w_tax' => $this->total_w_tax,
        'status' => $this->status,
        'schedule_date' => $this->schedule_date,
        'voucher_amount' => $this->voucher_amount,
        'payment_status' => $this->payment_status,
        'service_address' => $this->service_address,
        'otp' => $this->otp,
        'cgst' => $this->cgst,
        'sgst' => $this->sgst,
        'is_verify' => $this->is_verify,
        'service_type' => $this->service_type,
        'created_on' => $this->created_on,
        'updated_on' => $this->updated_on,
        'create_user_id' => $this->create_user_id,
        'update_user_id' => $this->update_user_id,
    ]);

    $query->andFilterWhere(['like', 'json_details', $this->json_details])
        ->andFilterWhere(['like', 'trans_type', $this->trans_type])
        ->andFilterWhere(['like', 'payment_type', $this->payment_type])
        ->andFilterWhere(['like', 'cancel_reason', $this->cancel_reason])
        ->andFilterWhere(['like', 'cancel_description', $this->cancel_description])
        ->andFilterWhere(['like', 'schedule_time', $this->schedule_time])
        ->andFilterWhere(['like', 'service_instruction', $this->service_instruction])
        ->andFilterWhere(['like', 'voucher_code', $this->voucher_code])
        ->andFilterWhere(['like', 'voucher_type', $this->voucher_type])
        ->andFilterWhere(['like', 'ip_ress', $this->ip_ress]);

    return $dataProvider;
}

    public function managersearch($params)
    {
        $query = Orders::find()
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
            'vendor_details_id' => $this->vendor_details_id,
            'qty' => $this->qty,
            'sub_total' => $this->sub_total,
            'tip_amt' => $this->tip_amt,
            'tax' => $this->tax,
            'processing_charges' => $this->processing_charges,
            'service_charge' => $this->service_charge,
            'taxable_total' => $this->taxable_total,
            'total_w_tax' => $this->total_w_tax,
            'status' => $this->status,
            'schedule_date' => $this->schedule_date,
            'voucher_amount' => $this->voucher_amount,
            'payment_status' => $this->payment_status,
            'service_address' => $this->service_address,
            'otp' => $this->otp,
            'cgst' => $this->cgst,
            'sgst' => $this->sgst,
            'is_verify' => $this->is_verify,
            'service_type' => $this->service_type,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'create_user_id' => $this->create_user_id,
            'update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', 'json_details', $this->json_details])
            ->andFilterWhere(['like', 'trans_type', $this->trans_type])
            ->andFilterWhere(['like', 'payment_type', $this->payment_type])
            ->andFilterWhere(['like', 'cancel_reason', $this->cancel_reason])
            ->andFilterWhere(['like', 'cancel_description', $this->cancel_description])
            ->andFilterWhere(['like', 'schedule_time', $this->schedule_time])
            ->andFilterWhere(['like', 'service_instruction', $this->service_instruction])
            ->andFilterWhere(['like', 'voucher_code', $this->voucher_code])
            ->andFilterWhere(['like', 'voucher_type', $this->voucher_type])
            ->andFilterWhere(['like', 'ip_ress', $this->ip_ress]);

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
}
