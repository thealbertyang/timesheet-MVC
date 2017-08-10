<?php 

class ApiModel extends Model {

	public function __construct($foo = null){
		parent::__construct();	
	}

	function formatDate($date){
		$date= new DateTime(str_replace("-","/",$date));
		$date = $date->format("n/d/y");

		return $date;	
	}

	function formatDateForLink($date,$type = NULL){
		if($type == "/"){
			$date= new DateTime(str_replace("-","/",$date));
			$date = $date->format("m/d/Y");
			return $date;	
		}
		else if($type == "-"){
			$date= new DateTime(str_replace("-","/",$date));
			$date = $date->format("m-d-Y");
			return $date;
		}
		else {
			$date= new DateTime(str_replace("-","/",$date));
			$date = $date->format("m-d-Y");
			return $date;	
		}

	}

	function formatDateForJS($date){
		$date= new DateTime(str_replace("-","/",$date));
		$date = $date->format("n/d/Y");

		return $date;	
	}

	function getLastSunday(){
		$days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
		$daysInNumberFormat = array();
		$disabled = false;
		$prevSun = abs(strtotime("previous sunday"));
	    $currentDate = abs(strtotime("today"));
	    $seconds = 86400; //86400 seconds in a day
	 
	    $dayDiff = ceil( ($currentDate-$prevSun)/$seconds ); 
	 
	    if( $dayDiff < 7 ){
	        $dayDiff += 1; //if it's sunday the difference will be 0, thus add 1 to it
	        $prevSun = strtotime( "previous sunday", strtotime("-$dayDiff day") );
	    }
	 
	  	$prevSun = date("n/j/y",$prevSun);

	  	return $prevSun;		 
	}

	function getLastSaturday(){
		$prevSun = new DateTime($this->getLastSunday());
		$prevSat = $prevSun->modify("+6 day");
		$prevSat = $prevSun->format("n/j/y");

		return $prevSat;
	}

	function getThisSunday(){
		$days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
		$daysInNumberFormat = array();
		$disabled = false;
		$prevSun = abs(strtotime("sunday last week"));
	    $currentDate = abs(strtotime("today"));
	    $seconds = 86400; //86400 seconds in a day
	 
	    $dayDiff = ceil( ($currentDate-$prevSun)/$seconds ); 
	 
	    if( $dayDiff < 7 ){
	        $dayDiff += 1; //if it's sunday the difference will be 0, thus add 1 to it
	        $prevSun = strtotime( "sunday this week", strtotime("-$dayDiff day") );
	    }
	 
	  	$prevSun = date("n/j/y",$prevSun);

	  	return $prevSun;		 
	}

	function getThisSaturday(){
		$prevSun = new DateTime($this->getThisSunday());
		$prevSat = $prevSun->modify("+6 day");
		$prevSat = $prevSun->format("n/j/y");

		return $prevSat;
	}

