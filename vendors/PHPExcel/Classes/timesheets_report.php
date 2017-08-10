<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';

if(isset($_GET['data_url'])){
      $weekStarting = $_GET['weekStart'];
      $weekEnding = $_GET['weekEnd'];
      $sortBy = $_GET['sortBy'];

      $json = file_get_contents($_GET['data_url']);
      $timesheets = json_decode($json, TRUE);
      //var_dump($timesheets);

      if(isset($sortBy) && $sortBy == "project"){
      	usort($timesheets, function($a, $b){ return strcmp($a["companyName"], $b["companyName"]); });
      }

      $externalTimesheets = [];
      $internalTimesheets = [];

      //Seperate into external and internal
      foreach($timesheets as $key => $val){
      	if($timesheets[$key]['companyID'] !== "0"){
      		$externalTimesheets[] = $val;
      	}
      	else {
      		$internalTimesheets[] = $val;
      	}
      }

      //var_dump($externalTimesheets);

      //var_dump($internalTimesheets);

      // Create new PHPExcel object
      $objPHPExcel = new PHPExcel();

      // Set document properties
      $objPHPExcel->getProperties()->setCreator("JLM Staffing")
      							 ->setLastModifiedBy("Albert Yang")
      							 ->setTitle("Office 2007 XLSX Document")
      							 ->setSubject("Office 2007 XLSX Document")
      							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
      							 ->setKeywords("office 2007 openxml php")
      							 ->setCategory("Result File");

      //Draw Main Header
      $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
      $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Payroll: '.$weekStarting.' - '.$weekEnding);
	  $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
			                    array(
			                        'font'  => array(
			                        		'name' => 'Cambria',
			                                'color' => array('rgb' => '2D2D2D'),
			                                'size' => 28
			                      )
			                    )
			                );
	  $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setIndent(1);

      //Define Columns
      $col = [];

      $col['firstname']['cell'] = "First Name";
      $col['lastname']['cell'] = "Last Name";
      $col['company']['cell'] = "Company";
      $col['payRate']['cell'] = "Pay Rate";
      $col['billRate']['cell'] = "Bill Rate";
      $col['submitted']['cell'] = "Submitted";
      $col['approved']['cell'] = "Approved";
      $col['regular']['cell'] = "Regular";
      $col['ot']['cell'] = "OT";
      $col['dbl']['cell'] = "DOT";
      $col['sickLeave']['cell'] = "Sick Leave";
      $col['holiday']['cell'] = "Holiday";
      $col['jlm']['cell'] = "JLM";
      $col['notes']['cell'] = "Notes";

      $a = 0;
      $alphas = range('A', 'Z');

      $objPHPExcel->setActiveSheetIndex(0);

      //Excel Header
      foreach($col as $key => $value){

            $col[$key]['col'] = $alphas[$a];
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(80);
             $objPHPExcel->getActiveSheet()->getStyle($col[$key]['col'].'1')->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '4e80da')
                        ),
                        'font'  => array(
                                'bold'  => true,
                                'color' => array('rgb' => 'FFFFFF'),
                      )
                    )
                );

            $objPHPExcel->getActiveSheet()
                  ->setCellValue($alphas[$a].'2', $col[$key]['cell']);

           if($key == "received"){
            	$objPHPExcel->getActiveSheet()->getColumnDimension($col[$key]['col'])->setAutoSize(false);
            	$objPHPExcel->getActiveSheet()->getColumnDimension($col[$key]['col'])->setWidth("20");
            }
            else{
            	$objPHPExcel->getActiveSheet()->getColumnDimension($col[$key]['col'])->setAutoSize(true);
        	}

            $objPHPExcel->getActiveSheet()->getStyle($col[$key]['col'].'2')->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '4472C4')
                        ),
                        'font'  => array(
                                'bold'  => true,
                                'color' => array('rgb' => 'FFFFFF'),
                      )
                    )
                );
            $objPHPExcel->getActiveSheet()->getStyle($col[$key]['col'].'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
           // $objPHPExcel->getActiveSheet()->getStyle($alphas[$i].'2')->getAlignment()->setIndent(5);
            $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(40);

            //if($i < 26){
            //      $i = 0;
            //}
            //else {
                  //$i++;
            //}
      		if($a+1 == count($col)){
      			$a = 0;
      		}
      		else {
      			$a++;
      		}
      }

      //Timesheet Info
      $currencyFormat = '_($* #,##0.00_);_($* (#,##0.00);_($* "-"??_);_(@_)';

      $startRow = 3;
      $odd = 0;

      $i = $startRow;
      $continue = true;
      $round = 1;
      $internalOrExternal = "external";
      $TotalReg = "0";
      $TotalOT = "0";
      $TotalDbl = "0";
      //$Total

      while($continue){
      	if($round == 2){ 
      			$internalOrExternal = "internal"; $i += 5;
      			//Excel Header For Internal
			      foreach($col as $key => $value){

			            $col[$key]['col'] = $alphas[$a];
			            $objPHPExcel->getActiveSheet()
			                  ->setCellValue($col[$key]['col'].$i, $col[$key]['cell']);

			            $objPHPExcel->getActiveSheet()->getStyle($col[$key]['col'].$i)->applyFromArray(
			                    array(
			                        'fill' => array(
			                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
			                            'color' => array('rgb' => '4472C4')
			                        ),
			                        'font'  => array(
			                                'bold'  => true,
			                                'color' => array('rgb' => 'FFFFFF'),
			                      )
			                    )
			                );

			            $objPHPExcel->getActiveSheet()->getStyle($col[$key]['col'].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(40);

			            if($a+1 == count($alphas)){
			      			$a = 0;
			      		}
			      		else {
			      			$a++;
			      		}
			      }

			      $i++;
      		} 
	      foreach(${$internalOrExternal.'Timesheets'} as $key => $val){
	            //echo $timesheets[$key]['firstname'];     

	            $objPHPExcel->getActiveSheet()
	                  ->setCellValue($col['firstname']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['firstname'])                 
	                  ->setCellValue($col['lastname']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['lastname'])
	                  ->setCellValue($col['company']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['companyName']);

	                  	if($internalOrExternal == "external"){
	                  		$objPHPExcel->getActiveSheet()->setCellValue($col['payRate']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['quickbooks']['payRate'])->setCellValue($col['billRate']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['quickbooks']['billRate']);
	              		}
	              		else {
	              			$objPHPExcel->getActiveSheet()->setCellValue($col['payRate']['col'].$i, "--")->setCellValue($col['billRate']['col'].$i, "--");
	              		}

                    //var_dump(strtolower(${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['status']));

                      if(isset(${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['timeSubmitted']) && ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['timeSubmitted'] !== "--"){ $submitted = "x"; } else { $submitted = "--"; }
                      if(isset(${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['timeApproved']) && ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['timeApproved'] !== "--"){ $approved = "x"; } else { $approved = "--"; }


              				$objPHPExcel->getActiveSheet()->setCellValue($col['submitted']['col'].$i, $submitted);
              				$objPHPExcel->getActiveSheet()->setCellValue($col['approved']['col'].$i, $approved);


	                 //echo "<br/>count: ".count(${$internalOrExternal.'Timesheets'});
	                 // echo "<br/>key: ".$key;
	 
	                  	

	            if($odd == 0){
	            	$bgColor = "D9E1F2";
	            	$odd++;
	            }
	            else {
	            	$bgColor = "B4C6E7";
	            	$odd = 0;
	            }

		          foreach($col as $colKey => $colVal){
		                $objPHPExcel->getActiveSheet()->getStyle($col[$colKey]['col'].$i)->applyFromArray(
		                  array(
		                      'fill' => array(
		                          'type' => PHPExcel_Style_Fill::FILL_SOLID,
		                          'color' => array('rgb' => $bgColor)
		                      ),
		                      'font'  => array(
		                              'bold'  => false,
		                              'color' => array('rgb' => '000000'),
		                       'borders' => array(
		                              'left' => array(
		                                  'style' => PHPExcel_Style_Border::BORDER_THIN,
		                                  'color' => array('rgb' => '766F6E')
		                               ),
		                              'right' => array(
		                                  'style' => PHPExcel_Style_Border::BORDER_THIN,
		                                  'color' => array('rgb' => '766F6E')
		                               )
		                      )
		                    )
		                  )
		              );    
		          }



              if(!empty(${$internalOrExternal.'Timesheets'}[$key]['timesheets'])){                  



  	            if(${$internalOrExternal.'Timesheets'}[$key]['state'] == "ca"){
                  //echo "<br/>top: ".${$internalOrExternal.'Timesheets'}[$key]['firstname'];
  	                  $objPHPExcel->getActiveSheet()->setCellValue($col['regular']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg'])
  	                  ->setCellValue($col['ot']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'])
  	                  ->setCellValue($col['dbl']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl']);

  	                  $TotalReg += ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg'];
  	                  $TotalOT += ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'];
  	                  $TotalDbl += ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl'];
  	            }
  	            else if(${$internalOrExternal.'Timesheets'}[$key]['state'] == "summary"){
                                    //echo "<br/>bottom: ".${$internalOrExternal.'Timesheets'}[$key]['firstname'];
  	            	$TotalReg += ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['TotalWorkTime'];

                  //If over 40 then OT
                  $TotalWorkTime = ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['TotalWorkTime'];

                  if($TotalWorkTime > 40){
                    $regWorkTime = 40;
                    $otWorkTime = $TotalWorkTime - 40;
                    $objPHPExcel->getActiveSheet()->setCellValue($col['regular']['col'].$i, 40);
                    $objPHPExcel->getActiveSheet()->setCellValue($col['ot']['col'].$i, $otWorkTime);
                  }
                  else {
  	                  $objPHPExcel->getActiveSheet()->setCellValue($col['regular']['col'].$i, ${$internalOrExternal.'Timesheets'}[$key]['timesheets'][0]['TotalWorkTime']);
  	               }
                }
              }
        	    if($internalOrExternal == "external" && count(${$internalOrExternal.'Timesheets'}) == $key+1){
              		$objPHPExcel->getActiveSheet()->setCellValue($col['submitted']['col'].($i+1), "EXTERNAL");
              		$objPHPExcel->getActiveSheet()->setCellValue($col['approved']['col'].($i+1), "=");
              		$objPHPExcel->getActiveSheet()->getStyle($col['approved']['col'].($i+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

              		$objPHPExcel->getActiveSheet()
				          ->setCellValue($col['regular']['col'].($i+1),
				          '=SUM('.$col['regular']['col'].$startRow.':'.$col['regular']['col'].$i.')');

        				  $objPHPExcel->getActiveSheet()
        				  ->setCellValue($col['ot']['col'].($i+1),
                  '=SUM('.$col['ot']['col'].$startRow.':'.$col['ot']['col'].$i.')');

        				  $objPHPExcel->getActiveSheet()
        				  ->setCellValue($col['dbl']['col'].($i+1),
        				  '=SUM('.$col['dbl']['col'].$startRow.':'.$col['dbl']['col'].$i.')');

              		//$objPHPExcel->getActiveSheet()->setCellValue($col['regular']['col'].($i+1), $TotalReg);
              		//$objPHPExcel->getActiveSheet()->setCellValue($col['ot']['col'].($i+1), $TotalOT);
              		//$objPHPExcel->getActiveSheet()->setCellValue($col['dbl']['col'].($i+1), $TotalDbl);

              		$objPHPExcel->getActiveSheet()->setCellValue($col['submitted']['col'].($i+3), "Total External(Regular,OT\r,DOT,Sick,Holiday,Training)");
              		$objPHPExcel->getActiveSheet()->getStyle($col['submitted']['col'].($i+3))->getAlignment()->setWrapText(true);
					        $objPHPExcel->getActiveSheet()->setCellValue($col['approved']['col'].($i+3), "=");
        					$objPHPExcel->getActiveSheet()->getStyle($col['approved']['col'].($i+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        					$objPHPExcel->getActiveSheet()->setCellValue($col['regular']['col'].($i+3), '=SUM('.$col['regular']['col'].($i+1).':'.$col['holiday']['col'].($i+1).')');
					
              	}

	            $objPHPExcel->getActiveSheet()->getStyle($col['billRate']['col'].$i)->getNumberFormat()->setFormatCode($currencyFormat);
	            $objPHPExcel->getActiveSheet()->getStyle($col['payRate']['col'].$i)->getNumberFormat()->setFormatCode($currencyFormat);
	            $i++;
	            //echo $timesheets[$key]['timesheets'][0];

	            
	      }	

	    if($round == 2){ 
			$continue = false;
		}

		$round = 2;

	   }

      // Rename worksheet
      $objPHPExcel->getActiveSheet()->setTitle('Week '.$weekStarting.' - '.$weekEnding);


      // Set active sheet index to the first sheet, so Excel opens this as the first sheet
      $objPHPExcel->setActiveSheetIndex(0);


      // Redirect output to a client’s web browser (Excel2007)
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="01simple.xlsx"');
      header('Cache-Control: max-age=0');
      // If you're serving to IE 9, then the following may be needed
      header('Cache-Control: max-age=1');

      // If you're serving to IE over SSL, then the following may be needed
      header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
      header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
      header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
      header ('Pragma: public'); // HTTP/1.0

      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setPreCalculateFormulas(TRUE);
      $objWriter->save('php://output');

}

exit;
