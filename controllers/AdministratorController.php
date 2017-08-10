<?php

class AdministratorController extends Controller {

	public function __construct($params = NULL){	
		parent::__construct();	
		Auth::handleLogin();
	}

	public function index($params = NULL){
		$this->dashboard();
	}

	public function dashboard($params = NULL){

		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;

		//RENDER BODY
		if(User::checkRole("admin")) {
			$this->view->page['title'] = "";
			$this->companies();
		}
		else {
			$this->timeSheet();
		}
	}

	public function projects($params = NULL){
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER BODY
		//PAGE: Projects: Edit project
		if (isset($params[0]) && $params[0] == "edit" && isset($params[1])){
			$this->view->form = $this->model->listProject($params[1]);

			//If request post exists then try to see if we can update the project
			if(!empty($this->request->post)){

				unset($this->view->form);

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				//If supervisor list exist then we have to get the ID and find name and email
				if(!empty($post['supervisors']) && isset($post['supervisors'])){
					foreach($post['supervisors'] as $supervisorKey => $supervisorVal){
						$supervisorID = $post['supervisors'][$supervisorKey];
						$supervisor = $this->model->listSupervisor($supervisorID);

						unset($post['supervisors'][$supervisorKey]);
						$post['supervisors'][$supervisorKey]['ID'] = $supervisorID;
						$post['supervisors'][$supervisorKey]['firstname'] = $supervisor['firstname'];
						$post['supervisors'][$supervisorKey]['lastname'] = $supervisor['lastname'];
						$post['supervisors'][$supervisorKey]['email'] = $supervisor['email'];
					}
				}
				else {

				}

				$this->view->form = $post;
				//Put inputs required in a variable
				$nameRequired = Form::isRequired($post['name']);
				$companyRequired = Form::isRequired($post['company']);
				$companyExistInProjects = $this->model->companyExistInProjects($post['company'], $params[1]);

				//If inputs all were required and exists
				if($nameRequired && $companyRequired && !$companyExistInProjects){

					//Put Project into a variable
					$supervisorsToAdd = [];

					if(isset($post['supervisors'])){
						foreach ($post['supervisors'] as $key => $value){
							$supervisorsToAdd[] = $post['supervisors'][$key]['ID'];
						}
					}
					$employeesToAdd = $this->model->listProject($params[1])['employees'];

					$post['supervisors'] = $supervisorsToAdd;
					$post['employees'] = $employeesToAdd;

					$saveProject = $this->model->saveProject($params[1], $post);

					//If update is successfull then redirect to employee main page with a success msg
					if($saveProject){
						$this->view->msg['message'] = "Sucessfully saved ".$post['name'].".";
						$this->view->msg['type'] = "success";
					}
					else {
						$this->view->msg['message'] = "Project with company already exists.";
						$this->view->msg['type'] = "error";
					}
				}

				//If there is an error in any input 
				else {

					//Send page error message
					$this->view->msg['type'] = "error";


					//Send view error of which input was required but didn't exist
					if(!$nameRequired || !$companyRequired){ 					
						$this->view->msg['message'] = "Make sure fields are not empty! "; 
					 	if(!$nameRequired || !$companyRequired) { $this->view->form['error']['name'] = true; }
					}

					if($companyExistInProjects){  						
						$this->view->msg['message'] .= "Project with company already exists. "; 
						$this->view->form['error']['company'] = true;  
					}					
				
				}
			}

			else { //If we don't get a post
									
				$projects =  $this->model->listProject($params[1]);

				//If project list exist then we have to get the ID and find name
				if(!empty($projects['supervisors'])){

						foreach($projects['supervisors'] as $key => $id){
							$supervisor = $this->model->listSupervisor($id);

							$projects['supervisors'][$key] = $supervisor;
							$projects['supervisors'][$key]['ID'] = $id;
						}
				}			

				$this->view->form = $projects;	
			}

			$this->view->data['projectID'] = $params[1];	
			$this->view->data['companiesList'] = $this->model->listCompanies();
			$this->view->render("/administrator/projects/edit");
		}

		//PAGE: Projects: Add project
		else if (isset($params[0]) && $params[0] == "add"){
			//$this->view->form = $this->model->listProject($params[1]);

			//If request post exists then try to see if we can add employee
			if(!empty($this->request->post)){	

				//Grab post from request & put all form input into view
				$post = $this->request->post;

				//If supervisor list exist then we have to get the ID and find name and email
				if(!empty($post['supervisors']) && isset($post['supervisors'])){
					foreach($post['supervisors'] as $supervisorKey => $supervisorVal){
						$supervisorID = $post['supervisors'][$supervisorKey];
						$supervisor = $this->model->listSupervisor($supervisorID);

						unset($post['supervisors'][$supervisorKey]);
						$post['supervisors'][$supervisorKey]['ID'] = $supervisorID;
						$post['supervisors'][$supervisorKey]['firstname'] = $supervisor['firstname'];
						$post['supervisors'][$supervisorKey]['lastname'] = $supervisor['lastname'];
						$post['supervisors'][$supervisorKey]['email'] = $supervisor['email'];
					}
				}

				$this->view->form = $post;
				
				//Put inputs required in a variable
				$nameRequired = Form::isRequired($post['name']);
				$companyRequired = Form::isRequired($post['company']);
				$companyExistInProjects = $this->model->companyExistInProjects($post['company']);

				//If inputs all were required and exists
				if($nameRequired && $companyRequired && !$companyExistInProjects){

					//Put Project into a variable
					if(isset($post['supervisors'])){
						$supervisorsToAdd;
						foreach ($post['supervisors'] as $key => $value){
							$supervisorsToAdd[] = $post['supervisors'][$key]['ID'];
						}

						$post['supervisors'] = $supervisorsToAdd;
					}
						
					$addProject = $this->model->addProject($post);

					//If add employee is successfull then redirect to employee main page with a success msg
					if($addProject){
						$msg['message'] = "Sucessfully added ".$post['name']." to the company.";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$msg['type']."/".$msg['message']."/");
					}
					else {
						$this->view->msg['message'] = "Project with company already exists.";
						$this->view->msg['type'] = "error";
					}

				}

				//If there is an error in any input 
				else {

					//Send page error message
					$this->view->msg['type'] = "error";

					//Send view error of which input was required but didn't exist
					if(!$nameRequired || !$companyRequired){ 					
						$this->view->msg['message'] = "Make sure fields are not empty! "; 
					 	if(!$nameRequired || !$companyRequired) { $this->view->form['error']['name'] = true; }
					}

					if($companyExistInProjects){  						
						$this->view->msg['message'] .= "Project with company already exists. "; 
						$this->view->form['error']['company'] = true;  
					}
				}

			}	

			//Render project add page
			$this->view->data['companiesList'] = $this->model->listCompaniesFilter("existingCompanies");

			$this->view->form['supervisorsSelector'] = $this->model->listSupervisors();
			$this->view->render("/administrator/projects/add");
		}

