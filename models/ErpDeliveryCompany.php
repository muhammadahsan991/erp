<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%erp_delivery_company}}".
 *
 * @property int $id_erp_delivery_company
 * @property int $fk_erp_delivery_company_city
 * @property string $name
 *
 * @property ErpDeliveryCompanyCity $fkErpDeliveryCompanyCity
 * @property ErpOms[] $erpOms
 */
class ErpDeliveryCompany extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%erp_delivery_company}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fk_erp_delivery_company_city', 'name'], 'required'],
            [['fk_erp_delivery_company_city'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['fk_erp_delivery_company_city'], 'exist', 'skipOnError' => true, 'targetClass' => ErpDeliveryCompanyCity::className(), 'targetAttribute' => ['fk_erp_delivery_company_city' => 'id_erp_delivery_company_city']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_erp_delivery_company' => 'Id Erp Delivery Company',
            'fk_erp_delivery_company_city' => 'Fk Erp Delivery Company City',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkErpDeliveryCompanyCity()
    {
        return $this->hasOne(ErpDeliveryCompanyCity::className(), ['id_erp_delivery_company_city' => 'fk_erp_delivery_company_city']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getErpOms()
    {
        return $this->hasMany(ErpOms::className(), ['fk_delivery_company' => 'id_erp_delivery_company']);
    }
}
