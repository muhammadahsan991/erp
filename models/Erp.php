<?php

namespace app\models;

use yii\db\Exception;
use yii\db\Query;

class Erp
{
    /**
     * Erp constructor.
     *
     */
    public function __construct()
    {
        $this->STATUS_INACTIVE = 0;
        $this->STATUS_ACTIVE = 1;
        $this->STATUS_OVERPAID = 2;
        $this->ERP_RECEIPTS_DATA_INSERTION = '0';
        $this->ERP_OMS_DATA_INSERTION = '1';
        $this->ERP_REPORT_GENERATION = '2';
        $this->ERROR_FILE = "error-".date("Y-m-d").".txt";
        $this->FILE_PERMISSIONS = 0777;
        $this->ONE_DAY = 1;
        $this->SIX_DAYS = 6;
        $this->EIGHT_DAYS = 7;
        $this->FIFTEEN_DAYS = 14;
        $this->SIXTY_DAYS = 59;
        $this->NINETY_DAYS = 89;
        $this->ONE_TWENTY_DAYS = 119;
        $this->PREVIOUS_ONE_YEAR = 1;
        $this->ERP_REPORT = "erpReport.csv";
        $this->DETAIL_AGING_REPORT = "detailAgingReport";
        $this->AGING_REPORT_INTERNAL = "agingReportInternal.csv";
    }

