<?php

namespace app\controllers;

ini_set("max_execution_time", 0);
ini_set('memory_limit', '-1');

use Yii;
use yii\db\Exception;
use yii\web\Controller;
use app\models\Erp;

class ErpController extends Controller
{
    public function actionIndex()
    {
        $erpModel = new Erp();

        //For Eight Days Aging Report
        $currentDate = date("Y-m-d");

        $erpReceiptsData = $erpModel->checkErpDataInserted($currentDate, $erpModel->ERP_RECEIPTS_DATA_INSERTION);
        $erpOmsData = $erpModel->checkErpDataInserted($currentDate, $erpModel->ERP_OMS_DATA_INSERTION);

        if ($erpReceiptsData && $erpOmsData) {

            $eightDays = $erpModel->getPreviousDays($currentDate, $erpModel->EIGHT_DAYS);

            //For 6 Days Aging Report
            $startDateSixDaysReport = $erpModel->getPreviousDays($eightDays, $erpModel->ONE_DAY);
            $sixDays = $erpModel->getPreviousDays($startDateSixDaysReport, $erpModel->SIX_DAYS);

            //For 30 Days Aging Report
            $startDateThirtyDaysReport = $erpModel->getPreviousDays($sixDays, $erpModel->ONE_DAY);
            $thirtyDays = $erpModel->getPreviousDays($startDateThirtyDaysReport, $erpModel->FIFTEEN_DAYS);

            //For 60 Days Aging Report
            $startDateSixtyDaysReport = $erpModel->getPreviousDays($thirtyDays, $erpModel->ONE_DAY);
            $sixtyDays = $erpModel->getPreviousDays($startDateSixtyDaysReport, $erpModel->SIXTY_DAYS);

            //For 90 Days Aging Report
            $startDateNinetyDaysReport = $erpModel->getPreviousDays($sixtyDays, $erpModel->ONE_DAY);
            $ninetyDays = $erpModel->getPreviousDays($startDateNinetyDaysReport, $erpModel->NINETY_DAYS);

            //For 180 Days Aging Report
            $startDateOneEightyDaysReport = $erpModel->getPreviousDays($ninetyDays, $erpModel->ONE_DAY);
            $hundredEightyDays = $erpModel->getPreviousYear($currentDate, $erpModel->ONE_DAY);

            $previousYear = $erpModel->getPreviousYear($currentDate, $erpModel->PREVIOUS_ONE_YEAR);

            //For days above one year
            $greaterThanThreeSixFive = $erpModel->getPreviousDays($previousYear, $erpModel->ONE_DAY);

            //8 Days Aging Report Outstanding Amount
            $erpReportEight = $erpModel->getAgingReport($eightDays, $currentDate, 'a');

            //Next 6 Days Aging Report Outstanding Amount
            $erpReportNextEight = $erpModel->getAgingReport(
                $sixDays,
                $startDateSixDaysReport,
                'b'
            );

            //Next 15 Days Aging Report Outstanding Amount
            $erpReportFifteen = $erpModel->getAgingReport(
                $thirtyDays,
                $startDateThirtyDaysReport,
                'c'
            );

            //Next 60 Days Aging Report Outstanding Amount
            $erpReportSixty = $erpModel->getAgingReport(
                $sixtyDays,
                $startDateSixtyDaysReport,
                'd'
            );

            //Next 90 Days Aging Report Outstanding Amount
            $erpReportNinety = $erpModel->getAgingReport(
                $ninetyDays,
                $startDateNinetyDaysReport,
                'e'
            );

            //Next 180 Days Aging Report Outstanding Amount
            $erpReportOneEighty = $erpModel->getAgingReport(
                $hundredEightyDays,
                $startDateOneEightyDaysReport,
                'f'
            );

            //Older than 365 Days Aging Report Outstanding Amount
            $erpReportOlderThanYear = $erpModel->getOldAgingReport(
                $greaterThanThreeSixFive,
                'g'
            );

            $agingReport = array_merge_recursive(
                $erpReportEight,
                $erpReportNextEight,
                $erpReportFifteen,
                $erpReportSixty,
                $erpReportNinety,
                $erpReportOneEighty,
                $erpReportOlderThanYear
            );

            //Create Separate Folder for all dates
            $erpFolderName = 'ErpReport-' . $currentDate;

            //Creating Aging Report For Outstanding Amount
            if (count($agingReport) > 0) {
                $file = null;

                //Create Folder if not Exist
                $erpModel->createFolderDateWise($erpFolderName);
                $erpReportFileName = $erpFolderName . '/' . $erpModel->ERP_REPORT;

                try {
                    $file = fopen($erpReportFileName, 'w');
                    chmod($erpReportFileName, $erpModel->FILE_PERMISSIONS);
                } catch (Exception $e) {
                    $erpModel->createLogFile(
                        $erpModel->ERROR_FILE,
                        $erpReportFileName . " not created " . $e->getMessage() . "\n"
                    );
                }

                if (file_exists($erpReportFileName)) {
                    $erpModel->createAgingReportCsvFileHeader($file, [
                        'Delivery Company' => 'Company',
                        '1-8' => '8 Days (' . $eightDays . '-' . $currentDate . ')',
                        '9-15' => '7 Days (' . $sixDays . '-' . $startDateSixDaysReport . ')',
                        '16-30' => '15 Days (' . $thirtyDays . '-' . $startDateThirtyDaysReport . ')',
                        '31-90' => '60 Days (' . $sixtyDays . '-' . $startDateSixtyDaysReport . ')',
                        '91-180' => '90 Days (' . $ninetyDays . '-' . $startDateNinetyDaysReport . ')',
                        '181-365' => '180 Days (' . $hundredEightyDays . '-' . $startDateOneEightyDaysReport . ')',
                        'more365' => '> 365 Days',
                        'Outstanding' => 'Outstanding Amount'
                    ]);

                    //Creating Aging Report For Outstanding Amount
                    $erpModel->createAgingReportCsvFile($file, $agingReport);
                    fclose($file);
                }
            }

            //Detailed Aging Report

            //8 Days Aging Report Outstanding Amount
            $erpDetailReportEight = $erpModel->getDetailAgingReport($eightDays, $currentDate);

            //Next 6 Days Aging Report Outstanding Amount
            $erpDetailReportNextEight = $erpModel->getDetailAgingReport(
                $sixDays,
                $startDateSixDaysReport
            );

            //Next 15 Days Aging Report Outstanding Amount
            $erpDetailReportFifteen = $erpModel->getDetailAgingReport(
                $thirtyDays,
                $startDateThirtyDaysReport
            );

            //Next 60 Days Aging Report Outstanding Amount
            $erpDetailReportSixty = $erpModel->getDetailAgingReport(
                $sixtyDays,
                $startDateSixtyDaysReport
            );

            //Next 90 Days Aging Report Outstanding Amount
            $erpDetailReportNinety = $erpModel->getDetailAgingReport(
                $ninetyDays,
                $startDateNinetyDaysReport
            );

            //Next 180 Days Aging Report Outstanding Amount
            $erpDetailReportOneEighty = $erpModel->getDetailAgingReport(
                $hundredEightyDays,
                $startDateOneEightyDaysReport
            );

            $detailAgingReport = array_merge_recursive(
                $erpDetailReportEight,
                $erpDetailReportNextEight,
                $erpDetailReportFifteen,
                $erpDetailReportSixty,
                $erpDetailReportNinety,
                $erpDetailReportOneEighty
            );

            //Creating Detail Aging Report For Outstanding Amount
            if (count($detailAgingReport) > 0) {
                $detailAgingReportFile = null;

                //Create Folder if not Exist
                $erpModel->createFolderDateWise($erpFolderName);
                $detailAgingReportFileName = $erpFolderName . '/' . $erpModel->DETAIL_AGING_REPORT;

                try {
                    $detailAgingReportFile = fopen($detailAgingReportFileName, 'w');
                    chmod($detailAgingReportFileName, $erpModel->FILE_PERMISSIONS);
                } catch (Exception $e) {
                    $erpModel->createLogFile(
                        $erpModel->ERROR_FILE,
                        $detailAgingReportFileName . " not created " . $e->getMessage() . "\n"
                    );
                }

                if (file_exists($detailAgingReportFileName)) {
                    $erpModel->createDetailAgingReportCsvFileHeader($detailAgingReportFile, [
                        'tracking' => 'Tracking Number',
                        'receivables' => 'Amount Receivables',
                        'received' => 'Amount Received',
                        'outstanding' => 'Outstanding Amount',
                        'deposit_date' => 'Deposit Date',
                        'company' => 'Company Name',
                        'order' => 'Order Number',
                        'city' => 'City Name',
                        'delivery_date' => 'Delivery Date',
                        'package_nr' => 'Package Number',
                        'payment_days' => 'Payment Days',
                        'days' => 'Reporting Days',
                        'week_wise' => 'Week Wise Outstanding'
                    ]);

                    //Creating Detail Aging Report For Outstanding Amount
                    $erpModel->createDetailAgingReport($detailAgingReportFile, $detailAgingReport);
                    fclose($detailAgingReportFile);
                }
            }

            //Aging Report For Internal

            //60 Days for internal aging report
            $sixtyDaysInternal = $erpModel->getPreviousDays($currentDate, $erpModel->SIXTY_DAYS);

            //For 120 Days Aging Report
            $startDateSixtyDaysReportInternal = $erpModel->getPreviousDays($sixtyDaysInternal, $erpModel->ONE_DAY);
            $oneTwentyDaysInternal = $erpModel->getPreviousDays(
                $startDateSixtyDaysReportInternal,
                $erpModel->ONE_TWENTY_DAYS
            );

            //For previous Days to current date previous year
            $startDateOneEightyDaysReportInternal = $erpModel->getPreviousDays(
                $oneTwentyDaysInternal,
                $erpModel->ONE_DAY
            );

            //For days above one year
            $oldDate = $erpModel->getPreviousDays($previousYear, $erpModel->ONE_DAY);

            //First 60 Days Internal Aging Report
            $erpReportSixtyInternal = $erpModel->getAgingReport(
                $sixtyDaysInternal,
                $currentDate,
                'a'
            );

            //120 Days Internal Aging Report
            $erpReportOneHundredTwentyInternal = $erpModel->getAgingReport(
                $oneTwentyDaysInternal,
                $startDateSixtyDaysReportInternal,
                'b'
            );

            $erpReportOneHundredEightyInternal = $erpModel->getAgingReport(
                $previousYear,
                $startDateOneEightyDaysReportInternal,
                'c'
            );

            $erpReportOldInternal = $erpModel->getOldAgingReport(
                $oldDate,
                'd'
            );

            $agingReportInternal = array_merge_recursive(
                $erpReportSixtyInternal,
                $erpReportOneHundredTwentyInternal,
                $erpReportOneHundredEightyInternal,
                $erpReportOldInternal
            );

            //Creating Aging Report Internal For Outstanding Amount
            if (count($agingReportInternal) > 0) {
                $agingReportFileInternal = null;

                //Create Folder if not Exist
                $erpModel->createFolderDateWise($erpFolderName);
                $agingReportInternalFileName = $erpFolderName . '/' . $erpModel->AGING_REPORT_INTERNAL;

                try {
                    $agingReportFileInternal = fopen($agingReportInternalFileName, 'w');
                    chmod($agingReportInternalFileName, $erpModel->FILE_PERMISSIONS);
                } catch (Exception $e) {
                    $erpModel->createLogFile(
                        $erpModel->ERROR_FILE,
                        $agingReportInternalFileName . " not created " . $e->getMessage() . "\n"
                    );
                }

                if (file_exists($agingReportInternalFileName)) {
                    $erpModel->createAgingReportInternalCsvFileHeader($agingReportFileInternal, [
                        'Delivery Company' => 'Company',
                        '1-60' => '60 Days (' . $sixtyDaysInternal . '-' . $currentDate . ')',
                        '61-180' => '120 Days (' . $oneTwentyDaysInternal . '-'
                            . $startDateSixtyDaysReportInternal . ')',
                        '181-365' => '180 Days (' . $previousYear . '-' . $startDateOneEightyDaysReportInternal . ')',
                        'more365' => '> 365 Days',
                        'Outstanding' => 'Outstanding Amount'
                    ]);

                    //Creating Aging Report For Internal Use of Outstanding Amount
                    $erpModel->createAgingReportCsvFileInternal($agingReportFileInternal, $agingReportInternal);
                    fclose($agingReportFileInternal);
                }
            }

            if (!$erpModel->logCronCompleted($erpModel->ERP_REPORT_GENERATION)) {
                $erpModel->createLogFile($erpModel->ERROR_FILE, 'Log Data for Reporting Failed' . "\n");
            }
        }
        die;
    }

    public function actionInsert()
    {
        $erpModel = new Erp();
        if ($erpModel->insertErpReceiptsData('ErpReceipts.csv')) {
            if (!$erpModel->logCronCompleted($erpModel->ERP_RECEIPTS_DATA_INSERTION)) {
                $erpModel->createLogFile($erpModel->ERROR_FILE, 'Log Erp Receipts Data insertion Failed'."\n");
            }
        }

        if ($erpModel->insertErpOmsData('ErpOms.csv')) {
            if (!$erpModel->logCronCompleted($erpModel->ERP_OMS_DATA_INSERTION)) {
                $erpModel->createLogFile($erpModel->ERROR_FILE, 'Log Erp OMS Data insertion Failed'."\n");
            }
        }
    }
}
