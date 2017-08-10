<?php 

class AdministratorModel extends Model {

	public function __construct($foo = null){
		parent::__construct();
	}

	function index($params = NULL){
		return $this->view($params);
	}

	function formatDate($date){
		$date= new DateTime(str_replace("-","/",$date));
		$date = $date->format("n/d/y");

		return $date;	
	}

	function formatDateForLink($date,$type = NULL,$length = NULL){
		if($type == "/"){
			$date= new DateTime(str_replace("-","/",$date));
			$date = $date->format("m/d/Y");
			return $date;	
		}
		else if($type == "-"){
			$date= new DateTime(str_replace("-","/",$date));
			if(isset($length) && $length == "short"){
				$date = $date->format("n-d-y");
			}
			else {
				$date = $date->format("m-d-Y");
			}
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

	function filterEmployeeTimesheets($employees, $by, $values){
		foreach($employees as $eK => $eV){
				$timesheets = $employees[$eK]['timesheets'];
				$timesheetsAdd = [];

				if(isset($timesheets) || !empty($timesheets)){
					foreach($timesheets as $tK => $tV){
						foreach($values as $valueK => $valueV){
							if($timesheets[$tK][$by] == $valueV){
								$timesheetsAdd[] = $timesheets[$tK];
							}
						}
					}
				}	

				//Remove all of employee's timesheet and replace with new
				unset($employees[$eK]['timesheets']);
				$employees[$eK]['timesheets'] = $timesheetsAdd;

				if(!isset($employees[$eK]['timesheets']) || empty($employees[$eK]['timesheets'])){ 
					unset($employees[$eK]);
				}
		}

		return $employees;
	}

	function deleteInvoice($qb_ID){
		$qbInvoice = $this->checkInvoiceExists("qb_ID",$qb_ID);
		if($qbInvoice){
			$deleteQbInvoice = $this->deleteQbInvoice($qb_ID);

			if($deleteQbInvoice['response'] == true){
				$this->db->query("DELETE FROM invoices WHERE qb_ID = :qb_ID");
				$this->db->bind(":qb_ID", $qb_ID);
				$queryResults = $this->db->execute();

				return $queryResults;
			}
		}
		else {
			return true;
		}
	}

	function deleteQbInvoice($qb_ID){
		$return = [];
		$deleteInvoice = true;

		$json_url = APP_PATH."/vendors/quickbooks/docs/partner_platform/example_app_ipp_v3/invoice_query.php";
		$json = include($json_url);

		return $return;
	}

	function createQbInvoice($companyID = NULL, $projectName = NULL, $endDate = NULL, $emails = NULL, $lines = NULL){
		$return = [];
		$endDate = $this->formatDateForLink($endDate,"/");
		$json_url = APP_PATH."/vendors/quickbooks/docs/partner_platform/example_app_ipp_v3/invoice_query.php";
		$json = include($json_url);

		return $return;

	}

	function checkInvoiceExists($by, $value){
		if($by == "companyID"){
			if(isset($value)){
				$companyID = $value[0];
				$startDate = $value[1];

				$this->db->query("SELECT ID, qb_ID, data FROM invoices WHERE company = :company AND startDate = :startDate");
				$this->db->bind(":company", $companyID);
				$this->db->bind(":startDate", $startDate);
				$queryResults = $this->db->fetchOne();

				return $queryResults;
			}
			else {
				return false;
			}
		}
		else if($by == "qb_ID"){
			if(isset($value)){
				$this->db->query("SELECT ID, company, startDate, data FROM invoices WHERE qb_ID = :qb_ID");
				$this->db->bind(":qb_ID", $value);
				$queryResults = $this->db->fetchOne();

				return $queryResults;
			}
			else {
				return false;
			}
		}
	}

	function createInvoice($qbCompanyID = NULL, $companyID = NULL, $projectName = NULL, $startDate = NULL, $endDate = NULL, $emails = NULL, $lines = NULL){

		//Check to see if invoice is already created on our server
		if(empty($this->checkInvoiceExists("companyID", array($companyID, $startDate)))){
			$createQbInvoices = $this->createQbInvoice($qbCompanyID, $projectName, $endDate, $emails, $lines);
			$queryResults;

			//fix lines for when pulling from server
			foreach($lines as $lK => $lV){
				$employee = $this->getAccountInfo($lines[$lK]['employeeID']);

				$lines[$lK]['invoice']['employeeID'] = $lines[$lK]['employeeID'];

				if(isset($employee['qb_id'])){
					$lines[$lK]['qb_id'] = $employee['qb_id'];
					$lines[$lK]['UnitPrice'] = $employee['quickbooks']['billRate'];
				}

				if(isset($lines[$lK]['regHours'])){ $lines[$lK]['invoice']['regHours'] = $lines[$lK]['regHours']; }
				if(isset($lines[$lK]['regRate'])){ $lines[$lK]['invoice']['regRate'] = $lines[$lK]['regRate']; }
				if(isset($lines[$lK]['regAmount'])){ $lines[$lK]['invoice']['regAmount'] = $lines[$lK]['regAmount']; }
				if(isset($lines[$lK]['otHours'])){ $lines[$lK]['invoice']['otHours'] = $lines[$lK]['otHours']; }
				if(isset($lines[$lK]['otRate'])){ $lines[$lK]['invoice']['otRate'] = $lines[$lK]['otRate']; }
				if(isset($lines[$lK]['otAmount'])){ $lines[$lK]['invoice']['otAmount'] = $lines[$lK]['otAmount']; }						
				if(isset($lines[$lK]['dblHours'])){ $lines[$lK]['invoice']['dblHours'] = $lines[$lK]['dblHours']; }
				if(isset($lines[$lK]['dblRate'])){ $lines[$lK]['invoice']['dblRate'] = $lines[$lK]['dblRate']; }
				if(isset($lines[$lK]['dblAmount'])){ $lines[$lK]['invoice']['dblAmount'] = $lines[$lK]['dblAmount']; }
				if(isset($lines[$lK]['TotalAmount'])){ $lines[$lK]['invoice']['TotalAmount'] = $lines[$lK]['TotalAmount']; }

			}

			$data = array(
				"qbCompanyID" => $qbCompanyID,
				"companyID" => $companyID,
				"projectName" => $projectName,
				"startDate" => $startDate,
				"endDate" => $endDate,
				"emails" => $emails,
				"lines" => $lines
			);
			
			//Create a copy on the server
			if($createQbInvoices['response']){
				$this->db->query("INSERT INTO invoices (company, startDate, qb_ID, data) VALUES (:company, :startDate, :qb_ID, :data)");
				$this->db->bind(":startDate", $startDate);
				$this->db->bind(":company", $companyID);
				$this->db->bind(":qb_ID", $createQbInvoices['qbInvoiceID']);
				$this->db->bind(":data", json_encode($data));
				$queryResults = $this->db->execute();

				return $queryResults;
			}
		}
		else {
			return false;
		}
		
	}

	function getQbInvoice($companyID, $startDate){
		if(isset($companyID)){
			$this->db->query("SELECT ID, qb_ID, data FROM invoices WHERE company = :company AND startDate = :startDate");
			$this->db->bind(":company", $companyID);
			$this->db->bind(":startDate", $startDate);
			$queryResults = $this->db->fetchOne();

			return $queryResults;
		}
		else {
			return false;
		}
	}

	function getQbInvoiceArray($companyTimesheet){
		$array = [];
		$timesheets = $companyTimesheet;

		if(isset($timesheets)){
			foreach($timesheets['employeesList'] as $employeesK => $employeeV){
				$employee = $timesheets['employeesList'][$employeesK];
				$arrayInsert = array(
						'employeeID' => $employee['qb_id'],
						'regHours' => $employee['invoice']['regHours'],
						'regRate' => $employee['invoice']['regRate'],
						'regAmount' => $employee['invoice']['regAmount'],
						'otHours' => $employee['invoice']['otHours'],
						'otRate' => $employee['invoice']['otRate'],
						'otAmount' => $employee['invoice']['otAmount'],							
						'dblHours' => $employee['invoice']['dblHours'],
						'dblRate' => $employee['invoice']['dblRate'],
						'dblAmount' => $employee['invoice']['dblAmount'],
						'TotalAmount' => $employee['invoice']['TotalAmount']
					);
				$array[] = $arrayInsert;
			}
			return $array;
		}


	}

	function getQbFirstname($qb_id){
		return $this->listQbEmployee($qb_id)['firstname'];
	}

	function getQbLastname($qb_id){
		return $this->listQbEmployee($qb_id)['lastname'];
	}

	function calcInvoices($startDate){
		$catTimesheetsByCompany = $this->catTimesheetsByCompany($startDate);
		foreach($catTimesheetsByCompany as $companyK => $companyV){
			$catTimesheetsByCompany[$companyK]['TotalWorkHours'] = 0;
			$employeesList = $catTimesheetsByCompany[$companyK]['employeesList'];
			foreach($employeesList as $employeeK => $employeeV){
				$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK] = $this->calcCaTimesheet($employeesList[$employeeK]);
				$employee = $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK];


				//Grab quickbook's name
				$qb_id = $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['qb_id'];

				$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['firstname'] = $this->getQbFirstname($qb_id);
				$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['lastname'] = $this->getQbLastname($qb_id);

				//Grab quickbooks unit price and cost
				$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['Cost'] = $employee['quickbooks']['Cost'];
				$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['UnitPrice'] = $employee['quickbooks']['UnitPrice'];

				if($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['state'] == "ca"){

					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regHours'] = $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg'];
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regRate'] = number_format($employee['quickbooks']['billRate'], 2, '.', '');
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regHours'] * $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regRate']), 2, '.', '');
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otHours'] = $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'];
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otRate'] = number_format(($employee['quickbooks']['billRate'] * 1.5), 2, '.', '');
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otHours']) * ($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otRate']), 2, '.', '');

					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblHours'] = $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl'];
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblRate'] = number_format(($employee['quickbooks']['billRate'] * 2), 2, '.', '');
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblHours']) * ($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblRate']), 2, '.', '');

					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['TotalAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regAmount'] + $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otAmount'] + $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['dblAmount']), 2, '.', '');
				}
				else if($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['state'] == "summary"){

					if($employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'] > 0){
						$regHours = 40;
						$otHours = $employee['timesheets'][0]['TotalWorkTime'] - 40;

						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regHours'] = $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg'];
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regRate'] = number_format($employee['quickbooks']['billRate'], 2, '.', '');
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regHours'] * $catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regRate']), 2, '.', '');

						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otHours'] = $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'] +  $employee['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl'];
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otRate'] = number_format(($employee['quickbooks']['billRate'] * 1.5), 2, '.', '');
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otAmount'] = number_format(($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otHours']) * ($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otRate']), 2, '.', '');
					}
					else {
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regHours'] = $employee['timesheets'][0]['TotalWorkTime'];
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regRate'] = number_format($employee['quickbooks']['billRate'], 2, '.', '');
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['regAmount'] = number_format(($employee['timesheets'][0]['TotalWorkTime'] * $employee['quickbooks']['billRate']), 2, '.', '');

						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otHours'] = 0;
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otRate'] = number_format(($employee['quickbooks']['billRate'] * 1.5), 2, '.', '');
						$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice']['otAmount'] = number_format(0, 2, '.', '');
					}
				}

				foreach($catTimesheetsByCompany[$companyK]['employeesList'][$employeeK]['invoice'] as $iK => $iV){
					$catTimesheetsByCompany[$companyK]['employeesList'][$employeeK][$iK] = $iV;
				}

				$catTimesheetsByCompany[$companyK]['TotalWorkHours'] += $employeesList[$employeeK]['timesheets'][0]['TotalWorkTime'];


			}
		}


		//var_dump($catTimesheetsByCompany);
		return $catTimesheetsByCompany;


	}

	function catTimesheetsByCompany($startDate = NULL, $endDate = NULL, $include = NULL){
		if(!isset($startDate)){
			$startDate = $this->formatDateForLink($this->getLastSunday(),"/");
		}
		else {
			$startDate = $this->formatDateForLink($startDate,"/");
		}
			$include = ["approved","submitted"];
			$companiesList = $this->listCompanies();
			$employeesList = $this->filterEmployeeTimesheets($this->listEmployees(), 'sundayWorkDate', array($startDate));
			$employeesList = $this->filterEmployeeTimesheets($employeesList, 'status', $include);

			$timesheetsReturn = [];

				//var_dump($companiesList);

			foreach($companiesList as $cK => $cV){
				$companiesAdd = [];


				foreach($employeesList as $key => $val){
					if($employeesList[$key]['companyID'] == $companiesList[$cK]['ID']){
						$companiesAdd['ID'] = $companiesList[$cK]['ID'];
						$companiesAdd['name'] = $companiesList[$cK]['name'];
						$companiesAdd['employeesList'][] = $employeesList[$key];
					}
				}

				if(isset($companiesAdd) && !empty($companiesAdd) && ($companiesAdd['ID'] != 0)){
					$timesheetsReturn[] = $companiesAdd;
				}
			}
				//var_dump($timesheetsReturn);

			return $timesheetsReturn;

		
	}

	function addEmployee($form){

		//If username or email exists then send msg
		$employeesList = $this->listEmployees();
		foreach($employeesList as $employeeKey => $employeeVal){
			if($employeesList[$employeeKey]['username'] == $form['username']){
				return false;
			}
		}

		//Check if unique ID already exists
		$referenceID = '';
		for ($i = 0; $i<5; $i++) {
		    $referenceID .= mt_rand(0,9);
		}

		$employeesList = $this->listEmployees();
		foreach($employeesList as $employeeKey => $employeeVal){
			while($employeesList[$employeeKey]['reference'] !== $referenceID){
				$form['reference'] = $referenceID;
				break;
			}
		}


		$this->db->query("INSERT INTO users (username, password, email, firstname, lastname, role, reference, qb_id) VALUES (:username, :password, :email, :firstname, :lastname, :role, :reference, :qb_id)");
		$this->db->bind(":username", $form['username']);
		$this->db->bind(":password", password_hash($form['password'], PASSWORD_DEFAULT));
		$this->db->bind(":email", $form['email']);
		$this->db->bind(":firstname", $form['firstname']);
		$this->db->bind(":lastname", $form['lastname']);
		$this->db->bind(":role", $form['role']);
		$this->db->bind(":reference", $form['reference']);
		$this->db->bind(":qb_id", $form['qbEmployee']);
		$queryResults = $this->db->execute();
				
		if($queryResults){
			$employeeID;

			//If username or email was created then add to project
			$employeesList = $this->listEmployees();
			foreach($employeesList as $employeeKey => $employeeVal){
				if($employeesList[$employeeKey]['username'] == $form['username']){
					$employeeID = $employeesList[$employeeKey]['ID']; //Get the ID from server
				}
			}

			//Add employee to new project
			$projectToAddEmployee = $this->listProject($form['project']['ID']);
			$projectToAddEmployee['employees'][] = $employeeID;

			$saveProjectToAddEmployee = $this->saveProject($projectToAddEmployee['ID'], $projectToAddEmployee);


			if(!$saveProjectToAddEmployee){
				return false;
			}
		}

		return $queryResults;

	}

	function updateEmployee($employeeID, $form){
		//Update projects
		if(!empty($form['project']['ID']) || isset($form['project']['ID'])){
			$projectEmployeeInID = $this->getAccountInfo($employeeID)['projectEmployeeIn'];
			$projectEmployeeIn = $this->listProject($projectEmployeeInID);
			$projectToAddEmployee;


			//If the chosen project is different than current then update and remove from old
			if($form['project']['ID'] !== $projectEmployeeInID){

				//Remove from old first
				if(!empty($projectEmployeeIn['employees'])){
					foreach($projectEmployeeIn['employees'] as $employeeKey => $employeeVal){
						if($projectEmployeeIn['employees'][$employeeKey] == $employeeID){
							unset($projectEmployeeIn['employees'][$employeeKey]);
						}
					}
				}

				//Add employee to new project
				$projectToAddEmployee = $this->listProject($form['project']['ID']);
				$projectToAddEmployee['employees'][] = $employeeID;
			}

			else { //If employee isn't in a project

			}

			if(isset($projectToAddEmployee)){
				$saveProjectEmployeeIn = $this->saveProject($projectEmployeeInID, $projectEmployeeIn);
				$saveProjectToAddEmployee = $this->saveProject($projectToAddEmployee['ID'], $projectToAddEmployee);

				if(!isset($saveProjectEmployeeIn) || !isset($saveProjectToAddEmployee)){
					return false;
				}
			}
		}

		if(!empty($form['password'])){
			$this->db->query("UPDATE users SET username = :username, email = :email, firstname = :firstname, lastname = :lastname, role = :role, password = :password, state = :state, qb_id = :qb_id WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$this->db->bind(":username", $form['username']);
			$this->db->bind(":password", password_hash($form['password'], PASSWORD_DEFAULT));
			$this->db->bind(":email", $form['email']);
			$this->db->bind(":firstname", $form['firstname']);
			$this->db->bind(":lastname", $form['lastname']);
			$this->db->bind(":role", $form['role']);
			$this->db->bind(":state", $form['state']);
			$this->db->bind(":qb_id", $form['qbEmployee']);
		}
		else {
			$this->db->query("UPDATE users SET username = :username, email = :email, firstname = :firstname, lastname = :lastname, role = :role, state = :state, qb_id = :qb_id WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$this->db->bind(":username", $form['username']);
			$this->db->bind(":email", $form['email']);
			$this->db->bind(":firstname", $form['firstname']);
			$this->db->bind(":lastname", $form['lastname']);
			$this->db->bind(":role", $form['role']);
			$this->db->bind(":state", $form['state']);
			$this->db->bind(":qb_id", $form['qbEmployee']);
		}	
		
		$queryResults = $this->db->execute();

		return $queryResults;
	}

	function deleteEmployees($id){
		$this->db->query("DELETE FROM users WHERE ID = :id");
		$this->db->bind(":id", $id);
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

	//Lookup employee's project
	function listEmployeesProject($employeeID){
		$listProjects = $this->listProjects();
		$projects = "";
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

	function listEmployeesInProject($employeeID){
		$listProjects = $this->listProjects();
		$projects = "";

		if(!empty($listProjects)){
			foreach($listProjects as $key => $val){
				if(!empty($listProjects[$key]['employees'])){
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

	function getAccountInfo($employeeID) {

		$this->db->query("SELECT username, password, email, firstname, lastname, role, reference, state, qb_id FROM users WHERE ID = :id");
		$this->db->bind(":id", $employeeID);
		$queryResults = $this->db->fetchOne();

		$queryResults['ID'] = $employeeID;
		$queryResults['projectEmployeeIn'] = $this->listEmployeesInProject($employeeID);
		$queryResults['projectID'] =  $this->listEmployeesProject($employeeID);
		$queryResults['projectName'] = $this->listProject($this->listEmployeesProject($employeeID))['name'];
		$queryResults['companyName'] = $this->listCompany(($this->listProject($queryResults['projectEmployeeIn'])['company']))['name'];
		$queryResults['companyID'] = $this->listCompany(($this->listProject($queryResults['projectEmployeeIn'])['company']))['ID'];
		
		if(isset($queryResults['qb_id'])){
			$queryResults['quickbooks'] = $this->listQbEmployee($queryResults['qb_id']);
			$queryResults['qb_id'] = $queryResults['qb_id'];
		}

		if(isset($queryResults['quickbooks'])){
			$queryResults['quickbooks']['billRate'] = $queryResults['quickbooks']['UnitPrice'];
			$queryResults['quickbooks']['payRate'] = $queryResults['quickbooks']['Cost'];
		}
		//var_dump($queryResults);

		return $queryResults;
	}

	function listSupervisors(){
		$this->db->query("SELECT * FROM supervisors");
		$queryResults = $this->db->fetchAll();

		return $queryResults;
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

	function prettyPrintTimesheet($employeeID){
		$currentWeekTimesheet = $this->listCurrentWeekTimesheet($employeeID);

	}

	function sendCancelToSupervisor($employeeID,$sundayWorkDate,$type){
		$timesheet = $this->listTimesheetbyDate($employeeID, $sundayWorkDate);
		$supervisorID = $timesheet['supervisor'];

		//Get Supervisor Info
		$supervisor = $this->listSupervisor($supervisorID);

		$accountInfo = $this->getAccountInfo($employeeID);

		//Set token
		//$token = bin2hex(random_bytes(16));

		echo "<script> console.log('supervisor Email: ".$supervisor['email']."'); </script>";

 
		$mail = $this->vendor['PHPMailer'];	
		$mail->CharSet = 'utf-8';
		ini_set('default_charset', 'UTF-8');
	
		$to = $supervisor['email'];
		if(!PHPMailer::validateAddress($to)) {
		  $return = "Email address " . $to . " is invalid -- aborting!";
		}
		$mail->isMail();
		$mail->addReplyTo("no-reply@jlmstrategic.com", "JLM Strategic Talent Partners");
		$mail->setFrom("no-reply@jlmstrategic.com", "JLM Strategic Talent Partners");

		if($type == "approved"){
			$mail->addAddress($supervisor['email'], $supervisor['firstname']." ".$supervisor['lastname']);
			$mail->Subject  = "Timesheet Approval Cancel: ".$accountInfo['firstname']." ".$accountInfo['lastname'];
			$body = "<div style='max-width:676px;'><h2 style='color: #4f4f4f; font-weight: 200;'>Hi ".$supervisor['firstname'].",</h2><hr style='background-color: #eeeeee; height: 1px; border: 0; margin: 25px 0;'><p style='color: #4f4f4f;'>".$accountInfo['firstname']." ".$accountInfo['lastname']."'s <span style='border-bottom:1px dotted #4f4f4f;'>".$timesheet['sundayWorkDate']." - ".$timesheet['saturdayWorkDate']."</span> timesheet was canceled.</p></div>";
		}

		else if($type == "submit"){
			$mail->addAddress($accountInfo['email'], $accountInfo['firstname']." ".$accountInfo['lastname']);
			$mail->Subject  = "Timesheet Submission Cancel: ".$accountInfo['firstname']." ".$accountInfo['lastname'];
			$body = "<div style='max-width:676px;'><h2 style='color: #4f4f4f; font-weight: 200;'>Hi ".$supervisor['firstname'].",</h2><hr style='background-color: #eeeeee; height: 1px; border: 0; margin: 25px 0;'><p style='color: #4f4f4f;'>Your <span style='border-bottom:1px dotted #4f4f4f;'>".$timesheet['sundayWorkDate']." - ".$timesheet['saturdayWorkDate']."</span> timesheet was submission was canceled.</p></div>";
		}

		$mail->WordWrap = 78;
		$mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images
		//$mail->addAttachment('images/phpmailer_mini.png','phpmailer_mini.png');  // optional name
		//$mail->addAttachment('images/phpmailer.png', 'phpmailer.png');  // optional name

		if($mail->send()){
		   return true;
		   
		}
		else {
		  echo 'Unable to send to: ' . $mail->ErrorInfo;
		  return false;
		}
	}

	function sendTimesheetToSupervisor($employeeID,$sundayWorkDate, $supervisorID, $token){
		echo "<script> console.log('employeeID: ".$employeeID."; sundayWorkDate: ".$sundayWorkDate."; supervisor: ".$supervisorID."'); </script>";
		//Get Employee Timesheet with ID
		$currentWeekTimesheet;
		$currentWeekTimesheet[0]['timesheets'][0] = $this->listTimesheetByDate($employeeID, $sundayWorkDate);

		foreach($currentWeekTimesheet as $timesheetKey => $timesheetVal){
			$currentWeekTimesheet[$timesheetKey] = $this->calcCaTimesheet($currentWeekTimesheet[$timesheetKey]);
		}

		//Get Supervisor Info
		$supervisor = $this->listSupervisor($supervisorID);

		$accountInfo = $this->getAccountInfo($employeeID);

		//Set token
		//$token = bin2hex(random_bytes(16));

		echo "<script> console.log('supervisor Email: ".$supervisor['email']."'); </script>";

		$results_messages = array();
 
		$mail = $this->vendor['PHPMailer'];	
		$mail->CharSet = 'utf-8';

		ini_set('default_charset', 'UTF-8');
	
			$mail->isSMTP();
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 2;
			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'html';
			//Set the hostname of the mail server
			$mail->Host = "localhost";
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = 25;
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = "no-reply@jlmstrategic.com";
			//Password to use for SMTP authentication
			$mail->Password = "h0lyFUCK0x!";


		$to = $supervisor['email'];
		if(!PHPMailer::validateAddress($to)) {
		  $return = "Email address " . $to . " is invalid -- aborting!";
		}
		$mail->isMail();
		$mail->addReplyTo("no-reply@jlmstrategic.com", "JLM Strategic Talent Partners");
		$mail->setFrom("no-reply@jlmstrategic.com", "JLM Strategic Talent Partners");
		$mail->addAddress($supervisor['email'], $supervisor['firstname']." ".$supervisor['lastname']);
		$mail->Subject  = "Timesheet Approval: ".$accountInfo['firstname']." ".$accountInfo['lastname'];

		$timesheetTable = "";
		$days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
		$i = 0;

		if($accountInfo['state'] == "ca"){
			$timesheetTable .= "<thead><tr><td width='80px'>Day</td><td width='80px'>Date</td><td width='100px'>Break</td><td width='100px'>Regular hours</td><td width='100px'>Overtime</td><td width='100px'>Dbl Overtime</td></tr></thead>";
		}
		else if($accountInfo['state'] == "summary"){
			$timesheetTable .= "<thead><tr><td width='80px'>Day</td><td width='80px'>Date</td><td width='100px'>Hours</td></tr></thead>";
		}

		$timesheetTable .= "<tbody>";
		while($i < 7){
			$timesheetTable .= "<tr>";
			$timesheetTable .= "<td><strong>".ucfirst($days[$i])."</strong></td>";

			if($accountInfo['state'] == "ca"){
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0][$days[$i].'WorkDate']."</td>";
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours'][$days[$i].'BreaksTime']." hrs</td>";
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours'][$days[$i].'WorkTimeReg']." hrs</td>";
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours'][$days[$i].'WorkTimeOT']." hrs</td>";
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours'][$days[$i].'WorkTimeDbl']." hrs</td>";
				
			}
			else if($accountInfo['state'] == "summary" || $accountInfo['state'] == "" || isset($accountInfo['state'])){
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0][$days[$i].'WorkDate']."</td>";
				$timesheetTable .= "<td>".$currentWeekTimesheet[0]['timesheets'][0][$days[$i].'WorkTime']." hrs</td>";
			}

			$timesheetTable .= "</tr>";
			$i++;
		}

		if($accountInfo['state'] == "ca"){
			$timesheetTable .= "<tr>";
			$timesheetTable .= "<td></td><td></td><td></td>";
			$timesheetTable .= "<td><strong>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg']." hrs</strong></td>";
			$timesheetTable .= "<td><strong>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT']." hrs</strong></td>";
			$timesheetTable .= "<td><strong>".$currentWeekTimesheet[0]['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl']." hrs</strong></td>";
			$timesheetTable .= "</tr>";
		}
		else if($accountInfo['state'] == "summary"){
			$timesheetTable .= "<tr>";
			$timesheetTable .= "<td></td><td></td>";
			$timesheetTable .= "<td><strong>".$currentWeekTimesheet[0]['timesheets'][0]['TotalWorkTime']." hrs</strong></td>";
			$timesheetTable .= "</tr>";
		}

		$timesheetTable .= "</tbody>";


		$body = "<div style='max-width:676px;'><h2 style='color: #4f4f4f; font-weight: 200;'>Hi ".$supervisor['firstname'].",</h2><hr style='background-color: #eeeeee; height: 1px; border: 0; margin: 25px 0;'><p style='color: #4f4f4f;'>Please review ".$accountInfo['firstname']." ".$accountInfo['lastname']."'s timesheet.</p><table>".$timesheetTable."</table><hr style='background-color: #eeeeee; height: 1px; border: 0; margin: 25px 0;'><a href='".APP_FULL_URL."/api/timesheet/approve/".$employeeID."/".str_replace("/","-",$currentWeekTimesheet[0]['timesheets'][0]['sundayWorkDate'])."/".$token."' style='background-color: #315ca7; color: #ffffff; text-decoration: none; padding: 10px 25px; text-transform: uppercase; letter-spacing: 1px; font-weight: 400; font-family: Lato, sans-serif; font-size: 10px; border-radius: 3px;'>Approve Timesheet</a> <a href='".APP_FULL_URL."/api/timesheet/disapprove/".$employeeID."/".str_replace("/","-",$currentWeekTimesheet[0]['timesheets'][0]['sundayWorkDate'])."/".$token."' style='background-color: #315ca7; color: #ffffff; text-decoration: none; padding: 10px 25px; text-transform: uppercase; letter-spacing: 1px; font-weight: 400; font-family: Lato, sans-serif; font-size: 10px; border-radius: 3px;'>Disapprove Timesheet</a><br/><br/><span style='color: #fff;'>".rand()."</span></div>";

		$mail->WordWrap = 78;
		$mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images
		//$mail->addAttachment('images/phpmailer_mini.png','phpmailer_mini.png');  // optional name
		//$mail->addAttachment('images/phpmailer.png', 'phpmailer.png');  // optional name

		if($mail->send()){
		   return true;
		   
		}
		else {
		  echo 'Unable to send to: ' . $mail->ErrorInfo;
		  return false;
		}

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

	function listSupervisor($employeeID){
		$this->db->query("SELECT * FROM supervisors WHERE ID = :id");
		$this->db->bind(":id", $employeeID);
		$queryResults = $this->db->fetchOne();

		$supervisorsList = $this->listSupervisors();
		$projectsList = $this->listProjects();

		$queryResults['projects'] = $this->listProjectsOfSupervisor($employeeID);
		return $queryResults;
	}

	function addSupervisor($form){
		//To handle saving of supervisors
		$this->db->query("INSERT INTO supervisors (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
		$this->db->bind(":firstname", $form['firstname']);
		$this->db->bind(":lastname", $form['lastname']);
		$this->db->bind(":email", $form['email']);
		$queryResults = $this->db->execute();

		$supervisorID = $this->db->lastInsertId();

		//Add supervisor to project
		if(!empty($form['projects'])){
			foreach($form['projects'] as $projectKey => $projectVal){
				$projectID = $form['projects'][$projectKey];
				$listProject = $this->listProject($projectID);

				$listProject['supervisors'][] = $supervisorID;
				$saveProject = $this->saveProject($projectID, $listProject);

				if(!$saveProject){
					return false;
				}
	
				
			}
		}

		return $queryResults;
	}

	function saveSupervisor($id, $form){
			//To handle saving of projects
			$projectsOfSupervisor = $this->listProjectsOfSupervisor($id);

			//If form projects are empty then we are not submitting anything, we to have to make sure to remove those from the server
			if(empty($form['projects'])){
				if(!empty($projectsOfSupervisor)){
					foreach($projectsOfSupervisor as $projectOfSupervisor){
						$projectToRemoveFromSupervisor = $this->listProject($projectOfSupervisor); 
						if(!empty($projectToRemoveFromSupervisor)){
							 foreach($projectToRemoveFromSupervisor['supervisors'] as $supervisorKey => $supervisorVal){
									unset($projectToRemoveFromSupervisor['supervisors'][$supervisorKey]);
									$this->saveProject($projectOfSupervisor, $projectToRemoveFromSupervisor);
							}
						}
					}
				}
			}
			else {
				//If form submitted is higher than on server then add the ones that are new
				if(count($form['projects']) > count($projectsOfSupervisor)){
					//For each form project submitted
					foreach($form['projects'] as $projectFormIDKey => $projectFormIDVal){
						//If the supervisor has any projects then we have to make sure what is the difference otherwise leave form as is for submission
						if(!empty($projectsOfSupervisor)){
							foreach($projectsOfSupervisor as $projectOfSupervisor){
								//if form's project is equal to server's ID then we don't need to add it, so remove from list that we will use to send to server
								if(isset($form['projects'][$projectFormIDKey]) && $form['projects'][$projectFormIDKey] == $projectOfSupervisor){
									unset($form['projects'][$projectFormIDKey]);
								}
							}
						}
					}

					//List of projects to save supervisor into
					$projectsToAdd = $form['projects'];
					foreach($projectsToAdd as $projectToAdd){
						
						$projectToAdd = $this->listProject($projectToAdd);
						$projectToAdd['supervisors'][] = $id;
						
						$this->saveProject($projectToAdd['ID'], $projectToAdd);
					}

				}

				else if(count($form['projects']) == count($projectsOfSupervisor)){

					$formProject = $form['projects'];

					//If there are any projects that are new then add them
					foreach($formProject as $projectFormIDKey => $projectFormIDVal){
						//If the supervisor has any projects then we have to make sure what is the difference otherwise leave form as is for submission
						if(!empty($projectsOfSupervisor)){
							foreach($projectsOfSupervisor as $projectOfSupervisor){
								//if form's project is equal to server's ID then we don't need to add it, so remove from list that we will use to send to server
								if(isset($formProject[$projectFormIDKey]) && $formProject[$projectFormIDKey] == $projectOfSupervisor){
									unset($formProject[$projectFormIDKey]);
								}
							}
						}
					}

					//List of projects to save supervisor into
					$projectsToAdd = $formProject;
					foreach($projectsToAdd as $projectToAdd){
						
						$projectToAdd = $this->listProject($projectToAdd);
						$projectToAdd['supervisors'][] = $id;
						
						$this->saveProject($projectToAdd['ID'], $projectToAdd);
					}

					$formProject = $form['projects'];

					//If there are any projects that are left then add remove
					foreach($formProject as $projectKey => $projectVal){

						if(!empty($projectsOfSupervisor)){
							foreach($projectsOfSupervisor as $projectOfSupervisorKey => $projectOfSupervisorVal){
								if($formProject[$projectKey] == $projectsOfSupervisor[$projectOfSupervisorKey]){
									unset($projectsOfSupervisor[$projectOfSupervisorKey]);
								}
							}
						}
					}

					//List of projects to remove supervisor from
					$projectsToRemove = $projectsOfSupervisor;
					$projectToRemoveSupervisor;

					if(!empty($projectsToRemove)){
						foreach($projectsToRemove as $projectToRemoveKey => $projectToRemoveVal){
							
							$projectToRemoveSupervisor = $this->listProject($projectsToRemove[$projectToRemoveKey]);
							foreach($projectToRemoveSupervisor['supervisors'] as $supervisorKey => $supervisorVal){
								if($projectToRemoveSupervisor['supervisors'][$supervisorKey] == $id){
									unset($projectToRemoveSupervisor['supervisors'][$supervisorKey]);
								}
							}

						$this->saveProject($projectsToRemove[$projectToRemoveKey], $projectToRemoveSupervisor);
						}
					}
				}

				else if(count($form['projects']) < count($projectsOfSupervisor)){
					//If there is less projects from form than on server then that means the difference is the one we need to remove
					foreach($form['projects'] as $projectKey => $projectVal){

						if(!empty($projectsOfSupervisor)){
							foreach($projectsOfSupervisor as $projectOfSupervisorKey => $projectOfSupervisorVal){
								if($form['projects'][$projectKey] == $projectsOfSupervisor[$projectOfSupervisorKey]){
									unset($projectsOfSupervisor[$projectOfSupervisorKey]);
								}
							}
						}
					}

					//List of projects to remove supervisor from
					$projectsToRemove = $projectsOfSupervisor;
					$projectToRemoveSupervisor;

					if(!empty($projectsToRemove)){
						foreach($projectsToRemove as $projectToRemoveKey => $projectToRemoveVal){
							
							$projectToRemoveSupervisor = $this->listProject($projectsToRemove[$projectToRemoveKey]);
							foreach($projectToRemoveSupervisor['supervisors'] as $supervisorKey => $supervisorVal){
								if($projectToRemoveSupervisor['supervisors'][$supervisorKey] == $id){
									unset($projectToRemoveSupervisor['supervisors'][$supervisorKey]);
								}
							}

						$this->saveProject($projectsToRemove[$projectToRemoveKey], $projectToRemoveSupervisor);
						}
					}

				}	
			}
			
			//To handle saving of supervisors
			$this->db->query("UPDATE supervisors SET firstname = :firstname, lastname = :lastname, email = :email WHERE ID = :id");
			$this->db->bind(":id", $id);
			$this->db->bind(":firstname", $form['firstname']);
			$this->db->bind(":lastname", $form['lastname']);
			$this->db->bind(":email", $form['email']);
			$queryResults = $this->db->execute();

			return $queryResults;
	}

	function deleteSupervisor($id){

		$listProjects = $this->listProjects();

		foreach($listProjects as $projectKey => $projectVal){
			foreach($listProjects[$projectKey]['supervisors'] as $supervisorKey => $supervisorVal){
				if($listProjects[$projectKey]['supervisors'][$supervisorKey] == $id){
					unset($listProjects[$projectKey]['supervisors'][$supervisorKey]);
					$saveProject = $this->saveProject($listProjects[$projectKey]['ID'], $listProjects[$projectKey]);

					if(!$saveProject){
						return false;
					}
				}
			}
		}

		$this->db->query("DELETE FROM supervisors WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->execute();

		return $queryResults;
	}

	//Get Company's project
	function getCompanysProject($companyID){
		$listProjects = $this->listProjects();

		if(isset($companyID)){
			foreach ($listProjects as $k => $v){
				if($listProjects[$k]['company'] == $companyID){
					return array("ID"=>$listProjects[$k]['ID'], "name"=>$listProjects[$k]['name']);
				}
			}
		}
	}

	function companyExistInProjects($companyID, $projectID = NULL){
		$selfCompanyID = $this->listProject($projectID)['company'];
		$listProjects = $this->listProjects();

		if(isset($projectID)){
			foreach ($listProjects as $k => $v){
				if($listProjects[$k]['company'] == $companyID && $companyID !== $selfCompanyID){
					return true;
					break;
				}
			}

			return false;
		}
		else {
			foreach ($listProjects as $k => $v){
				if($listProjects[$k]['company'] == $companyID){
					return true;
					break;
				}
			}

			return false;
		}
	}

	function saveProject($id, $form){

			if(empty($form['employees'])){
				$form['employees'] = [];
			}

			if(empty($form['supervisors'])){
				$form['supervisors'] = [];
			}

			//If company doesn't exist in another project
			if(!$this->companyExistInProjects($form['company'], $id)){

				//If project name, company, and supervisors exists then send msg
				$this->db->query("UPDATE projects SET name = :name, company = :company, supervisors = :supervisors, employees = :employees WHERE ID = :id");
				$this->db->bind(":id", $id);
				$this->db->bind(":name", $form['name']);
				$this->db->bind(":company", $form['company']);
				$this->db->bind(":supervisors", json_encode($form['supervisors']));
				$this->db->bind(":employees", json_encode($form['employees']));
				$queryResults = $this->db->execute();

				return $queryResults;

			}
			else {
				return false;
			}
	}

	function addProject($form){

		if(!isset($form['supervisors'])){
			$form['supervisors'] = [];
		}

		$form['employees'] = [];

		//If company doesn't exist in another project
		if(!$this->companyExistInProjects($form['company'])){

			//If project name, company, and supervisors exists then send msg
			$this->db->query("INSERT INTO projects (name, supervisors, employees, company) VALUES (:name, :supervisors, :employees, :company)");
			$this->db->bind(":name", $form['name']);
			$this->db->bind(":employees", json_encode($form['employees']));
			$this->db->bind(":supervisors", json_encode($form['supervisors']));
			$this->db->bind(":company", $form['company']);
			$queryResults = $this->db->execute();

			return $queryResults;
		}
	}

	function deleteProject($id){

		$this->db->query("DELETE FROM projects WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->execute();

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

	function listProject($id){
		$this->db->query("SELECT * FROM projects WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->fetchOne();
		
		if(empty($queryResults)){
			return false;
		}
		else {
			$queryResults['supervisors'] = json_decode($queryResults['supervisors'], true);
			//json decode employees
			$queryResults['employees'] = json_decode($queryResults['employees'], true);

			return $queryResults;
		}
	}

	function listCompaniesFilter($filter = NULL){
		if($filter == "existingCompanies"){
			$listCompanies = $this->listCompanies();
			$listProjects = $this->listProjects();

			foreach($listProjects as $projectK => $projectV){
				foreach($listCompanies as $companyK => $companyV){
					if($listProjects[$projectK]['company'] == $listCompanies[$companyK]['ID']){
						unset($listCompanies[$companyK]);
					}
				}
			}

			return $listCompanies;
		}
	}

	function listCompanies(){
		$this->db->query("SELECT * FROM companies");
		$queryResults = $this->db->fetchAll();

		return $queryResults;
	}

	function listCompany($id){
		$this->db->query("SELECT * FROM companies WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->fetchOne();

		return $queryResults;
	}

	function deleteCompany($id){
		$this->db->query("DELETE FROM companies WHERE ID = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->execute();

		return $queryResults;
	}

	function filterCreatedCompanies(){
		//Get list of companies from our portal
		$companies = $this->listCompanies();

		//Get list of companies from QuickBooks
		$qbCompanies = $this->listQbCompanies();

		//If any QB companies has portal company's ID
		foreach ($qbCompanies as $qbKey => $value) {
			//While looking at current QB ID, look thru portal companies and see if they have a matching qb_ID
			foreach($companies as $key => $value){
				//If matches then do next QB 
				if(isset($companies[$key]['qb_id']) && isset($qbCompanies[$qbKey]['ID'])){
					if($companies[$key]['qb_id'] === $qbCompanies[$qbKey]['ID']){
						unset($qbCompanies[$qbKey]);
					}
				}
			}
		}
		return $qbCompanies;
	}

	function importQuickBookCompanies($id, $companyInfo){
		//Check if company is already imported
		$this->db->query("SELECT * FROM companies WHERE qb_id = :id");
		$this->db->bind(":id", $id);
		$queryResults = $this->db->fetchOne();

		if(!$queryResults){

		$this->db->query("INSERT INTO companies (qb_id, name) VALUES (:qb_id, :name)");
		$this->db->bind(":qb_id", $companyInfo['ID']);
		$this->db->bind(":name", $companyInfo['Name']);
		$queryResults = $this->db->execute();
			return true;
		}

		else {
			return false;
		}
	}

	function listEmployeesWithTimesheetByDate($date){
			$employeeList = $this->listEmployees();
			$employeesWithTimesheet = [];

			//var_dump($employeeList);
			foreach($employeeList as $employeeKey => $employeeVal){
				$employeeID = $employeeList[$employeeKey]['ID'];
				$timesheets = $employeeList[$employeeKey]['timesheets'];
				$dateFormat = new DateTime(str_replace("-","/",$date));
				$dateFormat = $dateFormat->format("m/d/Y");
				$date = $dateFormat;

				//If date matches then get that timesheet
				if(!empty($timesheets)){
					foreach($timesheets as $timesheetKey => $timesheetVal){
						if($timesheets[$timesheetKey]['sundayWorkDate'] == $date){ //if timesheet for this week exists then return timesheet
							if(empty($timesheets[$timesheetKey]['timeApproved']) || !isset($timesheets[$timesheetKey]['timeApproved'])){
								$employeeList[$employeeKey]['timesheets'][$timesheetKey]['timeApproved'] = "--";
							}	
							if(empty($timesheets[$timesheetKey]['timeSubmitted']) || !isset($timesheets[$timesheetKey]['timeSubmitted'])){
								$employeeList[$employeeKey]['timesheets'][$timesheetKey]['timeSubmitted'] = "--";
							}
							$employeesWithTimesheet[] = $employeeList[$employeeKey];
						}
					}
				}
			}		

			return $employeesWithTimesheet;
	}

	function listAllTimesheetsByDate($date){
			$employeeList = $this->listEmployees();

			foreach($employeeList as $employeeKey => $employeeVal){
				$employeeID = $employeeList[$employeeKey]['ID'];
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
	}

	function listTimesheetbyDate($employeeID, $date){
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

	function listLastWeekTimesheet($employeeID){
			$timesheets = $this->listTimesheets($employeeID);

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

	function listCurrentWeekTimesheet($employeeID){
			$timesheets = $this->listTimesheets($employeeID);

			//Calc current sunday
			$prevSun = abs(strtotime("sunday last week"));
		    $currentDate = abs(strtotime("today"));
		    $seconds = 86400; //86400 seconds in a day
		 
		    $dayDiff = ceil( ($currentDate-$prevSun)/$seconds ); 
		 
		    if( $dayDiff < 7 )
		    {
		        $dayDiff += 1; //if it's sunday the difference will be 0, thus add 1 to it
		        $prevSun = strtotime( "sunday this week", strtotime("-$dayDiff day") );
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


	function deleteTimesheet($employeeID, $date){
		$queryResults;
		$timesheets = $this->listTimesheets($employeeID);

		foreach($timesheets as $timesheetsKey => $timesheetsVal){
			if($timesheets[$timesheetsKey]['sundayWorkDate'] == $this->formatDateForLink($date,"/")){
				unset($timesheets[$timesheetsKey]);
			}
		}


			$timesheets = array_values($timesheets); 

			$this->db->query("UPDATE users SET timesheets = :timesheets WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$this->db->bind(":timesheets", json_encode($timesheets));

			$queryResults = $this->db->execute();
			return $queryResults;


	}


	function listTimesheets($employeeID){
			//If project name, company, and supervisors exists then send msg
			$this->db->query("SELECT * FROM users WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$queryResults = $this->db->fetchOne();

			if(isset($queryResults['timesheets']) && !empty($queryResults['timesheets'])){
				$queryResults['timesheets'] = json_decode($queryResults['timesheets'], true);
				
				uksort($queryResults['timesheets'], function($a, $b) {
				  return strcmp($a['sundayWorkDate'], $b['sundayWorkDate']);
				});

				//var_dump(json_decode($queryResults['timesheet'], true));
				return $queryResults['timesheets'];
			}
	}

	function listTimesheetDates($employeeID){
		$timesheets = $this->listTimesheets($employeeID);
		$dates = [];

		if(isset($timesheets) && !empty($timesheets)){
			foreach($timesheets as $timesheetKey => $timesheetVal){
				$dates[] = $timesheets[$timesheetKey]['sundayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['mondayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['tuesdayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['wednesdayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['thursdayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['fridayWorkDate'];
				$dates[] = $timesheets[$timesheetKey]['saturdayWorkDate'];
			}
			return $dates;
		}
	}

	function saveTimesheets($employeeID, $form){
			//grab timesheets and add 
			$timesheetsList = $this->listTimesheets($employeeID);
			$matched = false;


			if(!isset($form['timeApproved'])){
				$form['timeApproved'] = "";
			}

			if(!isset($form['timeSubmitted'])){
				$form['timeSubmitted'] = "";
			}

			if(!isset($form['status'])){
				$form['status'] = "incomplete";
			}

			//if this week doesn't exist already
			if(!empty($timesheetsList)){
				foreach($timesheetsList as $timesheetKey => $timesheetVal){
					if($timesheetsList[$timesheetKey]['sundayWorkDate'] == $form["sundayWorkDate"]){
						$timesheetsList[$timesheetKey] = $form;
						$matched = true;
					}
				}
				if(!$matched){
					array_unshift($timesheetsList, $form);
				}
			}
			else {
				$timesheetsList[] = $form;
			}

			$this->db->query("UPDATE users SET timesheets = :timesheets WHERE ID = :id");
			$this->db->bind(":id", $employeeID);
			$this->db->bind(":timesheets", json_encode($timesheetsList));
			$queryResults = $this->db->execute();

			return $queryResults;			
	}

	function listQbEmployee($employeeID){
		$qbEmployees = $this->listQbEmployees();

		if(isset($qbEmployees) && !empty($qbEmployees)){
			foreach($qbEmployees as $qbEmployeeKey => $qbEmployeeVal){
				if($qbEmployees[$qbEmployeeKey]['ID'] == $employeeID){

					$fullname = explode(",",$qbEmployees[$qbEmployeeKey]['Name']);   

					if(isset($fullname[0])){ $qbEmployees[$qbEmployeeKey]['lastname'] = $fullname[0]; } else {  $qbEmployees[$qbEmployeeKey]['lastname'] = ""; }
					if(isset($fullname[1])){ $qbEmployees[$qbEmployeeKey]['firstname'] = $fullname[1]; } else {  $qbEmployees[$qbEmployeeKey]['firstname'] = ""; }

					$qbEmployees[$qbEmployeeKey]['billRate'] = $qbEmployees[$qbEmployeeKey]['UnitPrice'];
					$qbEmployees[$qbEmployeeKey]['payRate'] = $qbEmployees[$qbEmployeeKey]['Cost'];

					return $qbEmployees[$qbEmployeeKey];
				}
			}
		}
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
		$data = json_encode($json);
		
		$this->db->query("UPDATE system_settings SET value = :value WHERE name = :name");
		$this->db->bind(":name", "qbCompanies");
		$this->db->bind(":value", $data);
		$queryResults = $this->db->execute();

		return $queryResults;
	}
	
	function cmp($a, $b) {
		global $array;
    	return strcmp($array[$a]['db'], $array[$b]['db']);
	}

	function logOut(){
	
	}

}


?>