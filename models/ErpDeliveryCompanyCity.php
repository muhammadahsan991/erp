<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%erp_delivery_company_city}}".
 *
 * @property int $id_erp_delivery_company_city
 * @property string $name
 *
 * @property ErpDeliveryCompany[] $erpDeliveryCompanies
 */
class ErpDeliveryCompanyCity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%erp_delivery_company_city}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_erp_delivery_company_city' => 'Id Erp Delivery Company City',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getErpDeliveryCompanies()
    {
        return $this->hasMany(ErpDeliveryCompany::className(), ['fk_erp_delivery_company_city' => 'id_erp_delivery_company_city']);
    }
}
