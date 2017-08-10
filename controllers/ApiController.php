<?php

class ApiController extends Controller {

	public function __construct($params = NULL){	
		parent::__construct();
		//Auth::handleLogin();
	}

	public function index($params = NULL){
		//$this->dashboard();
	}

	public function get($params = NULL){
		if($params[0] == "list"){
			//LIST: Companies
			if ($params[1] == "companies"){
				echo json_encode($this->model->listCompanies());
			}		
			//LIST: Supervisors
			if ($params[1] == "supervisors"){
				echo json_encode($this->model->listSupervisors());
			}
			//LIST: Projects
			if ($params[1] == "projects"){
				echo json_encode($this->model->listProjects());
			}
			//LIST: Projects In Company
			if($params[1] == "projectsInCompany"){
				if(isset($params[2])){
					echo json_encode($this->model->listProjectsInCompany($params[2]));
				}
			}
			//LIST: QuickBooks Employees
			if($params[1] == "qbEmployees"){
				if(isset($params[2])){
					echo json_encode($this->model->listQbEmployees());
				}
			}
		}	
		if($params[0] == "value"){
			//GET: Timesheets disclaimer
			if($params[1] = "timesheetsDisclaimer"){
				echo json_encode($this->model->getSystemSettings('timesheets-disclaimer')['value']);
			}
		}
	}

	public function quickBooks($params = NULL){
		//Refresh/ Update QuickBooks Employees
		if($params[0] == "refresh"){
			if(isset($params[1]) && $params[1] == "companies"){
				echo json_encode($this->model->refreshQbCompanies());
			}
			else if(isset($params[1]) && $params[1] == "employees"){
				echo json_encode($this->model->refreshQbEmployees());
			}
		}
		//List QB Employees
		if($params[0] == "list"){
			if(isset($params[1]) && $params[1] == "single"){
				if(isset($params[2]) && $params[2] == "employee"){
					echo json_encode($this->model->listQbEmployee($params[3]));
				}
				else if(isset($params[2]) && $params[2] == "company"){
					echo json_encode($this->model->listQbCompany($params[3]));
				}
			}
			else {
				if(!empty($params[1]) && $params[1] == "employees"){
					echo json_encode($this->model->listQbEmployees());
				}
				else if(!empty($params[1]) && $params[1] == "employeesForQb"){
					echo json_encode($this->model->listQbEmployeesInCompany());
				}
				else if(!empty($params[1]) && $params[1] == "companies"){
					echo json_encode($this->model->listQbCompanies());
				}
			}
		}
	}

	public function invoice($params = NULL){
		$invoices = $this->model->createInvoices();
	}

	public function timesheet($params = NULL){
			$action = $params[0];

			if($action == "download"){
				$by = $params[1];
				$value = $params[2];
				$dateStarting = $params[3];
				$dateEnding = $params[4];
				$includeType = [];

				if((isset($params[5]) && $params[5] == "includeAll") || !isset($params[5])) {
					$includeType = "all";
				}
				else {
					if(isset($params[5]) && $params[5] == "includeApproved"){
						$includeType = "approved";
					}
					if(isset($params[5]) && $params[5] == "includeApprovedAndSubmitted"){
						$includeType = "submitted";
					}
					if(isset($params[5]) && $params[5] == "includeApprovedAndSubmittedAndIncomplete"){
						$includeType = "incomplete";
					}
				}

				if($by == "projects"){
					if($value == "all"){
						$employeesList = $this->model->listEmployees();	
					}
					else {
						$employeesList = $this->model->listEmployeesInProject($value); 
					}
					
					if(isset($params[6]) && $params[6] == "excel"){
						$data_url = APP_FULL_URL."/api/timesheet/download/projects/all/".$dateStarting."/".$dateEnding."/".$params[5]."&sortBy=project&weekStart=".$dateStarting."&weekEnd=".$dateEnding;
						$this->model->timesheetReportDownload("excel", $data_url);
					}
					else {
						$filteredTimesheets = $this->model->filterTimesheetsBy("date",$dateStarting,$includeType,$employeesList);
						//var_dump($filteredTimesheets);

						foreach($filteredTimesheets as $timesheetKey => $timesheetVal){
							$filteredTimesheets[$timesheetKey] = $this->model->calcCaTimesheet($filteredTimesheets[$timesheetKey]);
						}

						echo json_encode($filteredTimesheets, true);
					}
				}
			}

			else {
				$approval = $params[0];
				$employeeID = $params[1];
				$timesheetSundayDate = $params[2];
				$timesheetToken = $params[3];
				
				$submitForApproval = $this->model->timesheets($employeeID, $timesheetToken, $timesheetSundayDate, $params[0]);

				//RENDER PAGE META
				$this->view->page['name'] = $this->request->controller;
				$this->view->page['title'] = $this->request->action;

				//RENDER HEADER
				$this->view->render("/header");

				if($submitForApproval){
					if($approval == "approve"){
						$this->view->msg['message'] = "You will be redirected. <strong>Thanks for approving.</strong> ";
						$this->view->data['redirect'] = true;
					}
					else if($approval == "disapprove"){
						$this->view->msg['message'] = "<strong>Disapproved.</strong> Employee will be notified.";
					}
				}

				else {
					$timesheetSundayDate = str_replace("-","/",$timesheetSundayDate);
					$listTimesheetByDate = $this->model->listTimeSheetByDate($employeeID, $timesheetSundayDate);
					$status;

					if($listTimesheetByDate['status'] == "approved"){
						$status = "approved";
						$this->view->msg['message'] = "This timesheet has already <strong>been ".$status.".</strong>";
					}
					else if ($listTimesheetByDate['status'] == "disapproved"){
						$status = "disapproved";
						$this->view->msg['message'] = "This timesheet has already <strong>been ".$status.".</strong>";
					}
					else {
						$status = "incomplete";
						$this->view->msg['message'] = "The employee's timesheet is <strong>".$status.".</strong>";
					}

					
				}	

				$this->view->render("/api/timesheet/index");
			
				//RENDER FOOTER
				$this->view->render("/footer");
			}

	}

	public function dashboard($params = NULL){

	}
}

?> 