		//PAGE: Project: Default
		else {
			if(isset($params[0])){
				//ACTION: Projects: Delete project
				if($params[0] == "delete" && isset($params[1])){
					if($this->model->deleteProject($params[1])){
						$this->view->msg['message'] = "Successfully deleted project.";
						$this->view->msg['type'] = "success";
					}
				}

				//ACTION: Projects: Recieve messages
				if($params[0] == "msg" && isset($params[1]) && isset($params[2])){
					if($params[1] == "success"){
						$this->view->msg['message'] = urldecode($params[2]);
						$this->view->msg['type'] = "success";
					}
					else if($params[1] == "error"){

					}
				}
			}
			//For each project, locate company id in database and find name
			$this->view->data['projectsList'] = $this->model->listProjects();

			if(isset($this->view->data['projectsList'])){
				foreach($this->view->data['projectsList'] as $key => $value){
					$this->view->data['projectsList'][$key]['company'] = $this->model->listCompany($this->view->data['projectsList'][$key]['company'])['name']; 
				}
			}

			$this->view->render("/administrator/projects/index");
		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function supervisors($params){
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER BODY

		//PAGE: Supervisors: Edit supervisor
		if (isset($params[0]) && $params[0] == "add"){
			//If request post exists then try to see if we can update the supervisor
			if(!empty($this->request->post)){

				///Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				//Put inputs required in a variable
				$firstnameRequired = Form::isRequired($post['firstname']);
				$lastnameRequired = Form::isRequired($post['lastname']);
				$emailRequired = Form::isRequired($post['email']);

				//If inputs all were required and exists
				if($firstnameRequired && $emailRequired && $lastnameRequired){

					if(!empty($post['projects'])){
						$projectsToAdd;
						
						foreach ($post['projects'] as $key => $value){
							$projectsToAdd[] = $post['projects'][$key];
						}

						$post['projects'] = $projectsToAdd;
					}

					//Put add supervisor into a variable
					$addSupervisor = $this->model->addSupervisor($post);

					//If add supervisor is successfull then redirect to main page with a success msg
					if($addSupervisor){
						$msg['message'] = "Sucessfully added ".$post['firstname']." ".$post['lastname'].".";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$msg['type']."/".$msg['message']."/");
					}
				}

				//If there is an error in any input 
				else {
					//If user posted projects but not validated then get ID and Name
					if(!empty($post['projects'])){

						$listProjects = $this->model->listProjects();

						foreach($post['projects'] as $key => $id){ 
							foreach($listProjects as $projects){ 
								if($projects['ID'] == $id){	
									unset($post['projects'][$key]); 

									$post['projects'][$key]['ID'] = $id;
									$post['projects'][$key]['name'] = $projects['name'];
									$post['projects'][$key]['company'] = $projects['company'];
									$post['projects'][$key]['supervisors'] = $projects['supervisors'];
								}
							}
						}
					}

					$this->view->form = $post;

					//Send page error message
					$this->view->msg['type'] = "error";

					if(!$firstnameRequired || !$lastnameRequired || !$emailRequired){

						//Send view error of which input was required but didn't exist
						if(!$firstnameRequired){  $this->view->form['error']['firstname'] = true;	}
						if(!$lastnameRequired){  $this->view->form['error']['lastname'] = true;  }
						if(!$emailRequired){  $this->view->form['error']['email'] = true;  }

						$this->view->msg['message'] = "Make sure fields are not empty! ";
					}

				}
			}

			$this->view->render("/administrator/supervisors/add");
		}

		//PAGE: Supervisors: Edit supervisor
		else if (isset($params[0]) && $params[0] == "edit" && isset($params[1])){
			
			//If request post exists then try to see if we can update the supervisor
			if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;

				//If user posted projects that we already have on the server, only get the ones we need to add
				if(!empty($post['projects'])){

					$listProjects = $this->model->listProjects();

					foreach($post['projects'] as $key => $id){ 
						foreach($listProjects as $projects){ 
							if($projects['ID'] == $id){	
								unset($post['projects'][$key]); 

								$post['projects'][$key]['ID'] = $id;
								$post['projects'][$key]['name'] = $projects['name'];
								$post['projects'][$key]['company'] = $projects['company'];
								$post['projects'][$key]['supervisors'] = $projects['supervisors'];
							}
						}
					}
				}

				$this->view->form = $post;

				//Put inputs required in a variable
				$firstnameRequired = Form::isRequired($post['firstname']);
				$lastnameRequired = Form::isRequired($post['lastname']);
				$emailRequired = Form::isRequired($post['email']);

				//If inputs all were required and exists
				if($firstnameRequired && $emailRequired && $lastnameRequired){

					if(!empty($post['projects'])){
						$projectsToAdd;
						
						foreach ($post['projects'] as $key => $value){
							$projectsToAdd[] = $post['projects'][$key]['ID'];
						}

						$post['projects'] = $projectsToAdd;
					}

					//Put save supervisor into a variable
					$saveSupervisor = $this->model->saveSupervisor($params[1], $post);

					//If add supervisor is successfull then redirect to main page with a success msg
					if($saveSupervisor){
						$this->view->msg['message'] = "Sucessfully saved ".$post['firstname']." ".$post['lastname'].".";
						$this->view->msg['type'] = "success";
					}
				}

				//If there is an error in any input 
				else {

					//Send page error message
					$this->view->msg['type'] = "error";

					if(!$firstnameRequired || !$lastnameRequired || !$emailRequired){

						//Send view error of which input was required but didn't exist
						if(!$firstnameRequired){  $this->view->form['error']['firstname'] = true;	}
						if(!$lastnameRequired){  $this->view->form['error']['lastname'] = true;  }
						if(!$emailRequired){  $this->view->form['error']['email'] = true;  }

						$this->view->msg['message'] = "Make sure fields are not empty! ";
					}

				}

			}

		
			$this->view->data['supervisorID'] = $params[1];	
			$this->view->data['projectsList'] = $this->model->listProjects();

			$post = $this->request->post;

			$this->view->form = $post;

			if(!isset($post)){
				$supervisor = $this->model->listSupervisor($params[1]);
				unset($supervisor['projects']);

				$supervisorProjects =  $this->model->listProjectsOfSupervisor($params[1]);

				//If supervisor's project list exist then we have to get the ID and find name
				if(!empty($supervisorProjects)){

						foreach($supervisorProjects as $key => $val){
							$project = $this->model->listProject($supervisorProjects[$key]);
							$supervisor['projects'][] = $project;
						}
				}

				$this->view->form =	$supervisor;
			}
			else {

				if(isset($this->view->form['projects'])){
					foreach($this->view->form['projects'] as $projectKey => $projectVal){
						$projectID = $projectVal;
						unset($this->view->form['projects'][$projectKey]);
						$this->view->form['projects'][$projectKey] = $this->model->listProject($projectID);
					}
				}
			}

			$this->view->render("/administrator/supervisors/edit");
		}

