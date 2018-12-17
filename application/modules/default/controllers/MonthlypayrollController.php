<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2015 Sapplica
 *   
 *  Sentrifugo is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Sentrifugo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Sentrifugo.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Sentrifugo Support <support@sentrifugo.com>
 ********************************************************************************/

class Default_MonthlypayrollController extends Zend_Controller_Action
{

	private $_options;
	public function preDispatch()
	{
		$session = sapp_Global::_readSession();
		if(!isset($session))
		{
			if($this->getRequest()->isXmlHttpRequest())
			{
				echo Zend_Json::encode( array('login' => 'failed') );
				die();
			}
			else
			{
				$this->_redirect('');
			}
		}
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('getdepartments', 'json')->initContext();
		$ajaxContext->addActionContext('getpositions', 'json')->initContext();

	}

	public function init()
	{
		$this->_options= $this->getInvokeArg('bootstrap')->getOptions();
	}

	public function indexAction()
	{

        $monthly_model = new Default_Model_Monthlypayroll();

        $call = $this->_getParam('call');
        if($call == 'ajaxcall')
            $this->_helper->layout->disableLayout();

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }


        $statusidstring =$this->_request->getParam('con');
        $refresh = $this->_getParam('refresh');
        $data = array();
        $searchQuery = '';
        $searchArray = array();
        $dashboardcall = $this->_getParam('dashboardcall');
        $tablecontent='';

        	$statusid =  sapp_Global::_decrypt($statusidstring);
		       if($statusid !='' && is_numeric($statusid))
               {
                   if($statusid == 1)
                       $req_type =1;
                   else if($statusid == 2)
                       $req_type = 2;
                   else if($statusid == 3)
                       $req_type = 3;
                   else
                       $req_type = 1;
               }
               else
               {
                   $req_type = 1;
               }

        if($refresh == 'refresh')
        {
            if($dashboardcall == 'Yes')
                $perPage = DASHBOARD_PERPAGE;
            else
                $perPage = PERPAGE;
            $sort = 'ASC';$by = 'r.id';$pageNo = 1;$searchData = '';$searchQuery = '';
            $searchArray = array();
        }
        else
        {
            $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'ASC';
            $by = ($this->_getParam('by')!='')? $this->_getParam('by'):'r.id';
            if($dashboardcall == 'Yes')
                $perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
            else
                $perPage = $this->_getParam('per_page',PERPAGE);
            $pageNo = $this->_getParam('page', 1);
            $searchData = $this->_getParam('searchData');
        }

        // Monthly payroll Term Calc

        $term = $this->_getParam('term');
        $nowDate = new Zend_date();
        $term = $term ? date('Y-m-d',strtotime($term)):$nowDate->get('YYYY-MM-dd');
        $term_monthlypayroll = $monthly_model->getCreatedpayrollbyMonth($term);


        // Create new payroll
        $newpayroll = $this->_getParam('newpayroll');
        if($newpayroll){
            if($term_monthlypayroll['count'] == 0){
                $result = $monthly_model->CreateNewMonthlypayroll($loginUserId,$loginuserGroup);
            }
        }

        $create_monthlypayroll = "disabled";
        if($term_monthlypayroll['count'] == 0 && $term == $nowDate->get('YYYY-MM-dd')){
            $date_arr = explode('-', (string)$term);
            if($date_arr[2]<='15'){
                $create_monthlypayroll = "enabled";
            }
            else
                $create_monthlypayroll = "disabled";
        }

        $export_payroll = "disabled";
        if($term_monthlypayroll['count'] != 0){
            $export_payroll = "enabled";
        }


