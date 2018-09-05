<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%erp_oms}}".
 *
 * @property int $id_erp_oms
 * @property int $tracking_nr
 * @property int $order_nr
 * @property string $package_nr
 * @property int $fk_delivery_company
 * @property string $return_reasons
 * @property string $shipped_date
 * @property string $delivered_date
 * @property string $receivables
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 *
 * @property ErpDeliveryCompany $fkDeliveryCompany
 */
class ErpOms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%erp_oms}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tracking_nr', 'order_nr', 'fk_delivery_company', 'status'], 'integer'],
            [['order_nr', 'package_nr', 'fk_delivery_company', 'shipped_date', 'delivered_date', 'receivables'], 'required'],
            [['shipped_date', 'delivered_date', 'created_at', 'updated_at'], 'safe'],
            [['receivables'], 'number'],
            [['package_nr'], 'string', 'max' => 255],
            [['return_reasons'], 'string', 'max' => 1024],
            [['fk_delivery_company'], 'exist', 'skipOnError' => true, 'targetClass' => ErpDeliveryCompany::className(), 'targetAttribute' => ['fk_delivery_company' => 'id_erp_delivery_company']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_erp_oms' => 'Id Erp Oms',
            'tracking_nr' => 'Tracking Nr',
            'order_nr' => 'Order Nr',
            'package_nr' => 'Package Nr',
            'fk_delivery_company' => 'Fk Delivery Company',
            'return_reasons' => 'Return Reasons',
            'shipped_date' => 'Shipped Date',
            'delivered_date' => 'Delivered Date',
            'receivables' => 'Receivables',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkDeliveryCompany()
    {
        return $this->hasOne(ErpDeliveryCompany::className(), ['id_erp_delivery_company' => 'fk_delivery_company']);
    }
}
