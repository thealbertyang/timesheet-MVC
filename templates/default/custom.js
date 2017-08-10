$(document).ready(function() {

	// FUNCTIONS
	function msg(message){
		alert(message);
	}

	function loadProjects(){
		$.ajax({
	  			method: "POST",
	  			url: appDir+"/api/get/list/projects/",
	  			dataType: "json"
			}).done(function( msg ) {

					var projects = [];
  					msg.forEach(function(e){ 
  						//if there is already a table with supervisors, only add the ones that are not on it
  						if($(".projectsList .table-row").length > 0){

  							var projectsList = [];

  							var doNotPush = false;
  							$(".projectsList .table-row .input-value input").each(function(){
  								if($(this).val() == e['ID']){
  									//Do not push
  									doNotPush = true;
  								}
  							});
  							if(!doNotPush){
  								projects.push({id: e['ID'], text: e['name']}); 
  							}	
  						}
  						else { projects.push({id: e['ID'], text: e['name']}); } 
  					});

  					var hasError = function(){ if($(".input-search-ajax").hasClass("has-error")){ return "has-error"; } };
  					
					$(".input-projects-selector.input-search-ajax option:nth-child(1)").text("Choose a project.");
  					$(".input-projects-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: projects, placeholder: { id: '-1', text: 'Select a supervisor.'} });

  			}).fail(function( jqXHR, textStatus ) {
  				alert( "Request failed: " + textStatus );
  			});
	}


	if($(".form-employees-add, .form-employees-edit").length > 0){
			$.ajax({
	  			method: "POST",
	  			url: appDir+"/api/quickbooks/list/employees/",
	  			dataType: "json"
			}).done(function( msg ) {

        console.log(msg);


					$(".form-employees-add .input-box .fa-spin, .form-employees-edit .input-box .fa-spin").fadeToggle();
					var qbEmployees = [];
  					msg.forEach(function(e){ qbEmployees.push({id: e['ID'], text: e['Name'], unitPrice: e['UnitPrice'], cost: e['Cost']}); });

  					window.qbEmployees = qbEmployees;

  					var hasError = function(){ if($(".input-qbEmployees-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
  					
					$(".input-qbEmployees-selector.input-search-ajax option:nth-child(1)").text("Find employee on QuickBooks.");
  					$(".input-qbEmployees-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: qbEmployees });

  			}).fail(function( jqXHR, textStatus ) {
  				alert( "Request failed: " + textStatus );
  			});

        $.ajax({
          method: "POST",
          url: appDir+"/api/quickbooks/refresh/employees/",
          dataType: "json"
      }).done(function( msg ) {
        console.log("Refreshed Employee Cache List");
        }).fail(function( jqXHR, textStatus ) {
          alert( "Request failed: " + textStatus );
        });
  	}

  	if($(".form-invoice").length > 0){



					//$(".form-employees-add .input-box .fa-spin, .form-employees-edit .input-box .fa-spin").fadeToggle();


  				var hasError = function(){ if($(".input-invoice-qbEmployees-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
  					
					$(".input-invoice-qbEmployees-selector.input-search-ajax option:nth-child(1)").text("Find employee on QuickBooks.");
  					//$(".input-qbEmployees-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: qbEmployees }); qbEmployees 
  					$(".input-invoice-qbEmployees-selector.input-search-ajax").selectize({
					    valueField: 'ID',
					    labelField: 'Name',
					    searchField: 'Name',
					    preload: true,
					    create: false,
					    render: {
					        option: function(item, escape) {
                    //console.log(item);
                    item.state = (item.state).charAt(0).toUpperCase() + (item.state).slice(1);
					            return '<div>' +
					                '<span class="title">' +
					                    '<span class="name"><i class="icon ' + (item.ID ? 'fork' : 'source') + '"></i>' + escape(item.Name) + '</span>' +
					                '</span>' +
					                '<ul class="meta">' +
					                    '<li class="bill-rate"><span>' + escape(item.UnitPrice) + '</span> Bill Rate</li>' +
					                    '<li class="state"><span>' + escape(item.state) + '</span> Calculation</li>' +
					                '</ul>' +
					            '</div>';
					        }
					    },
					    load: function(query, callback) {

					        $.ajax({
					       		method: "POST",
					  			url: appDir+"/api/quickbooks/list/employeesForQb/",
					  			dataType: "json",
					            error: function() {
					                callback();
					            },
					            success: function(res) {
					                callback(res);
					            }
					        });
					    },
					    onChange: function(value, $item) {
                //if ($item) {
                 // $item.forEach((value) => {

                    var rowID = this.$input.closest("tr").data("row");

                    var employeeID = this.$input.closest("tr").find("input[name='lines["+rowID+"][employeeID]']");
                    var firstname = this.$input.closest("tr").find("input[name='lines["+rowID+"][firstname]']");
                    var lastname = this.$input.closest("tr").find("input[name='lines["+rowID+"][lastname]']");
                    var state = this.$input.closest("tr").find("input[name='lines["+rowID+"][state]']");
                    var regRate = this.$input.closest("tr").find("input[name='lines["+rowID+"][regRate]']");
                    var otRate = this.$input.closest("tr").find("input[name='lines["+rowID+"][otRate]']");
                    var dblRate = this.$input.closest("tr").find("input[name='lines["+rowID+"][dblRate]']");
                    var otherRate = this.$input.closest("tr").find("input[name='lines["+rowID+"][otherRate]']");
                    var regHours = this.$input.closest("tr").find("input[name='lines["+rowID+"][regHours]']");
                    var otHours = this.$input.closest("tr").find("input[name='lines["+rowID+"][otHours]']");
                    var dblHours = this.$input.closest("tr").find("input[name='lines["+rowID+"][dblHours]']");
                    var otherHours = this.$input.closest("tr").find("input[name='lines["+rowID+"][otherHours]']");

                    var ID = this.options[value].ID;
                    var billRate = this.options[value].UnitPrice;
                    var name = this.options[value].Name;

                    var full_name = name;
                    var name = full_name.split(', ');
                    var last_name = name[0];
                    var first_name = name[1];

                    employeeID.val(ID);
                    regRate.val(billRate);
                    otRate.val(billRate);
                    dblRate.val(billRate);

                    //Rate to go to zero only if other
                    otherRate.val(0);

                    regHours.val(0);
                    otHours.val(0);
                    dblHours.val(0);
                    otHours.val(0);

                    firstname.val(first_name);
                    lastname.val(last_name);

                    console.log(this.options[value]);
                    console.log(name);
                    console.log(first_name);

                    calcInvoice(this.$input);

                    $.ajax({
                      method: "POST",
                      url: appDir+"/api/quickbooks/refresh/employees/",
                      dataType: "json"
                    }).done(function( msg ) {
                    console.log("Refreshed Employee Cache List");
                    }).fail(function( jqXHR, textStatus ) {
                      alert( "Request failed: " + textStatus );
                    });
					    }
					});

          $(".input-invoice-line.line-type").selectize({
          	create: true, 
            createOnBlur: true,
          	render: { 
          		option: function(item, escape) {
                    //console.log(item);
                    //item.state = (item.state).charAt(0).toUpperCase() + (item.state).slice(1);
                      return '<div>' +
                          '<span class="title">' +
                              '<span class="name"><i class="icon ' + (item.value ? 'fork' : 'source') + '"></i>' + escape(item.text) + '</span>' +
                          '</span>' +
                      '</div>';
                  },
            onChange: function(value, $item){

            }
      		}});


  	}

	if($(".form-account-edit, .form-employees-add, .form-employees-edit").length > 0){

				$.ajax({
		  			method: "POST",
		  			url: appDir+"/api/get/list/companies",
		  			dataType: "json"
				}).done(function( msg ) {

						var companies = [];
	  					msg.forEach(function(e){ 
								 companies.push({id: e['ID'], text: e['name']});
	  					});

	  					var hasError = function(){ if($(".input-companies-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
	  					

						$(".input-companies-selector.input-search-ajax option:nth-child(1)").text("Choose a company.");
	  					$(".input-companies-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: companies, placeholder: { id: '-1', text: 'Select a supervisor.'} });

	  			}).fail(function( jqXHR, textStatus ) {
	  				alert( "Request failed: " + textStatus );
	  			});

	  			var companiesSelector = $('select[name=companiesSelector]');

				if(companiesSelector.val()){

			  		$.ajax({
			  			method: "POST",
			  			url: appDir+"/api/get/list/projectsInCompany/"+companiesSelector.val(),
			  			dataType: "json"
					}).done(function( msg ) {

							var projects = [];
		  					msg.forEach(function(e){ 
		  							var projectsList = [];
									projects.push({id: e['ID'], text: e['name'], 'data': 'test' }); 
		  					});

		  					var hasError = function(){ if($(".input-projects-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
		  					
							$(".input-projects-selector.input-search-ajax option:nth-child(1)").text("Choose a project.");
		  					$(".input-projects-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: projects, placeholder: { id: '-1', text: 'Select a supervisor.'} });

		  					//Add data-company


		  			}).fail(function( jqXHR, textStatus ) {
		  				alert( "Request failed: " + textStatus );
		  			});
		  		}
		  		else {
		  			$(".input-projects-selector.input-search-ajax option:nth-child(1)").text("Choose a project.");
		  			$(".input-projects-selector.input-search-ajax").select2({ containerCssClass : "disabled" });

		  		}

	  	}
  		if($(".form-project-add, .form-project-edit").length > 0){

			$.ajax({
	  			method: "POST",
	  			url: appDir+"/api/get/list/supervisors/",
	  			dataType: "json"
			}).done(function( msg ) {

					var supervisors = [];
  					msg.forEach(function(e){ 
  						//if there is already a table with supervisors, only add the ones that are not on it
  						if($(".supervisorsList .table-row").length > 0){

  							var supervisorsList = [];

  							var doNotPush = false;
  							$(".supervisorsList .table-row .input-value input").each(function(){
  								if($(this).val() == e['ID']){
  									//Do not push
  									doNotPush = true;
  								}
  							});
  							if(!doNotPush){
  								supervisors.push({id: e['ID']+","+e['email'], text: e['firstname']+" "+e['lastname']}); 
  							}	
  						}
  						else { supervisors.push({id: e['ID']+","+e['email'], text: e['firstname']+" "+e['lastname']}); } 
  					});

  					var hasError = function(){ if($(".input-supervisor-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
  					
					$(".input-supervisor-selector.input-search-ajax option:nth-child(1)").text("Choose a supervisor.");
  					$(".input-supervisor-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: supervisors, placeholder: { id: '-1', text: 'Select a supervisor.'} });

  			}).fail(function( jqXHR, textStatus ) {
  				alert( "Request failed: " + textStatus );
  			});
  		}
  		if($(".form-supervisors-add, .form-supervisors-edit").length > 0){

			$.ajax({
	  			method: "POST",
	  			url: appDir+"/api/get/list/projects/",
	  			dataType: "json"
			}).done(function( msg ) {

					var projects = [];
  					msg.forEach(function(e){ 
  						//if there is already a table with supervisors, only add the ones that are not on it
  						if($(".projectsList .table-row").length > 0){

  							var projectsList = [];

  							var doNotPush = false;
  							$(".projectsList .table-row .input-value input").each(function(){
  								if($(this).val() == e['ID']){
  									//Do not push
  									doNotPush = true;
  								}
  							});
  							if(!doNotPush){
  								projects.push({id: e['ID'], text: e['name']}); 
  							}	
  						}
  						else { projects.push({id: e['ID'], text: e['name']}); } 
  					});

  					var hasError = function(){ if($(".input-projects-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
  					
					$(".input-projects-selector.input-search-ajax option:nth-child(1)").text("Choose a project.");
  					$(".input-projects-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: projects, placeholder: { id: '-1', text: 'Select a supervisor.'} });

  			}).fail(function( jqXHR, textStatus ) {
  				alert( "Request failed: " + textStatus );
  			});
  		}	

  	/* ON LOAD */
  	$("select[name=role]").ready(function(){
  		var hasError = function(){ if($("select[name=role]").hasClass("has-error")){ return "has-error"; } };
  		$("select[name=role]").select2({ containerCssClass : hasError() });
  		
  	});

    $('.data-table.display').not(".table-companies").DataTable({
    	"fnInitComplete": function () {
        $('.dataTables_length label:eq(0)').remove();
    		if($(this).hasClass("table-timesheet")){
    			 this.api().order( [ 0, 'desc' ]).draw();
    		}
    		else if($(this).hasClass("table-admin-timesheets")){
    			this.api().order( [ 0, 'desc' ]).draw();
              var table = this;
              this.api().columns(':eq(0)').every( function () {
              var column = this;
              $(".dataTables_length").addClass("table-header-actions");
              $(".dataTables_filter").addClass("table-header-actions");
              $(".dataTables_filter").wrapInner("<div class='input-group'><div class='input-box'></div></div>");
              $(".dataTables_filter .input-box").prepend("<div class='input-label'><label>Search</label></div>");

              if($(table).hasClass("pdf-download")){

                $('<div class="input-label"><label for="clockIn">Download By</label></div>').appendTo(".dataTables_length");
                var select = $('<select class="timesheet-project"><option value="">All projects</option></select>')
                    .appendTo(".dataTables_length")
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
                       // console.log(val);
                       // console.log(column.nodes().unique().to$());
                        var i = 0;
                    $.fn.dataTable.ext.search.push(
                      
                       function(settings, data, dataIndex) {
                        var cell = column.nodes();
                       // console.log("data: "+data);
                        //console.log("data index: "+dataIndex);
                        //console.log("column html: "+$(column.order( [ 0, 'desc' ]).nodes().unique().to$()[i]));
                       // console.log($(column.order( [ 0, 'desc' ]).nodes().unique().to$()[i]));
                        projectID = $(column.order( [ 0, 'desc' ]).nodes().unique().to$()[i]).data("projectId");
                        i++;
                        if(val == ""){
                          return true;
                        }
                        else if(projectID == val){
                          return true;
                        }
                        else {
                          return false;
                        }
                        
                       }     
                    );

                    column.order( [ 0, 'desc' ]).draw();
                       $.fn.dataTable.ext.search.pop();
                    });

                 this.nodes().unique().to$().each( function(d,j){
                  if(select.find("option[value='"+$(this).data("projectId")+"']").length > 0){} else {
                    select.append( '<option value="'+$(this).data("projectId")+'">'+$(this).text()+'</option>' );
                  }
                });

                select.select2();

                $(".dataTables_length").addClass("three-fourths");
                $(".dataTables_length").wrapInner("<div class='input-group one-fourth'></div>");
                
                var download = $('<div class="input-group" style="width:18%;"><div class="input-box"><div class="input-label"><label for="clockIn" style="visibility:hidden;">Download</label></div><a href="#" class="timesheet-download input-button input-button-dropdown"><i class="fa fa-download" aria-hidden="true"></i> PDF</a><div class="input-button-caret input-button large"><i class="fa fa-caret-down"></i></div> <div class="dropdown-menu"></div></div></div>').appendTo(".dataTables_length"); 
                var dropdownExcel = $('<a class="dropdown-item" href="#">Excel Report</a>').appendTo($(download).find(".dropdown-menu"))
                .on( 'click', function () {
                        //project ID
                        var projectID = $(".timesheet-project").val();

                        if(projectID == ""){
                          projectID = "all";
                        }

                        //date
                        var startDate = $(".date-select").data("startDate").replace(/\//g, "-");
                        var endDate = $(".date-select").data("endDate").replace(/\//g, "-");

                        //include submitted
                        var includeSubmitted = $(".timesheet-download-include-submitted input[name=includeSubmitted]").prop("checked");

                        if(includeSubmitted == true){
                          includeSubmitted = "includeSubmitted";
                        }
                        else {
                          includeSubmitted = "";
                        }


                         //build the new URL
                          var excelUrl = appDir+"/api/timesheet/download/projects/"+projectID+"/"+startDate+"/"+endDate+"/includeAll/excel";

                          console.log(excelUrl);
                          
                          //load it into a hidden iframe
                          var iframe = $("<iframe/>").attr({
                                                  src: excelUrl,
                                                  style: "visibility:hidden;display:none"
                                              }).appendTo("body");
                          $(this).closest(".dropdown-menu").toggle();
                      });


                var includeSubmitted = $('<div class="input-group one-fourth timesheet-download-include-submitted"><div class="input-label"><label for="clockIn">Options</label></div><div class="input-box"><input type="checkbox" name="includeSubmitted"><span>Include Submitted</span></div></div>')
                	.appendTo(".dataTables_length");
              }
              $(includeSubmitted).find('input[name="includeSubmitted"]').on( 'change', function () {
                       $(this).siblings("span").toggleClass("active"); });
                
            });
    		}
        },
        "columnDefs": [ {
          "targets": 'no-sort',
          "orderable": false,
    	}],
    	"language": {
	        search: "_INPUT_",
	        searchPlaceholder: "Search..."
    	}
	});

  
  $('.data-table.table-companies').DataTable({
      "fnInitComplete": function () {
            $.ajax({
                method: "POST",
                url: appDir+"/api/quickbooks/refresh/companies",
                dataType: "json"
            }).done(function( msg ) {
              console.log("Refreshed Company Cache List");
              }).fail(function( jqXHR, textStatus ) {
                alert( "Request failed: " + textStatus );
              });
      },      
      "language": {
          search: "_INPUT_",
          searchPlaceholder: "Search..."
      },
      "lengthChange":false

  });

  $('.data-table.table-invoice').DataTable({
      "fnInitComplete": function () {},
        "searching": false,
         "paging": false, 
         "info": false,         
         "lengthChange":false,
         "ordering": false,

  });

    $('.timesheet-download').click(function(){
    	if($('.table-admin-timesheets')){

        //project ID
        var projectID = $(".timesheet-project").val();

        if(projectID == ""){
          projectID = "all";
        }

        //date
        var startDate = $(".date-select").data("startDate").replace(/\//g, "-");
        var endDate = $(".date-select").data("endDate").replace(/\//g, "-");

        //include submitted
        var includeSubmitted = $(".timesheet-download-include-submitted input[name=includeSubmitted]").prop("checked");

        if(includeSubmitted == true){
        	includeSubmitted = "includeApprovedAndSubmitted";
        }
        else {
        	includeSubmitted = "includeApproved";
        }

 
              $.ajax({
                  method: "POST",
                  url: appDir+"/api/get/value/timesheetsDisclaimer",
                  dataType: "json"
              }).done(function( msg ) {
              window.timesheetsDisclaimer = msg;
                return msg;

              }).fail(function( jqXHR, textStatus ) {
          alert( "Request failed: " + textStatus );
        });

        console.log(appDir+"/api/timesheet/download/projects/"+projectID+"/"+startDate+"/"+endDate+"/"+includeSubmitted);
            
        $.ajax({
                  method: "POST",
                  url: appDir+"/api/timesheet/download/projects/"+projectID+"/"+startDate+"/"+endDate+"/"+includeSubmitted,
                  dataType: "json"
              }).done(function( msg ) {



          var timesheetsList = [];
            msg.forEach(function(e,i){ 
              	timesheetsList.push({
                  firstname: e['firstname'], 
                  lastname: e['lastname'], 
                  companyName: e['companyName'], 
                  state: e['state'], 
                  status: e['timesheets'][0]['status'], 
                  sundayWorkDate: e['timesheets'][0]['sundayWorkDate'], 
                  mondayWorkDate: e['timesheets'][0]['mondayWorkDate'], 
                  tuesdayWorkDate: e['timesheets'][0]['tuesdayWorkDate'], 
                  wednesdayWorkDate: e['timesheets'][0]['wednesdayWorkDate'], 
                  thursdayWorkDate: e['timesheets'][0]['thursdayWorkDate'], 
                  fridayWorkDate: e['timesheets'][0]['fridayWorkDate'], 
                  saturdayWorkDate: e['timesheets'][0]['saturdayWorkDate'], 
                  sundayWorkTime: e['timesheets'][0]['sundayWorkTime'], 
                  mondayWorkTime: e['timesheets'][0]['mondayWorkTime'], 
                  tuesdayWorkTime: e['timesheets'][0]['tuesdayWorkTime'], 
                  wednesdayWorkTime: e['timesheets'][0]['wednesdayWorkTime'], 
                  thursdayWorkTime: e['timesheets'][0]['thursdayWorkTime'], 
                  fridayWorkTime: e['timesheets'][0]['fridayWorkTime'], 
                  saturdayWorkTime: e['timesheets'][0]['saturdayWorkTime'], 
                  TotalWorkTime: e['timesheets'][0]['TotalWorkTime'], 
                  supervisorName: e['timesheets'][0]['supervisorName'], 
                  timeSubmitted: e['timesheets'][0]['timeSubmitted'], 
                  timeApproved: e['timesheets'][0]['timeApproved'],
                  sundayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['sundayWorkTimeReg'],
                  sundayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['sundayWorkTimeOT'],
                  sundayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['sundayWorkTimeDbl'],
                  mondayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['mondayWorkTimeReg'],
                  mondayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['mondayWorkTimeOT'],
                  mondayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['mondayWorkTimeDbl'],
                  tuesdayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['tuesdayWorkTimeReg'],
                  tuesdayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['tuesdayWorkTimeOT'],
                  tuesdayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['tuesdayWorkTimeDbl'],
                  wednesdayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['wednesdayWorkTimeReg'],
                  wednesdayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['wednesdayWorkTimeOT'],
                  wednesdayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['wednesdayWorkTimeDbl'],
                  thursdayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['thursdayWorkTimeReg'],
                  thursdayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['thursdayWorkTimeOT'],
                  thursdayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['thursdayWorkTimeDbl'],
                  fridayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['fridayWorkTimeReg'],
                  fridayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['fridayWorkTimeOT'],
                  fridayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['fridayWorkTimeDbl'],
                  saturdayWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['saturdayWorkTimeReg'],
                  saturdayWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['saturdayWorkTimeOT'],
                  saturdayWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['saturdayWorkTimeDbl'],
                  sundayBreaksTime: e['timesheets'][0]['caTimesheetHours']['sundayBreaksTime'],
                  mondayBreaksTime: e['timesheets'][0]['caTimesheetHours']['mondayBreaksTime'],
                  tuesdayBreaksTime: e['timesheets'][0]['caTimesheetHours']['tuesdayBreaksTime'],
                  wednesdayBreaksTime: e['timesheets'][0]['caTimesheetHours']['wednesdayBreaksTime'],
                  thursdayBreaksTime: e['timesheets'][0]['caTimesheetHours']['thursdayBreaksTime'],
                  fridayBreaksTime: e['timesheets'][0]['caTimesheetHours']['fridayBreaksTime'],
                  saturdayBreaksTime: e['timesheets'][0]['caTimesheetHours']['saturdayBreaksTime'],
                  TotalBreaksTime: e['timesheets'][0]['caTimesheetHours']['TotalBreaksTime'],
                  TotalWorkTimeReg: e['timesheets'][0]['caTimesheetHours']['TotalWorkTimeReg'],
                  TotalWorkTimeOT: e['timesheets'][0]['caTimesheetHours']['TotalWorkTimeOT'],
                  TotalWorkTimeDbl: e['timesheets'][0]['caTimesheetHours']['TotalWorkTimeDbl']
                   });
            });

            console.log(timesheetsList);

            

            if(timesheetsList.length > 0){
	            var doc = new PDFDocument();
	  			var stream = doc.pipe(blobStream());

	  			timesheetsList.forEach(function(e,i){
					console.log(e['state']);

					if(e['state'] == "summary"){
		  				if(i !== 0){ doc.addPage(); }
			  			//doc.image(appDir+'/public/images/jlm-logo-dark.png', 0, 5, {width: 300});
			  			var data = pdfTemplate;
				        //$('img').attr('src', data);

				        doc.image(data, {width: doc.page.width}, 0, 0);
				        doc.font('Helvetica-Bold').fontSize(18).fillColor("#4A4A4A")
				           .text(timesheetsList[i]['companyName'], 33, 195, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fontSize(18).fillColor("#D1D1D1")
				           .text('  |  ', { lineBreak: false, align: 'left' }).fillColor("#8D8D8D")
				           .text(timesheetsList[i]['firstname']+" "+timesheetsList[i]['lastname'], { align: 'left' });

				        //TIMESHEET ID - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Timesheet:', 33, 240, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(' 78698', { align: 'left' });

				        //DATE SUBMITTED - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Date Submitted:', 305, 240, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['timeSubmitted'], { align: 'left' });

				        //STATUS - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Status:', 33, 265, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['status'], { align: 'left' });

				        //DATE APPROVED - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Date Approved:', 305, 265, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['timeApproved'], { align: 'left' });

				        //PERIOD START - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Period Start:', 33, 290, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['sundayWorkDate'], { align: 'left' });

				        //APPROVED SUPERVISOR - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Approved By:', 305, 290, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['supervisorName'], { align: 'left' });

			            //TIME INFO POSITIONING MATH
			            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
			            var workDays = ['sundayWorkDate', 'mondayWorkDate', 'tuesdayWorkDate', 'wednesdayWorkDate', 'thursdayWorkDate', 'fridayWorkDate', 'saturdayWorkDate'];
			            var workTimes = ['sundayWorkTime', 'mondayWorkTime', 'tuesdayWorkTime', 'wednesdayWorkTime', 'thursdayWorkTime', 'fridayWorkTime', 'saturdayWorkTime'];
                
			            var disclaimerPositionX = 32;
			            var dayPositionX = 62;
			            var datePositionX = 167;
			            var hourPositionX = 520;

			            var disclaimerPositionY = 655;
			            var rowPositionY = 392;
			            var totalsPositionY;

			            days.forEach(function(item, index){

			                  //DAYS
			                  doc.font('Helvetica').fontSize(11).fillColor("#585858")
			                   .text(item, dayPositionX, rowPositionY+(index*22));
			                   
			                  doc.font('Helvetica').fontSize(11).fillColor("#8D8D8D")
			                   .text(' '+timesheetsList[i][workDays[index]], datePositionX, rowPositionY+(index*22));

			                  doc.font('Helvetica').fontSize(11).fillColor("#555555")
			                   .text(' '+timesheetsList[i][workTimes[index]], hourPositionX, rowPositionY+(index*22));
                         console.log(timesheetsList[i]);
                         console.log(workTimes[index]);
                         console.log(timesheetsList[i][workTimes[index]]);
			            });


			         //TOTAL
			          totalsPositionY = rowPositionY+(7*22+10);
               doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
			                   .text(' '+timesheetsList[i]['TotalWorkTime'], hourPositionX, totalsPositionY);

                        //TIMESHEET DISCLAIMER
                 doc.font('Helvetica-Bold').fontSize(6).fillColor("#393939")
                         .text(window.timesheetsDisclaimer.replace(/\t/g, ' ').replace(/(?:\r\n|\r|\n)/g, ' '), disclaimerPositionX, disclaimerPositionY, {width: 545, lineHeight: 2, align: 'justify'} );

                         //console.log(window.timesheetsDisclaimer);



			        }

			        else if(e['state'] == "ca"){
			        	if(i !== 0){ doc.addPage(); }
			  			//doc.image(appDir+'/public/images/jlm-logo-dark.png', 0, 5, {width: 300});
			  			var data = pdfTemplateCa;
				        //$('img').attr('src', data);

				        doc.image(data, {width: doc.page.width}, 0, 0);
				        doc.font('Helvetica-Bold').fontSize(15).fillColor("#4A4A4A")
				           .text(timesheetsList[i]['companyName'], 33, 195, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fontSize(15).fillColor("#D1D1D1")
				           .text('  |  ', { lineBreak: false, align: 'left' }).fillColor("#8D8D8D")
				           .text(timesheetsList[i]['firstname']+" "+timesheetsList[i]['lastname'], { align: 'left' });

				        //TIMESHEET ID - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Timesheet:', 33, 240, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(' 78698', { align: 'left' });

				        //DATE SUBMITTED - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Date Submitted:', 305, 240, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['timeSubmitted'], { align: 'left' });

				        //STATUS - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Status:', 33, 262, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['status'], { align: 'left' });

				        //DATE APPROVED - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Date Approved:', 305, 262, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['timeApproved'], { align: 'left' });

				        //PERIOD START - LEFT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Period Start:', 33, 284, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['sundayWorkDate'], { align: 'left' });

				        //APPROVED SUPERVISOR - RIGHT
				        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
				           .text('Approved By:', 305, 284, { lineBreak: false, align: 'left' })
				           .font('Helvetica').fillColor("#8D8D8D")
				           .text(" "+timesheetsList[i]['supervisorName'], { align: 'left' });

			            //TIME INFO POSITIONING MATH
			            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
			            var workDays = ['sundayWorkDate', 'mondayWorkDate', 'tuesdayWorkDate', 'wednesdayWorkDate', 'thursdayWorkDate', 'fridayWorkDate', 'saturdayWorkDate'];
			            var workTimes = ['sundayWorkTime', 'mondayWorkTime', 'tuesdayWorkTime', 'wednesdayWorkTime', 'thursdayWorkTime', 'fridayWorkTime', 'saturdayWorkTime'];
                  var breaksTimes = ['sundayBreaksTime', 'mondayBreaksTime', 'tuesdayBreaksTime', 'wednesdayBreaksTime', 'thursdayBreaksTime', 'fridayBreaksTime', 'saturdayBreaksTime'];
                  var workTimeReg = ['sundayWorkTimeReg', 'mondayWorkTimeReg', 'tuesdayWorkTimeReg', 'wednesdayWorkTimeReg', 'thursdayWorkTimeReg', 'fridayWorkTimeReg', 'saturdayWorkTimeReg'];
                  var workTimeOT = ['sundayWorkTimeOT', 'mondayWorkTimeOT', 'tuesdayWorkTimeOT', 'wednesdayWorkTimeOT', 'thursdayWorkTimeOT', 'fridayWorkTimeOT', 'saturdayWorkTimeOT'];
                  var workTimeDbl = ['sundayWorkTimeDbl', 'mondayWorkTimeDbl', 'tuesdayWorkTimeDbl', 'wednesdayWorkTimeDbl', 'thursdayWorkTimeDbl', 'fridayWorkTimeDbl', 'saturdayWorkTimeDbl'];

                  var disclaimerPositionX = 32;
			            var dayPositionX = 62;
			            var datePositionX = 167;
                  var breaksTimePositionX = 285;
                  var regTimePositionX = 365;
                  var OTTimePositionX = 445;
			            var dblTimePositionX = 520;

                  var disclaimerPositionY = 655;
			            var rowPositionY = 392;
			            var TotalWorkTimeRegPositionY;
                  var TotalWorkTimeOTPositionY;
                  var TotalWorkTimeDblPositionY;
                  var TotalBreaksTimePositionY;

			            days.forEach(function(item, index){

			                  //DAYS
			                  doc.font('Helvetica').fontSize(11).fillColor("#585858")
			                   .text(item, dayPositionX, rowPositionY+(index*22));
			                   
			                  doc.font('Helvetica').fontSize(11).fillColor("#8D8D8D")
			                   .text(' '+timesheetsList[i][workDays[index]], datePositionX, rowPositionY+(index*22));

                        doc.font('Helvetica').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i][breaksTimes[index]], breaksTimePositionX, rowPositionY+(index*22));

                        doc.font('Helvetica').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i][workTimeReg[index]], regTimePositionX, rowPositionY+(index*22));

                        doc.font('Helvetica').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i][workTimeOT[index]], OTTimePositionX, rowPositionY+(index*22));

			                  doc.font('Helvetica').fontSize(11).fillColor("#555555")
			                   .text(' '+timesheetsList[i][workTimeDbl[index]], dblTimePositionX, rowPositionY+(index*22));

			            });


			                  //TOTALS
			                  TotalWorkTimeDblPositionY = rowPositionY+(7*22+10);
			                  doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
			                   .text(' '+timesheetsList[i]['TotalWorkTimeDbl'], dblTimePositionX, TotalWorkTimeDblPositionY);

                        TotalWorkTimeOTPositionY = rowPositionY+(7*22+10);
                        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i]['TotalWorkTimeOT'], OTTimePositionX, TotalWorkTimeOTPositionY);

                        TotalWorkTimeRegPositionY = rowPositionY+(7*22+10);
                        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i]['TotalWorkTimeReg'], regTimePositionX, TotalWorkTimeRegPositionY);

                        TotalBreaksTimePositionY = rowPositionY+(7*22+10);
                        doc.font('Helvetica-Bold').fontSize(11).fillColor("#555555")
                         .text(' '+timesheetsList[i]['TotalBreaksTime'], breaksTimePositionX, TotalBreaksTimePositionY);

                        //TIMESHEET DISCLAIMER
                 doc.font('Helvetica-Bold').fontSize(6).fillColor("#2D2D2D")
                         .text(window.timesheetsDisclaimer.replace(/\t/g, ' ').replace(/(?:\r\n|\r|\n)/g, ' '), disclaimerPositionX, disclaimerPositionY, {width: 545, lineHeight: 2, align: 'justify'} );

                         //console.log(window.timesheetsDisclaimer);
			        	}

			        });

			  			// end and display the document in the iframe to the right
			  			doc.end();
			  			//alert(timesheetsList[0]['state']);
			  			var saveData = (function () {
			  			    var a = document.createElement("a");
			  			    document.body.appendChild(a);
			  			    a.style = "display: none";
			  			    return function (blob, fileName) {
			  			        var url = window.URL.createObjectURL(blob);
			  			        a.href = url;
			  			        a.download = fileName;
			  			        a.click();
			  			        window.URL.revokeObjectURL(url);
			  			    };
			  			}());

			  			stream.on('finish', function() {
			  			  var blob = stream.toBlob('application/pdf');
			  			  saveData(blob, 'aa.pdf');
			  			  // iframe.src = stream.toBlobURL('application/pdf');
			  			});
	  		}
	  		else {
	  			alert("There aren't any projects that are approved to download.");
	  		}
        }).fail(function( jqXHR, textStatus ) {
          alert( "Request failed: " + textStatus );
        });

    	}
    });

    $(".tool-tip").tooltip();

    $(".delete").click(function(e){
    	var confirm = window.confirm("Are you sure you want to delete?");
    	if(confirm == false){
    		e.preventDefault();
    	}
    });

    $('.input-button-caret').click(function(e){
      $(this).closest(".input-box").find(".dropdown-menu").toggle();
    });

    $('.dropdown-menu').mouseleave(function(e){
      $(this).toggle();
    });

	/* PAGE ACTIONS */
	$(".input-companies-selector").change(function(){
		var companiesSelector = $('select[name=companiesSelector]');

		if(companiesSelector.val()){
			console.log("Company changed to: "+companiesSelector.val());

			//Find the projects under companies
			$.ajax({
		  			method: "POST",
		  			url: appDir+"/api/get/list/projectsInCompany/"+companiesSelector.val(),
		  			dataType: "json"
				}).done(function( msg ) {

						var projects = [];
	  					msg.forEach(function(e){ 
		  					projects.push({id: e['ID'], text: e['name']}); 
	  					});

	  					console.log(projects);
	  					var hasError = function(){ if($(".input-projects-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
	  					
              if(companiesSelector.val() == 0){ 
                $("select[name=role]").val('internal').change();
              }

						$(".input-projects-selector.input-search-ajax option:nth-child(1)").text("Choose a project.");

						$(".input-projects-selector.input-search-ajax").html("");
						$(".input-projects-selector.input-search-ajax").select2('destroy');
	  					$(".input-projects-selector.input-search-ajax").select2({ containerCssClass : hasError(), data: projects, placeholder: { id: '-1', text: 'Select a project.'} });

	  			}).fail(function( jqXHR, textStatus ) {
	  				alert( "Request failed: " + textStatus );
	  			});

		}

	});
	/* NEED WORK */
	$(".input-projects-selector").ready(function(){
		var hasError = function(){ if($(".input-projects-selector.input-search-ajax").hasClass("has-error")){ return "has-error"; } };
	  	var projectValue = $(this).val();

		if(!projectValue){		
			//$(".input-projects-selector").select2("disabled");
		}
	});

	/* PAGE ACTIONS */
	$(".input-qbEmployees-selector").change(function(){

		window.qbEmployees.forEach(function(){
			console.log("test");

		});

		console.log($(this).val());
		console.log(appDir+"/api/quickbooks/list/single/employee/"+$(this).val());
  			var costsList = $(".costsList");

			$.ajax({
		  			method: "POST",
		  			url: appDir+"/api/quickbooks/list/single/employee/"+$(this).val(),
		  			dataType: "json"
				}).done(function( msg ) {
	
          costsList.html("");
					 costsList.append('<div class="table-row"><div class="table-cell"><div class="input-value full"><input type="hidden" name="qbBillRate" value="'+msg['UnitPrice']+'"><strong>Bill Rate:</strong> $'+msg['UnitPrice']+'</div></div></div>');
			     costsList.append('<div class="table-row"><div class="table-cell"><div class="input-value full"><input type="hidden" name="qbPayRate" value="'+msg['Cost']+'"><strong>Pay Rate:</strong> $'+msg['Cost']+'</div></div></div>');
      
	  			}).fail(function( jqXHR, textStatus ) {
	  				alert( "Request failed: " + textStatus );
	  			});
	
			});

	//$(".date-select span").after(" <i class='fa fa-chevron-down' aria-hidden='true'></i>");
	$(".date-select .date, .date-select .fa-chevron-down").click(function(){
		$(".calendar-select").fadeToggle(100);

	});

	$(".calendar-select").mouseleave(function(){
		$(".calendar-select").fadeOut();
	});



	$(function() {

      	var dateText = $('.date-select').data("startDate");
      	display = $('.date-select .date');
	  	var disableddates = ["20-05-2015", "12-11-2014", "12-25-2014", "12-20-2014"];

		$(".date-select .fa.fa-plus").click(function(e){

			if(typeof window.startDateText !== "undefined"){
				if($('.date-select').data('dateFor') == "employee"){
	       			window.location.href = appDir+"/administrator/timesheet/add/"+window.startDateText.replace(/\//g, "-")+"/"+window.endDateText.replace(/\//g, "-");
	       		
	       		}
	       		else {
	       			var employeeId = $('.date-select').data("employeeId");
	       			window.location.href = appDir+"/administrator/timesheets/add/employee/"+employeeId+"/"+window.startDateText.replace(/\//g, "-")+"/"+window.endDateText.replace(/\//g, "-");
	       		}
       		}
       		else {
       			alert("Select a date first.");
       		}
		});

		$(".date-select .fa.fa-search").click(function(e){
			if(typeof window.startDateText !== "undefined"){
				    if($('.date-select').data('dateFor') == "employee"){
	       			window.location.href = appDir+"/administrator/timesheet/add/"+window.startDateText.replace(/\//g, "-")+"/"+window.endDateText.replace(/\//g, "-");   		
	       		}
            else if($('.date-select').data('dateFor') == "invoices"){
              window.location.href = appDir+"/administrator/invoices/"+window.startDateText.replace(/\//g, "-")+"/"+window.endDateText.replace(/\//g, "-");      
            }
	       		else {
	       			window.location.href = appDir+"/administrator/timesheets/view/all/"+window.startDateText.replace(/\//g, "-")+"/"+window.endDateText.replace(/\//g, "-");
	       		}
      }
   		else {
      		alert("Select a date first.");
      }
		});

		function DisableSpecificDates(date) {
	    	var string = jQuery.datepicker.formatDate('dd-mm-yy', date);
	    	return [disableddates.indexOf(string) == -1];
	  	}
      $('.calendar-select').weekpicker({
        currentText: dateText,
        onSelect: function(dateText, startDateText, endDateText, startDate, endDate, inst) {

        	var startDateText = moment(startDateText.replace(/\//g, "-"),'M/D/YYYY');
			var endDateText = moment(endDateText.replace(/\//g, "-"),'M/D/YYYY');

			window.startDateText = moment(startDateText).format('M/D/YY');
			window.endDateText = moment(endDateText).format('M/D/YY');

          	display.html('<sup><i class="fa fa-calendar"></i>'+ window.startDateText +" - "+ window.endDateText +"</sup>");
          	console.log(window.startDateText +" "+ window.endDateText);

        	if($('.date-select').data('dateFor') == "employee"){
       			//window.location.href = appDir+"/administrator/timesheet/add/"+startDateText.replace(/\//g, "-")+"/"+endDateText.replace(/\//g, "-");
       		
       		}
       		else {
       			//window.location.href = appDir+"/administrator/timesheets/view/all/"+startDateText.replace(/\//g, "-")+"/"+endDateText.replace(/\//g, "-");
       		}

       		$(".calendar-select").fadeToggle(100);
        }
      });

    });
	 $.widget('lugolabs.weekpicker', {
	    _weekOptions: {
	      showOtherMonths:   true,
	      selectOtherMonths: true
	    },

	    _create: function() {
	      var self = this;
	      this._dateFormat = this.options.dateFormat || $.datepicker._defaults.dateFormat;
	      var date = this._initialDate();
	      this._setWeek(date);
	      var onSelect = this.options.onSelect;
	      this._picker = $(this.element).datepicker($.extend(this.options, this._weekOptions, {
	        onSelect: function(dateText, inst) {
	          self._select(dateText, inst, onSelect);
	        },
	        beforeShowDay: function(date) {
	          if($(".date-select").data("disabled-dates")){
		          var datesDisabled = $(".date-select").data("disabled-dates").split(',');
		          console.log(datesDisabled);

		        	var string = jQuery.datepicker.formatDate('mm-dd-yy', date);
		        	console.log(string);
		        	return [ datesDisabled.indexOf(string) == -1 ];
		       }
		       else {
		       	return self._showDay(date);  
		       }
	        },
	        onChangeMonthYear: function(year, month, inst) {
	          self._selectCurrentWeek();
	        },
	        maxDate: new Date, 
	        minDate: new Date(2007, 6, 12),
	        dateFormat: 'm-d-yy' 
	      }));
	      $(document)
	        .on('mousemove',  '.ui-datepicker-calendar tr', function() { $(this).find('td a').addClass('ui-state-hover'); })
	        .on('mouseleave', '.ui-datepicker-calendar tr', function() { $(this).find('td a').removeClass('ui-state-hover'); });
	      this._picker.datepicker('setDate', date);
	    },

	    _initialDate: function() {
	      if (this.options.currentText) {
	        return $.datepicker.parseDate(this._dateFormat, this.options.currentText);
	      } else {
	        return new Date;
	      }
	    },

	    _select: function(dateText, inst, onSelect) {
	      this._setWeek(this._picker.datepicker('getDate'));
	      var startDateText = $.datepicker.formatDate(this._dateFormat, this._startDate, inst.settings);
	      var endDateText = $.datepicker.formatDate(this._dateFormat, this._endDate, inst.settings);

	      this._picker.val(startDateText);
	      if (onSelect) onSelect(dateText, startDateText, endDateText, this._startDate, this._endDate, inst);
	    },

	    _showDay: function(date) {
	      var cssClass = date >= this._startDate && date <= this._endDate ? 'ui-datepicker-current-day' : '';
	      return [true, cssClass];
	    },

	    _setWeek: function(date) {
	      var year = date.getFullYear(),
	        month = date.getMonth(),
	        day   = date.getDate() - date.getDay();
	      this._startDate = new Date(year, month, day);
	      this._endDate   = new Date(year, month, day + 6);
	    },

	    _selectCurrentWeek: function() {
	      $('.ui-datepicker-calendar')
	        .find('.ui-datepicker-current-day a')
	        .addClass('ui-state-active');
	    }
	  });

	$(".input-add-supervisors").click(function(){
		var supervisorsSelector = $('select[name=supervisorsSelector] option:selected')

		if(typeof supervisorsSelector.val() !== "undefined"){
			if(supervisorsSelector.val() !== ""){
				var supervisorsList = $(".supervisorsList");
				var name = $.trim(supervisorsSelector.text());

				//Split id because it contains email and id
				var values = $.trim(supervisorsSelector.val());
				var ID = values.split(",")[0];
				var email = values.split(",")[1];

				//Remove option from list
				$('select[name=supervisorsSelector] option[value="'+values+'"]').detach();

				supervisorsList.append('<div class="table-row"><div class="table-cell"><div class="input-value full"><input type="hidden" name="supervisors[]" value="'+ID+'"><span class="supervisorName">'+name+'</span> <span class="divider">|</span> <span class="supervisorEmail">'+email+'</span> <span class="input-remove input-remove-supervisors"><i class="fa fa-minus-square"></i></span></div></div></div>');
			}
			else { 	msg("Cannot add! Please select a supervisor!"); }
		}

		else {
			msg("Cannot add! Please select a supervisor!");
		}
	});
	$(".input-add-projects").click(function(){

		var projectsSelector = $('select[name=projectsSelector] option:selected')

		if(typeof projectsSelector.val() !== "undefined"){
			if(projectsSelector.val() !== ""){
				var projectsList = $(".projectsList");
				var name = $.trim(projectsSelector.text());
				var value = $.trim(projectsSelector.val());

				//Remove option from list
				$('select[name=projectsSelector] option[value="'+value+'"]').detach();

				projectsList.append('<div class="table-row"><div class="table-cell"><div class="input-value full"><input type="hidden" name="projects[]" value="'+value+'"><span class="projectsName">'+name+'</span> <span class="divider">|</span> <span class="input-remove input-remove-projects"><i class="fa fa-minus-square"></i></span></div></div></div>');
			}
			else { 	msg("Cannot add! Please select a project!"); }
		}

		else {
			msg("Cannot add! Please select a project!");
		}
	});

	var fixHelperModified = function(e, tr) {
	    var $originals = tr.children();
	    var $helper = tr.clone();
	    $helper.children().each(function(index) {
	        $(this).width($originals.eq(index).width())
	    });
	    return $helper;
	},
    updateIndex = function(e, ui) {
        $('tr', ui.item.parent()).each(function (i) {
        	var tr = $(this);

        	var inputName = tr.find("input, select");


          	console.log("index loop: "+i);

            $(this).attr('data-row',i);

            inputName.each(function(index,value){          	
            	if(typeof $(this).attr('name') !== typeof undefined && $(this).attr('name') !== false){
       	        	console.log("input Name: ")
		        	console.log(inputName);
		        	console.log("input Name exists?:");
		        	console.log(inputName.attr('name'));        		
          		
            	console.log($(this));
            	console.log("changed!");   
            		//inputName.attr('name', inputName.attr('name').replace(/\[\d+](?!.*\[\d)/, '['+(i)+']'));
            		$(this).attr('name', $(this).attr('name').replace(/\[\d+](?!.*\[\d)/, '['+(i)+']'));

            	}

            });
        });
    };

	$(".table-invoice.data-table tbody").sortable({
	    helper: fixHelperModified,
	    stop: updateIndex,
      handle: '.handle'
	});

	$(".input-add-invoice-line").click(function(e){
	    e.preventDefault();

	    var invoiceTable = $('.table-invoice');
	    //if(invoiceTable){

	     // var rowNode = invoiceTable.row.add($('<tr class="table-row"><td class="table-cell"></td><td class="table-cell"></td><td class="table-cell" align="right">           </td> <td class="table-cell" align="right"></td> <td class="table-cell" align="right"></td><td class="table-cell table-actions" align="right"><a href="" class="tool-tip delete" title="Delete Line"><i class="fa fa-trash-o" aria-hidden="true"></i></a> </td></tr>')).draw( false ).nodes()
	    //.to$();

      var rowID = invoiceTable.find("tr").last().data("row");
      var rowElement = $('<tr class="table-row" data-row="'+(rowID+1)+'"><td class="table-cell"><a href="#" class="handle ui-sortable-handle"><i class="fa fa-bars"></i></a></td><td class="table-cell"></td><td class="table-cell"></td><td class="table-cell" align="right"></td> <td class="table-cell" align="right"></td> <td class="table-cell" align="right"></td><td class="table-cell table-actions" align="right"><a href="#" class="tool-tip delete input-remove-invoice-line" title="Delete Line"><i class="fa fa-trash-o" aria-hidden="true"></i></a> </td></tr>');
      var employeeElement = $("<select name='lines["+(rowID+1)+"][employeeID]' class='input-invoice-qbEmployees-selector'><option value='' selected='selected'>Please select an employee.</option></select>");
      var typeElement = $("<select name='lines["+(rowID+1)+"][type]' class=''><option value='reg'>Regular</option><option value='ot'>Over Time</option><option value='dbl'>Double Time</option><option value='other'>Other</option></select>");
      //typeElement.selectize();
      var hoursElement = $("<input type='text' class='input-invoice-line reg-hours' name='lines["+(rowID+1)+"][regHours]' value='0' readonly>");
      var rateElement = $("<input type='text' class='input-invoice-line reg-rate' name='lines["+(rowID+1)+"][regRate]' value='0' readonly>");
      var amountElement = $("<input type='text' class='input-invoice-line reg-amount' name='lines["+(rowID+1)+"][regAmount]' value='0' readonly>");

      rowElement.find("td:eq(2)").append(typeElement);
      rowElement.find("td:eq(3)").append(hoursElement);
      rowElement.find("td:eq(4)").append(rateElement);
      rowElement.find("td:eq(5)").append(amountElement);

      invoiceTable.append(rowElement);

      

	   // rowNode.addClass( 'table-row' );
	    //rowNode.children("td").addClass( 'table-cell' );

	    //}
	});

  $(".input-add-invoice-emails").click(function(){

    var invoiceEmailInput = $('input[name=invoice-emails]')

    if(typeof invoiceEmailInput.val() !== "undefined"){
      if(invoiceEmailInput.val() !== ""){
        var invoiceEmailsList = $(".invoiceEmailsList");
        var value = $.trim(invoiceEmailInput.val());

        invoiceEmailsList.append('<div class="table-row"><div class="table-cell"><div class="input-value full"><input type="hidden" name="invoiceEmails[]" value="'+value+'"><span class="invoiceEmail">'+value+'</span> <span class="input-remove input-remove-invoice-emails"><i class="fa fa-minus-square"></i></span></div></div></div>');
      }
      else {  msg("Cannot add! Please enter an email!"); }
    }

    else {
      msg("Cannot add! Please enter an email!");
    }
  });

  $(".table-invoice .reg-hours, .table-invoice .ot-hours, .table-invoice .dbl-hours").mousedown(function(event){
    if($(this).val() == 0){
      $(this).val("");
    }
  });

  $(".table-invoice .reg-hours, .table-invoice .ot-hours, .table-invoice .dbl-hours").blur(function(event){
    if($(this).val() == ""){
      $(this).val(0);
    }
  });

  $(document).on("mousedown", ".table-invoice .other-rate", function(event){
    if($(this).val() == 0){
      $(this).val("");
    }
  });

 $(document).on("blur", ".table-invoice .other-rate", function(event){
    if($(this).val() == ""){
      $(this).val(0);
    }
  });


  $(".table-invoice input.reg-hours, .table-invoice input.ot-hours, .table-invoice input.dbl-hours, .table-invoice input.other-hours, .table-invoice input.reg-rate, .table-invoice input.ot-rate, .table-invoice input.dbl-rate, .table-invoice input.other-rate").keypress(function(event){

  	 var enteredChar = String.fromCharCode(event.which); 

  	 if(event.which != 8 && (isNaN(enteredChar) && enteredChar !== '.')){
           event.preventDefault(); //stop character from entering input
           calcInvoice($(this));
       }
       else {
  			calcInvoice($(this));
  		}
  });

  $(".table-invoice input.reg-hours, .table-invoice input.ot-hours, .table-invoice input.dbl-hours, .table-invoice input.other-hours, .table-invoice input.reg-rate, .table-invoice input.ot-rate, .table-invoice input.dbl-rate, .table-invoice input.other-rate").keyup(function(event){

  	 var enteredChar = String.fromCharCode(event.which); 

  	 if(event.which != 8 && (isNaN(enteredChar) && enteredChar !== '.')){
           event.preventDefault(); //stop character from entering input
           calcInvoice($(this));
       }
       else {
  			calcInvoice($(this));
  		}
  });

  $(".table-invoice .input-invoice-line.line-type").change(function(e){

    var amount, rate;
    var tr = $(this).closest("tr");
    var rowID = tr.data("row");

    //Our employee dropdown values
    var employeeElement = tr.find(".input-invoice-qbEmployees-selector")[0].selectize;
    var employeeValues = $.map(employeeElement.items, function(value) {
        return employeeElement.options[value];
    });

    var employeeUnitPrice = employeeValues[0].UnitPrice;
    var employeeState = employeeValues[0].state;

    //Element before dropdown change
    var elementToChange = function(type, removeClass, changeClassTo) {

      if(typeof removeClass == 'undefined'){ removeClass= false; }
      
      var lineType = tr.attr("data-line-type");

        if(lineType == "caReg" || lineType == "summary"){
          console.log("reg");
          if(!removeClass){
           return tr.find(".reg-"+type); 
          }
          else {


            if(changeClassTo !== null){


             // alert("class change from: .reg-"+type+" to "+changeClassTo+"-"+type);
              if(("reg-"+type) !== (changeClassTo+"-"+type)){
              tr.find(".reg-"+type).addClass(changeClassTo+"-"+type);
              tr.find(".reg-"+type).fadeIn("slow");
              tr.find(".reg-"+type).removeClass("reg-"+type); 
              }
            }

            
          }
         } 
         else if(lineType == "caOt"){
          console.log("ot");
          if(!removeClass){
           return tr.find(".ot-"+type); 
          }
          else {
            if(changeClassTo !== null){
              if(("ot-"+type) !== (changeClassTo+"-"+type)){
               //alert("class change from: .ot-"+type+" to "+changeClassTo+"-"+type);
                tr.find(".ot-"+type).addClass(changeClassTo+"-"+type);
                tr.find(".ot-"+type).fadeIn("slow");
                tr.find(".ot-"+type).removeClass("ot-"+type); 
              }
            }

            
          }
         } 
         else if(lineType == "caDbl"){ 
          console.log("dbl");
            if(!removeClass){
              return tr.find(".dbl-"+type); 
            }
            else {

           if(changeClassTo !== null){
              if(("dbl-"+type) !== (changeClassTo+"-"+type)){
               // alert("class change from: .dbl-"+type+" to "+changeClassTo+"-"+type);
                tr.find(".dbl-"+type).addClass(changeClassTo+"-"+type);
                tr.find(".dbl-"+type).fadeIn("slow");             
                tr.find(".dbl-"+type).removeClass("dbl-"+type); 
              }
            }

            
            }
        } 
        else if(lineType == "other"){
          console.log("other");
            if(!removeClass){
              return tr.find(".other-"+type); 
            }
            else {

           if(changeClassTo !== null){
              if(("other-"+type) !== (changeClassTo+"-"+type)){
               // alert("class change from: .dbl-"+type+" to "+changeClassTo+"-"+type);
                tr.find(".other-"+type).addClass(changeClassTo+"-"+type);
                tr.find(".other-"+type).fadeIn("slow");             
                tr.find(".other-"+type).removeClass("other-"+type); 
              }
            }

            
            }
        }

        //if it's not other then remove other description
        if(lineType !== "other"){

        }
    };

    var elementClass = elementToChange

    //console.log(elementToChange);

    //console.log(employeeValues);
    //console.log(employeeValues[0].UnitPrice);

    //The value that we selected
    if($(this).val() == "reg"){
        rate = 1;
        amount = (employeeUnitPrice * rate);   
    //  alert("changing to reg");

        console.log(elementToChange("rate"));

        elementToChange("rate").attr("name","lines["+rowID+"][regRate]");
        elementToChange("hours").attr("name","lines["+rowID+"][regHours]");
        elementToChange("amount").attr("name","lines["+rowID+"][regAmount]");

        elementToChange("rate").toggle();
        elementToChange("amount").toggle();

        elementToChange("hours").val("0");
        elementToChange("hours").focus();

        elementToChange("rate").prop("readonly", true);
        elementToChange("amount").prop("readonly", true);

        elementToChange("rate",true,"reg");
        elementToChange("hours",true,"reg");
        elementToChange("amount",true,"reg");

    //  alert("class changed");

      if(employeeState == "ca"){
        tr.attr("data-line-type","caReg");
      }
      else {
        tr.attr("data-line-type","summary");
      }

          console.log($(this).val());
    amount = parseFloat(Math.round(amount* 100) / 100).toFixed(2);

    elementToChange("rate").val(amount);
    }
    else if($(this).val() == "ot"){
        rate = 1.5;
        amount = (employeeUnitPrice * rate);   
      //        alert("changing to ot");
        console.log(elementToChange("rate"));
        elementToChange("rate").attr("name","lines["+rowID+"][otRate]");
        elementToChange("hours").attr("name","lines["+rowID+"][otHours]");
        elementToChange("amount").attr("name","lines["+rowID+"][otAmount]");

        elementToChange("rate").toggle();
        elementToChange("amount").toggle();

        elementToChange("hours").val("0");
        elementToChange("hours").focus();

        elementToChange("rate").prop("readonly", true);
        elementToChange("amount").prop("readonly", true);

        elementToChange("rate",true,"ot");
        elementToChange("hours",true,"ot");
        elementToChange("amount",true,"ot");
    //  alert("class changed");

        tr.attr("data-line-type","caOt");

        console.log($(this).val());
    amount = parseFloat(Math.round(amount* 100) / 100).toFixed(2);

    elementToChange("rate").val(amount);

    }
    else if($(this).val() == "dbl"){  
      rate = 2;
      amount = (employeeUnitPrice * rate);   
       //       alert("changing to dbl");
      console.log(elementToChange("rate"));
      elementToChange("rate").attr("name","lines["+rowID+"][dblRate]");
      elementToChange("hours").attr("name","lines["+rowID+"][dblHours]");
      elementToChange("amount").attr("name","lines["+rowID+"][dblAmount]");

      elementToChange("rate").toggle();
      elementToChange("amount").toggle();

      elementToChange("hours").val("0");
      elementToChange("hours").focus();

      elementToChange("rate").prop("readonly", true);
      elementToChange("amount").prop("readonly", true);

      elementToChange("rate",true,"dbl");
      elementToChange("hours",true,"dbl");
      elementToChange("amount",true,"dbl");
    //  alert("class changed");


      tr.attr("data-line-type","caDbl");

      console.log($(this).val());
      amount = parseFloat(Math.round(amount* 100) / 100).toFixed(2);

      elementToChange("rate").val(amount);

    }
    else if($(this).val() == "other"){  
      elementToChange("rate").attr("name","lines["+rowID+"][otherRate]");
      elementToChange("hours").attr("name","lines["+rowID+"][otherHours]");
      elementToChange("amount").attr("name","lines["+rowID+"][otherAmount]");



      elementToChange("hours").val("0");
      //elementToChange("hours").focus();

      elementToChange("rate").val("0");
     // elementToChange("rate").focus();

      elementToChange("rate").prop("readonly", false);
      elementToChange("hours").prop("readonly", false);
      elementToChange("amount").prop("readonly", true);

      elementToChange("rate",true,"other");
      elementToChange("hours",true,"other");
      elementToChange("amount",true,"other");

      tr.attr("data-line-type","other");      

         
      $(this).val("");
      $(this).focus();  console.log($(this).val()+ "Test");
     // console.log($(this).selectize);
      //console.log(this);

      this.selectize.clear(true);
      //this.selectize.close();
      this.selectize.focus();

      //this.selectize.addOption({value:"othertest",text:'test'}); //option can be created manually or loaded using Ajax
      //this.selectize.addItem("other"); 



      //     console.log($(this).val());
    //amount = parseFloat(Math.round(amount* 100) / 100).toFixed(2);

   // elementToChange("rate").val(amount);
    }


    //alert("element Rate "+elementToChange("rate").val());
  	calcInvoice($(this));
  });

  $(".table-invoice-totals").ready(function(){
    $(".table-invoice-totals td").each(function(i,v){
      if(i == 0){
          $(this).width($(".table-invoice td:eq("+i+")").width() + $(".table-invoice td:eq("+(i+1)+")").width());  
          $(this).next("td").remove();
      }
      else {
        $(this).width($(".table-invoice td:eq("+i+")").width());
      }
    });   
    $(".table-invoice-totals").animate({
    opacity: 1,
  }, 100);
  });



  $(document).on("click", ".input-remove-invoice-emails, .input-remove-invoice-line", function(){

      $(this).closest(".table-row").remove();
  });

	$(document).on("click", ".input-remove-supervisors", function(){
			var id = $(this).closest(".input-value").children("input").val();
			var name = $(this).closest(".input-value").children(".supervisorName").text();
			var email = $(this).closest(".input-value").children(".supervisorEmail").text();

			var values = id+","+email;

			$('select[name=supervisorsSelector]').append("<option value='"+values+"'>"+name+"</option>")

			$(this).closest(".table-row").remove();
	});
	$(document).on("click", ".input-remove-projects", function(){
			var id = $(this).closest(".input-value").children("input").val();
			var name = $(this).closest(".input-value").children(".projectsName").text();

			$('select[name=projectsSelector]').append("<option value='"+id+"'>"+name+"</option>")

			$(this).closest(".table-row").remove();
	});
	$(document).on("click", "input[type=password]", function(){
		if($(this).val() && $(this).val() == "********"){
			$(this).val("");
		}
	});
	$(".disabled, .select2-container--disabled").mousedown(function(e){
		e.preventDefault();
	});
	$(document).on("mouseout", ".form-account-edit input[type=password]", function(){
		if($(this).val() == ""){
			$(this).val("********");
		}
	});

//Invoice - Element is the input being clicked
function calcInvoice(element){

  var tr = element.closest("tr");
  var lineType = tr.attr("data-line-type");



  //$(".input-invoice-qbEmployees-selector.input-search-ajax")
  var amountElement, amount, rate, hours;

  //alert(lineType);

  if(lineType == "summary" || lineType == "caReg"){
  	rate = element.closest("tr").find(".reg-rate").val();


  	hours = element.closest("tr").find(".reg-hours").val();
    amountElement = element.closest("tr").find(".reg-amount");
  }
  else if(lineType == "caOt"){
    rate = element.closest("tr").find(".ot-rate").val();
    hours = element.closest("tr").find(".ot-hours").val();
    amountElement = element.closest("tr").find(".ot-amount");
  }
  else if(lineType == "caDbl"){
    rate = element.closest("tr").find(".dbl-rate").val();
    hours = element.closest("tr").find(".dbl-hours").val();
    amountElement = element.closest("tr").find(".dbl-amount");
  }
  else if(lineType == "other"){
    rate = element.closest("tr").find(".other-rate").val();
    hours = element.closest("tr").find(".other-hours").val();
    amountElement = element.closest("tr").find(".other-amount");
  }
  //Calc Reg, OT, Dbl

if(hours == ""){
  hours = 0;
}


  //Calc row
  var amount = parseFloat(rate) * parseFloat(hours);
  amount = parseFloat(Math.round(amount* 100) / 100).toFixed(2);
	amountElement.val(amount);	

  //Calc Total

  var table = element.closest("table");
  var linesElement = table.find("tr");
  var total = 0;

  linesElement.each(function(){
    var lineType = $(this).attr("data-line-type");
    if(lineType == "summary" || lineType == "caReg"){
     // alert("reg calc");
      total += parseFloat($(this).find(".reg-amount").val());
      //alert(parseFloat($(this).find(".reg-amount").val()));
    }
    else if(lineType == "caOt"){
          //  alert("ot calc");
      total += parseFloat($(this).find(".ot-amount").val());
      //alert(parseFloat($(this).find(".ot-amount").val()));
    }
    else if(lineType == "caDbl"){
       //     alert("dbl calc");
       total += parseFloat($(this).find(".dbl-amount").val());
      // alert(parseFloat($(this).find(".dbl-amount").val()));
    }
  });

//alert("equals = "+total);
  var invoiceTotalElement = $(".table-invoice-totals span.invoice-total");

  invoiceTotalElement.text(parseFloat(Math.round(total* 100) / 100).toFixed(2));


}

//TimeSheets
function calcTotalWeekTime(){
	//Foreach row we need to calc time

	var total = 0;
	var i = 0;
	var days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];

	$(".form-timesheet-submit .form-row").each(function(){

		//only if this is a date row
		if($(this).data("date")){

			var clockInHr = $(this).find(".clock-in select:eq(0)").val();
			var clockInMin = $(this).find(".clock-in select:eq(1)").val();
			var clockOutHr = $(this).find(".clock-out select:eq(0)").val();
			var clockOutMin = $(this).find(".clock-out select:eq(1)").val();	

			console.log(i+"; clockInHr: "+clockInHr);
			console.log(i+"; clockInMin: "+clockInMin);
			console.log(i+"; clockOutHr: "+clockOutHr);
			console.log(i+"; clockOutMin: "+clockOutMin);	

			if(clockInHr && clockInMin && clockOutHr && clockOutMin){
				var timeStart = new Date("01/01/2007 " + $(this).find(".clock-in select:eq(0)").val()+":"+$(this).find(".clock-in select:eq(1)").val()+" "+$(this).find(".clock-in select:eq(2)").val());
				var timeEnd = new Date("01/01/2007 " + $(this).find(".clock-out select:eq(0)").val()+":"+$(this).find(".clock-out select:eq(1)").val()+" "+$(this).find(".clock-out select:eq(2)").val());
				
        var timeEndAMorPM = $(this).closest(".form-row").find(".clock-out select:eq(2)").val();
        if(clockOutHr == "12" && timeEndAMorPM == "am"){
            timeEnd = new Date("01/02/2007 " + $(this).closest(".form-row").find(".clock-out select:eq(0)").val()+":"+$(this).closest(".form-row").find(".clock-out select:eq(1)").val()+" "+$(this).closest(".form-row").find(".clock-out select:eq(2)").val());
        }

        var breakTime = ($(this).find(".breaks select:eq(0)").val()*3600000)+($(this).find(".breaks select:eq(1)").val()*60000); 

				var diff = (timeEnd - timeStart - breakTime) / 60000; //dividing by seconds and milliseconds
        var minutes = (diff % 60);
        var minutesForCalc = ((diff % 60) / 60).toFixed(2).substring(2);
       // minutesForCalc = parseFloat(Math.round(minutes * 100) / 100).toFixed(2);

				var hours = (diff - minutes) / 60;

				total += diff;
				console.log(diff);
				console.log(total);
			}

      //If saturday (last one) then calc total work time
			if(days[i] == "saturday"){
				//Total breakdown summation

				var minutes = (total % 60);
        var minutesForCalc = ((total % 60) / 60).toFixed(2).substring(2);

				var hours = (total - minutes) / 60;

				console.log("hours: "+hours+"; minutes: "+minutes+";");

				//Present info Total Work Time
				if(minutes == 0){
					minutes = "";
				}
				else if(minutes < 0){	
					minutes = Math.abs(minutes);
					if(hours == 0){
						hours = "-"+hours;
					}
					else {
						hours = -Math.abs(hours);
					}
				}
				$(".total-work-time input[type=hidden]").val(hours+"."+minutesForCalc);
				$(".total-work-time span").text(hours+"."+minutesForCalc);
			}
			i++;
		}
	});

		//If this row doesn't have an empty input then calc and add to total time
}

//If input is changed, then check row and calc row
$(".form-timesheet-submit .form-row select").change(function(){
		var selectedVal = $(this).val();
		$(this).val(selectedVal);

		console.log("changed to: "+$(this).val());

		//If what we picked wasn't empty calc
		if($(this).val() !== ""){

			console.log("changed to: "+$(this).val());

			//change option to selected for php
			$("option[value=" + $(this).val() + "]", this).attr("selected", true).siblings().removeAttr("selected");

			var clockInHr = $(this).closest(".form-row").find(".clock-in select:eq(0)").val();
			var clockInMin = $(this).closest(".form-row").find(".clock-in select:eq(1)").val();
      var breakHr = $(this).closest(".form-row").find(".breaks select:eq(0)").val();
      var breakMin = $(this).closest(".form-row").find(".breaks select:eq(1)").val();
			var clockOutHr = $(this).closest(".form-row").find(".clock-out select:eq(0)").val();
			var clockOutMin = $(this).closest(".form-row").find(".clock-out select:eq(1)").val();

			console.log("clockInHr: "+clockInHr);
			console.log("clockInMin: "+clockInMin);
      console.log("breakHr: "+breakHr);
      console.log("breakMin: "+breakMin); 
			console.log("clockOutHr: "+clockOutHr);
			console.log("clockOutMin: "+clockOutMin);

			if(clockInHr && clockInMin && clockOutHr && clockOutMin){
				console.log("we're going in");

				var timeStart = new Date("01/01/2007 " + $(this).closest(".form-row").find(".clock-in select:eq(0)").val()+":"+$(this).closest(".form-row").find(".clock-in select:eq(1)").val()+" "+$(this).closest(".form-row").find(".clock-in select:eq(2)").val());
				var timeEnd = new Date("01/01/2007 " + $(this).closest(".form-row").find(".clock-out select:eq(0)").val()+":"+$(this).closest(".form-row").find(".clock-out select:eq(1)").val()+" "+$(this).closest(".form-row").find(".clock-out select:eq(2)").val());
        
        var timeEndAMorPM = $(this).closest(".form-row").find(".clock-out select:eq(2)").val();
        if(clockOutHr == "12" && timeEndAMorPM == "am"){
            timeEnd = new Date("01/02/2007 " + $(this).closest(".form-row").find(".clock-out select:eq(0)").val()+":"+$(this).closest(".form-row").find(".clock-out select:eq(1)").val()+" "+$(this).closest(".form-row").find(".clock-out select:eq(2)").val());
        }

				var breakTime = ($(this).closest(".form-row").find(".breaks select:eq(0)").val()*3600000)+($(this).closest(".form-row").find(".breaks select:eq(1)").val()*60000); 

				var diff = (timeEnd - timeStart - breakTime) / 60000; //dividing by seconds and milliseconds
        var minutes = (diff % 60);

				var minutesForCalc = ((diff % 60) / 60).toFixed(2).substring(2);
				var hours = (diff - minutes) / 60;

				//Calculate Individual Row Work Time
				if(minutes == 0){
					minutes = "";
				}
				else if(minutes < 0){	
					minutes = Math.abs(minutes);
					if(hours == 0){
						hours = "-"+hours;
					}
					else {
						hours = -Math.abs(hours);
					}
				}

				$(this).closest(".form-row").find(".work-time span").text(hours+"."+minutesForCalc);				
				$(this).closest(".form-row").find(".work-time input[type=hidden]").val(hours+"."+minutesForCalc);

			}
			else {
				//If there is one that is missing then 
				//change option to selected for php
				$(this).closest(".form-row").find(".work-time span").text("00.00");				
				$(this).closest(".form-row").find(".work-time input[type=hidden]").val("00.00");
			}
		}
		else {
			//change option to selected for php
			$("option:eq(0)", this).attr("selected", true).siblings().removeAttr("selected");
			$(this).closest(".form-row").find(".work-time span").text("00.00");				
			$(this).closest(".form-row").find(".work-time input[type=hidden]").val("00.00");
		}

		//We have to calc week's total
		calcTotalWeekTime();
});


});