        $dataTmp = $monthly_model->getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$loginUserId,$loginuserGroup,$req_type,'monthlypayroll',$dashboardcall, $term);


        // Each Department Employees Count
        $opdepartments = $monthly_model->getDepartmentCount(1, $term);
        $opdepartmentcount = $opdepartments['count'];
        $this->view->op_count = $opdepartmentcount;

        $nonopdepartments = $monthly_model->getDepartmentCount(2, $term);
        $nonopdepartmentcount = $nonopdepartments['count'];
        $this->view->nonop_count = $nonopdepartmentcount;

        $admdepartments = $monthly_model->getDepartmentCount(3, $term);
        $admdepartmentcount = $admdepartments['count'];
        $this->view->adm_count = $admdepartmentcount;

        $this->view->statusidstring = $statusidstring;

        array_push($data,$dataTmp);
        $this->view->dataArray = $dataTmp;
        $this->view->req_type=$req_type;
        $this->view->call = $call;
        $this->view->term = $term;
        $this->view->create_monthlypayroll = $create_monthlypayroll;
        $this->view->export_payroll = $export_payroll;
        $this->view->message = "this is monthly payroll page";
        $this->view->messages = $this->_helper->flashMessenger->getMessages();

	}

	public function add1Action()
	{
		$auth = Zend_Auth::getInstance();
		$data = array();

		if($auth->hasIdentity())
		{
			$sess_vals = $auth->getStorage()->read();
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserRole = $auth->getStorage()->read()->emprole;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}

		$job_title_permission = sapp_Global::_checkprivileges(JOBTITLES,$loginuserGroup,$loginuserRole,'add');
		$positions_permission = sapp_Global::_checkprivileges(POSITIONS,$loginuserGroup,$loginuserRole,'add');
		$emp_status_permission = sapp_Global::_checkprivileges(EMPLOYMENTSTATUS,$loginuserGroup,$loginuserRole,'add');

		$form = new Default_Form_Monthlypayroll();
		$monthlypayroll_model = new Default_Model_Monthlypayroll();
		$user_model = new Default_Model_Usermanagement();

        $norec_arr = array();

        $elements = $form->getElements();

        $this->view->form = $form;
        $this->view->loginuserGroup = $loginuserGroup;
        $this->view->data = $data;

        // To check whether to display Employment Status configuration link or not
//        $employmentstatusmodel = new Default_Model_Employmentstatus();
//        $activeEmploymentStatusArr =  $employmentstatusmodel->getEmploymentStatuslist();
//        $empstatusstr = '';
//        if(!empty($activeEmploymentStatusArr))
//        {
//            for($i=0;$i<sizeof($activeEmploymentStatusArr);$i++)
//            {
//                $newarr1[] = $activeEmploymentStatusArr[$i]['workcodename'];
//            }
//            $empstatusstr = implode(",",$newarr1);
//        }

//        $norec_arr['department'] = "Departments are not added yet.";
//
//        $this->view->messages = $norec_arr;


        if($this->getRequest()->getPost())
        {
            $result = $this->save($form,array());

            $this->view->msgarray = $result;
            $this->view->messages = $result;
        }
	}

	/**
	 * This action is used for adding/updating data.
	 * @parameters
	 * @param $id  =  id of requisition.
	 *
	 * @return Zend_Form.
	 */
	public function editAction()
	{
	    $id = $this->getRequest()->getParam('id',null);
		$auth = Zend_Auth::getInstance();
		$data = array();

		if($auth->hasIdentity())
		{
			$sess_vals = $auth->getStorage()->read();
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserRole = $auth->getStorage()->read()->emprole;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}


		$form = new Default_Form_Monthlypayroll();
		$monthlypayroll_model = new Default_Model_Monthlypayroll();
		$usersModel = new Default_Model_Users();
		$user_model = new Default_Model_Usermanagement();

		$form->setAttrib('action',BASE_URL.'monthlypayroll/edit/id/'.$id);
		$form->submit->setLabel('Update');

		$edit_flag = '';
		$edit_order = '';

		try {
            if ($id > 0 && is_numeric($id)) {
                $id = abs($id);

                $data = $monthlypayroll_model->getMonthlypayrollForEdit($id);

                if (!empty($data)) {

                    // date format convert

                    $data['contract_date'] = sapp_Global::change_date($data['contract_date'], 'view');
                    $data['starting_date'] = sapp_Global::change_date($data['starting_date'], 'view');

                    $form->setDefault('id', $id);
                    $form->setDefault('employee_id', $data['employee_id']);
//                    $form->employee_id->setAttrib("disabled", "disabled");
                    $form->setDefault('starting_date', $data['starting_date']);
//                    $form->starting_date->setAttrib("disabled", "disabled");
                    $form->setDefault('employee_name', $data['employee_name']);
//                    $form->employee_name->setAttrib("disabled", "disabled");
                    $form->setDefault('department', $data['department']);
//                    $form->department->setAttrib("disabled", "disabled");
                    $form->setDefault('grossbase_salary', $data['grossbase_salary']);
//                    $form->grossbase_salary->setAttrib("disabled", "disabled");
                    $form->setDefault('contract_date', $data['contract_date']);
//                    $form->contract_date->setAttrib("disabled", "disabled");
                    $form->setDefault('comments',$data['comments']);
                    $form->setDefault('sick_leavedays',$data['sick_leavedays']);
                    $form->setDefault('standby_hours',$data['standby_hours']);
                    $form->setDefault('overtime_hours',$data['overtime_hours']);
                    $form->setDefault('addition_rollposition',$data['addition_rollposition']);
                    $form->setDefault('annual_leavedays',$data['annual_leavedays']);
                    $form->setDefault('weekend_nationaldays',$data['weekend_nationaldays']);
                    $form->setDefault('daily_allowance',$data['daily_allowance']);
                    $form->setDefault('deductadd_salary',$data['deductadd_salary']);
                    $form->setDefault('gross_salary',$data['gross_salary']);
                    $form->setDefault('work_days',$data['work_days']);
                    $form->setDefault('monthlygross_salary',$data['monthlygross_salary']);
                    $form->setDefault('contribution_salary',$data['contribution_salary']);
                    $form->setDefault('employeesocial_insurance',$data['employeesocial_insurance']);
                    $form->setDefault('employeehealth_insurance',$data['employeehealth_insurance']);
                    $form->setDefault('employeetotal_insurance',$data['employeetotal_insurance']);
                    $form->setDefault('employersocial_insurance',$data['employersocial_insurance']);
                    $form->setDefault('employerhealth_insurance',$data['employerhealth_insurance']);
                    $form->setDefault('employertotal_insurance',$data['employertotal_insurance']);
                    $form->setDefault('whtaxpaided_salary',$data['whtaxpaided_salary']);
                    $form->setDefault('progresive_whtax',$data['progresive_whtax']);
                    $form->setDefault('bankpaid_salary',$data['bankpaid_salary']);
                    $form->setDefault('addtax_salary',$data['addtax_salary']);
                    $form->setDefault('whtax_salary',$data['whtax_salary']);
                    $form->setDefault('total_bankpaid_salary',$data['total_bankpaid_salary']);


                    $edit_flag = "yes";
                    $this->view->loginuserGroup = $loginuserGroup;
                    $this->view->form = $form;
                    $this->view->data = $data;
                    $this->view->edit_flag = $edit_flag;

                    if ($this->getRequest()->getPost()) {
                        $result = $this->save($form,$data);
                        $this->view->msgarray = $result;
                        $this->view->messages = $result;
                    }
                    $this->view->ermsg = '';

                } else {
                    $this->view->nodata = 'nodata';
                }
            } else {
                $this->view->nodata = 'nodata';
            }
        }
		catch(Exception $e)
		{
			$this->view->nodata = 'nodata';
		}
	}

	public function save($monthlypayrollform,$data)
	{
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		$monthlypayroll_model = new Default_Model_Monthlypayroll();
		$user_model = new Default_Model_Usermanagement();

		$appr_mail = '';$appr_per = '';


		if($monthlypayrollform->isValid($this->_request->getPost()))
		{
			$trDb = Zend_Db_Table::getDefaultAdapter();
			// starting transaction
			$trDb->beginTransaction();

			try
			{
              	$id = (int)$this->_getParam('id',null);
				$employee_id = $this->_getParam('employee_id',null);
                $employee_name = $this->_getParam('employee_name',null);
				$department = $this->_getParam('department',null);
				$starting_date = $this->_getParam('starting_date',null);
				$comments = $this->_getParam('comments',null);
				$sick_leavedays = $this->_getParam('sick_leavedays',null);
				$standby_hours = $this->_getParam('standby_hours',null);
                $overtime_hours = $this->_getParam('overtime_hours',null);
                $addition_rollposition = $this->_getParam('addition_rollposition',null);
                $annual_leavedays = $this->_getParam('annual_leavedays',null);
                $weekend_nationaldays = $this->_getParam('weekend_nationaldays',null);
                $contract_date = $this->_getParam('contract_date',null);
                $daily_allowance = $this->_getParam('daily_allowance',null);
                $grossbase_salary = $this->_getParam('grossbase_salary',null);
                $deductadd_salary = $this->_getParam('deductadd_salary',null);
                $gross_salary = $this->_getParam('gross_salary',null);
                $work_days	 = $this->_getParam('work_days',null);
                $monthlygross_salary = $this->_getParam('monthlygross_salary',null);
                $contribution_salary = $this->_getParam('contribution_salary',null);
                $employeesocial_insurance = $this->_getParam('employeesocial_insurance',null);
                $employeehealth_insurance= $this->_getParam('employeehealth_insurance',null);
                $employeetotal_insurance= $this->_getParam('employeetotal_insurance',null);
                $employersocial_insurance = $this->_getParam('employersocial_insurance',null);
                $employerhealth_insurance = $this->_getParam('employerhealth_insurance',null);
                $employertotal_insurance = $this->_getParam('employertotal_insurance',null);
                $whtaxpaided_salary = $this->_getParam('whtaxpaided_salary',null);
                $progresive_whtax = $this->_getParam('progresive_whtax',null);
                $bankpaid_salary = $this->_getParam('bankpaid_salary',null);
                $total_bankpaid_salary = $this->_getParam('total_bankpaid_salary',null);
                $addtax_salary = $this->_getParam('addtax_salary',null);
                $whtax_salary = $this->_getParam('whtax_salary',null);
                $edit_flag = $this->_getParam('edit_flag',null);


                if($edit_flag !='' && $edit_flag=='yes'){

                    $employee_id = $data['employee_id'];
                    $employee_name = $data['employee_name'];
                    $department = $data['department'];
                    $starting_date = $data['starting_date'];
                    $contract_date = $data['contract_date'];
                    $grossbase_salary = $data['grossbase_salary'];

                }

                $data = array(
                    'employee_id' 	    =>	trim($employee_id),
                    'employee_name' 	=>	trim($employee_name),
                    'department' 		=>	trim($department),
                    'starting_date'	    =>	sapp_Global::change_date(trim($starting_date),'database'),
                    'comments'		    =>	trim($comments),
                    'sick_leavedays'    => 	trim($sick_leavedays),
                    'standby_hours'	    =>	trim($standby_hours),
                    'overtime_hours'	=>	trim($overtime_hours),
                    'addition_rollposition' => 	trim($addition_rollposition),
                    'annual_leavedays'      => 	trim($annual_leavedays),
                    'weekend_nationaldays' 	=> 	trim($weekend_nationaldays),
                    'contract_date' 		=> 	sapp_Global::change_date(trim($contract_date),'database'),
                    'daily_allowance' 		=> 	trim($daily_allowance),
                    'grossbase_salary' 	    => 	trim($grossbase_salary),
                    'deductadd_salary' 	    => 	trim($deductadd_salary),
                    'gross_salary' 	        => 	trim($gross_salary),
                    'work_days' 	        => 	trim($work_days),
                    'monthlygross_salary' 	=> 	trim($monthlygross_salary),
                    'contribution_salary' 	=> 	trim($contribution_salary),
                    'employeesocial_insurance' 	=> 	trim($employeesocial_insurance),
                    'employeehealth_insurance' 	=> 	trim($employeehealth_insurance),
                    'employeetotal_insurance' 	=> 	trim($employeetotal_insurance),
                    'employersocial_insurance' 	=> 	trim($employersocial_insurance),
                    'employerhealth_insurance' 	=> 	trim($employerhealth_insurance),
                    'employertotal_insurance' 	=> 	trim($employertotal_insurance),
                    'whtaxpaided_salary' 	    => 	trim($whtaxpaided_salary),
                    'progresive_whtax' 	        => 	trim($progresive_whtax),
                    'bankpaid_salary' 	        => 	trim($bankpaid_salary),
                    'total_bankpaid_salary' 	=> 	trim($total_bankpaid_salary),
                    'addtax_salary'             =>  trim($addtax_salary),
                    'whtax_salary' 	            => 	trim($whtax_salary),
                    'createdby' 		        => 	trim($loginUserId),
                    'modifiedby' 		        => 	trim($loginUserId),
                    'createdon' 		        => 	gmdate("Y-m-d H:i:s"),
                    'modifiedon' 		        => 	gmdate("Y-m-d H:i:s")

                );
					
//                if($edit_flag!='' && $edit_flag == 'yes')
//                {
//                    $data = array(
//                          'modifiedby' => trim($loginUserId),
//                          'modifiedon' => gmdate("Y-m-d H:i:s"),
//                    );
//                }

				$where = "";
				if($id != '')
				{
					unset($data['createdby']);
					unset($data['createdon']);
					$where = "id = ".$id;

				}



				$result = $monthlypayroll_model->SaveorUpdateMonthlypayrollData($data, $where);


                if($id != '')
                $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Monthlypayroll updated successfully."));
                else
                $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Monthlypayroll added successfully."));
                $trDb->commit();
                $this->_redirect('/monthlypayroll');

			}
			catch (Exception $e)
			{
				$trDb->rollBack();

				$this->_helper->getHelper("FlashMessenger")->addMessage(array("error"=>"Something went wrong, please try again later."));
				$this->_redirect('/monthlypayroll');
			}
		}
		else
		{
			$messages = $monthlypayrollform->getMessages();
			
			foreach ($messages as $key => $val)
			{
				foreach($val as $key2 => $val2)
				{
					$msgarray[$key] = $val2;
					break;
				}
			}
			return $msgarray;
		}
	}

    public function payslipAction()
    {

        $term = $this->_getParam('term',null);

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $month_string = array(
            '01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'
        );


        // Data filtering option
        $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'ASC';
        $by = ($this->_getParam('by')!='')? $this->_getParam('by'):'r.id';
        $searchQuery = "";
        $userid = null;
        $usergroup = null;


        $this->_helper->layout->disableLayout();
        $sort_name = $this->_getParam('sort_name',null);
        $sort_type = $this->_getParam('sort_type',null);


        $payroll_model = new Default_Model_Monthlypayroll();


        $req_type = 4; // department: ALL 4
        $payroll_data_req = $payroll_model->getMonthlypayrollexportData($sort, $by, $searchQuery, $userid, $usergroup, $req_type, $term,$searchQuery);

        $date_arr = explode('-',$term);
        $period = $month_string[$date_arr[1]]." ".$date_arr['0'];

        $date = sapp_Global::change_date($term,'');



        /*
         * Payslip publish
         */

        $org_model = new Default_Model_Organisationinfo();
        $org_logo = $org_model->getOrgLogo();
        $org_data = $org_model->getOrganisationInfo();
        $org_name = $org_data[0]['organisationname'];




        require_once('FPDF/fpdf.php');
        defined('FPDF_FONTPATH') || define('FPDF_FONTPATH', 'FPDF/font');



        if($payroll_data_req){
            foreach($payroll_data_req as $data){

                $emp_id = $data['employee_id'];
                $empbankdata = $payroll_model->getEmpBankData($emp_id);

                $bankname = "";
                $accountnumber = "";
                if($empbankdata['bankname']){
                    $bankname = $empbankdata['bankname'];
                }
                if($empbankdata['accountnumber']){
                    $accountnumber = $empbankdata['accountnumber'];
                }
                $user_id = $empbankdata['user_id'];


                $pdf=new FPDF('P','mm');

                $pdf->AliasNbPages();
                $pdf->SetAutoPageBreak(false);
                $pdf->AddPage();

                $pdf->SetLeftMargin(8);
                $pdf->SetrightMargin(8);

                // logo size is 280X200
                $logo_path = BASE_PATH.'/uploads/organisation/'.$org_logo['org_image'];
                $pdf->Image($logo_path,10,10,21,15);

                $pdf->SetFont('Arial','B',13);
                $pdf->SetY(10);
                $pdf->SetX(32);
                $pdf->Cell(0,10,$org_name,0,0,'L');

                $pdf->SetFont('Arial','',10);
                $pdf->SetX(143);
                $pdf->Cell(0,8,'Page',0,0,'L');

                $pdf->SetX(160);
                $pdf->Cell(0,8,'1',0,0,'L');

                $pdf->SetX(175);
                $pdf->Cell(0,8,'to',0,0,'L');

                $pdf->SetX(190);
                $pdf->Cell(0,8,'1',0,0,'L');

                $pdf->SetFont('Arial','B',13);
                $pdf->SetY(18);
                $pdf->SetX(32);
                $pdf->Cell(0,8,'L71513046P',0,0,'L');

                $pdf->SetY(28);
                $pdf->Cell(0,8,'Payslip Form',0,0,'C');

                $pdf->SetFont('Arial','',10);
                $pdf->SetY(35);
                $pdf->SetX(8);
                $pdf->Cell(0,8,'Period',0,0,'L');

                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(30);
                $pdf->Cell(0,8,$period,0,0,'L');

                $pdf->SetFont('Arial','',10);
                $pdf->SetX(145);
                $pdf->Cell(0,8,'Date',0,0,'L');

                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(0,8,$date,0,0,'R');

                $pdf->SetFont('Arial','',10);
                $pdf->SetY(43);
                $pdf->SetX(8);
                $pdf->Cell(0,8,'Employee',0,0,'L');

                $pdf->SetFont('Arial','B',13);
                $pdf->SetX(30);
                $pdf->Cell(0,8,$data['employee_name'],0,0,'L');

                $pdf->SetFont('Arial','',10);
                $pdf->SetX(133);
                $pdf->Cell(0,8,'Gross Base Salary',0,0,'L');

                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(0,8,number_format($data['grossbase_salary']).' ALL',0,0,'R');

                // Dash - - - - - - - - - - -
                $pdf->SetLineWidth(0.5);
                $pdf->SetDrawColor(128,128,128);
                for($i=8;$i<=202;$i+=4){
                    $pdf->Line($i,55,$i+2,55);
                }

                $pdf->SetFont('Arial','',10);
                $pdf->SetY(55);
                $pdf->SetX(8);
                $pdf->Cell(0,8,'Job Title',0,0,'L');

//                if($data['shiftpayroll'] == 1){
//                    $pdf->SetFont('Arial','B',10);
//                    $pdf->SetX(30);
//                    $pdf->Cell(0,8,'Shift',0,0,'L');
//                }


                $pdf->SetFont('Arial','',10);
                $pdf->SetX(145);
                $pdf->Cell(0,8,'Bank',0,0,'L');

                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(0,8,$bankname,0,0,'R');

                $pdf->SetFont('Arial','',10);
                $pdf->SetY(62);
                $pdf->SetX(140);
                $pdf->Cell(0,8,'Account',0,0,'L');

                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(0,8,$accountnumber,0,0,'R');



                // table start $x1=8 $y1=70 step +8  x2=202
                $x1=8; $x2=150; $x3=202; $y=70;$step=8;$w=194;

                //$pdf->SetDrawColor();
                //$pdf->SetTextColor();

                /*
                 * Payment
                 */

                $pdf->SetFillColor(220,220,220);
                $pdf->Rect($x1,$y,$w,$step,'F');

                $pdf->SetFont('Arial','',10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'1      Payment',0,0,'L');

                $y+=$step;

                $pdf->SetFillColor(50,50,50);
                $pdf->Rect($x1,$y,$w,$step,'F');

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(240,240,240);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Descriptiion',0,0,'L');

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'Quantity',0,0,'R');

                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Value',0,0,'R');

                $y+=$step;


                $payment_total = 0;
                $grossbase_salary = $data['grossbase_salary'];


                // Overtime hours
                if($data['overtime_hours'] != 0){
                    $overtimesalary = round($data['overtime_hours']*($grossbase_salary/21/8)*0.25);
                    $payment_total += $overtimesalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Overtime',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,$data['overtime_hours'],0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($overtimesalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);

                }

                // Addition Role Position

                if($data['addition_rollposition'] != 0){
                    $addrollpositionsalary = round($data['addition_rollposition']);
                    $payment_total += $addrollpositionsalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Additon Roll',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,'',0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($addrollpositionsalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);

                }

                // Weekend National Days

                if($data['weekend_nationaldays'] != 0){
                    $weekend_nationalsalary = round($data['weekend_nationaldays']*4500);
                    $payment_total += $weekend_nationalsalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Weekend/National Day',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,number_format($data['weekend_nationaldays']),0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($weekend_nationalsalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);

                }


                //Daily Allowance

                if($data['daily_allowance']){
                    $daily_allowancesalary = $data['daily_allowance']*400;
                    $payment_total += $daily_allowancesalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Daily Allowance',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,$data['daily_allowance'],0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($daily_allowancesalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);
                }


                // Sick Leave Days

                if($data['sick_leavedays'] != 0){

                    $sickleavesalary =  (-1)*round(($grossbase_salary/21)*$data['sick_leavedays']*0.2);
                    $payment_total += $sickleavesalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Sick/Leave Days',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,$data['sick_leavedays'],0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($sickleavesalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);
                }

                // Standby Hours

                if($data['standby_hours'] != 0){

                    $standbysalary = (-1)*round($data['standby_hours']*120);
                    $payment_total += $standbysalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Standby Hours',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,$data['standby_hours'],0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($standbysalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);
                }


                // Addition Deduct Salary

                if($data['deductadd_salary'] != 0){

                    $deductaddsalary = $data['deductadd_salary'];
                    $payment_total += $deductaddsalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Added Salary',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,'',0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($deductaddsalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);
                }


                // Work Days

                if($data['work_days'] != 21){

                    $restworkdays = $data['work_days'] - 21;
                    $restworkdaysalary = ($grossbase_salary + $payment_total)/21*$restworkdays;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Workdays',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,'21'.'('.$restworkdays.')',0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($restworkdaysalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);
                }



                // Total
                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'Total',0,0,'R');

                $pdf->SetFont('Arial','B',10);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format(($grossbase_salary + $payment_total)/21*$data['work_days']),0,0,'R');
                $y+=$step;


                /*
                 * Insurance
                 */

                $insurance_total = 0;


                $pdf->SetFillColor(220,220,220);
                $pdf->Rect($x1,$y,$w,$step,'F');

                $pdf->SetFont('Arial','',10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'2      Insurance and TAP',0,0,'L');

                $y+=$step;

                $pdf->SetFillColor(50,50,50);
                $pdf->Rect($x1,$y,$w,$step,'F');

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(240,240,240);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Descriptiion',0,0,'L');

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'Quantity',0,0,'R');

                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Value',0,0,'R');

                $y+=$step;


                //Tap total

                $insurance_total += $data['progresive_whtax'];

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'TAP total',0,0,'L');

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'',0,0,'R');

                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format(-1*$data['progresive_whtax']),0,0,'R');
                $y+=$step;

                $pdf->Line($x1,$y,$x3,$y);


                // Social Insurance Employee

                $insurance_total += $data['employeesocial_insurance'];

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Social Insurance Employee',0,0,'L');

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'',0,0,'R');

                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format(-1*$data['employeesocial_insurance']),0,0,'R');
                $y+=$step;

                $pdf->Line($x1,$y,$x3,$y);


                // Health Insurnace Employee

                $insurance_total += $data['employeehealth_insurance'];

                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Health Insurance Employee',0,0,'L');

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'',0,0,'R');

                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format(-1*$data['employeehealth_insurance']),0,0,'R');
                $y+=$step;

                $pdf->Line($x1,$y,$x3,$y);


                // Social Insurance Employer