		//Default Page
		else {

			//ACTION: Projects: Delete project
			if(isset($params[0]) && $params[0] == "delete"){
				if($this->model->deleteSupervisor($params[1])){
					$this->view->msg['message'] = "Successfully deleted supervisor.";
					$this->view->msg['type'] = "success";
				}
			}

			$this->view->data['supervisorsList'] = $this->model->listSupervisors();
			$this->view->data['projectsList'] = $this->model->listProjects();

			if(!empty($supervisor['projects'])){

					foreach($supervisor['projects'] as $key => $id){
						$project = $this->model->listProject($id);

						$supervisor['projects'][$key] = $project;
						$supervisor['projects'][$key]['ID'] = $id;
					}
			}


			$supervisorsList = $this->view->data['supervisorsList'];
			$projectsList = $this->view->data['projectsList'];

			//Look up supervisors in the projects
			foreach($supervisorsList as $supervisorKey => $supervisorVal){

				//Get Supervisor's projects
				$supervisorProjects = $this->model->listProjectsOfSupervisor($supervisorsList[$supervisorKey]['ID']);
				
				if(!empty($supervisorProjects)){
					foreach($supervisorProjects as $projectID){
						$project = $this->model->listProject($projectID);
						foreach($project['supervisors'] as $supervisorsID){
							if($supervisorsID == $supervisorsList[$supervisorKey]['ID']){
								$this->view->data['supervisorsList'][$supervisorKey]['projects'][] = $project; 
							}
						}
					}
				}
			}

			//ACTION: Employees: Recieve messages
			if(isset($params[0]) && $params[0] == "msg"){
				if($params[1] == "success"){
					$this->view->msg['message'] = urldecode($params[2]);
					$this->view->msg['type'] = "success";
				}
				else if($params[1] == "error"){

				}
			}

			$this->view->render("/administrator/supervisors/index");
		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function invoices($params = NULL){

		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		if(isset($params[0]) && $params[0] == "process"){

			if(isset($params[3])){
				$pageKey = $params[3];
			}
			else {
				$pageKey = 0;
			}

			if(isset($params[1]) && isset($params[2])){
					$this->view->data['saturdayDate'] = $this->model->formatDate($params[2]);
					$this->view->data['sundayDate'] = $this->model->formatDate($params[1]);
					$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($params[2]);
					$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($params[1]);
					$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($params[1]);
					$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($params[2]);

					$employeesList = $this->model->listEmployeesWithTimesheetByDate($params[1]);
			}
			else {
					$this->view->data['saturdayDate'] = $this->model->getLastSaturday();
					$this->view->data['sundayDate'] = $this->model->getLastSunday();
					$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSaturday());
					$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSunday());
					$employeesList = $this->model->listEmployeesWithTimesheetByDate($this->model->getLastSunday());
			}
			
			$timesheetsCatByCompany = $this->model->calcInvoices($this->view->data['sundayDate']);

			$this->view->data['timesheetsList'] = $timesheetsCatByCompany;

			$i = 0;
			$companyID;

			//var_dump($timesheetsCatByCompany);

			foreach($timesheetsCatByCompany as $key => $val){
				if($i == $pageKey){
					$companyID = $timesheetsCatByCompany[$key]['ID'];
				}  
			$i++;
			}

			$this->view->data['startDate'] = $params[1];
			$this->view->data['endDate'] = $params[2];
			$this->view->data['company'] = $this->model->listCompany($companyID);
			$this->view->data['pageKey'] = $pageKey;
			$this->view->data['employeesList'] = $employeesList;
			$this->view->data['invoiceLines'] = json_decode($this->model->getQbInvoice($companyID, $params[1])['data'], TRUE)['lines'];

			if(isset($params[4]) && $params[4] == "submit"){
				if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				//var_dump($post);

					//$lines = $this->model->getQbInvoiceArray($timesheetsCatByCompany[$pageKey]);
					$lines = $post['lines'];
					$qbCompanyID = $this->view->data['company']['qb_id'];
					$companyID = $companyID;
					$companyName = $this->view->data['company']['name'];
					$projectName = $this->model->getCompanysProject($this->view->data['company']['ID']);
					$startDate = $params[1];
					$endDate = $params[2];
					$emails = json_decode($this->model->getSystemSettings("invoice-emails")['value']);
					$this->view->data['invoiceLines'] = $lines;

					foreach($lines as $lK => $lV){
						if(isset($lines[$lK]['regHours'])){ $this->view->data['invoiceLines'][$lK]['invoice']['regHours'] = $lines[$lK]['regHours']; }
						if(isset($lines[$lK]['regRate'])){ $this->view->data['invoiceLines'][$lK]['invoice']['regRate'] = $lines[$lK]['regRate']; }
						if(isset($lines[$lK]['regAmount'])){ $this->view->data['invoiceLines'][$lK]['invoice']['regAmount'] = $lines[$lK]['regAmount']; }
						if(isset($lines[$lK]['otHours'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otHours'] = $lines[$lK]['otHours']; }
						if(isset($lines[$lK]['otRate'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otRate'] = $lines[$lK]['otRate']; }
						if(isset($lines[$lK]['otAmount'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otAmount'] = $lines[$lK]['otAmount']; }
						if(isset($lines[$lK]['dblHours'])){ $this->view->data['invoiceLines'][$lK]['invoice']['dblHours'] = $lines[$lK]['dblHours']; }
						if(isset($lines[$lK]['dblRate'])){ $this->view->data['invoiceLines'][$lK]['invoice']['dblRate'] = $lines[$lK]['dblRate']; }
						if(isset($lines[$lK]['dblAmount'])){ $this->view->data['invoiceLines'][$lK]['invoice']['dblAmount'] = $lines[$lK]['dblAmount']; }
						if(isset($lines[$lK]['otherHours'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otherHours'] = $lines[$lK]['otherHours']; }
						if(isset($lines[$lK]['otherRate'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otherRate'] = $lines[$lK]['otherRate']; }
						if(isset($lines[$lK]['otherAmount'])){ $this->view->data['invoiceLines'][$lK]['invoice']['otherAmount'] = $lines[$lK]['otherAmount']; }
					}

					$createInvoices = $this->model->createInvoice($qbCompanyID, $companyID, $projectName, $startDate, $endDate, $emails, $lines);

					if($createInvoices){
						$this->view->msg['message'] = "Sucessfully created invoice for company.";
						$this->view->msg['type'] = "success";
					}
					else {
						$this->view->msg['message'] = "This invoice has been already created.";
						$this->view->msg['type'] = "error";
					}

				}
			}
			else if(isset($params[4]) && $params[4] == "delete"){
				$qbInvoiceID = $this->model->getQbInvoice($companyID, $params[1])['qb_ID'];

				if($this->model->deleteInvoice($qbInvoiceID)){
					$this->view->msg['message'] = "Sucessfully deleted invoice for company.";
					$this->view->msg['type'] = "success";
				}
				else {
					if(!empty($this->model->deleteInvoice($qbInvoiceID))){
						$this->view->msg['message'] = "Could not delete invoice.";
						$this->view->msg['type'] = "error";
					}
				}
			}			

			$this->view->data['invoiceExists'] = $this->model->checkInvoiceExists("companyID", array($companyID, $params[1]));
			

			//var_dump($this->view->data['invoiceExists']);

			//RENDER BODY
			$this->view->render("/administrator/invoices/process");
		}
		else {
			if(isset($params[0]) && isset($params[1])){
					$this->view->data['saturdayDate'] = $this->model->formatDate($params[1]);
					$this->view->data['sundayDate'] = $this->model->formatDate($params[0]);
					$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($params[1]);
					$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($params[0]);
					$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($params[0]);
					$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($params[1]);
					$this->view->data['sundayDateForLinkShort'] = $this->model->formatDateForLink($params[0],"-","short");
					$this->view->data['saturdayDateForLinkShort'] = $this->model->formatDateForLink($params[1],"-","short");

					$employeesList = $this->model->listEmployeesWithTimesheetByDate($params[0]);
			}
			else {
					$this->view->data['saturdayDate'] = $this->model->getLastSaturday();
					$this->view->data['sundayDate'] = $this->model->getLastSunday();
					$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSaturday());
					$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSunday());
					$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSunday());
					$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSaturday());
					$this->view->data['sundayDateForLinkShort'] = $this->model->formatDateForLink($this->model->getLastSunday(),"-","short");
					$this->view->data['saturdayDateForLinkShort'] = $this->model->formatDateForLink($this->model->getLastSaturday(),"-","short");
					$employeesList = $this->model->listEmployeesWithTimesheetByDate($this->model->getLastSunday());

			}

			$timesheetsCatByCompany = $this->model->calcInvoices($this->view->data['sundayDate']);

			$this->view->data['timesheetsList'] = $timesheetsCatByCompany;
			$companyCount = count($timesheetsCatByCompany);
			$employeeCount = count($employeesList);

			$this->view->data['employeesList'] = $employeesList;


			if($companyCount > 0){
				$this->view->msg['message'] = "There are ".$companyCount." invoices that haven't been processed for this week.&nbsp;&nbsp;&nbsp; <a href='".APP_URL."/administrator/invoices/process/".$this->view->data['sundayDateForLinkShort']."/".$this->view->data['saturdayDateForLinkShort']."'><i class='fa fa-share' aria-hidden='true'></i>Process</a>";
				$this->view->msg['type'] = "warning";
			}

			//RENDER BODY
			$this->view->render("/administrator/invoices/index");
		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function employees($params = NULL) {

		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		//RENDER PAGE MESSAGE
		$this->view->msg['message'] = $this->request->get['msg'];
		$this->view->msg['type'] = $this->request->get['msgType'];


		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER BODY

		//PAGE: Employees: Add employee
		if (isset($params[0]) && $params[0] == "add"){

			//If request post exists then try to see if we can add employee
			if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$this->view->form['qbEmployee'] = $this->model->listQbEmployee($this->view->form['qbEmployee']);
				

				if(isset($this->view->form['projectsSelector'])){
					$this->view->form['project'] = $this->model->listProject($post['projectsSelector']);
					$this->view->form['company'] = $this->model->listCompany($this->view->form['project']['company']);			

					$post['company']['ID'] = $post['companiesSelector'];
					$post['project']['ID'] = $post['projectsSelector'];
				}
				else {
					$post['companiesSelector'] = NULL;
					$post['projectsSelector'] = NULL;
				}

				//Put inputs required in a variable
				$qbEmployeeRequired = Form::isRequired($post['qbEmployee']);
				$usernameRequired = Form::isRequired($post['username']);
				$passwordRequired = Form::isRequired($post['password']);
				$passwordConfirmRequired = Form::isRequired($post['passwordConfirm']);
				$passwordConfirmMatches = Form::matches($post['password'], $post['passwordConfirm']);
				$emailRequired = Form::isRequired($post['email']);
				$firstnameRequired = Form::isRequired($post['firstname']);
				$lastnameRequired = Form::isRequired($post['lastname']);
				$companyRequired = Form::isRequired($post['companiesSelector']);
				$projectsRequired = Form::isRequired($post['projectsSelector']);
				$roleRequired = Form::isRequired($post['role']);

				//If inputs all were required and exists
				if($qbEmployeeRequired && $usernameRequired && $passwordRequired && $passwordConfirmRequired && $passwordConfirmMatches && $emailRequired && $firstnameRequired && $lastnameRequired && $companyRequired && $roleRequired){

					//Put addEmployee into a variable
					$addEmployee = $this->model->addEmployee($post);

					//If add employee is successfull then redirect to employee main page with a success msg
					if($addEmployee){
						$msg['message'] = "Sucessfully added ".$post['firstname']." ".$post['lastname']." to the company.";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$msg['type']."/".$msg['message']."/");
					}

					else {
						$this->view->msg['type'] = "error";
						$this->view->msg['message'] = "The username already exists.";
					}
				}

				//If there is an error in any input 
				else {

					//Send page error message
					$this->view->msg['type'] = "error";
					$this->view->msg['message'] = "Make sure fields are not empty! ";

					if(!$qbEmployeeRequired || !$usernameRequired || !$passwordRequired || !$passwordConfirmRequired || !$passwordConfirmMatches || !$emailRequired || !$firstnameRequired || !$lastnameRequired || !$companyRequired || !$roleRequired){

						//Send view error of which input was required but didn't exist
						if(!$qbEmployeeRequired){  $this->view->form['error']['qbEmployee'] = true;	}
						if(!$usernameRequired){  $this->view->form['error']['username'] = true;	}
						if(!$passwordRequired){  $this->view->form['error']['password'] = true;	}
						if(!$passwordConfirmRequired){  $this->view->form['error']['passwordConfirm'] = true;  }
						if(!$passwordConfirmMatches){  $this->view->form['error']['passwordConfirm'] = true; $this->view->msg['message'] .= " Password's do not match!"; }
						if(!$emailRequired){  $this->view->form['error']['email'] = true;  }
						if(!$firstnameRequired){  $this->view->form['error']['firstname'] = true;  }
						if(!$lastnameRequired){  $this->view->form['error']['lastname'] = true;  }
						if(!$companyRequired){  $this->view->form['error']['company'] = true;  }
						if(!$projectsRequired){  $this->view->form['error']['projects'] = true;  }
						if(!$roleRequired){  $this->view->form['error']['role'] = true;  }


					}
				}

			}

			//Render employee add page
			$this->view->data['qbEmployeesList'] = $this->model->listQbEmployees();
			$this->view->data['companiesList'] = $this->model->listCompanies();
			$this->view->render("/administrator/employees/add");
		}

		else if(isset($params[0]) && $params[0] == "edit"){

			//RENDER PAGE DATA
			$this->view->data['companiesList'] = $this->model->listCompanies();
			$this->view->data['qbEmployeesList'] = $this->model->listQbEmployees();

			$accountInfo = $this->model->getAccountInfo($params[1]);
			$this->view->form['ID'] = $accountInfo['ID'];
			$this->view->form['username'] = $accountInfo['username'];
			$this->view->form['email'] = $accountInfo['email'];
			$this->view->form['firstname'] = $accountInfo['firstname'];
			$this->view->form['lastname'] = $accountInfo['lastname'];
			$this->view->form['project'] = $this->model->listProject($accountInfo['projectEmployeeIn']);
			$this->view->form['company'] = $this->model->listCompany($this->view->form['project']['company']);
			$this->view->form['role'] = $accountInfo['role'];
			$this->view->form['reference'] = $accountInfo['reference'];
			$this->view->form['state'] = $accountInfo['state'];
			$this->view->form['qbEmployee'] = $accountInfo['quickbooks'];

			if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$this->view->form['ID'] = $post['ID'];
				$this->view->form['project'] = $this->model->listProject($post['projectsSelector']);
				$this->view->form['company'] = $this->model->listCompany($this->view->form['project']['company']);			
				$this->view->form['reference'] = $accountInfo['reference'];
				$this->view->form['qbEmployee'] = $this->model->listQbEmployee($this->view->form['qbEmployee']);

				$post['project']['ID'] = $post['projectsSelector'];

				//Put inputs required in a variable
				$qbEmployeeRequired = Form::isRequired($post['qbEmployee']);
				$usernameRequired = Form::isRequired($post['username']);
				$emailRequired = Form::isRequired($post['email']);
				$firstnameRequired = Form::isRequired($post['firstname']);
				$lastnameRequired = Form::isRequired($post['lastname']);
				$companyRequired = Form::isRequired($post['companiesSelector']);
				$projectRequired = Form::isRequired($post['projectsSelector']);
				$roleRequired = Form::isRequired($post['role']);
				$passwordRequired = true;
				$passwordConfirmRequired = true;
				$passwordConfirmMatches = true;

				if($post['password'] !== "********"){
					$passwordRequired = Form::isRequired($post['password']);
					$passwordConfirmRequired = Form::isRequired($post['passwordConfirm']);
					$passwordConfirmMatches = Form::matches($post['password'],$post['passwordConfirm']);
				}
				else {
					$post['password'] = "";
				}

				//If inputs all were required and exists
				if($qbEmployeeRequired && $usernameRequired && $emailRequired && $firstnameRequired && $lastnameRequired && $projectRequired && $companyRequired && $roleRequired && $passwordRequired && $passwordConfirmRequired && $passwordConfirmMatches){

					//Put addEmployee into a variable
					$updateEmployee = $this->model->updateEmployee($this->view->form['ID'],$post);

					//If update is successfull then send message
					if($updateEmployee){
						$this->view->msg['message'] = "Sucessfully updated account information.";
						$this->view->msg['type'] = "success";
					}
					else {
						$this->view->msg['type'] = "error";
						$this->view->msg['message'] = "The username already exists.";
					}
				}

				//If one input was required but doesn't exist
				else {
					//Send page error message
					$this->view->msg['type'] = "error";
					$this->view->msg['message'] .= "Make sure fields are not empty!";

					//Send view error of which input was required but didn't exist
					if(!$qbEmployeeRequired){  $this->view->form['error']['qbEmployee'] = true; }
					if(!$usernameRequired){  $this->view->form['error']['username'] = true;	}
					if(!$passwordRequired){  $this->view->form['error']['password'] = true;	}
					if(!$passwordConfirmRequired){  $this->view->form['error']['passwordConfirm'] = true; }
					if(!$passwordConfirmMatches){  $this->view->form['error']['passwordConfirm'] = true; $this->view->msg['message'] .= " Password's do not match!"; }
					if(!$emailRequired){  $this->view->form['error']['email'] = true; }
					if(!$firstnameRequired){  $this->view->form['error']['firstname'] = true; }
					if(!$lastnameRequired){  $this->view->form['error']['lastname'] = true; }
					if(!$companyRequired){  $this->view->form['error']['company'] = true; }
					if(!$roleRequired){  $this->view->form['error']['role'] = true; }
				}
			}

			//Render employee add page
			$this->view->render("/administrator/employees/edit");
		}
		

		//PAGE: Employees: Default 
		else {

			//ACTION: Employees: Delete Employee
			if(isset($params[0]) && $params[0] == "delete"){
				if($this->model->deleteEmployees($params[1]) && $params[1] !== 0){
					$this->view->msg['message'] = "Successfully deleted user.";
					$this->view->msg['type'] = "success";
				}
				else {
					$this->view->msg['message'] = "Cannot delete administrator.";
					$this->view->msg['type'] = "error";
				}
			}


			//ACTION: Employees: Recieve messages
			if(isset($params[0]) && $params[0] == "msg"){
				if($params[1] == "success"){
					$this->view->msg['message'] = urldecode($params[2]);
					$this->view->msg['type'] = "success";
				}
				else if($params[1] == "error"){

				}
			}

			//Get list of employees
			$employeesList = $this->model->listEmployees();

			foreach($employeesList as $employeesKey => $employeesVal){
				$employeesList[$employeesKey]['projectEmployeeIn'] = $this->model->listProject($this->model->listEmployeesProject($employeesList[$employeesKey]['ID']))['name'];
			}

			$this->view->data['employeesList'] = $employeesList;
			$this->view->render("/administrator/employees/index");

		}			

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function account($params = NULL) {
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		//RENDER PAGE DATA
		$this->view->data['companiesList'] = $this->model->listCompanies();

		//RENDER MESSAGE
		$this->view->msg['message'] = $this->request->get['msg'];
		$this->view->msg['type'] = $this->request->get['msgType'];

		//RENDER FORM DATA
		$accountInfo = $this->model->getAccountInfo($this->request->session['ID']);
		$this->view->form['username'] = $accountInfo['username'];
		$this->view->form['email'] = $accountInfo['email'];
		$this->view->form['firstname'] = $accountInfo['firstname'];
		$this->view->form['lastname'] = $accountInfo['lastname'];
		$this->view->form['project'] = $this->model->listProject($accountInfo['projectEmployeeIn']);
		$this->view->form['company'] = $this->model->listCompany($this->view->form['project']['company']);
		$this->view->form['role'] = $accountInfo['role'];
		$this->view->form['reference'] = $accountInfo['reference'];

		//RENDER BODY
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//PAGE: Account: Save Account
		//If request post exists then try to see if we can add employee
		if(!empty($this->request->post)){

			//Grab post from request & put all form input into view
			$post = $this->request->post;
			$notInProject = false;
			$cannotChangeAdmin = false;

			//If not admin then make sure fields that are disabled are filled in
			if(!User::checkRole("admin")) {

				$post['username'] = $this->view->form['username'];
				$post['firstname'] = $this->view->form['firstname'];
				$post['lastname'] = $this->view->form['lastname'];
				$post['project'] = $this->view->form['project'];
				$post['company'] = $this->view->form['company'];
				$post['role'] = $this->view->form['role'];
				$post['reference'] = $accountInfo['reference'];

				$this->view->form = $post;

				if(!empty($post['project'])){
					$post['projectsSelector'] = $post['project']['ID'];
					$post['companiesSelector'] = $post['company']['ID'];
				} 
				else {
					$notInProject = true;
				}
			}

			else {
				$post['username'] = "admin";
				$post['role'] = "admin";
				$this->view->form = $post;

				if(empty($this->view->form['project'])){ //Not in a project
					$notInProject = true;

					if(!empty($post['projectsSelector']) || isset($post['projectsSelector'])){ //Not in project but post submitted
						if($post['projectsSelector'] !== "0"){ //If what was submitted isn't base project then show that can't change
							$cannotChangeAdmin = true;
						}
						else if ($post['projectsSelector'] == "0"){
							$post['project']['ID'] = "0";
						}
					}
				}


				$this->view->form['project'] = $this->model->listProject($post['projectsSelector']);
				$this->view->form['company'] = $this->model->listCompany($this->view->form['project']['company']);			
			}

			//Put inputs required in a variable
			$usernameRequired = Form::isRequired($post['username']);
			$emailRequired = Form::isRequired($post['email']);
			$firstnameRequired = Form::isRequired($post['firstname']);
			$lastnameRequired = Form::isRequired($post['lastname']);
			$projectRequired = Form::isRequired($post['projectsSelector']);
			$companyRequired = Form::isRequired($post['companiesSelector']);
			$roleRequired = Form::isRequired($post['role']);
			$passwordRequired = true;
			$passwordConfirmRequired = true;
			$passwordConfirmMatches = true;

			if($post['password'] !== "********"){
				$passwordRequired = Form::isRequired($post['password']);
				$passwordConfirmRequired = Form::isRequired($post['passwordConfirm']);
				$passwordConfirmMatches = Form::matches($post['password'],$post['passwordConfirm']);
			}
			else {
				$post['password'] = "";
			}

			if($notInProject){
				$projectRequired = true;
				$companyRequired = true;
			}

			if($cannotChangeAdmin){
				if($cannotChangeAdmin){ $this->view->msg['type'] = "error"; $this->view->msg['message'] = "Cannot change administrator's project!"; $this->view->form['error']['project'] = true; $this->view->form['error']['company'] = true; }
			}

			//If inputs all were required and exists
			if($usernameRequired && $emailRequired && $firstnameRequired && $lastnameRequired && $projectRequired && $companyRequired && $roleRequired && $passwordRequired && $passwordConfirmRequired && $passwordConfirmMatches){

				//Put addEmployee into a variable
				$updateEmployee = $this->model->updateEmployee($this->request->session['ID'],$post);

				//If update is successfull then send message
				if($updateEmployee){
					$this->view->msg['message'] = "Sucessfully updated account information.";
					$this->view->msg['type'] = "success";
				}
			}

			//If one input was required but doesn't exist
			else {
				//Send page error message
				$this->view->msg['type'] = "error";
				$this->view->msg['message'] = "Make sure fields are not empty!";

				//Send view error of which input was required but didn't exist
				if(!$qbUsernameRequired){  $this->view->form['error']['qbUsername'] = true;	}
				if(!$usernameRequired){  $this->view->form['error']['username'] = true;	}
				if(!$passwordRequired){  $this->view->form['error']['password'] = true;	}
				if(!$passwordConfirmRequired){  $this->view->form['error']['passwordConfirm'] = true; }
				if(!$passwordConfirmMatches){  $this->view->form['error']['passwordConfirm'] = true; $this->view->msg['message'] .= " Password's do not match!"; }
				if(!$emailRequired){  $this->view->form['error']['email'] = true;  }
				if(!$firstnameRequired){  $this->view->form['error']['firstname'] = true;  }
				if(!$lastnameRequired){  $this->view->form['error']['lastname'] = true;  }
				if(!$projectRequired){  $this->view->form['error']['project'] = true;  }
				if(!$companyRequired){  $this->view->form['error']['company'] = true;  }
				if(!$roleRequired){  $this->view->form['error']['role'] = true;  }				

				if($cannotChangeAdmin){ $this->view->msg['message'] .= " Cannot change administrator role!"; $this->view->form['error']['project'] = true; $this->view->form['error']['company'] = true; }
			}

		}
	

		$this->view->render("/administrator/account/index");
	

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function companies($params = NULL){
		if(!isset($params[0])){ $params[0] = ""; }

		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;	

		//RENDER PAGE DATA
		$this->view->data['companiesList'] = $this->model->listCompanies();


		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER BODY
		//PAGE: Companies: Add company
		if ($params[0] == "add" && !isset($params[1])){
			$this->view->render("/administrator/companies/add");
		}
		//PAGE: Companies: Edit company
		if ($params[0] == "edit" && isset($params[1])){

			$this->view->form = $this->model->listCompany($params[1]);
			$this->view->render("/administrator/companies/edit");
		}
		//PAGE: Companies: Add QuickBooks company
		else if ($params[0] == "import" && $params[1] == "quickbooks"){

			if(!empty($params[2])){

				$id = $params[2];

				//GET QUICKBOOKS COMPANY BY ID
				$qbCompany = $this->model->listQbCompany($params[2]);

				$importQbCompanies = $this->model->importQuickBookCompanies($id, $qbCompany);


				if(!$importQbCompanies) {
					$this->view->msg['message'] = $qbCompany['Name']." already exists, cannot import.";
					$this->view->msg['type'] = "warning";
				}

				else  {
					$this->view->msg['message'] = "Successfully imported ".$qbCompany['Name']." into companies.";
					$this->view->msg['type'] = "success";
				}
			}

			//GET QUICKBOOKS COMPANIES & FILTER OUT THE ONE'S WE HAVE
			$json = $this->model->listQbCompanies();

			$this->view->data['companiesList2'] = $this->model->filterCreatedCompanies(); 
			$companiesDifference = count($this->view->data['companiesList2']);

			if($companiesDifference !== 0){
				//$this->view->msg['message'] = "There are ".$companiesDifference." companies in QuickBooks not added to the company portal.";
				//$this->view->msg['type'] = "warning";
			}

			//RENDER PAGE DATA
			$this->view->data['companiesList'] = $this->view->data['companiesList2'];

			$this->view->render("/administrator/companies/import_quickbooks");
		}

		//PAGE: Company: Default 
		else {

			//ACTION: Company: Delete Company
			if($params[0] == "delete"){
				if($params[1] == "0"){
					$this->view->msg['message'] = "Could not delete your company.";
					$this->view->msg['type'] = "error";					
				}
				else if($this->model->deleteCompany($params[1])){
					$this->view->msg['message'] = "Successfully deleted company.";
					$this->view->msg['type'] = "success";
				}

				//Render new list
				$this->view->data['companiesList'] = $this->model->listCompanies();
			}

			//GET QUICKBOOKS COMPANIES
			$json = $this->model->listQbCompanies();

			$this->view->data['companiesList2'] = $json; 
			$companiesDifference = count($this->model->filterCreatedCompanies());

			if($companiesDifference !== 0){
				$this->view->msg['message'] = "There are ".$companiesDifference." companies in QuickBooks not added to the company portal.&nbsp;&nbsp;&nbsp; <a href='".APP_URL."/administrator/companies/import/quickbooks/'><i class='fa fa-cloud-download' aria-hidden='true'></i>Import Now</a>";
				$this->view->msg['type'] = "warning";
			}


			$this->view->render("/administrator/companies/index");
		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function timesheets($params = NULL){
	if(User::checkRole("admin")) {
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;
		$this->view->page['title'] = $this->request->action;

		if(isset($this->request->get['msg'])){
			//RENDER PAGE MESSAGE
			$this->view->msg['message'] = $this->request->get['msg'];
			$this->view->msg['type'] = $this->request->get['msgType'];
		}

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		if(isset($params[0]) && $params[0] == "view" && isset($params[1]) && $params[1] == "all"){
			//ACTION: Timesheets: Delete employee's timesheet
			if(isset($params[5]) && $params[5] == "delete"){
				if($this->model->deleteTimesheet($params[4],$params[2])){
					$this->view->msg['message'] = "Successfully deleted ".$this->model->getAccountInfo($params[4])['firstname']."'s ".$this->model->formatDateForLink($params[2],"/")." - ".$this->model->formatDateForLink($params[3],"/")." timesheet.";
					$this->view->msg['type'] = "success";
				}
			}

			if(isset($params[2]) && isset($params[3])){
				$this->view->data['saturdayDate'] = $this->model->formatDate($params[3]);
				$this->view->data['sundayDate'] = $this->model->formatDate($params[2]);
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($params[3]);
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($params[2]);
				$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($params[2]);
				$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($params[3]);

				$employeesList = $this->model->listEmployeesWithTimesheetByDate($params[2]);
			}
			else {
				$this->view->data['saturdayDate'] = $this->model->getLastSaturday();
				$this->view->data['sundayDate'] = $this->model->getLastSunday();
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSaturday());
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSunday());
				$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSunday());
				$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSaturday());
				
				$employeesList = $this->model->listEmployeesWithTimesheetByDate($this->model->getLastSunday());
			}

			//Get employees name
			foreach($employeesList as $employeesKey => $employeesVal){
				$employeesList[$employeesKey]['projectEmployeeIn'] = $this->model->listProject($this->model->listEmployeesProject($employeesList[$employeesKey]['ID']))['name'];
				$employeesList[$employeesKey]['projectEmployeeInID'] = $this->model->listEmployeesProject($employeesList[$employeesKey]['ID']);
				if(!empty($employeesList[$employeesKey]['timesheets'])){			
					foreach($employeesList[$employeesKey]['timesheets'] as $timesheetKey => $timesheetVal){

						//If timesheet is equal to selected
						if($employeesList[$employeesKey]['timesheets'][$timesheetKey]['sundayWorkDate'] == $this->model->formatDateForLink($this->view->data['sundayDate'],"/")){

							$employeesList[$employeesKey]['timesheets'][0] = $employeesList[$employeesKey]['timesheets'][$timesheetKey];

							$supervisor = $this->model->listSupervisor($employeesList[$employeesKey]['timesheets'][$timesheetKey]['supervisor']);
							if(!empty($supervisor['firstname']) && !empty($supervisor['lastname'])){
								$employeesList[$employeesKey]['timesheets'][0]['supervisor'] = $supervisor['firstname']." ".$supervisor['lastname']; 
							}
							else {
								$employeesList[$employeesKey]['timesheets'][0]['supervisor'] = "Supervisor was deleted.";
							}



						}
						else {
							unset($employeesList[$employeesKey]['timesheets'][$timesheetKey]);
						}

					}
				}
			}


			if(isset($params[4]) && $params[4] == "msg"){
				if($params[5] == "success"){
					$this->view->msg['message'] = urldecode($params[6]);
					$this->view->msg['type'] = "success";
				}
				else if($params[5] == "error"){

				}
			}

			$this->view->data['employeesList'] = $employeesList;
			$this->view->render("/administrator/timesheets/index");
		}
		else if(isset($params[0]) && $params[0] == "view" && isset($params[1]) && $params[1] == "employee"){
			//ACTION: Timesheets: Delete employee's timesheet
			if(isset($params[3]) && $params[3] == "delete"){
				if($this->model->deleteTimesheet($params[2],$params[4])){
					$this->view->msg['message'] = "Successfully deleted ".$this->model->formatDateForLink($params[4],"/")." - ".$this->model->formatDateForLink($params[5],"/")." timesheet.";
					$this->view->msg['type'] = "success";
				}
			}

			if(isset($params[3]) && $params[3] == "msg"){
				if($params[4] == "success"){
					$this->view->msg['message'] = urldecode($params[5]);
					$this->view->msg['type'] = "success";
				}
				else if($params[4] == "error"){

				}
			}


			$this->view->data['disabledDates'] = $this->model->listTimesheetDates($params[2]);

			$disabledDatesFormatted = [];

			if(isset($this->view->data['disabledDates']) && !empty($this->view->data['disabledDates'])){
				foreach($this->view->data['disabledDates'] as $disabledDatesKey => $disabledDatesVal){
					$disabledDatesFormatted[] = $this->model->formatDateForLink($this->view->data['disabledDates'][$disabledDatesKey]);
				}
				$this->view->data['disabledDatesFormatted'] = $disabledDatesFormatted;
			}

			$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSaturday());
			$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSunday());

			$this->view->data['user'] = $this->model->getAccountInfo($params[2]);
			$this->view->data['lastWeekTimesheetStatus'] = $this->model->listLastWeekTimesheet($params[2])['status'];
			$this->view->data['lastWeekTimesheetSundayDate'] = str_replace("/","-",$this->model->listLastWeekTimesheet($params[2])['sundayWorkDate']);

			$this->view->data['timesheets'] = $this->model->listTimesheets($params[2]);

			$this->view->render("/administrator/timesheets/view");
		}

		//PAGE: Timesheet: Add timesheet
		else if (isset($params[0]) && $params[0] == "add"){

			if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$saveTimesheets;

				//Save or Submit to Supervisor for approval
				if(!isset($post['sendTimesheetToSupervisor'])){	
					$post['status'] = "incomplete";
					$post['supervisor'] = "";
					$post['timeSubmitted'] = "";
					$post['timeApproved'] = "";
					$saveTimesheets = $this->model->saveTimesheets($params[2], $post);

					if($saveTimesheets){
						$msg['message'] = "Sucessfully added ".str_replace("/","-",$post['sundayWorkDate'])." - ".str_replace("/","-",$post['saturdayWorkDate']).".";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/view/employee/".$params[2]."/msg/".$msg['type']."/".$msg['message']."/");
					}
				}

				else {
					unset($post['sendTimesheetToSupervisor']);
					$post['status'] = "submitted";
					$post['token'] = bin2hex(openssl_random_pseudo_bytes(16));

					$saveTimesheets = $this->model->saveTimesheets($params[2], $post);
					$sendTimesheetToSupervisor = $this->model->sendTimesheetToSupervisor($params[2],$post['sundayWorkDate'],$post['supervisor'], $post['token']);

					$supervisor = $this->model->listSupervisor($post['supervisor']);

					if($saveTimesheets && $sendTimesheetToSupervisor){
						$msg['message'] = "Sucessfully submitted timesheet for approval to ".$supervisor['firstname']." ".$supervisor['lastname'].".";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/view/employee/".$params[2]."/msg/".$msg['type']."/".$msg['message']."/");
					}
				}
			}


			if(isset($params[3]) && isset($params[4])){
				$this->view->data['saturdayDate'] = $this->model->formatDate($params[4]);
				$this->view->data['sundayDate'] = $this->model->formatDate($params[3]);
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($params[4]);
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($params[3]);
				$this->view->data['anotherDate'] = true;
			}
			else {
				$this->view->data['saturdayDate'] = $this->model->getThisSaturday();
				$this->view->data['sundayDate'] = $this->model->getThisSunday();
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSaturday());
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSunday());
			}

			$this->view->data['disabledDates'] = $this->model->listTimesheetDates($params[2]);

			if(isset($this->view->data['disabledDates'])){
				$disabledDatesFormatted = [];
				foreach($this->view->data['disabledDates'] as $disabledDatesKey => $disabledDatesVal){
					$disabledDatesFormatted[] = $this->model->formatDateForLink($this->view->data['disabledDates'][$disabledDatesKey]);
				}
				$this->view->data['disabledDatesFormatted'] = $disabledDatesFormatted;

				$disabledDates = $this->view->data['disabledDates'];

				foreach($disabledDates as $disabledDatesKey => $disabledDatesVal){
					if($disabledDates[$disabledDatesKey] == $this->model->formatDateForLink($this->view->data['sundayDateForJS'], "/")){
							$this->view->data['pageDisabled'] = true;
							$this->view->msg['message'] = "This week of ".$this->view->data['sundayDate']." - ".$this->view->data['saturdayDate']." has been already created.";
							$this->view->msg['type'] = "error";
					}
				}
			}
			if(isset($params[3]) && $params[3] == "msg"){
				if($params[4] == "success"){
					$this->view->msg['message'] = urldecode($params[5]);
					$this->view->msg['type'] = "success";
				}
				else if($params[4] == "error"){

				}
			}

			//Fetch Supervisors Info
			$accountInfo = $this->model->getAccountInfo($params[2]);
			$project = $this->model->listProject($accountInfo['projectEmployeeIn']);

			foreach($project['supervisors'] as $projectSupervisorID){
				$this->view->data['supervisorsList'][] = $this->model->listSupervisor($projectSupervisorID);
			}

			$this->view->data['user'] = $accountInfo;

			$this->view->render("/administrator/timesheets/add");
		}

		//PAGE: Timesheet: Edit timesheet
		else if (isset($params[0]) && $params[0] == "edit" && isset($params[1]) && $params[1] == "employee" && !empty($this->model->listTimesheetByDate($params[2],str_replace("-","/",$params[3])))){

			$this->view->request = $this->request;
			$this->view->data['user'] = $this->model->getAccountInfo($params[2]);
			$status = $this->model->listTimesheetByDate($params[2],str_replace("-","/",$params[3]))['status'];

			//If request post exists then try to see if we can update the project
			if(!empty($this->request->post) && ($status == "incomplete" || $status == "disapproved")){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$saveTimesheets;

				//Save or Submit to Supervisor for approval
				if(!isset($post['sendTimesheetToSupervisor'])){	
					$post['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($params[2], $post);

					if($saveTimesheets){
						$this->view->msg['message'] = "Sucessfully saved your timesheet.";
						$this->view->msg['type'] = "success";
					}
				}

				else {
					unset($post['sendTimesheetToSupervisor']);
					$post['status'] = "submitted";
					$post['timeSubmitted'] = date('n/j/Y h:i A', time());
					$post['timeApproved'] = "";
					$post['token'] = bin2hex(openssl_random_pseudo_bytes(16));

					$saveTimesheets = $this->model->saveTimesheets($params[2], $post);
					$sendTimesheetToSupervisor = $this->model->sendTimesheetToSupervisor($params[2],$post['sundayWorkDate'],$post['supervisor'], $post['token']);

					$supervisor = $this->model->listSupervisor($post['supervisor']);

					if($saveTimesheets && $sendTimesheetToSupervisor){
						$accountInfo = $this->model->getAccountInfo($params[2]);
						$msg['message'] = urlencode("Sucessfully submitted ".$accountInfo['firstname']." ".$accountInfo['lastname']."'s timesheet for approval to ".$supervisor['firstname']." ".$supervisor['lastname'].".");
						$msg['type'] = "success";

						$ref = $this->request->get['ref'];											

						if($ref == "view"){
							$this->request->reRoute($this->request->controller."/".$this->request->action."/view/all/".$params[3]."/".$params[4]."/msg/".$msg['type']."/".$msg['message']."/");
						}
						else {
							$this->request->reRoute($this->request->controller."/".$this->request->action."/view/employee/".$this->view->data['user']['ID']."/msg/".$msg['type']."/".$msg['message']."/");
						}
					}
				}

				//If add employee is successfull then redirect to employee main page with a success msg
				if(isset($saveTimesheet) && isset($saveTimesheets)){

					$msg['message'] = "Sucessfully saved ".$post['name'].".";
					$msg['type'] = "success";


				}
			}

			//If user cancels timesheet
			else if (isset($params[5]) && $params[5] == "cancelSubmit"){
					$this->view->msg['message'] = "Canceled timesheet submission for approval.";
					$this->view->msg['type'] = "success";

					$weekTimesheet = $this->model->listTimesheetbyDate($params[2], str_replace("-","/",$params[3]));

					$weekTimesheet['timeSubmitted'] = "";
					$weekTimesheet['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($params[2], $weekTimesheet);
					$sendCancel = $this->model->sendCancelToSupervisor($params[2], $weekTimesheet['sundayWorkDate'], "submit");

			}

			//If user cancels approval
			else if (isset($params[5]) && $params[5] == "cancelApproved"){
					$this->view->msg['message'] = "Canceled timesheet approval.";
					$this->view->msg['type'] = "success";

					$weekTimesheet = $this->model->listTimesheetbyDate($params[2], str_replace("-","/",$params[3]));

					$weekTimesheet['timeSubmitted'] = "";
					$weekTimesheet['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($params[2], $weekTimesheet);
					$sendCancel = $this->model->sendCancelToSupervisor($params[2], $weekTimesheet['sundayWorkDate'], "approved");

			}

			if(isset($params[2]) && $params[2] == "currentWeek"){
				$this->view->form = $this->model->listLastWeekTimesheet($params[2]);
			}
			else {	//Try to fetch by sunday
				$listTimesheetByDate = $this->model->listTimesheetByDate($params[2], str_replace("-","/",$params[3]));

				$this->view->form = $listTimesheetByDate;
			}
			$this->view->page['title'] = "Timesheet";
			$this->view->data['sundayDate'] = $this->model->formatDate($params[3]);
			$this->view->data['saturdayDate'] = $this->model->formatDate($params[4]);
			$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($params[3]);
			$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($params[4]);

			//Fetch Supervisors Info
			$accountInfo = $this->model->getAccountInfo($params[2]);
			$project = $this->model->listProject($accountInfo['projectEmployeeIn']);

			foreach($project['supervisors'] as $projectSupervisorID){
				$this->view->data['supervisorsList'][] = $this->model->listSupervisor($projectSupervisorID);
			}

			$this->view->render("/administrator/timesheets/edit");
		}
		else {
			//Get list of employees
			$this->view->data['saturdayDate'] = $this->model->getLastSaturday();
			$this->view->data['sundayDate'] = $this->model->getLastSunday();
			$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSaturday());
			$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getLastSunday());
			$employeesList = $this->model->listEmployeesWithTimesheetByDate($this->model->getLastSunday());

			$this->view->data['sundayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSunday());
			$this->view->data['saturdayDateForLink'] = $this->model->formatDateForLink($this->model->getLastSaturday());

			//Get employees name
			foreach($employeesList as $employeesKey => $employeesVal){
				$employeesList[$employeesKey]['projectEmployeeIn'] = $this->model->listProject($this->model->listEmployeesProject($employeesList[$employeesKey]['ID']))['name'];
				$employeesList[$employeesKey]['projectEmployeeInID'] = $this->model->listEmployeesProject($employeesList[$employeesKey]['ID']);

				if(!empty($employeesList[$employeesKey]['timesheets'])){			
					foreach($employeesList[$employeesKey]['timesheets'] as $timesheetKey => $timesheetVal){

						//If timesheet is equal to selected
						if($employeesList[$employeesKey]['timesheets'][$timesheetKey]['sundayWorkDate'] == $this->model->formatDateForLink($this->model->getLastSunday(),"/")){

							$employeesList[$employeesKey]['timesheets'][0] = $employeesList[$employeesKey]['timesheets'][$timesheetKey];

							$supervisor = $this->model->listSupervisor($employeesList[$employeesKey]['timesheets'][$timesheetKey]['supervisor']);
							if(!empty($supervisor['firstname']) && !empty($supervisor['lastname'])){



								$employeesList[$employeesKey]['timesheets'][0]['supervisor'] = $supervisor['firstname']." ".$supervisor['lastname']; 
							}
							else {
								$employeesList[$employeesKey]['timesheets'][0]['supervisor'] = "Supervisor was deleted.";
							}
						}
						else {
							unset($employeesList[$employeesKey]['timesheets'][$timesheetKey]);
						}
					}
				}
			}

			$this->view->data['employeesList'] = $employeesList;

			$this->view->render("/administrator/timesheets/index");
		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}
	}

	public function timeSheet($params = NULL){
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;

		if(!empty($this->request->action)){
			$this->view->page['title'] = $this->request->action;
		}
		else {
			$this->view->page['title'] = "Timesheets";
		}

		//RENDER PAGE MESSAGE
		$this->view->msg['message'] = $this->request->get['msg'];
		$this->view->msg['type'] = $this->request->get['msgType'];

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER BODY
		
		//PAGE: Timesheet: Add timesheet
		if (isset($params[0]) && $params[0] == "add"){

			if(!empty($this->request->post)){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$saveTimesheets;

				//Save or Submit to Supervisor for approval
				if(!isset($post['sendTimesheetToSupervisor'])){	
					$post['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($this->request->session['ID'], $post);

					if($saveTimesheets){
						$this->view->msg['message'] = "Sucessfully saved ".$post['sundayWorkDate']." - ".$post['saturdayWorkDate'].".";
						$this->view->msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$this->view->msg['type']."/".$this->view->msg['message']."/");
					}
				}

				else {
					unset($post['sendTimesheetToSupervisor']);
					$post['status'] = "submitted";
					$post['token'] = bin2hex(openssl_random_pseudo_bytes(16));

					$saveTimesheets = $this->model->saveTimesheets($this->request->session['ID'], $post);
					$sendTimesheetToSupervisor = $this->model->sendTimesheetToSupervisor($this->request->session['ID'],$post['sundayWorkDate'],$post['supervisor'], $post['token']);

					$supervisor = $this->model->listSupervisor($post['supervisor']);

					if($saveTimesheets && $sendTimesheetToSupervisor){
						$msg['message'] = "Sucessfully submitted timesheet for approval to ".$supervisor['firstname']." ".$supervisor['lastname'].".";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$msg['type']."/".$msg['message']."/");
					}
				}



			}

			if(isset($params[1]) && isset($params[2])){
				$this->view->data['saturdayDate'] = $this->model->formatDate($params[2]);
				$this->view->data['sundayDate'] = $this->model->formatDate($params[1]);
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($params[2]);
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($params[1]);
				$this->view->data['anotherDate'] = true;
			}
			else {

				$this->view->data['saturdayDate'] = $this->model->getThisSaturday();
				$this->view->data['sundayDate'] = $this->model->getThisSunday();
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSaturday());
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSunday());
			}

			$this->view->data['disabledDates'] = $this->model->listTimesheetDates($this->request->session['ID']);

			$disabledDatesFormatted = [];
			if(isset($this->view->data['disabledDates'])){
				foreach($this->view->data['disabledDates'] as $disabledDatesKey => $disabledDatesVal){
					$disabledDatesFormatted[] = $this->model->formatDateForLink($this->view->data['disabledDates'][$disabledDatesKey]);
				}
			}
			$this->view->data['disabledDatesFormatted'] = $disabledDatesFormatted;

			$disabledDates = $this->view->data['disabledDates'];
			if(isset($disabledDates)){
				foreach($disabledDates as $disabledDatesKey => $disabledDatesVal){
					if($disabledDates[$disabledDatesKey] == $this->model->formatDateForLink($this->view->data['sundayDateForJS'], "/")){
							$this->view->data['pageDisabled'] = true;
							$this->view->msg['message'] = "This week of ".$this->view->data['sundayDate']." - ".$this->view->data['saturdayDate']." has been already created.";
							$this->view->msg['type'] = "error";
					}
				}
			}

			//Fetch Supervisors Info
			$accountInfo = $this->model->getAccountInfo($this->request->session['ID']);
			$project = $this->model->listProject($accountInfo['projectEmployeeIn']);

			foreach($project['supervisors'] as $projectSupervisorID){
				$this->view->data['supervisorsList'][] = $this->model->listSupervisor($projectSupervisorID);
			}

			$this->view->render("/administrator/timesheet/add");
		}

		//PAGE: Timesheet: Edit timesheet
		else if (isset($params[0]) && $params[0] == "edit" && !empty($this->model->listTimesheetByDate($this->request->session['ID'],str_replace("-","/",$params[1])))){
			
			$status = $this->model->listTimesheetByDate($this->request->session['ID'],str_replace("-","/",$params[1]))['status'];

			//If request post exists then try to see if we can update the project
			if(!empty($this->request->post) && ($status == "incomplete" || $status == "disapproved")){

				//Grab post from request & put all form input into view
				$post = $this->request->post;
				$this->view->form = $post;

				$saveTimesheets;

				//Save or Submit to Supervisor for approval
				if(!isset($post['sendTimesheetToSupervisor'])){	
					$post['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($this->request->session['ID'], $post);

					if($saveTimesheets){
						$this->view->msg['message'] = "Sucessfully saved your timesheet.";
						$this->view->msg['type'] = "success";
					}
				}

				else {
					unset($post['sendTimesheetToSupervisor']);
					$post['status'] = "submitted";
					$post['token'] = bin2hex(openssl_random_pseudo_bytes(16));

					$saveTimesheets = $this->model->saveTimesheets($this->request->session['ID'], $post);
					$sendTimesheetToSupervisor = $this->model->sendTimesheetToSupervisor($this->request->session['ID'],$post['sundayWorkDate'],$post['supervisor'], $post['token']);

					$supervisor = $this->model->listSupervisor($post['supervisor']);

					if($saveTimesheets && $sendTimesheetToSupervisor){
						$msg['message'] = "Sucessfully submitted timesheet for approval to ".$supervisor['firstname']." ".$supervisor['lastname'].".";
						$msg['type'] = "success";

						$this->request->reRoute($this->request->controller."/".$this->request->action."/msg/".$msg['type']."/".$msg['message']."/");
					}
				}


				//If add employee is successfull then redirect to employee main page with a success msg
				if(isset($saveTimesheet) && isset($saveTimesheets)){
					$msg['message'] = "Sucessfully saved ".$post['name'].".";
					$msg['type'] = "success";
				}
			}

			//If user cancels timesheet
			else if (isset($params[2]) && $params[2] == "cancelSubmit"){
					$this->view->msg['message'] = "Canceled timesheet submission for approval.";
					$this->view->msg['type'] = "success";

					$weekTimesheet = $this->model->listTimesheetbyDate($this->request->session['ID'], str_replace("-","/",$params[1]));

					$weekTimesheet['timeSubmitted'] = "";
					$weekTimesheet['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($this->request->session['ID'], $weekTimesheet);

			}

			//If user cancels approval
			else if (isset($params[2]) && $params[2] == "cancelApproved"){
					$this->view->msg['message'] = "Canceled timesheet approval.";
					$this->view->msg['type'] = "success";

					$weekTimesheet = $this->model->listTimesheetbyDate($this->request->session['ID'], str_replace("-","/",$params[1]));

					$weekTimesheet['timeSubmitted'] = "";
					$weekTimesheet['status'] = "incomplete";

					$saveTimesheets = $this->model->saveTimesheets($$this->request->session['ID'], $weekTimesheet);

			}

			if($params[1] == "currentWeek"){
				$this->view->form = $this->model->listLastWeekTimesheet($this->request->session['ID']);
			}
			else {	//Try to fetch by sunday
				$listTimesheetByDate = $this->model->listTimesheetByDate($this->request->session['ID'], str_replace("-","/",$params[1]));

				//var_dump($listTimesheetByDate);
				$this->view->form = $listTimesheetByDate;
			}

			//Fetch Supervisors Info
			$accountInfo = $this->model->getAccountInfo($this->request->session['ID']);
			$project = $this->model->listProject($accountInfo['projectEmployeeIn']);

			foreach($project['supervisors'] as $projectSupervisorID){
				$this->view->data['supervisorsList'][] = $this->model->listSupervisor($projectSupervisorID);
			}

			$this->view->render("/administrator/timesheet/edit");
		}
		else {
			$this->view->page['title'] = "Timesheets";

			if(isset($params[0]) && $params[0] == "msg"){
				if($params[1] == "success"){
					$this->view->msg['message'] = urldecode($params[2]);
					$this->view->msg['type'] = "success";
				}
				else if($params[1] == "error"){

				}
			}

				$this->view->data['saturdayDate'] = $this->model->getThisSaturday();
				$this->view->data['sundayDate'] = $this->model->getThisSunday();
				$this->view->data['saturdayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSaturday());
				$this->view->data['sundayDateForJS'] = $this->model->formatDateForJS($this->model->getThisSunday());

				$this->view->data['disabledDates'] = $this->model->listTimesheetDates($this->request->session['ID']);

				$disabledDatesFormatted = [];
				if(isset($this->view->data['disabledDates'])){
					foreach($this->view->data['disabledDates'] as $disabledDatesKey => $disabledDatesVal){
						$disabledDatesFormatted[] = $this->model->formatDateForLink($this->view->data['disabledDates'][$disabledDatesKey]);
					}
				}
				$this->view->data['disabledDatesFormatted'] = $disabledDatesFormatted;

				$disabledDates = $this->view->data['disabledDates'];

				$this->view->data['currentWeekTimesheetStatus'] = $this->model->listCurrentWeekTimesheet($this->request->session['ID'])['status'];
				$this->view->data['currentWeekTimesheetSundayDate'] = str_replace("/","-",$this->model->listCurrentWeekTimesheet($this->request->session['ID'])['sundayWorkDate']);

				$this->view->data['timesheets'] = $this->model->listTimesheets($this->request->session['ID']);

				$this->view->render("/administrator/timesheet/index");

		}

		//RENDER FOOTER
		$this->view->render("/footer");
	}

	public function settings($params = NULL){
		//RENDER PAGE META
		$this->view->page['name'] = $this->request->controller;

		$this->view->page['title'] = $this->request->action;

		//RENDER PAGE MESSAGE
		$this->view->msg['message'] = $this->request->get['msg'];
		$this->view->msg['type'] = $this->request->get['msgType'];

		//RENDER HEADER
		$this->view->render("/header");
		$this->view->render("/administrator/sidebar");

		//RENDER FORM DATA
		$getSystemSettings = $this->model->getSystemSettings();
		$timesheetsDisclaimer;

		foreach($getSystemSettings as $systemSettingKey => $systemSettingVal){
			if($getSystemSettings[$systemSettingKey]['name'] == "timesheets-disclaimer"){
				$timesheetsDisclaimer = $getSystemSettings[$systemSettingKey]['value'];
			}
			if($getSystemSettings[$systemSettingKey]['name'] == "invoice-emails"){
				$invoiceEmails = json_decode($getSystemSettings[$systemSettingKey]['value']);
			}
		}

		$this->view->form['timesheets-disclaimer'] = $timesheetsDisclaimer;
		$this->view->form['invoice-emails'] = $invoiceEmails;

		//PAGE: System: Save Settings
		//If request post exists then try to see if we can save settings
		if(!empty($this->request->post)){

			//Grab post from request & put all form input into view
			$post = $this->request->post;
			$this->view->form = $post;

			if(isset($post['invoice-emails']) && isset($post['invoiceEmails'])){
				$post['invoice-emails'] = json_encode($post['invoiceEmails']);
				unset($post['invoiceEmails']);
			}
			else if(isset($post['invoice-emails'])){

			}
			
			//Put inputs required in a variable
			//$usernameRequired = Form::isRequired($post['username']);

			//if($cannotChangeAdmin){
				//if($cannotChangeAdmin){ $this->view->msg['type'] = "error"; $this->view->msg['message'] = "Cannot change administrator's project!"; $this->view->form['error']['project'] = true; $this->view->form['error']['company'] = true; }
			//}

			//If inputs all were required and exists
			//if($usernameRequired && $emailRequired && $firstnameRequired && $lastnameRequired && $projectRequired && $companyRequired && $roleRequired && $passwordRequired && $passwordConfirmRequired && $passwordConfirmMatches){

				//Put addEmployee into a variable
				$updateSystemSettings = $this->model->updateSystemSettings($post);

				//If update is successfull then send message
				if($updateSystemSettings){
					$this->view->msg['message'] = "Sucessfully updated system settings.";
					$this->view->msg['type'] = "success";
				}
			//}

		}

		$this->view->form['invoice-emails'] = json_decode($this->model->getSystemSettings('invoice-emails')['value']);

		//RENDER BODY
		$this->view->render("/administrator/settings/index");

		//RENDER FOOTER
		$this->view->render("/footer");

	}
	public function logOut(){
		Auth::logOut();
	}
}
?>