	function listCompany($id){
		$this->db->query("SELECT * FROM companies WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->fetchOne();

		if(isset($queryResults['projects'])){
			$queryResults['projects'] = json_decode($queryResults['projects'], true);
		}

		return $queryResults;
	}

	function listCompanies(){
		$this->db->query("SELECT * FROM companies");
		$queryResults = $this->db->fetchAll();

		return $queryResults;
	}

	function listSupervisor($employeeID){
		$this->db->query("SELECT * FROM supervisors WHERE ID = :id");
		$this->db->bind(":id", $employeeID);
		$queryResults = $this->db->fetchOne();

		$supervisorsList = $this->listSupervisors();
		$projectsList = $this->listProjects();

		$queryResults['projects'] = $this->listProjectsOfSupervisor($employeeID);
		return $queryResults;
	}

	function listSupervisors(){
		$this->db->query("SELECT * FROM supervisors");
		$queryResults = $this->db->fetchAll();

		return $queryResults;
	}

	function listProjectsOfSupervisor($id){
		$projectsList = $this->listProjects();
		$supervisorsList = $this->listSupervisors();
		$return = [];

		if(isset($projectsList) && !empty($projectsList)){
			foreach($projectsList as $projectKey => $projectVal){
				$supervisors = $projectsList[$projectKey]['supervisors'];
				if(isset($supervisors) && !empty($supervisors)){
					foreach($supervisors as $supervisorsID) {
						if($supervisorsID === $id){
						 $return[] = $projectsList[$projectKey]['ID'];
						}
					}
				}
			}
		}

		return $return;

	}

	function listProject($id){
		$this->db->query("SELECT * FROM projects WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->fetchOne();

		//json decode supervisors
		foreach ($queryResults as $key => $value){
			if(isset($queryResults[$key]['supervisors'])){
				$queryResults[$key]['supervisors'] = json_decode($queryResults[$key]['supervisors'], true);
			}
		}

		//json decode employees
		foreach ($queryResults as $key => $value){
			if(isset($queryResults[$key]['employees'])){
				$queryResults[$key]['employees'] = json_decode($queryResults[$key]['employees'], true);
			}
		}

		return $queryResults;
	}

	function listProjects(){
		$this->db->query("SELECT * FROM projects");
		$queryResults = $this->db->fetchAll();

		//json decode supervisors
		foreach ($queryResults as $key => $value){
			$queryResults[$key]['supervisors'] = json_decode($queryResults[$key]['supervisors'], true);
		}

		//json decode employees
		foreach ($queryResults as $key => $value){
			$queryResults[$key]['employees'] = json_decode($queryResults[$key]['employees'], true);
		}

		return $queryResults;
	}

	function listProjectsInCompany($companyID){

		$listProjects = $this->listProjects();
		$projects = []; 

		foreach($listProjects as $projectKey => $projectVal){
			if($listProjects[$projectKey]['company'] == $companyID){
				$projects[] = $listProjects[$projectKey];
			}
		}

		//var_dump($projects);
		$queryResults = $projects;

		return $queryResults;
	}

	function downloadPDF(){

	}

	function calcCaTimesheet($timesheet){

		//var_dump($timesheet['timesheets']);

		$days = array("sunday","monday","tuesday","wednesday","thursday","friday","saturday");
		

		foreach($timesheet['timesheets'] as $timesheetKey => $timesheetVal){
			$i = 0;
			$caTimesheetHours = [];
			$caTimesheetHours['TotalWorkTimeReg'] = 0;
			$caTimesheetHours['TotalWorkTimeOT'] = 0;
			$caTimesheetHours['TotalWorkTimeDbl'] = 0;
			$caTimesheetHours['TotalBreaksTime'] = 0;

			$oldTotalWorkTimeReg = 0;

			while($i < 7){
				${$days[$i].'WorkTime'} = $timesheet['timesheets'][$timesheetKey][$days[$i].'WorkTime'];

				if(${$days[$i].'WorkTime'} > 12){
					$caTimesheetHours[$days[$i].'WorkTimeReg'] = number_format(8.00, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeDbl'] = number_format(${$days[$i].'WorkTime'} - 12, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeOT'] =  number_format(${$days[$i].'WorkTime'} - 8.00 - $caTimesheetHours[$days[$i].'WorkTimeDbl'], 2, '.', '');
				}
				else if(${$days[$i].'WorkTime'} > 8){
					$caTimesheetHours[$days[$i].'WorkTimeReg'] = number_format(8.00, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeDbl'] = number_format(0.00, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeOT'] = number_format(${$days[$i].'WorkTime'} - 8.00,2, '.', '');
				}
				else {
					if(${$days[$i].'WorkTime'} == 0 || !isset(${$days[$i].'WorkTime'})){
						${$days[$i].'WorkTime'} = number_format(0.00, 2, '.', '');
					}

					$caTimesheetHours[$days[$i].'WorkTimeReg'] = number_format(${$days[$i].'WorkTime'}, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeDbl'] = number_format(0.00, 2, '.', '');
					$caTimesheetHours[$days[$i].'WorkTimeOT'] = number_format(0.00, 2, '.', '');
				}

				//Lunch Break
				${$days[$i].'BreaksHr'} = $timesheet['timesheets'][$timesheetKey][$days[$i].'BreaksHr'];
				${$days[$i].'BreaksMin'} = $timesheet['timesheets'][$timesheetKey][$days[$i].'BreaksMin'];
				${$days[$i].'BreaksTime'} = (${$days[$i].'BreaksHr'} + (${$days[$i].'BreaksMin'} / 60));
				$caTimesheetHours[$days[$i].'BreaksTime'] = number_format(${$days[$i].'BreaksTime'},2, '.', '');


				//If over 40 hours
				$oldTotalWorkTimeReg += $caTimesheetHours[$days[$i].'WorkTimeReg'];


				if($oldTotalWorkTimeReg > 40 && $caTimesheetHours[$days[$i].'WorkTimeReg'] > 0){
					//If it's under or equal to 48 then adjust for that date
					if(($oldTotalWorkTimeReg - 40) <= 8){
						$caTimesheetHours[$days[$i].'WorkTimeReg'] = $caTimesheetHours[$days[$i].'WorkTimeReg'] - ($oldTotalWorkTimeReg - 40);
						$caTimesheetHours[$days[$i].'WorkTimeOT'] += ($oldTotalWorkTimeReg - 40);
					}
					else if($oldTotalWorkTimeReg > 48){
						$caTimesheetHours[$days[$i].'WorkTimeReg'] = 0;
						$caTimesheetHours[$days[$i].'WorkTimeOT'] += 8;
					}
				}

				//Consecutive 7th day first eight hours: OT, & Consecutive 7th day excess hours after eight: DblTime
				if(($i == 6) && ($sundayWorkTime > 0) && ($mondayWorkTime > 0) && ($tuesdayWorkTime > 0) && ($wednesdayWorkTime > 0) && ($thursdayWorkTime > 0) && ($fridayWorkTime > 0) && ($saturdayWorkTime > 0)){

						$oldWorkTimeReg = $caTimesheetHours['saturdayWorkTimeReg'] + $caTimesheetHours['saturdayWorkTimeOT'] + $caTimesheetHours['saturdayWorkTimeDbl'];

						if($oldWorkTimeReg > 8){
							$caTimesheetHours['saturdayWorkTimeDbl'] = $oldWorkTimeReg - 8;
							$caTimesheetHours['saturdayWorkTimeOT'] = $oldWorkTimeReg - $caTimesheetHours['saturdayWorkTimeDbl'];
						}
						else if($oldWorkTimeReg <= 8){
							$caTimesheetHours['saturdayWorkTimeOT'] = $oldWorkTimeReg;
						}
				}

				//Add day to total time everytime we loop day
				$caTimesheetHours['TotalWorkTimeReg'] += $caTimesheetHours[$days[$i].'WorkTimeReg'];
				$caTimesheetHours['TotalWorkTimeOT'] += $caTimesheetHours[$days[$i].'WorkTimeOT'];
				$caTimesheetHours['TotalWorkTimeDbl'] += $caTimesheetHours[$days[$i].'WorkTimeDbl'];
				$caTimesheetHours['TotalBreaksTime'] += $caTimesheetHours[$days[$i].'BreaksTime'];

				$timesheet['timesheets'][$timesheetKey]['caTimesheetHours'] = $caTimesheetHours;

				$i++;
			}

			$timesheet['timesheets'][$timesheetKey]['caTimesheetHours']['TotalWorkTimeReg'] = number_format($caTimesheetHours['TotalWorkTimeReg'], 2, '.', '');
			$timesheet['timesheets'][$timesheetKey]['caTimesheetHours']['TotalWorkTimeOT'] = number_format($caTimesheetHours['TotalWorkTimeOT'], 2, '.', '');
			$timesheet['timesheets'][$timesheetKey]['caTimesheetHours']['TotalWorkTimeDbl'] = number_format($caTimesheetHours['TotalWorkTimeDbl'], 2, '.', '');
			$timesheet['timesheets'][$timesheetKey]['caTimesheetHours']['TotalBreaksTime'] = number_format($caTimesheetHours['TotalBreaksTime'], 2, '.', '');
		}

		return $timesheet;

		//var_dump($timesheet);
	}

	function timesheets($employeeID, $timesheetToken, $timesheetSundayDate, $approval){

			$timesheetSundayDate = str_replace("-","/",$timesheetSundayDate);
			$listTimesheetByDate = $this->listTimeSheetByDate($employeeID, $timesheetSundayDate);

			if($listTimesheetByDate['status'] !== "approved" && $listTimesheetByDate['status'] !== "disapproved"){
				if(isset($listTimesheetByDate['token']) && $listTimesheetByDate['token'] == $timesheetToken){
					if($approval == "approve") { 

						$listTimesheetByDate['token'] = "";
						$listTimesheetByDate['status'] = "approved"; 
						$listTimesheetByDate['timeApproved'] = date('n/j/Y h:i A', time());

						$saveTimesheets = $this->saveTimesheets($employeeID, $listTimesheetByDate);

						if($saveTimesheets){
							return true;
						}
						else {
							return false;
						}
					}
					else if($approval == "disapprove") { 
						$listTimeSheetByDate['token'] = "";
						$listTimesheetByDate['status'] = "disapproved"; 
						$saveTimesheets = $this->saveTimesheets($employeeID, $listTimesheetByDate);
						if($saveTimesheets){
							return true;
						}
						else {
							return false;
						}
					}
				}
			}
			else {
				return false;
			}

	}

	function timesheetReportDownload($type = NULL, $data_url = NULL){
		if($type == "excel"){
			$this->request->reRoute("/vendors/PHPExcel/Classes/timesheets_report.php?data_url=".$data_url);
		}
	}

	function filterTimesheetsby($type,$value,$includeType,$employeesList){
		$return = [];
		if($type == "date"){
			foreach($employeesList as $employeeKey => $employeeVal){
				//Don't show admin
				if(isset($employeesList[$employeeKey]['ID']) == "0"){
					unset($employeesList[$employeeKey]);
				}

				//Fill in empty timesheets if all (For Excel)
				if($includeType == "all"){
					$days = array("sunday","monday","tuesday","wednesday","thursday","friday","saturday");
					$labelReg =  array("WorkTime","BreaksHr","BreaksMin");

					//Check if date matches
					if(isset($employeesList[$employeeKey]['timesheets']) && !empty($employeesList[$employeeKey]['timesheets'])){
						foreach($employeesList[$employeeKey]['timesheets'] as $timesheetKey => $timesheetVal){
							$timesheet = $employeesList[$employeeKey]['timesheets'][$timesheetKey];
							if(isset($timesheet['sundayWorkDate']) && $timesheet['sundayWorkDate'] !== $this->formatDateForLink($value,"/")){
								unset($employeesList[$employeeKey]['timesheets'][$timesheetKey]);
							}
						}
					}

					$i = 0;
					while($i < 7){

						$z = 0;
						while($z < count($labelReg)){
							if(empty($employeesList[$employeeKey]['timesheets'][0][$days[$i].$labelReg[$z]])){
								$employeesList[$employeeKey]['timesheets'][0][$days[$i].$labelReg[$z]] = 0;
							}
							$z++;
						}

						$i++;
					}



					if(!isset($employeesList[$employeeKey]['timesheets'][0])){
						$employeesList[$employeeKey]['timesheets'][0]['TotalWorkTime'] = 0;
						$employeesList[$employeeKey]['timesheets'][0]['supervisor'] = "";
						$employeesList[$employeeKey]['timesheets'][0]['status'] = "";
						$employeesList[$employeeKey]['timesheets'][0]['timeApproved'] = "";
						$employeesList[$employeeKey]['timesheets'][0]['timeSubmitted'] = "";
						$employeesList[$employeeKey]['timesheets'][0]['supervisor'] = "";
					}
				}
				else { //Not include all

					//If employee has timesheet
					if(isset($employeesList[$employeeKey]['timesheets']) && !empty($employeesList[$employeeKey]['timesheets'])){
						foreach($employeesList[$employeeKey]['timesheets'] as $timesheetKey => $timesheetVal){
							$timesheet = $employeesList[$employeeKey]['timesheets'][$timesheetKey];

							if($includeType == "approved"){
								if($timesheet['status'] !== "approved" || $timesheet['sundayWorkDate'] !== $this->formatDateForLink($value,"/")){
									unset($employeesList[$employeeKey]['timesheets'][$timesheetKey]);
								}
							}
							else if ($includeType == "submitted"){
								if(($timesheet['status'] !== "submitted" && $timesheet['status'] !== "approved") || $timesheet['sundayWorkDate'] !== $this->formatDateForLink($value,"/")){
									unset($employeesList[$employeeKey]['timesheets'][$timesheetKey]);
								}
							}
							else if ($includeType == "incomplete"){
								if($timesheet['sundayWorkDate'] !== $this->formatDateForLink($value,"/")){
									unset($employeesList[$employeeKey]['timesheets'][$timesheetKey]);
								}
							}
						}
					}
					else { //If employee doesn't have any timesheet
						unset($employeesList[$employeeKey]);
					}				

					//echo "<br/>Before: <br/>";
					//var_dump($employeesList[$employeeKey]);

					if(isset($employeesList[$employeeKey]) && isset($employeesList[$employeeKey]['timesheets']) && ($employeesList[$employeeKey]['timesheets'] == NULL || empty($employeesList[$employeeKey]['timesheets']) || (isset($employeesList[$employeeKey]['timesheets'][0]) && $employeesList[$employeeKey]['timesheets'][0]['TotalWorkTime'] == "--"))){
						unset($employeesList[$employeeKey]);
					}

					//echo "<br/>After: <br/>";
					//var_dump($employeesList[$employeeKey]);
				}
				
			}

			//Fix array from {} to [] & prettify empty fields
			$timesheets = [];
			
			foreach($employeesList as $employeeKey => $employeeVal){
				
			$employee = $employeesList[$employeeKey];

			$timesheets = [];

				//Fix array from {} to []
				foreach($employee['timesheets'] as $timesheetKey => $timesheetVal){

					if(isset($timesheetVal['status'])){
						$timesheetVal['status'] = ucfirst($timesheetVal['status']);

						//Show Supervisor
						$supervisor = $this->listSupervisor($timesheetVal['supervisor']);
						if(isset($supervisor['firstname'])){
							$timesheetVal['supervisorName'] = $supervisor['firstname']." ".$supervisor['lastname'];
						}
						else {
							$timesheetVal['supervisorName'] = "This supervisor was deleted.";
						}

						//Show Time Submitted "--" if empty
						if(isset($timesheetVal['timeApproved']) && $timesheetVal['timeApproved'] == ""){
							$timesheetVal['timeApproved'] = "--";
						}

						//Show Time Approved "--" if submitted
						if(isset($timesheetVal['timeSubmitted']) && $timesheetVal['timeSubmitted'] == ""){
							$timesheetVal['timeSubmitted'] = "--";
						}

						$employeesList[$employeeKey]['timesheets'][$timesheetKey] = $timesheetVal;
						$timesheets[] = $timesheetVal;
					}
				}

				$employeesList[$employeeKey]['timesheets'] = $timesheets;

				$return[] = $employeesList[$employeeKey];
			}
		}

		return $return;
	}

	function listTimesheetbyDate($employeeID = NULL, $date){

		if($employeeID !== NULL){
			$timesheets = $this->listTimesheets($employeeID);
			//If date matches then get that timesheet
			if(!empty($timesheets)){
				foreach($timesheets as $timesheetKey => $timesheetVal){
					if($timesheets[$timesheetKey]['sundayWorkDate'] == $date){ //if timesheet for this week exists then return timesheet
						return $timesheets[$timesheetKey];
						break;
					}
				}
			}
		}

		else {
			$timesheetsList = $this->listAllTimesheets();
			foreach ($timesheetsList as $timesheetKey => $timesheetVal){
				if($timesheetsList[$timesheetKey]['sundayWorkDate'] == $date){
					$return[] = $timesheetsList[$timesheetKey];
				}
			}

			return $return;
		}
	}

	function listAllTimesheets(){
		$employeesList = $this->listEmployees();
		$return = [];
		foreach($employeesList as $employeeKey => $employeeVal){
			$return[$employeesList[$employeeKey]['ID']] = $employeesList[$employeeKey]['timesheets'];
		}

		return $return;
	}


	function listCurrentWeekTimeSheet($employeeID){
			$timesheets = $this->listTimesheets($employeeID);

			//var_dump($timesheets);
			
			//Calc current sunday
			$prevSun = abs(strtotime("previous sunday"));
		    $currentDate = abs(strtotime("today"));
		    $seconds = 86400; //86400 seconds in a day
		 
		    $dayDiff = ceil( ($currentDate-$prevSun)/$seconds ); 
		 
		    if( $dayDiff < 7 )
		    {
		        $dayDiff += 1; //if it's sunday the difference will be 0, thus add 1 to it
		        $prevSun = strtotime( "previous sunday", strtotime("-$dayDiff day") );
		    }
		 
		    $prevSun = date("m/d/Y",$prevSun);

		    //Find this current week
			if(!empty($timesheets)){
				foreach($timesheets as $timesheetKey => $timesheetVal){
					if($timesheets[$timesheetKey]['sundayWorkDate'] == $prevSun){ //if timesheet for this week exists then return timesheet
						return $timesheets[$timesheetKey];
						break;
					}
				}
			}

	}


	function listTimesheets($employeeID){
			//If project name, company, and supervisors exists then send msg
			$this->db->query("SELECT * FROM users WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$queryResults = $this->db->fetchOne();

			//var_dump(json_decode($queryResults['timesheet'], true));
			return json_decode($queryResults['timesheets'], true);
	}


	function saveTimesheets($employeeID, $form){
			//grab timesheets and add 
			$timesheetsList = $this->listTimesheets($employeeID);
			$matched = false;
			//var_dump($timesheetsList);

			//if this week doesn't exist already
			if(!empty($timesheetsList)){
				foreach($timesheetsList as $timesheetKey => $timesheetVal){
					if($timesheetsList[$timesheetKey]['sundayWorkDate'] == $form["sundayWorkDate"]){
						$timesheetsList[$timesheetKey] = $form;
						$matched = true;
					}
				}
			}
			else {
				$timesheetsList[] = $form;
			}

			if(!$matched){
				array_unshift($timesheetsList, $form);
			}

			//var_dump($timesheetsList);
			//var_dump($form);
			$this->db->query("UPDATE users SET timesheets = :timesheets WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$this->db->bind(":timesheets", json_encode($timesheetsList));
			$queryResults = $this->db->execute();

			return $queryResults;			
	}

	function listEmployees() {
		$this->db->query("SELECT * FROM users");
		$queryResults = $this->db->fetchAll();

		foreach($queryResults as $employeesKey => $employeesVal){
				unset($queryResults[$employeesKey]['password']);
				
				$queryResults[$employeesKey]['timesheets'] = json_decode($queryResults[$employeesKey]['timesheets'], true);
				$queryResults[$employeesKey]['projectEmployeeIn'] = $this->listEmployeesProject($queryResults[$employeesKey]['ID']);
				$queryResults[$employeesKey]['projectName'] = $this->listProject($queryResults[$employeesKey]['projectEmployeeIn'])['name'];
				$queryResults[$employeesKey]['companyID'] = $this->getAccountInfo($queryResults[$employeesKey]['ID'])['companyID'];
				$queryResults[$employeesKey]['companyName'] = $this->getAccountInfo($queryResults[$employeesKey]['ID'])['companyName'];

				if(empty($queryResults[$employeesKey]['state']) || !isset($queryResults[$employeesKey]['state'])){
					$queryResults[$employeesKey]['state'] = "summary";
				}		

				$queryResults[$employeesKey]['quickbooks'] = $this->listQbEmployee($queryResults[$employeesKey]['qb_id']);

				//if(isset($queryResults[$employeesKey]['quickbooks'])){
					//$queryResults[$employeesKey]['quickbooks']['billRate'] = $queryResults[$employeesKey]['quickbooks']['UnitPrice'];
				//	$queryResults[$employeesKey]['quickbooks']['payRate'] = $queryResults[$employeesKey]['quickbooks']['Cost'];
				//}

		}

		return $queryResults;
	}

	//Get a list of employees in project
	function listEmployeesInProject($projectID){
		$listEmployees = $this->listEmployees();

		$employees = [];

		if(!empty($listEmployees)){
			foreach($listEmployees as $employeeKey => $employeeVal){
				if($listEmployees[$employeeKey]['projectEmployeeIn'] == $projectID){
					$employees[] = $listEmployees[$employeeKey];
				}
			}
		}

		return $employees;
	}

	//Lookup employee's project
	function listEmployeesProject($employeeID){
		$listProjects = $this->listProjects();
		$projects = [];
		//var_dump($listProjects);

		if(!empty($listProjects)){
			foreach($listProjects as $key => $val){
				if(isset($listProjects[$key]['employees']) && !empty($listProjects[$key]['employees'])){
					foreach($listProjects[$key]['employees'] as $employeesID){
						if($employeeID == $employeesID){
							$projects = $listProjects[$key]['ID'];
						}
					}
				}
			}
		}
		//var_dump($projects);

		$queryResults = $projects;

		return $queryResults;
	}


	function listQbEmployee($employeeID){
		$qbEmployees = $this->listQbEmployees();

		foreach($qbEmployees as $qbEmployeeKey => $qbEmployeeVal){
			if($qbEmployees[$qbEmployeeKey]['ID'] == $employeeID){

				$qbEmployees[$qbEmployeeKey]['billRate'] = number_format($qbEmployees[$qbEmployeeKey]['UnitPrice'], 2, '.', '');
				$qbEmployees[$qbEmployeeKey]['payRate'] = number_format($qbEmployees[$qbEmployeeKey]['Cost'], 2, '.', '');

				return $qbEmployees[$qbEmployeeKey];
			}
		}
	}

	function listQbEmployeesInCompany(){
		$this->db->query("SELECT ID, value FROM system_settings WHERE name = :name");
		$this->db->bind(":name", "qbEmployees");
		$queryResults = $this->db->fetchOne();

		$return = [];
		$value = json_decode($queryResults['value'], TRUE);
		$employeesList = $this->listEmployees();

		foreach($value as $vK => $vV){
			foreach($employeesList as $eK => $eV){
				if($employeesList[$eK]['qb_id'] == $value[$vK]['ID']){
					$return[] = $value[$vK];
				}
			}
		}

		return $return;
	}

	function listQbEmployees(){
		$this->db->query("SELECT ID, value FROM system_settings WHERE name = :name");
		$this->db->bind(":name", "qbEmployees");
		$queryResults = $this->db->fetchOne();

		return json_decode($queryResults['value'], TRUE);
	}

	function refreshQbEmployees(){

		$json_url = APP_FULL_URL."/vendors/quickbooks/docs/partner_platform/example_app_ipp_v3/items_query.php?type=list_all";
		$json = file_get_contents($json_url);
		$data = json_decode($json, TRUE);

		//add additional fields
		//add state
		$employeesList = $this->listEmployees(); 

		foreach ($employeesList as $eK => $eV){
			foreach($data as $dK => $dV){
				if($data[$dK]['ID'] == $employeesList[$eK]['qb_id']){
					$data[$dK]['state'] = $employeesList[$eK]['state'];
				}
			}
		}

		$data = json_encode($data);

		$this->db->query("UPDATE system_settings SET value = :value WHERE name = :name");
		$this->db->bind(":name", "qbEmployees");
		$this->db->bind(":value", $data);
		$queryResults = $this->db->execute();

		return $queryResults;
	}

	function listQbCompany($companyID){
		$qbCompanies = $this->listQbCompanies();

		foreach($qbCompanies as $qbCompanyKey => $qbCompanyVal){
			if($qbCompanies[$qbCompanyKey]['ID'] == $companyID){
				return $qbCompanies[$qbCompanyKey];
			}
		}
	}

	function listQbCompanies(){
		$this->db->query("SELECT ID, value FROM system_settings WHERE name = :name");
		$this->db->bind(":name", "qbCompanies");
		$queryResults = $this->db->fetchOne();

		return json_decode($queryResults['value'], TRUE);
	}

	function refreshQbCompanies(){

		$json_url = APP_FULL_URL."/vendors/quickbooks/docs/partner_platform/example_app_ipp_v3/customer_query.php?type=list_all";
		$json = file_get_contents($json_url);
		$data = $json;
		
		$this->db->query("UPDATE system_settings SET value = :value WHERE name = :name");
		$this->db->bind(":name", "qbCompanies");
		$this->db->bind(":value", $data);
		$queryResults = $this->db->execute();

		return $queryResults;
	}
	
	function createInvoices(){

		$json_url = APP_PATH."/vendors/quickbooks/docs/partner_platform/example_app_ipp_v3/invoice_query.php";
		$json = include($json_url);

		
		//$this->db->query("UPDATE system_settings SET value = :value WHERE name = :name");
		//$this->db->bind(":name", "qbEmployees");
		//$this->db->bind(":value", $data);
		//$queryResults = $this->db->execute();

		//return $queryResults;
	}


	function getAccountInfo($employeeID) {

		$this->db->query("SELECT username, password, email, firstname, lastname, role, reference, state, qb_id FROM users WHERE ID = :id");
		$this->db->bind(":id", $employeeID);
		$queryResults = $this->db->fetchOne();

		$queryResults['ID'] = $employeeID;
		$queryResults['projectEmployeeIn'] = $this->listEmployeesProject($employeeID);
		$queryResults['companyName'] = $this->listCompany(($this->listProject($queryResults['projectEmployeeIn'])['company']))['name'];
		$queryResults['companyID'] = $this->listCompany(($this->listProject($queryResults['projectEmployeeIn'])['company']))['ID'];
		$queryResults['quickbooks'] = $this->listQbEmployee($queryResults['qb_id']);

		if(isset($queryResults['quickbooks'])){
			$queryResults['quickbooks']['billRate'] = $queryResults['quickbooks']['UnitPrice'];
			$queryResults['quickbooks']['payRate'] = $queryResults['quickbooks']['Cost'];
		}
		//var_dump($queryResults);

		return $queryResults;
	}

	function updateSystemSettings($form){

		foreach($form as $formKey => $formVal){
			$this->db->query("UPDATE system_settings SET value = :value WHERE name = :name");
			$this->db->bind(":value", $formVal);
			$this->db->bind(":name", $formKey);

			$queryResults = $this->db->execute();

			if(!$queryResults){
				return false;
			}
		}

		return true;
	}

	function getSystemSettings($name = NULL){

		if(isset($name)){
			$this->db->query("SELECT ID, value FROM system_settings WHERE name = :name");
			$this->db->bind(":name", $name);
			$queryResults = $this->db->fetchOne();

			return $queryResults;
		}
		else {
			$this->db->query("SELECT * FROM system_settings");
			$queryResults = $this->db->fetchAll();

			return $queryResults;
		}
	}
}


?>