//                $insurance_total += $data['employersocial_insurance'];
//
//                $pdf->SetFont('Arial','',10);
//                $pdf->SetTextColor(10,10,10);
//                $pdf->SetY($y);
//                $pdf->SetX($x1);
//                $pdf->Cell(0,8,'Social Insurance Employer',0,0,'L');
//
//                $pdf->SetX($x1);
//                $pdf->Cell($x2,8,'',0,0,'R');
//
//                $pdf->SetX($x1);
//                $pdf->Cell(0,8,number_format(-1*$data['employersocial_insurance']),0,0,'R');
//                $y+=$step;
//
//                $pdf->Line($x1,$y,$x3,$y);


                // Health Insurance Employer

//                $insurance_total += $data['employerhealth_insurance'];
//
//                $pdf->SetFont('Arial','',10);
//                $pdf->SetTextColor(10,10,10);
//                $pdf->SetY($y);
//                $pdf->SetX($x1);
//                $pdf->Cell(0,8,'Health Insurance Employer',0,0,'L');
//
//                $pdf->SetX($x1);
//                $pdf->Cell($x2,8,'',0,0,'R');
//
//                $pdf->SetX($x1);
//                $pdf->Cell(0,8,number_format(-1*$data['employerhealth_insurance']),0,0,'R');
//                $y+=$step;
//
//                $pdf->Line($x1,$y,$x3,$y);


                // Add Tax Salary

                if($data['addtax_salary']){
                    $addtaxsalary = $data['addtax_salary'];
                    $insurance_total -= $addtaxsalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Added Tax Salary',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,'',0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format($addtaxsalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);

                }


                // Deduct Tax Salary

                if($data['whtax_salary']){
                    $whtaxsalary = $data['whtax_salary'];
                    $insurance_total += $whtaxsalary;

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetTextColor(10,10,10);
                    $pdf->SetY($y);
                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,'Withhold Tax Salary',0,0,'L');

                    $pdf->SetX($x1);
                    $pdf->Cell($x2,8,'',0,0,'R');

                    $pdf->SetX($x1);
                    $pdf->Cell(0,8,number_format(-1*$whtaxsalary),0,0,'R');
                    $y+=$step;

                    $pdf->Line($x1,$y,$x3,$y);

                }


                // Total
                $pdf->SetFont('Arial','',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);

                $pdf->SetX($x1);
                $pdf->Cell($x2,8,'Total',0,0,'R');

                $pdf->SetFont('Arial','B',10);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format(-1*$insurance_total),0,0,'R');
                $y+=$step;


                /*
                 * Salary net
                 */
                $pdf->SetFillColor(220,220,220);
                $pdf->Rect($x1,$y,$x2,$step,'F');

                $pdf->SetFillColor(50,50,50);
                $pdf->Rect($x2+8,$y,$x3-$x2-8,$step,'F');

                $pdf->SetFont('Arial','B',10);
                $pdf->SetTextColor(10,10,10);
                $pdf->SetY($y);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,'Salary net',0,0,'L');

                $pdf->SetTextColor(240,240,240);
                $pdf->SetX($x1);
                $pdf->Cell(0,8,number_format($data['total_bankpaid_salary']),0,0,'R');



                $file = BASE_PATH.'/downloads/payslips/'.strtolower($data['employee_id']).'_'.$date_arr[0].'_'.$date_arr[1].'.pdf';
                if(File_exists($file)){
                    unlink($file);
                }

                $pdf->Output($file,'F');

                /*
                 *  payslip database insert
                 */

                $payslip_data = array(
                    'user_id' => $user_id,
                    'period' => $period,
                    'date' => $term,
                    'bankname' => $bankname,
                    'accountnumber' => $accountnumber,
                    'salary_net' => $data['total_bankpaid_salary'],
                    'filename' => strtolower($data['employee_id']).'_'.$date_arr[0].'_'.$date_arr[1].'.pdf'
                );
                $payslip_model = new Default_Model_Emppayslips;
                $payslip_result = $payslip_model->addUpdatePayslip($payslip_data);