    /**
     * @param $fileErpOms
     * @return bool
     */
    public function insertErpOmsData($fileErpOms)
    {
        $erpOmsDataFile = $this->readCsv($fileErpOms);
        $status = null;

        if (count($erpOmsDataFile) > 0) {
            foreach ($erpOmsDataFile as $data) {
                if ($this->checkTrackingNumberExistInErpOms($data[0])) {
                    $totalPaidAmount = $this->getErpReceiptsPaidAmount($data[0]);
                    $totalReceivables = $this->getErpOmsTotalAmount($data[0]);

                    if ($totalReceivables > $totalPaidAmount) {
                        $status = $this->STATUS_ACTIVE;
                    } elseif ($totalReceivables === $totalPaidAmount) {
                        $status = $this->STATUS_INACTIVE;
                    } elseif ($totalReceivables < $totalPaidAmount) {
                        $status = $this->STATUS_OVERPAID;
                    } else {
                        $status = null;
                    }

                    try {
                        $updateErpOmsModel = $this->getErpOmsDataFromTracking($data[0]);
                        $updateErpOmsModel->status = $status;
                        $updateErpOmsModel->updated_at = date('Y-m-d H:i:s');
                        $updateErpOmsModel->save();
                    } catch (Exception $e) {
                        $this->createLogFile(
                            $this->ERROR_FILE,
                            $data[0]." update Erp Oms failed ".$e->getMessage()."\n"
                        );
                    }

                } else {
                    try {
                        if (count($data) > 0) {
                            $erpOmsModel = $this->getErpOmsModel();
                            $erpOmsModel->tracking_nr = trim($data[0]);
                            $erpOmsModel->order_nr = trim($data[1]);
                            $erpOmsModel->package_nr = trim($data[2]);
                            $erpOmsModel->fk_delivery_company = trim($data[3]);
                            $erpOmsModel->return_reasons = trim($data[4]);
                            $erpOmsModel->shipped_date = trim($data[5]);
                            $erpOmsModel->delivered_date = trim($data[6]);
                            $erpOmsModel->receivables = trim($data[7]);
                            $erpOmsModel->created_at = date('Y-m-d H:i:s');
                            $erpOmsModel->updated_at = date('Y-m-d H:i:s');
                            $erpOmsModel->status = $this->STATUS_ACTIVE;
                            $erpOmsModel->save();
                        }
                    } catch (Exception $e) {
                        $this->createLogFile(
                            $this->ERROR_FILE,
                            $data[0]." insert Erp Oms failed ".$e->getMessage()."\n"
                        );
                    }

                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $fileErpReceipts
     *
     * @return bool
     */
    public function insertErpReceiptsData($fileErpReceipts)
    {
        $erpOmsReceiptsDataFile = $this->readCsv($fileErpReceipts);

        if (count($erpOmsReceiptsDataFile) > 0) {
            foreach ($erpOmsReceiptsDataFile as $erpReceiptsData) {
                try {
                    if (count($erpReceiptsData) > 0) {
                        $erpReceiptsModel = $this->getErpReceiptsModel();
                        $erpReceiptsModel->tracking_nr = trim($erpReceiptsData[0]);
                        $erpReceiptsModel->cod_received = trim($erpReceiptsData[1]);
                        $erpReceiptsModel->chq_nr = trim($erpReceiptsData[2]);
                        $erpReceiptsModel->deposit_date = trim($erpReceiptsData[3]);
                        $erpReceiptsModel->created_at = date('Y-m-d H:i:s');
                        $erpReceiptsModel->updated_at = date('Y-m-d H:i:s');
                        $erpReceiptsModel->save();
                    }
                } catch (Exception $e) {
                    $this->createLogFile(
                        $this->ERROR_FILE,
                        $erpReceiptsData[0]." insert Erp Receipts failed".$e->getMessage()."\n"
                    );
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $deliveredDate
     * @param $previousDate
     * @param $dataKey
     * @param $deliveryCompany
     *
     * @return array
     */
    public function getAgingReport($deliveredDate, $previousDate, $dataKey, $deliveryCompany)
    {
        return $this->getQueryModel()
            ->select('erp_delivery_company.name as name,
            erp_oms.receivables, erp_receipts.cod_received,
            (erp_oms.receivables - erp_receipts.cod_received) as '.$dataKey)
            ->from('erp_oms')
            ->leftJoin('erp_receipts', 'erp_oms.tracking_nr = erp_receipts.tracking_nr')
            ->innerJoin('erp_delivery_company', 'erp_delivery_company.
            id_erp_delivery_company=erp_oms.fk_delivery_company')
            ->innerJoin('erp_delivery_company_city', 'erp_delivery_company_city.
            id_erp_delivery_company_city = erp_delivery_company.fk_erp_delivery_company_city')
            ->where('erp_oms.status=:status', [':status' => $this->STATUS_ACTIVE])
            ->andWhere(['between', 'erp_oms.delivered_date', $deliveredDate, $previousDate])
            ->andWhere('erp_delivery_company.id_erp_delivery_company=:company', [':company' => $deliveryCompany])
            ->groupBy('erp_oms.tracking_nr')
            ->orderBy('erp_delivery_company.name ASC')->all();
    }

    /**
     * @param $deliveredDate
     * @param $previousDate
     * @param $deliveryCompany
     *
     * @return array
     */
    public function getDetailAgingReport($deliveredDate, $previousDate, $deliveryCompany)
    {
        return $this->getQueryModel()
            ->select('erp_oms.tracking_nr,erp_oms.receivables, 
            erp_receipts.cod_received, (erp_oms.receivables - erp_receipts.cod_received) as outstanding_amount,
            erp_receipts.deposit_date,erp_delivery_company.name as company, 
            erp_oms.order_nr,erp_delivery_company_city.name,
            erp_oms.delivered_date, erp_oms.package_nr')
            ->from('erp_receipts')
            ->rightJoin('erp_oms', 'erp_oms.tracking_nr = erp_receipts.tracking_nr')
            ->innerJoin('erp_delivery_company', 'erp_delivery_company.
            id_erp_delivery_company=erp_oms.fk_delivery_company')
            ->innerJoin('erp_delivery_company_city', 'erp_delivery_company_city.
            id_erp_delivery_company_city = erp_delivery_company.fk_erp_delivery_company_city')
            ->where(['between', 'erp_oms.delivered_date', $deliveredDate, $previousDate])
            ->andWhere('erp_delivery_company.id_erp_delivery_company=:company', [':company' => $deliveryCompany])
            ->orderBy('erp_delivery_company.name ASC')
            ->all();
    }

    /**
     * @param $date
     * @param $dataKey
     * @param $deliveryCompany
     *
     * @return array
     */
    public function getOldAgingReport($date, $dataKey, $deliveryCompany)
    {
        return $this->getQueryModel()
            ->select('erp_delivery_company.name as name,
            erp_oms.receivables, erp_receipts.cod_received,
            (erp_oms.receivables - erp_receipts.cod_received) as '.$dataKey)
            ->from('erp_oms')
            ->leftJoin('erp_receipts', 'erp_oms.tracking_nr = erp_receipts.tracking_nr')
            ->innerJoin('erp_delivery_company', 'erp_delivery_company.
            id_erp_delivery_company=erp_oms.fk_delivery_company')
            ->innerJoin('erp_delivery_company_city', 'erp_delivery_company_city.
            id_erp_delivery_company_city = erp_delivery_company.fk_erp_delivery_company_city')
            ->where('erp_oms.status=:status', [':status' => $this->STATUS_ACTIVE])
            ->andWhere('erp_oms.delivered_date <=:date', [':date' => $date])
            ->andWhere('erp_delivery_company.id_erp_delivery_company=:company', [':company' => $deliveryCompany])
            ->groupBy('erp_oms.tracking_nr')
            ->orderBy('erp_delivery_company.name ASC')->all();
    }

    /**
     * @param $fileName
     * @param $headerElement
     *
     * @return array
     */
    public function createAgingReportCsvFileHeader($fileName, $headerElement = [])
    {
        return fputcsv($fileName, [
            $headerElement['Delivery Company'],
            $headerElement['1-8'],
            $headerElement['9-15'],
            $headerElement['16-30'],
            $headerElement['31-90'],
            $headerElement['91-180'],
            $headerElement['181-365'],
            $headerElement['more365'],
            $headerElement['Outstanding']
        ]);
    }

    /**
     * @param $fileName
     * @param $agingReportData
     */
    public function createAgingReportCsvFile($fileName, $agingReportData)
    {
        if (is_array($agingReportData['name'])) {
            $agingReportData[-1] = $agingReportData['name'][0];
            unset($agingReportData['name']);
        }

        //Eight Days Report
        if (!isset($agingReportData['a'])) {
            $agingReportData['a'] = 0;
        }

        //Next 6 Days Report
        if (!isset($agingReportData['b'])) {
            $agingReportData['b'] = 0;
        }

        //Next 15 Days Report
        if (!isset($agingReportData['c'])) {
            $agingReportData['c'] = 0;
        }

        //Next 60 Days Report
        if (!isset($agingReportData['d'])) {
            $agingReportData['d'] = 0;
        }

        //Next 90 Days Report
        if (!isset($agingReportData['e'])) {
            $agingReportData['e'] = 0;
        }

        //Next 180 Days Report
        if (!isset($agingReportData['f'])) {
            $agingReportData['f'] = 0;
        }

        //Greater Than 180 Days Report
        if (!isset($agingReportData['g'])) {
            $agingReportData['g'] = 0;
        }

        $totalOutstanding = $agingReportData['a'] + $agingReportData['b'] + $agingReportData['c'] +
            $agingReportData['d'] + $agingReportData['e'] + $agingReportData['f'] + $agingReportData['g'];
        array_push($agingReportData, $totalOutstanding);
        ksort($agingReportData);
        try {
            fputcsv($fileName, $agingReportData);
        } catch (Exception $e) {
            $this->createLogFile(
                $this->ERROR_FILE,
                $agingReportData." not inserted ".$e->getMessage()."\n"
            );
        }

    }

    /**
     * @param $fileName
     * @param $agingReportData
     */
    public function createAgingReportCsvFileInternal($fileName, $agingReportData)
    {
        if (is_array($agingReportData['name'])) {
            $agingReportData[-1] = $agingReportData['name'][0];
            unset($agingReportData['name']);
        }

        //60 Days Erp Report Internal
        if (!isset($agingReportData['a'])) {
            $agingReportData['a'] = 0;
        }

        //120 Days Erp Report Internal
        if (!isset($agingReportData['b'])) {
            $agingReportData['b'] = 0;
        }

        //180 Days Erp Report Internal
        if (!isset($agingReportData['c'])) {
            $agingReportData['c'] = 0;
        }

        //Greater Than 180 Days Erp Report Internal
        if (!isset($agingReportData['d'])) {
            $agingReportData['d'] = 0;
        }

        $totalOutstanding = $agingReportData['a'] + $agingReportData['b'] + $agingReportData['c'] +
            $agingReportData['d'];
        array_push($agingReportData, $totalOutstanding);
        ksort($agingReportData);
        try {
            fputcsv($fileName, $agingReportData);
        } catch (Exception $e) {
            $this->createLogFile(
                $this->ERROR_FILE,
                $agingReportData." not inserted ".$e->getMessage()."\n"
            );
        }
    }

    /**
     * @param $date
     * @param $previousDays
     *
     * @return string
     */
    public function getPreviousDays($date, $previousDays)
    {
        return date('Y-m-d', strtotime('-'.$previousDays.' days', strtotime($date)));
    }

    /**
     * @param $date
     * @param $previousYear
     *
     * @return string
     */
    public function getPreviousYear($date, $previousYear)
    {
        $previousYearDate = date('Y-m-d', strtotime('-'.$previousYear.' year', strtotime($date)));
        return date('Y-m-d', strtotime('-'.$this->ONE_DAY.' day', strtotime($previousYearDate)));
    }

    /**
     * @param $fileName
     * @param $headerElement
     *
     * @return array
     */
    public function createDetailAgingReportCsvFileHeader($fileName, $headerElement = [])
    {
        return fputcsv($fileName, [
            $headerElement['tracking'],
            $headerElement['receivables'],
            $headerElement['received'],
            $headerElement['outstanding'],
            $headerElement['deposit_date'],
            $headerElement['company'],
            $headerElement['order'],
            $headerElement['city'],
            $headerElement['delivery_date'],
            $headerElement['package_nr'],
            $headerElement['payment_days'],
            $headerElement['days'],
            $headerElement['week_wise']
        ]);
    }

    /**
     * @param $fileName
     * @param $headerElement
     *
     * @return array
     */
    public function createAgingReportInternalCsvFileHeader($fileName, $headerElement = [])
    {
        return fputcsv($fileName, [
            $headerElement['Delivery Company'],
            $headerElement['1-60'],
            $headerElement['61-180'],
            $headerElement['181-365'],
            $headerElement['more365'],
            $headerElement['Outstanding']
        ]);
    }

    /**
     * @param $fileName
     * @param $detailAgingReportData
     */
    public function createDetailAgingReport($fileName, $detailAgingReportData)
    {
        foreach ($detailAgingReportData as $key => $value) {
            $dateDiffPaymentDays = date_diff(
                date_create($value['delivered_date']),
                date_create($value['deposit_date'])
            );
            $dateDiff  = date_diff(
                date_create($value['delivered_date']),
                date_create(date('Y-m-d'))
            );
            array_push($value, $dateDiffPaymentDays->format("%R%a"));
            array_push($value, $dateDiff->format("%R%a"));
            array_push($value, $this->getWeekOfMonth($value['delivered_date']));
            try {
                fputcsv($fileName, $value);
            } catch (Exception $e) {
                $this->createLogFile(
                    $this->ERROR_FILE,
                    $value." not inserted ".$e->getMessage()."\n"
                );
            }
        }
    }

    /**
     * @param $folderName
     *
     */
    public function createFolderDateWise($folderName)
    {
        if (!file_exists($folderName)) {
            mkdir($folderName, $this->FILE_PERMISSIONS, true);
            chmod($folderName, $this->FILE_PERMISSIONS);
        }
    }

    /**
     * @param $fileName
     * @param $errorText
     *
     */
    public function createLogFile($fileName, $errorText)
    {
        $file = null;
        if (!file_exists($fileName)) {
            $file = fopen($fileName, 'w');
        } else {
            $file = fopen($fileName, 'a');
        }

        fwrite($file, $errorText);
    }

    /**
     * @param $message
     *
     * @return bool
     */
    public function logCronCompleted($message)
    {
        $erpCronLogModel = $this->getErpCronLogModel();
        $erpCronLogModel->message = $message;
        $erpCronLogModel->created_at = date('Y-m-d');
        $erpCronLogModel->updated_at = date('Y-m-d');

        if ($erpCronLogModel->save()) {
            return true;
        }

        return false;
    }

    /**
     * @param $csvFile
     *
     * @return array
     */
    protected function readCsv($csvFile)
    {
        $lineText = [];
        $fileHandle = null;

        try {
            $fileHandle = fopen($csvFile, 'r');
        } catch (Exception $e) {
            $this->createLogFile(
                $this->ERROR_FILE,
                $csvFile." file not exist ".$e->getMessage()."\n"
            );
            die;
        }

        $dataLimit = 0;
        $fileData = fgetcsv($fileHandle);
        while (!feof($fileHandle)) {
            $dataLimit ++;
            if (!empty($fileData)) {
                $lineText[] = fgetcsv($fileHandle);
                if (!empty($limit) && ($dataLimit == $limit)) {
                    return $lineText;
                }
            }
        }

        fclose($fileHandle);

        return $lineText;
    }

    /**
     * @return ErpOms
     */
    protected function getErpOmsModel()
    {
        return new ErpOms();
    }

    /**
     * @return ErpReceipts
     */
    protected function getErpReceiptsModel()
    {
        return new ErpReceipts();
    }

    /**
     * @return ErpCronLog
     */
    protected function getErpCronLogModel()
    {
        return new ErpCronLog();
    }

    /**
     * @return ErpDeliveryCompany
     */
    protected function getErpDeliveryCompanyModel()
    {
        return new ErpDeliveryCompany();
    }

    /**
     * @param $trackingNumber
     *
     * @return number
     */
    protected function getErpReceiptsPaidAmount($trackingNumber)
    {
        $receivedAmount = $this->getErpReceiptsModel()->find()
            ->where(['tracking_nr' => $trackingNumber])
            ->all();
        $totalReceivedAmount = 0;

        if (count($receivedAmount) > 0) {
            foreach ($receivedAmount as $data) {
                $totalReceivedAmount += $data['cod_received'];
            }
        }

        return $totalReceivedAmount;
    }

    /**
     * @param $trackingNumber
     *
     * @return number
     */
    protected function getErpOmsTotalAmount($trackingNumber)
    {
        $totalAmount = $this->getErpOmsModel()->find()
            ->where(['tracking_nr' => $trackingNumber])
            ->all();
        $totalAmountPayable = 0;

        if (count($totalAmount) > 0) {

            foreach ($totalAmount as $data) {
                $totalAmountPayable = $data['receivables'];
            }
        }

        return $totalAmountPayable;
    }

    /**
     * @param $trackingNumber
     *
     * @return bool
     */
    protected function checkTrackingNumberExistInErpOms($trackingNumber)
    {
        $trackingNumberExist = $this->getErpOmsModel()->find()
            ->where(['tracking_nr' => $trackingNumber])
            ->one();

        if (count($trackingNumberExist) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $trackingNumber
     *
     * @return array
     */
    protected function getErpOmsDataFromTracking($trackingNumber)
    {
        return $this->getErpOmsModel()->find()
            ->where(['tracking_nr' => $trackingNumber])
            ->one();
    }

    /**
     * @return query
     */
    protected function getQueryModel()
    {
        return new query();
    }

    /**
     * @param $date
     *
     * @return string
     */
    protected function getWeekOfMonth($date)
    {
        list($y, $m, $d) = explode('-', date('Y-m-d', strtotime($date)));
        $w = 1;
        for ($i = 1; $i <= $d; $i++) {
            if ($i > 1 && date('w', strtotime("$y-$m-$i")) == 0) {
                $w++;
            }
        }

        return $w." week of ".date('F', strtotime($date));
    }

    /**
     * @param $currentDate
     * @param $messageId
     *
     * @return bool
     */
    public function checkErpDataInserted($currentDate, $messageId)
    {
        if ($this->getErpCronLogModel()->find()
            ->where(['created_at' => $currentDate])
            ->andWhere(['message' => $messageId])
            ->one()) {

            return true;
        }

        return false;
    }

    /**
     * @param $reportData
     *
     * @return array
     */
    public function setOutstandingAmountEqualToReceivables($reportData)
    {
        foreach ($reportData as $key => $data) {
            if ($reportData[$key]['outstanding_amount'] === null) {
                $reportData[$key]['outstanding_amount'] = $reportData[$key]['receivables'];
            }
        }

        return $reportData;
    }

    /**
     * @param $reportData
     * @param $dataKey
     *
     * @return array
     */
    public function setOutstandingAmount($reportData, $dataKey)
    {
        foreach ($reportData as $key => $data) {
            if ($reportData[$key]['cod_received'] === null) {
                $reportData[$key]['cod_received'] = 0;
                $reportData[$key][$dataKey] = $reportData[$key]['receivables'] -
                    $reportData[$key]['cod_received'];
            }
        }

        $result = [];
        foreach ($reportData as $v) {
            if (!isset($result[$v["name"]])) {
                $result[$v["name"]]["name"] = $v["name"];
                $result[$v["name"]][$dataKey] = $v[$dataKey];
            } else {
                if ($v[$dataKey] > 0) {
                    $result[$v["name"]][$dataKey] += $v[$dataKey];
                }
            }
        }

        $result = array_values($result);

        if (count($result) > 0) {
            foreach ($result as $data) {
                return $data;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllDeliveryCompanyIDs()
    {
        return $this->getErpDeliveryCompanyModel()->find()
            ->select('id_erp_delivery_company, name')
            ->all();
    }
}