//                $payslip_download = sapp_Global::downloadFile($file);

            }
        }

//        $file_name = $this->_getParam('file_name', NULL);
//        if(!empty($file_name)){
//            $file = BASE_PATH.'/downloads/reports/'.$this->_getParam('file_name');
//        }


        $this->_redirect('/monthlypayroll');


    }

    public function getexcelexportAction()
    {

        $term = $this->_getParam('term',null);


        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $month_string = array(
            '01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'
        );



        // Data filtering option
        $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'ASC';
        $by = ($this->_getParam('by')!='')? $this->_getParam('by'):'r.id';
        $searchQuery = "";
        $userid = null;
        $usergroup = null;


        $this->_helper->layout->disableLayout();
        $sort_name = $this->_getParam('sort_name',null);
        $sort_type = $this->_getParam('sort_type',null);

        $cols_param_arr1 = array('','','','','','','','','','','','','','',
            'deduction/adds from gross salary',
            'Gross Salary',
            'Date of Contract',
            'Work Days',
            'Monthly Gross Salary for days worked',
            'The salary on which the contributions are calculated',
            'Employee social insurance',
            'Employee healthy insurance',
            'Total of employee insurance',
            'Employer social insurance',
            'Employer health insurance',
            'Total of employer insurance',
            'The salary on which the withholding taxes are calculated',
            'Progresive withholding tax',
            'Net All salary to b paid through bank account',
            'Adds to New Salary',
            'Withhold from Net Salary',
            'Total Net Salary',
            '','',
        );
        $cols_param_arr2 = array(
            'index'             =>'No',
            'employee_id'       =>'AMD Employee unique number',
            'starting_date'     =>'Starting date at AMD',
            'employee_name'     =>'First_Name Last_ name',
            'department'        =>'DEP',
            'comments'          =>'Coments',
            'grossbase_salary'  =>'Gross Base Salary',
            'sick_leavedays'    =>'sick leave in days',
            'standby_hours'     =>'standby in hours',
            'overtime_hours'        =>'Overtime in hours',
            'addition_roleposition'     =>'Additional Role/Position',
            'weekend_nationaldays'      =>'weekend/national holidays',
            'annual_leavedays'          =>'Annual leave days',
            'daily_allowance'           =>'Daily allowance',
            'deductadd_salary'          =>'Additional/deductions from gross salary',
            'gross_salary'              =>'Paga bruto per muajin',
            'contact_date'              =>'Date hyrje',
            'work_days'                 =>'Dite pune',
            'monthlygross_salary'       =>'Paga Bruto per dite pune,Paga mbi te cilen llog kontributet',
            'contribution_salary'       =>'Paga mbi te cilen llog kontributet',
            'employeesocial_insurance'  =>'Sigurimet punemarresi 9,50%',
            'employeehealth_insurance'  =>'Sigurimet shendetesore 1,70%',
            'employeetotal_insurance'   =>'Totali I punemarresit 11,2%',
            'employersocial_insurance'  =>'Sigurimet e punedhenesit 15%',
            'employerhealth_insurance'  =>'Sigurimet shendetesore 1,70%',
            'employertotal_insurance'   =>'Totali I punedhenesit 16,70%',
            'whtaxpaided_salary'        =>'Paga para TAP',
            'progressive _whtax'        =>'TAP Progresiv',
            'bankpaid_salary'           =>'Paga me Banke ne leke',
            'addtax_salary'             =>'Shton pagën neto',
            'whtax_salary'              =>'Ndalesa nga paga neto',
            'total_bankpaid_salary'     =>'Paga me Banke ne leke',
            'check'                     =>'Check',
            'remark'                    =>'Remarks'
        );



        //$cols_param_arr = array('group_name' => 'Group','rolename' => 'Role','user_cnt' => 'Users count');
        $payroll_model = new Default_Model_Monthlypayroll();


        $req_type = 1; // department: OP
        $payroll_data_req1 = $payroll_model->getMonthlypayrollexportData($sort, $by, $searchQuery, $userid, $usergroup, $req_type, $term,$searchQuery);

        $req_type = 2; // department: NON-OP
        $payroll_data_req2 = $payroll_model->getMonthlypayrollexportData($sort, $by, $searchQuery, $userid, $usergroup, $req_type, $term,$searchQuery);

        $req_type = 3; // department: ADM
        $payroll_data_req3 = $payroll_model->getMonthlypayrollexportData($sort, $by, $searchQuery, $userid, $usergroup, $req_type, $term,$searchQuery);



        // Excel Export

        require_once 'Classes/PHPExcel.php';
        require_once 'Classes/PHPExcel/IOFactory.php';
        $objPHPExcel = new PHPExcel();


        $letters = range('A','Z');

        $push_letters = range('A','Z');
        foreach($push_letters as $letter) {
            $letters[] = 'A'.$letter;
        }

        $term_arr = explode('-',$term);


        $filename = "monthlypayroll (".$term_arr[0].'-'.$term_arr[1].").xlsx";
        $count =0;
        $unique_id = 0;
        $op_count = 0;
        $nonop_count = 0;
        $adm_count = 0;
        $cell_name="";


        $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$count])->setWidth(60);

        foreach ($cols_param_arr1 as $names)
        {
            $i = 1;
            $cell_name = $letters[$count].$i;
            $names = html_entity_decode($names,ENT_QUOTES,'UTF-8');

            $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $names);
            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray( array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '82CAFF')
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$count])->setAutoSize(true);

            $i++;
            $count++;
        }

        $count =0;
        foreach ($cols_param_arr2 as $names)
        {
            $i = 2;
            $cell_name = $letters[$count].$i;
            $names = html_entity_decode($names,ENT_QUOTES,'UTF-8');

            $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $names);
            // Make bold cells
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray( array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '82CAFF')
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$count])->setAutoSize(true);

            $i++;
            $count++;
        }


        // Display field/column values in Excel.

        $i = 3;
        foreach($payroll_data_req1 as $data)
        {
            $count1 =0;
            $unique_id++;
            $op_count++;
            foreach ($cols_param_arr2 as $column_key => $column_name)
            {
                // display field/column values
                $cell_name = $letters[$count1].$i;
                if ($column_key == 'index'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } elseif ($column_key == 'check'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } else {
                    $value = isset($data[$column_key])?(trim($data[$column_key]) == ''?" ":$data[$column_key]):" ";
                    $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                }

                $count1++;
            }
            $i++;
        }


        $count1 = 0;
        foreach ($cols_param_arr2 as $column_key => $column_name)
        {
            // display field/column values
            $cell_name = $letters[$count1].$i;
            if($column_key == 'index'){
                $cell_name = "A".$i;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "Total");
            } else if($column_key != 'employee_id' && $column_key != 'starting_date' && $column_key != 'employee_name' && $column_key != 'department'&& $column_key != 'comments' && $column_key != 'contract_date' && $column_key != 'check' && $column_key != 'remark' ){

                $value = "=SUM(".$letters[$count1].($i-$unique_id).':'.$letters[$count1].($i-1).")";

                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
            }

            $count1++;
        }

        $i++;
        $cell_name = "A".$i;
        $merge_cell_arange = $cell_name.':AC'.$i;
        $objPHPExcel->getActiveSheet()->mergeCells($merge_cell_arange);
        $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, " ");
        $i++;

        $cell_name = "A".$i;
        $merge_cell_arange = $cell_name.':AC'.$i;
        $objPHPExcel->getActiveSheet()->mergeCells($merge_cell_arange);
        $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "NON-OP  ".$month_string[$term_arr[1]]." Payroll");
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray( array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '82CAFF')
                )
            )
        );
        $i++;

        foreach($payroll_data_req2 as $data)
        {
            $count1 =0;
            $unique_id++;
            $nonop_count++;
            foreach ($cols_param_arr2 as $column_key => $column_name)
            {
                // display field/column values
                $cell_name = $letters[$count1].$i;
                if($column_key == 'index'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } elseif ($column_key == 'check'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } else {
                    $value = isset($data[$column_key])?(trim($data[$column_key]) == ''?" ":$data[$column_key]):" ";
                    $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                }

                $count1++;
            }
            $i++;
        }


        $count1 = 0;
        foreach ($cols_param_arr2 as $column_key => $column_name)
        {
            // display field/column values
            $cell_name = $letters[$count1].$i;
            if($column_key == 'index'){
                $cell_name = "A".$i;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "Total");
            } elseif ($column_key == 'check'){
                $value = $unique_id;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
            } else if($column_key != 'employee_id' && $column_key != 'starting_date' && $column_key != 'employee_name' && $column_key != 'department'&& $column_key != 'comments' && $column_key != 'contract_date' && $column_key != 'check' && $column_key != 'remark' ){

                $value = "=SUM(".$letters[$count1].($i-$nonop_count).':'.$letters[$count1].($i-1).")";

                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
            }

            $count1++;
        }

        $i++;
        $cell_name = "A".$i;
        $merge_cell_arange = $cell_name.':AC'.$i;
        $objPHPExcel->getActiveSheet()->mergeCells($merge_cell_arange);
        $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, " ");
        $i++;

        $cell_name = "A".$i;
        $merge_cell_arange = $cell_name.':AC'.$i;
        $objPHPExcel->getActiveSheet()->mergeCells($merge_cell_arange);
        $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "ADM  ".$month_string[$term_arr[1]]." Payroll");
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray( array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '82CAFF')
                )
            )
        );
        $i++;

        foreach($payroll_data_req3 as $data)
        {
            $count1 =0;
            $unique_id++;
            $adm_count++;
            foreach ($cols_param_arr2 as $column_key => $column_name)
            {
                // display field/column values
                $cell_name = $letters[$count1].$i;
                if($column_key == 'index'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } elseif ($column_key == 'check'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                } else {
                    $value = isset($data[$column_key])?(trim($data[$column_key]) == ''?" ":$data[$column_key]):" ";
                    $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                }

                $count1++;
            }
            $i++;
        }


        $count1 = 0;
        foreach ($cols_param_arr2 as $column_key => $column_name)
        {
            // display field/column values
            $cell_name = $letters[$count1].$i;
            if($column_key == 'index'){
                $cell_name = "A".$i;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "Total");
            } else if($column_key != 'employee_id' && $column_key != 'starting_date' && $column_key != 'employee_name' && $column_key != 'department'&& $column_key != 'comments' && $column_key != 'contract_date' && $column_key != 'check' && $column_key != 'remark' ){

                $value = "=SUM(".$letters[$count1].($i-$adm_count).':'.$letters[$count1].($i-1).")";

                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
            }

            $count1++;
        }


        $i++;
        $cell_name = "A".$i;
        $merge_cell_arange = $cell_name.':AC'.$i;
        $objPHPExcel->getActiveSheet()->mergeCells($merge_cell_arange);
        $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, " ");
        $i++;

        $count1 = 0;
        foreach ($cols_param_arr2 as $column_key => $column_name)
        {
            // display field/column values
            $cell_name = $letters[$count1].$i;
            if($column_key == 'index'){
                $cell_name = "A".$i;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "General Total");

            } else if($column_key != 'employee_id' && $column_key != 'starting_date' && $column_key != 'employee_name' && $column_key != 'department'&& $column_key != 'comments' && $column_key != 'contract_date' && $column_key != 'check' && $column_key != 'remark' ){

                $value = "=SUM(".$letters[$count1].'3:'.$letters[$count1].($i-1).")/2";

                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);

            }
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray( array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '82CAFF')
                    )
                )
            );
            $count1++;
        }



        sapp_Global::clean_output_buffer();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        sapp_Global::clean_output_buffer();

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        $this->_redirect('');


    }

	public function viewhrAction()
	{
		$this->view->message = 'This is view resource requisition action page';
	}


	/**
	 * This function is used for ajax call to get departments based on
	 * business unit id.
	 * @parameters
	 * @param {Integer} bunitid  =  id of business unit.
	 *
	 * @return Array of departments in json format.
	 */
	public function getdepartmentsAction()
	{
		$bunit_id = $this->_getParam('bunitid',null);
		$dept_model = new Default_Model_Departments();
			
		$options_data = "";
		$options_data .= sapp_Global::selectOptionBuilder('', 'Select Department');
		if($bunit_id != '')
		{
			$dept_data = $dept_model->getAllDeptsForUnit($bunit_id);
			foreach($dept_data as $dept)
			{
				$options_data .= sapp_Global::selectOptionBuilder($dept['id'], $dept['deptname']);
			}
		}

		$this->_helper->json(array('options'=>$options_data));
	}

	/**
	 * This function is used for ajax call to get positions based on
	 * business unit id and department id.
	 * @parameters
	 * @param {Integer} bunitid  =  id of business unit.
	 *
	 * @return Array of departments in json format.
	 */
	public function getpositionsAction()
	{
		$bunit_id = $this->_getParam('bunitid',null);
		$dept_id = $this->_getParam('dept_id',null);
		$job_id = $this->_getParam('job_id',null);
		$position_model = new Default_Model_Positions();
			
		$options_data = "";
		$options_data .= sapp_Global::selectOptionBuilder('', 'Select Position');
		if($job_id != '')
		{
			$dept_data = $position_model->getPositionOptions($bunit_id,$dept_id,$job_id);
			foreach($dept_data as $dept)
			{
				$options_data .= sapp_Global::selectOptionBuilder($dept['id'], $dept['positionname']);
			}
		}
		$this->_helper->json(array('options'=>$options_data));
	}

	public function viewpopupAction()
	{
		$id = $this->getRequest()->getParam('id');
		$call = $this->getRequest()->getParam('call');
		if($call == 'ajaxcall' ){
			Zend_Layout::getMvcInstance()->setLayoutPath(APPLICATION_PATH."/layouts/scripts/popup/");
		}
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserRole = $auth->getStorage()->read()->emprole;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		$data = array();$jobtitle = '';
		$requi_model = new Default_Model_Requisition();
		$jobtitleModel = new Default_Model_Jobtitles();
		$user_model = new Default_Model_Usermanagement();
		try{
			
                        $data = $requi_model->getReqDataForView($id);                                        
		}catch(Exception $e){
			$this->view->ermsg = 'nodata';
		}
		if(!empty($data))
		{
                    $data = $data[0];
                    $data['jobtitlename'] = '';
                    $data['businessunit_name'] = $data['businessunit_name'];									
                    $data['dept_name'] = $data['department_name'];									
                    $data['titlename'] = $data['jobtitle_name'];									
                    $data['posname'] = $data['position_name'];									
                    $data['empttype'] = $data['emp_type_name'];						                       
                    $data['mngrname'] = $data['reporting_manager_name'];						
                    $data['raisedby'] = $data['createdby_name'];			                        
                    $data['app1_name'] = $data['approver1_name'];
                        
                    if($data['approver2'] != '')
                    {                        
                        $data['app2_name'] = $data['approver2_name'];
                    }
                    else 
                    {
                        $data['app2_name'] = 'No Approver';
                    }
                        
                    if($data['approver3'] != '')
                    {                        
                        $data['app3_name'] = $data['approver3_name'];
                    }
                    else 
                    {
                        $data['app3_name'] = 'No Approver';
                    }                        
			
                   /*  foreach($data as $key=>$val)
                    {
                        $data[$key] = htmlentities($val, ENT_QUOTES, "UTF-8");
                    }	 */            
                    $data['onboard_date'] = sapp_Global::change_date($data['onboard_date'], 'view');
			$this->view->data = $data;
			$this->view->ermsg = '';
		}else {
			$this->view->ermsg = 'nodata';
		}
			
	}

        /**
     * This action is used for viewing data.
     * @parameters
     * @param id  =  id of requisition
     *
     * @return Zend_Form.
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $requi_model = new Default_Model_Requisition();
		$clientsModel = new Default_Model_Clients();
		$usersModel = new Default_Model_Users();
		$auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
            $login_group_id = $auth->getStorage()->read()->group_id;
            $login_role_id = $auth->getStorage()->read()->emprole;
        }
		$dataforapprovereject = $requi_model->getRequisitionForEdit($id,$loginUserId);
        $aflag =$dataforapprovereject['aflag'];
	    $aorder = $dataforapprovereject['aorder'];
        $ju_name = array();
        try
        {                        
            
            if(is_numeric($id) && $id >0)
            {
                $id = abs($id);
                $data = $requi_model->getReqDataForView($id);
                $app1_name = $app2_name = $app3_name='';
                if(count($data)>0  && $data[0]['req_status'] == 'Initiated')
                {
                    $data = $data[0];
                    $auth = Zend_Auth::getInstance();
                    if($auth->hasIdentity())
                    {
                        $loginUserId = $auth->getStorage()->read()->id;
                        $loginuserRole = $auth->getStorage()->read()->emprole;
                        $loginuserGroup = $auth->getStorage()->read()->group_id;
                    }
                    												 
                    $data['jobtitlename'] = '';			
                    $data['businessunit_name'] = $data['businessunit_name'];									
                    $data['dept_name'] = $data['department_name'];									
                    $data['titlename'] = $data['jobtitle_name'];									
                    $data['posname'] = $data['position_name'];									
                    $data['empttype'] = $data['emp_type_name'];						                       
                    $data['mngrname'] = $data['reporting_manager_name'];						
                    $data['raisedby'] = $data['createdby_name'];			                        
                    $data['app1_name'] = $data['approver1_name'];
                        
                    if($data['approver2'] != '')
                    {                        
                        $data['app2_name'] = $data['approver2_name'];
                    }
                    else 
                    {
                        $data['app2_name'] = 'No Approver';
                    }
                        
                    if($data['approver3'] != '')
                    {                        
                        $data['app3_name'] = $data['approver3_name'];
                    }
                    else 
                    {
                        $data['app3_name'] = 'No Approver';
                    }    
					if($data['client_id'] != '')
					{
						$clien_data = $clientsModel->getClientDetailsById($data['client_id']);
					    $data['client_id']=$clien_data[0]['client_name'];
					}  
					if($data['recruiters'] != '')
					{
						$name = '';
						$recData=$usersModel->getUserDetailsforView($data['recruiters']);
						if(count($recData)>0)
						{
							foreach($recData as $dataname){
								$name = $name.','.$dataname['name'];
							}

						}
						$data['recruiters']=ltrim($name,',');
					}                    
			
                    /*foreach($data as $key=>$val)
                    {
                        $data[$key] = htmlentities($val, ENT_QUOTES, "UTF-8");
                    }	*/

                    
                    if($data['req_priority'] == 1) {
                    	$data['req_priority']='High';
                    }else if($data['req_priority'] == 2) {
                    	$data['req_priority']='Medium';
                    }else {
                    $data['req_priority']='Low';
                    }
                    $data['onboard_date'] = sapp_Global::change_date($data['onboard_date'], 'view');
                  //to show requisition history in view
                    $reqh_model = new Default_Model_Requisitionhistory();
	                $requisition_history = $reqh_model->getRequisitionHistory($id);
					
                    $previ_data = sapp_Global::_checkprivileges(REQUISITION,$login_group_id,$login_role_id,'edit');

                    $this->view->previ_data = $previ_data;
                    $this->view->data = $data;
                    $this->view->requisition_history = $requisition_history;
                    $this->view->id = $id;
                    $this->view->controllername = "requisition";
                    $this->view->ermsg = '';
					$this->view->aflag = $aflag;
					$this->view->aorder = $aorder;
                }
                else
                {
                    $this->view->nodata = 'nodata';
                }
            }
            else
            {
                $this->view->nodata = 'nodata';
            }
        }
        catch(Exception $e)
        {               
            $this->view->nodata = 'nodata';
        }
    }
    

	/**
	 * This action is used to delete requisition.
	 * @parameters
	 * @param objid    =   id of requisition.
	 *
	 * @return  {String} =   success/failure message
	 */
	public function deleteAction()
	{
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$loginUserId = $auth->getStorage()->read()->id;
		}
		$id = $this->_request->getParam('objid');
		$deleteflag= $this->_request->getParam('deleteflag');
		$messages['message'] = '';
		if($id)
		{
			$monlthlypayroll_model = new Default_Model_Monthlypayroll();

            $result = $monlthlypayroll_model->DeletePayrollData($id);

			if($result == 'success')
			{
				$messages['message'] = 'Monthlypayroll deleted successfully.';
				$messages['msgtype'] = 'success';
				$messages['flagtype']='process';
			}
			else{
				$messages['message'] = 'Monthlypayroll cannot be deleted.';
				$messages['msgtype'] = 'error';
			}
		}
		else
		{
			$messages['message'] = 'Monlthlypayroll cannot be deleted.';
			$messages['msgtype'] = 'error';
		}
		if($deleteflag==1)
		{
			if(	$messages['msgtype'] == 'error')
			{
				$this->_helper->getHelper("FlashMessenger")->addMessage(array("error"=>$messages['message'],"msgtype"=>$messages['msgtype'] ,'deleteflag'=>$deleteflag));
			}
			if(	$messages['msgtype'] == 'success')
			{
				$this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>$messages['message'],"msgtype"=>$messages['msgtype'],'deleteflag'=>$deleteflag));
			}
			
		}
		$this->_helper->json($messages);
	}
	/**
	 * This function gives all data of a particular requisition id.
	 * @parameters
	 * @param {Integer} req_id = id of requisition.
	 *
	 * @return {Json} Json array of all values.
	 */
	public function getapprreqdataAction()
	{
		$req_data = array();
		$req_id = $this->_getParam('req_id',null);
		$requ_model = new Default_Model_Requisition();
		if($req_id != '')
		$req_data = $requ_model->getAppReqById($req_id);
		$this->_helper->json($req_data);
	}
	public function chkreqforcloseAction()
	{
		$req_id = $this->_getParam('req_id',null);
		$requ_model = new Default_Model_Requisition();
		$req_data = $requ_model->getRequisitionDataById($req_id);
		if($req_data['req_no_positions'] == $req_data['filled_positions'])
		$result = 'yes';
		else
		$result = 'no';
		$this->_helper->_json(array('result'=>$result));
	}

	/**
	 * This function is used for ajax call to get reporting managers based on  department
	 * @parameters	department id.
	 * @return Array of managers in json format.
	 */
	public function getempreportingmanagersAction()
	{
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('getempreportingmanagers', 'html')->initContext();
		$form = new Default_Form_Requisition();

		$dept_id = $this->_getParam('id',null);

		$requi_model = new Default_Model_Requisition();
		$auth = Zend_Auth::getInstance();

		if($auth->hasIdentity())
		{
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		if($dept_id != '')
		{
			if($loginuserGroup == '')
			$reportingManagerData = $requi_model->getReportingmanagers('', $loginUserId, '', $dept_id, 'requisition');
			else
			$reportingManagerData = $requi_model->getReportingmanagers('', '', '', $dept_id, 'requisition');//for hr,management
			if(empty($reportingManagerData))
			{
				$flag = 'true';
			}
			else
			{
				$flag = 'false';
			}
		}
		$this->view->RMdata=$reportingManagerData;
		$this->view->reqform=$form;
		$this->view->flag=$flag;

	}
	public function getemailcountAction()
	{
		$bunitid = $this->_getParam('bunitid',null);
		$count = '';
		if(defined("REQ_HR_".$bunitid) && defined("REQ_MGMT_".$bunitid))
		{
			$count = '1';
		}

		$this->_helper->_json(array('count'=>$count));
	}
	public function getapproversAction()
	{
		$report_id = $this->_getParam('report_id',null);
		$dept_id = $this->_getParam('dept_id',null);
			
		$auth = Zend_Auth::getInstance();

		if($auth->hasIdentity())
		{
			$sess_vals = $auth->getStorage()->read();
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		$approver1_val = $this->_getParam('approver1_val',0);
		$approver2_val = $this->_getParam('approver2_val',0);
		$requi_model = new Default_Model_Requisition();
		$options = $requi_model->getapprovers($report_id, $dept_id);
		if($approver1_val == '0')
		$opt_str = sapp_Global::selectOptionBuilder('', 'Select Approver -1');
		else if($approver2_val == '0')
		$opt_str = sapp_Global::selectOptionBuilder('', 'Select Approver -2');
		else
		$opt_str = sapp_Global::selectOptionBuilder('', 'Select Approver -3');
		if(count($options) > 0)
		{
			foreach($options as $opt)
			{
				if($approver1_val != $opt['id'] && $approver2_val != $opt['id'] && $loginUserId != $opt['id'])
				{
					$opt_str .= sapp_Global::selectOptionBuilder($opt['id'], ucwords($opt['name']),$opt['profileimg']);
				}
			}
		}
		$this->_helper->_json(array('options' =>$opt_str));
	}
	public function approverejectrequisitionAction()
	{
	    $req_status = $this->_getParam('flag',null);
		$req_id = $this->_getParam('req_id',null);
		$requi_model = new Default_Model_Requisition();
		//$requisitionData=$requi_model->getRequisitionDataById($req_id );
	
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$sess_vals = $auth->getStorage()->read();
			$loginUserId = $auth->getStorage()->read()->id;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
	   $requisitionData=$requi_model->getRequisitionForEdit($req_id,$loginUserId);
	   $aflag=$requisitionData['aflag'];
	   $aorder=$requisitionData['aorder'];
	
	if($req_status == 3)//for rejected
		{
			$data = array(
						'modifiedby' => trim($loginUserId),
						'modifiedon' => gmdate("Y-m-d H:i:s"),
						'appstatus'.$aorder => $req_status,                                       
						'req_status' => $req_status,
			);
		}
		
		else //for approved
		{
			
			if($aorder == 1)
			{
				if($requisitionData['approver2'] != '')
				{
					$data = array(
									'appstatus1' =>$req_status,
									'appstatus2' => 'Initiated',                                            
								  );
				}
				else
				{
					$data = array(
									'appstatus1' =>$req_status,
									'req_status' => $req_status,                                            
								 );
				}
			}
			else if($aorder == 2)
			{
				if($requisitionData['approver3'] != '')
				{
					$data = array(
									'appstatus2' =>$req_status,
									'appstatus3' => 'Initiated',                                            
								);
					
				}
				else
				{
					$data = array(
									'appstatus2' =>$req_status,
									'req_status' => $req_status,                                            
								  );								
				}
			}
			else if($aorder == 3)
			{
				$data = array(
									'appstatus3' =>$req_status,
									'req_status' => $req_status,                                            
				);
				
			}
			$data['modifiedby'] = trim($loginUserId);
			$data['modifiedon'] = gmdate("Y-m-d H:i:s");
		}
		$where = "id = ".$req_id;		
		$result = $requi_model->SaveorUpdateRequisitionData($data, $where);
				
	      //for saving Requisition history
			  if($result == 'update')
				{
					 $data = $requi_model->getReqDataForView($req_id);
				}
				if($result == 'update' && $data[0]['req_status']!='Initiated')
				{
                    $requisition_id=$req_id;
 					$history = 'Requisition status has been '.$data[0]['req_status'].' by ';
                    $createdby =$loginUserId;
					$modifiedby=$loginUserId;
					
					 $reqh_model = new Default_Model_Requisitionhistory();
					$requisition_history = array(											
										'requisition_id' =>$requisition_id,
										'description' => $history,
										'createdby' => $createdby,
										'modifiedby' => $modifiedby,
										'isactive' => 1,
										'createddate' =>gmdate("Y-m-d H:i:s"),
										'modifieddate'=> gmdate("Y-m-d H:i:s"),
									);
					$where = '';
					$historyId = $reqh_model->saveOrUpdateRequisitionHistory($requisition_history,$where); 
				}
				
			// History end
			
			//start of mailing
				$tableid = $result;
				if($result != '')
				{
					
						//start of mailing
						$user_model = new Default_Model_Usermanagement();
						$jobtitleModel = new Default_Model_Jobtitles();
						$requisition_data = $requi_model->getRequisitionDataById($req_id);
						$report_person_data = $user_model->getUserDataById($requisition_data['reporting_id']);

					    	$st_arr = array(
							'0'		=>		'Select status',
							'2'		=>		'Approved',
							'3'		=> 		'Rejected'
							);

							if($req_status == 3 )//for rejected
							{

								$approver1_person_data = $user_model->getUserDataById($requisition_data['approver1']);
								$Raisedby_person_data = $user_model->getUserDataById($requisition_data['createdby']);

								$jobttlArr = $jobtitleModel->getsingleJobTitleData(trim($requisition_data['jobtitle']));
								if(!empty($jobttlArr) && $jobttlArr != 'norows')
								{
									$jobtitlename = ' - '.$jobttlArr[0]['jobtitlename'];
								}
								else
								$jobtitlename = '';

								$mail_arr[0]['name'] = 'HR';
								$mail_arr[0]['email'] = defined('REQ_HR_'.$requisition_data['businessunit_id'])?constant('REQ_HR_'.$requisition_data['businessunit_id']):"";
								$mail_arr[0]['type'] = 'HR';
								$mail_arr[1]['name'] = 'Management';
								$mail_arr[1]['email'] = defined('REQ_MGMT_'.$requisition_data['businessunit_id'])?constant('REQ_MGMT_'.$requisition_data['businessunit_id']):"";
								$mail_arr[1]['type'] = 'Management';
								$mail_arr[2]['name'] = $Raisedby_person_data['userfullname'];
								$mail_arr[2]['email'] = $Raisedby_person_data['emailaddress'];
								$mail_arr[2]['type'] = 'Raise';
								$mail_arr[3]['name'] = $approver1_person_data['userfullname'];
								$mail_arr[3]['email'] = $approver1_person_data['emailaddress'];
								$mail_arr[3]['type'] = 'Approver';
								
								$appr_str = "";
								$appr_str = $approver1_person_data['userfullname'];
								/* if($requisition_data['approver2'] != '')
								{ */
									$approver2_person_data = $user_model->getUserDataById($requisition_data['approver2']);
									$appr_str .= ", ".$approver2_person_data['userfullname'];
									$mail_arr[4]['name'] = $approver2_person_data['userfullname'];
									$mail_arr[4]['email'] = $approver2_person_data['emailaddress'];
									$mail_arr[4]['type'] = 'Approver';
								//}
								/* if($requisition_data['approver3'] != '')
								{ */
									$approver3_person_data = $user_model->getUserDataById($requisition_data['approver3']);
									$appr_str .= " and ".$approver3_person_data['userfullname'];
									$mail_arr[5]['name'] = $approver3_person_data['userfullname'];
									$mail_arr[5]['email'] = $approver3_person_data['emailaddress'];
									$mail_arr[5]['type'] = 'Approver';
								//}
								// Check if the reporting person and raised person are same - Requisition raised by Manager case
								if($requisition_data['reporting_id'] != $requisition_data['createdby']){
									$mail_arr[6]['name'] = $report_person_data['userfullname'];
									$mail_arr[6]['email'] = $report_person_data['emailaddress'];
									$mail_arr[6]['type'] = 'Report';
								}
								
								$mail = array();
								for($ii = 0;$ii < count($mail_arr);$ii++)
								{
									$base_url = 'http://'.$this->getRequest()->getHttpHost() . $this->getRequest()->getBaseUrl();
									$view = $this->getHelper('ViewRenderer')->view;
									$this->view->emp_name = (!empty($mail_arr[$ii]['name']))?$mail_arr[$ii]['name']:'';
									$this->view->base_url=$base_url;
									$this->view->type = (!empty($mail_arr[$ii]['type']))?$mail_arr[$ii]['type']:'';
									$this->view->jobtitle = $jobtitlename;
									$this->view->requisition_code = $requisition_data['requisition_code'];
									$this->view->approver_str = $appr_str;
									$this->view->raised_name = $Raisedby_person_data['userfullname'];
									$this->view->req_status = $st_arr[$req_status];
									$this->view->reporting_manager = $report_person_data['userfullname'];
									$text = $view->render('mailtemplates/changedrequisition.phtml');
									$options['subject'] = ($st_arr[$req_status]=='Approved')?APPLICATION_NAME.': Requisition is approved':APPLICATION_NAME.': Requisition is rejected';
									
									$options['header'] = 'Requisition Status';
									$options['toEmail'] = (!empty($mail_arr[$ii]['email']))?$mail_arr[$ii]['email']:'';
									$options['toName'] = (!empty($mail_arr[$ii]['name']))?$mail_arr[$ii]['name']:'';
									$options['message'] = $text;
									$mail[$ii] =$options;
									$options['cron'] = 'yes';
									if($options['toEmail'] != ''){
										sapp_Global::_sendEmail($options);
									}
								}
									
							}
							else if($req_status == 2 )//for approved
							{
							
								$approver_person_data = $user_model->getUserDataById($loginUserId);
							/* 	$mail_arr[0]['name'] = $approver_person_data['userfullname'];
								$mail_arr[0]['email'] = $approver_person_data['emailaddress'];
								$mail_arr[0]['type'] = 'Approver'; */
								/* if($edit_flag == 'yes')
								{
									$approved_by_data = $user_model->getUserDataById($requisition_data['approver'.$appr_per]);
									$req_status = 2;
								} */
								//else
								$approved_by_data = $user_model->getUserDataById($loginUserId);

								$Raisedby_person_data = $user_model->getUserDataById($requisition_data['createdby']);
								$appr_str = $approved_by_data['userfullname'];
								
								
								$mail_arr[0]['name'] = 'HR';
								$mail_arr[0]['email'] = defined('REQ_HR_'.$requisition_data['businessunit_id'])?constant('REQ_HR_'.$requisition_data['businessunit_id']):"";
								$mail_arr[0]['type'] = 'HR';
								$mail_arr[1]['name'] = 'Management';
								$mail_arr[1]['email'] = defined('REQ_MGMT_'.$requisition_data['businessunit_id'])?constant('REQ_MGMT_'.$requisition_data['businessunit_id']):"";
								$mail_arr[1]['type'] = 'Management';
								$mail_arr[2]['name'] = $Raisedby_person_data['userfullname'];
								$mail_arr[2]['email'] = $Raisedby_person_data['emailaddress'];
								$mail_arr[2]['type'] = 'Raise';
								
								
								if($requisition_data['approver1'] != '')
								{
									$approver1_person_data = $user_model->getUserDataById($requisition_data['approver1']);
									//$appr_str .= ", ".$approver1_person_data['userfullname'];
									$mail_arr[3]['name'] = $approver1_person_data['userfullname'];
									$mail_arr[3]['email'] = $approver1_person_data['emailaddress'];
									$mail_arr[3]['type'] = 'Approver';
								}
								
								
								if($requisition_data['approver2'] != '')
								{
									$approver2_person_data = $user_model->getUserDataById($requisition_data['approver2']);
									//$appr_str .= ", ".$approver2_person_data['userfullname'];
									$mail_arr[4]['name'] = $approver2_person_data['userfullname'];
									$mail_arr[4]['email'] = $approver2_person_data['emailaddress'];
									$mail_arr[4]['type'] = 'Approver';
								}
								if($requisition_data['approver3'] != '')
								{
									$approver3_person_data = $user_model->getUserDataById($requisition_data['approver3']);
									//$appr_str .= " and ".$approver3_person_data['userfullname'];
									$mail_arr[5]['name'] = $approver3_person_data['userfullname'];
									$mail_arr[5]['email'] = $approver3_person_data['emailaddress'];
									$mail_arr[5]['type'] = 'Approver';
								}

								for($ii = 0;$ii < count($mail_arr);$ii++)
								{
									$base_url = 'http://'.$this->getRequest()->getHttpHost() . $this->getRequest()->getBaseUrl();
									$view = $this->getHelper('ViewRenderer')->view;
									$this->view->emp_name = $mail_arr[$ii]['name'];
									$this->view->base_url=$base_url;
									$this->view->type = $mail_arr[$ii]['type'];
									$this->view->requisition_code = $requisition_data['requisition_code'];
									$this->view->req_status = $st_arr[$req_status];
									$this->view->raised_name = $Raisedby_person_data['userfullname'];
									$this->view->approver_str = $appr_str;
									$text = $view->render('mailtemplates/changedrequisition.phtml');
									$options['subject'] = ($st_arr[$req_status]=='Approved')?APPLICATION_NAME.': Requisition is approved':APPLICATION_NAME.': Requisition is rejected';
									$options['header'] = 'Requisition Status';
									$options['toEmail'] = $mail_arr[$ii]['email'];
									$options['toName'] = $mail_arr[$ii]['name'];
									$options['message'] = $text;

									$options['cron'] = 'yes';
									if($options['toEmail'] != ''){
										sapp_Global::_sendEmail($options);
									}
								}
							}
					
				}//end of mailing
			
				$this->_helper->_json(array('msg' =>"success"));
	}
	public function addpopupAction()
	{
		Zend_Layout::getMvcInstance()->setLayoutPath(APPLICATION_PATH."/layouts/scripts/popup/");
		$auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
            $login_group_id = $auth->getStorage()->read()->group_id;
            $login_role_id = $auth->getStorage()->read()->emprole;
        }        		
        $id = $this->_getParam('id',null);
        $clientsModel = new Default_Model_Clients();
        $usersModel = new Default_Model_Users();
	    $requi_model = new Default_Model_Requisition();
            if(is_numeric($id) && $id >0)
            {
                $id = abs($id);
                $data = $requi_model->getReqDataForView($id);
                $app1_name = $app2_name = $app3_name='';
                if(count($data)>0  && $data[0]['req_status'] == 'Initiated')
                {
                    $data = $data[0];                 										 
                    $data['jobtitlename'] = '';			
                    $data['businessunit_name'] = $data['businessunit_name'];									
                    $data['dept_name'] = $data['department_name'];									
                    $data['titlename'] = $data['jobtitle_name'];									
                    $data['posname'] = $data['position_name'];									
                    $data['empttype'] = $data['emp_type_name'];						                       
                    $data['mngrname'] = $data['reporting_manager_name'];						
                    $data['raisedby'] = $data['createdby_name'];			                        
                    $data['app1_name'] = $data['approver1_name'];
                        
                    if($data['approver2'] != '')
                    {                        
                        $data['app2_name'] = $data['approver2_name'];
                    }
                    else 
                    {
                        $data['app2_name'] = 'No Approver';
                    }
                        
                    if($data['approver3'] != '')
                    {                        
                        $data['app3_name'] = $data['approver3_name'];
                    }
                    else 
                    {
                        $data['app3_name'] = 'No Approver';
                    }                        
			
                   /*  foreach($data as $key=>$val)
                    {
                        $data[$key] = htmlentities($val, ENT_QUOTES, "UTF-8");
                    }	 */
                    if($data['client_id'] != '')
                    {
                    	$clien_data = $clientsModel->getClientDetailsById($data['client_id']);
                    	$data['client_id']=$clien_data[0]['client_name'];
                    }
                    if($data['recruiters'] != '')
                    {
                    	$name = '';
                    	$recData=$usersModel->getUserDetailsforView($data['recruiters']);
                    	if(count($recData)>0)
                    	{
                    		foreach($recData as $dataname){
                    			$name = $name.','.$dataname['name'];
                    		}
                    
                    	}
                    	$data['recruiters']=ltrim($name,',');
                    }
                    
                    if($data['req_priority'] == 1) {
                    	$data['req_priority']='High';
                    }else if($data['req_priority'] == 2) {
                    	$data['req_priority']='Medium';
                    }else {
                    $data['req_priority']='Low';
                    }
                    $data['onboard_date'] = sapp_Global::change_date($data['onboard_date'], 'view');
                    
                    $previ_data = sapp_Global::_checkprivileges(REQUISITION,$login_group_id,$login_role_id,'edit');

                    $this->view->previ_data = $previ_data;
                    $this->view->data = $data;
                    
                    $this->view->id = $id;
                    $this->view->controllername = "requisition";
                    $this->view->ermsg = '';
					
                }
                else
                {
                    $this->view->nodata = 'nodata';
                }
            }
            else
            {
                $this->view->nodata = 'nodata';
            }
		
			$this->view->id=  $id;
			$this->view->controllername='requisition';
		
	}

	public function uploadAction()
    {

    }

    public function uploadviewAction()
    {

    }
}
