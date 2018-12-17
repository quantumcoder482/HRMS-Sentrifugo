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

class Default_ShiftpayrollController extends Zend_Controller_Action
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
        $ajaxContext->addActionContext('getemployeedata', 'json')->initContext();
//        $ajaxContext->addActionContext('getpositions', 'json')->initContext();
//        $ajaxContext->addActionContext('getexcelexport', 'json')->initContext();
//        $ajaxContext->addActionContext('payslip', 'json')->initContext();
    }

    public function init()
    {
        $this->_options= $this->getInvokeArg('bootstrap')->getOptions();
    }

    public function indexAction()
    {

        $shift_model = new Default_Model_Shiftpayroll();

        $call = $this->_getParam('call');
        if($call == 'ajaxcall')
            $this->_helper->layout->disableLayout();

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }


        $refresh = $this->_getParam('refresh');
        $data = array();
        $searchQuery = '';
        $searchArray = array();
        $dashboardcall = $this->_getParam('dashboardcall');
        $tablecontent='';


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

        // Shift payroll Term Calc

        $term = $this->_getParam('term');
        $nowDate = new Zend_date();
        $term = $term ? date('Y-m-d',strtotime($term)):$nowDate->get('YYYY-MM-dd');
        $term_shiftpayroll = $shift_model->getCreatedpayrollbyMonth($term);


        // Create new payroll
        $newpayroll = $this->_getParam('newpayroll');
        if($newpayroll){
            if($term_shiftpayroll['count'] == 0){
                $result = $shift_model->CreateNewShiftpayroll($loginUserId,$loginuserGroup);
            }
        }


        $export_payroll = "disabled";
        if($term_shiftpayroll['count'] != 0){
            $export_payroll = "enabled";
        }


        $dataTmp = $shift_model->getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$loginUserId,$loginuserGroup,'shiftpayroll',$dashboardcall, $term);

        // Each Department Employees Count

        array_push($data,$dataTmp);
        $this->view->dataArray = $dataTmp;
        $this->view->call = $call;
        $this->view->term = $term;
        $this->view->export_payroll = $export_payroll;
        $this->view->message = "this is shift payroll page";
        $this->view->messages = $this->_helper->flashMessenger->getMessages();

    }

    public function addAction()
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

        $form = new Default_Form_Shiftpayroll();
        $shiftpayroll_model = new Default_Model_Shiftpayroll();

        $norec_arr = array();
        $elements = $form->getElements();

        $employee_data = $shiftpayroll_model->getEmployeeData();

        if(count($employee_data) == 0){
            $norec_arr['employee_id'] = "This month payroll not created yet";
        }
        $form->employee_id->addMultiOptions(array(''=>'Select Employee Id')+$employee_data);

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


        $form = new Default_Form_Shiftpayroll();
        $shiftpayroll_model = new Default_Model_Shiftpayroll();
        $usersModel = new Default_Model_Users();
        $user_model = new Default_Model_Usermanagement();

        $form->setAttrib('action',BASE_URL.'shiftpayroll/edit/id/'.$id);
        $form->submit->setLabel('Update');


        $norec_arr = array();
        $elements = $form->getElements();

        $employee_data = $shiftpayroll_model->getEmployeeData();

        if(count($employee_data) == 0){
            $norec_arr['employee_id'] = "This month payroll not created yet";
        }
        $form->employee_id->addMultiOptions(array(''=>'Select Employee Id')+$employee_data);


        $edit_flag = '';
        $edit_order = '';

        try {
            if ($id > 0 && is_numeric($id)) {
                $id = abs($id);

                $data = $shiftpayroll_model->getShiftpayrollForEdit($id);


                if (!empty($data)) {


                    $form->setDefault('id', $id);
                    $form->setDefault('employee_id', $data['employee_id']);
//                    $form->employee_id->setAttrib("disabled", "disabled");
                    $form->setDefault('employee_name', $data['employee_name']);
//                    $form->employee_name->setAttrib("disabled", "disabled");
                    $form->setDefault('shift1_hours',$data['shift1_hours']);
                    $form->setDefault('shift21_hours',$data['shift21_hours']);
                    $form->setDefault('shift22_hours',$data['shift22_hours']);
                    $form->setDefault('shift23_hours',$data['shift23_hours']);
                    $form->setDefault('shift31_hours',$data['shift31_hours']);
                    $form->setDefault('shift32_hours',$data['shift32_hours']);
                    $form->setDefault('sunday_shift1_hours',$data['sunday_shift1_hours']);
                    $form->setDefault('sunday_shift21_hours',$data['sunday_shift21_hours']);
                    $form->setDefault('sunday_shift22_hours',$data['sunday_shift22_hours']);
                    $form->setDefault('sunday_shift23_hours',$data['sunday_shift23_hours']);
                    $form->setDefault('sunday_shift31_hours',$data['sunday_shift31_hours']);
                    $form->setDefault('sunday_shift32_hours',$data['sunday_shift32_hours']);
                    $form->setDefault('holiday_shift1_hours',$data['holiday_shift1_hours']);
                    $form->setDefault('holiday_shift21_hours',$data['holiday_shift21_hours']);
                    $form->setDefault('holiday_shift22_hours',$data['holiday_shift22_hours']);
                    $form->setDefault('holiday_shift23_hours',$data['holiday_shift23_hours']);
                    $form->setDefault('holiday_shift31_hours',$data['holiday_shift31_hours']);
                    $form->setDefault('holiday_shift32_hours',$data['holiday_shift32_hours']);
                    $form->setDefault('total_hours',$data['total_hours']);
                    $form->setDefault('gross_salary_hours',$data['gross_salary_hours']);
                    $form->setDefault('shift1_salary',$data['shift1_salary']);
                    $form->setDefault('shift21_salary',$data['shift21_salary']);
                    $form->setDefault('shift22_salary',$data['shift22_salary']);
                    $form->setDefault('shift23_salary',$data['shift23_salary']);
                    $form->setDefault('shift31_salary',$data['shift31_salary']);
                    $form->setDefault('shift32_salary',$data['shift32_salary']);
                    $form->setDefault('sunday_shift1_salary',$data['sunday_shift1_salary']);
                    $form->setDefault('sunday_shift21_salary',$data['sunday_shift21_salary']);
                    $form->setDefault('sunday_shift22_salary',$data['sunday_shift22_salary']);
                    $form->setDefault('sunday_shift23_salary',$data['sunday_shift23_salary']);
                    $form->setDefault('sunday_shift31_salary',$data['sunday_shift31_salary']);
                    $form->setDefault('sunday_shift32_salary',$data['sunday_shift32_salary']);
                    $form->setDefault('holiday_shift1_salary',$data['holiday_shift1_salary']);
                    $form->setDefault('holiday_shift21_salary',$data['holiday_shift21_salary']);
                    $form->setDefault('holiday_shift22_salary',$data['holiday_shift22_salary']);
                    $form->setDefault('holiday_shift23_salary',$data['holiday_shift23_salary']);
                    $form->setDefault('holiday_shift31_salary',$data['holiday_shift31_salary']);
                    $form->setDefault('holiday_shift32_salary',$data['holiday_shift32_salary']);
//                    $form->setDefault('total_gross_salary',$data['total_gross_salary']);


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

    public function save($shiftpayrollform,$data)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }
        $shiftpayroll_model = new Default_Model_Shiftpayroll();
        $user_model = new Default_Model_Usermanagement();

        $appr_mail = '';$appr_per = '';


        if($shiftpayrollform->isValid($this->_request->getPost()))
        {
            $trDb = Zend_Db_Table::getDefaultAdapter();
            // starting transaction
            $trDb->beginTransaction();

            try
            {
                $id = (int)$this->_getParam('id',null);
                $employee_id = $this->_getParam('employee_id',null);
                $employee_name = $this->_getParam('employee_name',null);
                $shift1_hours = $this->_getParam('shift1_hours',null);
                $shift21_hours = $this->_getParam('shift21_hours',null);
                $shift22_hours = $this->_getParam('shift22_hours',null);
                $shift23_hours = $this->_getParam('shift23_hours',null);
                $shift31_hours = $this->_getParam('shift31_hours',null);
                $shift32_hours = $this->_getParam('shift32_hours',null);
                $sunday_shift1_hours = $this->_getParam('sunday_shift1_hours',null);
                $sunday_shift21_hours=$this->_getParam('sunday_shift21_hours',null);
                $sunday_shift22_hours=$this->_getParam('sunday_shift22_hours',null);
                $sunday_shift23_hours=$this->_getParam('sunday_shift23_hours',null);
                $sunday_shift31_hours=$this->_getParam('sunday_shift31_hours',null);
                $sunday_shift32_hours=$this->_getParam('sunday_shift32_hours',null);
                $holiday_shift1_hours=$this->_getParam('holiday_shift1_hours',null);
                $holiday_shift21_hours=$this->_getParam('holiday_shift21_hours',null);
                $holiday_shift22_hours=$this->_getParam('holiday_shift22_hours',null);
                $holiday_shift23_hours=$this->_getParam('holiday_shift23_hours',null);
                $holiday_shift31_hours=$this->_getParam('holiday_shift31_hours',null);
                $holiday_shift32_hours=$this->_getParam('holiday_shift32_hours',null);
                $total_hours=$this->_getParam('total_hours',null);
                $gross_salary_hours=$this->_getParam('gross_salary_hours',null);
                $edit_flag = $this->_getParam('edit_flag',null);

                $shift1_salary = $shift1_hours * $gross_salary_hours;
                $shift21_salary = $shift21_hours * $gross_salary_hours;
                $shift22_salary = $shift22_hours * $gross_salary_hours * 1.2;
                $shift23_salary = $shift23_hours * $gross_salary_hours * 1.5;
                $shift31_salary = $shift23_hours * $gross_salary_hours * 1.5;
                $shift32_salary = $shift23_hours * $gross_salary_hours;

                $sunday_shift1_salary = $sunday_shift1_hours * $gross_salary_hours * 1.25;
                $sunday_shift21_salary = $sunday_shift21_hours * $gross_salary_hours * 1.25;
                $sunday_shift22_salary = $sunday_shift22_hours * $gross_salary_hours * 1.45;
                $sunday_shift23_salary = $sunday_shift23_hours * $gross_salary_hours * 1.75;
                $sunday_shift31_salary = $sunday_shift31_hours * $gross_salary_hours * 1.75;
                $sunday_shift32_salary = $sunday_shift32_hours * $gross_salary_hours * 1.25;

                $holiday_shift1_salary = $holiday_shift1_hours * $gross_salary_hours * 1.25;
                $holiday_shift21_salary = $holiday_shift21_hours * $gross_salary_hours * 1.25;
                $holiday_shift22_salary = $holiday_shift22_hours * $gross_salary_hours * 1.45;
                $holiday_shift23_salary = $holiday_shift23_hours * $gross_salary_hours * 1.75;
                $holiday_shift31_salary = $holiday_shift31_hours * $gross_salary_hours * 1.75;
                $holiday_shift32_salary = $holiday_shift32_hours * $gross_salary_hours * 1.25;


                $shift1_salary = $shift1_salary!=0?ceil($shift1_salary):null;
                $shift21_salary = $shift21_salary!=0?ceil($shift21_salary):null;
                $shift22_salary = $shift22_salary!=0?ceil($shift22_salary):null;
                $shift23_salary = $shift23_salary!=0?ceil($shift23_salary):null;
                $shift31_salary = $shift31_salary!=0?ceil($shift31_salary):null;
                $shift32_salary = $shift32_salary!=0?ceil($shift32_salary):null;

                $sunday_shift1_salary = $sunday_shift1_salary!=0?ceil($sunday_shift1_salary):null;
                $sunday_shift21_salary = $sunday_shift21_salary!=0?ceil($sunday_shift21_salary):null;
                $sunday_shift22_salary = $sunday_shift22_salary!=0?ceil($sunday_shift22_salary):null;
                $sunday_shift23_salary = $sunday_shift23_salary!=0?ceil($sunday_shift23_salary):null;
                $sunday_shift31_salary = $sunday_shift31_salary!=0?ceil($sunday_shift31_salary):null;
                $sunday_shift32_salary = $sunday_shift32_salary!=0?ceil($sunday_shift32_salary):null;

                $holiday_shift1_salary = $holiday_shift1_salary!=0?ceil($holiday_shift1_salary):null;
                $holiday_shift21_salary = $holiday_shift21_salary!=0?ceil($holiday_shift21_salary):null;
                $holiday_shift22_salary = $holiday_shift22_salary!=0?ceil($holiday_shift22_salary):null;
                $holiday_shift23_salary = $holiday_shift23_salary!=0?ceil($holiday_shift23_salary):null;
                $holiday_shift31_salary = $holiday_shift31_salary!=0?ceil($holiday_shift31_salary):null;
                $holiday_shift32_salary = $holiday_shift32_salary!=0?ceil($holiday_shift32_salary):null;


                $total_gross_salary = $shift1_salary+$shift21_salary+$shift22_salary+$shift23_salary+$shift31_salary+$shift32_salary;
                $total_gross_salary += $sunday_shift1_salary+$sunday_shift21_salary+$sunday_shift22_salary+$sunday_shift23_salary+$sunday_shift31_salary+$sunday_shift32_salary;
                $total_gross_salary += $holiday_shift1_salary+$holiday_shift21_salary+$holiday_shift22_salary+$holiday_shift23_salary+$holiday_shift31_salary+$holiday_shift32_salary;



                if($edit_flag !='' && $edit_flag=='yes'){

                    $employee_id = $data['employee_id'];
                    $employee_name = $data['employee_name'];

                }

                $data = array(
                    'employee_id' 	    =>	trim($employee_id),
                    'employee_name' 	=>	trim($employee_name),
                    'shift1_hours' 	=>	trim($shift1_hours),
                    'shift21_hours' 	=>	trim($shift21_hours),
                    'shift22_hours' 	=>	trim($shift22_hours),
                    'shift23_hours' 	=>	trim($shift23_hours),
                    'shift31_hours' 	=>	trim($shift31_hours),
                    'shift32_hours' 	=>	trim($shift32_hours),
                    'sunday_shift1_hours' 	=>	trim($sunday_shift1_hours),
                    'sunday_shift21_hours' 	=>	trim($sunday_shift21_hours),
                    'sunday_shift22_hours' 	=>	trim($sunday_shift22_hours),
                    'sunday_shift23_hours' 	=>	trim($sunday_shift23_hours),
                    'sunday_shift31_hours' 	=>	trim($sunday_shift31_hours),
                    'sunday_shift32_hours' 	=>	trim($sunday_shift32_hours),
                    'holiday_shift1_hours' 	=>	trim($holiday_shift1_hours),
                    'holiday_shift21_hours' 	=>	trim($holiday_shift21_hours),
                    'holiday_shift22_hours' 	=>	trim($holiday_shift22_hours),
                    'holiday_shift23_hours' 	=>	trim($holiday_shift23_hours),
                    'holiday_shift31_hours' 	=>	trim($holiday_shift31_hours),
                    'holiday_shift32_hours' 	=>	trim($holiday_shift32_hours),
                    'total_hours' 	=>	trim($total_hours),
                    'gross_salary_hours' 	=>	trim($gross_salary_hours),
                    'shift1_salary' 	=>	trim($shift1_salary),
                    'shift21_salary' 	=>	trim($shift21_salary),
                    'shift22_salary' 	=>	trim($shift22_salary),
                    'shift23_salary' 	=>	trim($shift23_salary),
                    'shift31_salary' 	=>	trim($shift31_salary),
                    'shift32_salary' 	=>	trim($shift32_salary),
                    'sunday_shift1_salary' 	=>	trim($sunday_shift1_salary),
                    'sunday_shift21_salary' 	=>	trim($sunday_shift21_salary),
                    'sunday_shift22_salary' 	=>	trim($sunday_shift22_salary),
                    'sunday_shift23_salary' 	=>	trim($sunday_shift23_salary),
                    'sunday_shift31_salary' 	=>	trim($sunday_shift31_salary),
                    'sunday_shift32_salary' 	=>	trim($sunday_shift32_salary),
                    'holiday_shift1_salary' 	=>	trim($holiday_shift1_salary),
                    'holiday_shift21_salary' 	=>	trim($holiday_shift21_salary),
                    'holiday_shift22_salary' 	=>	trim($holiday_shift22_salary),
                    'holiday_shift23_salary' 	=>	trim($holiday_shift23_salary),
                    'holiday_shift31_salary' 	=>	trim($holiday_shift31_salary),
                    'holiday_shift32_salary' 	=>	trim($holiday_shift32_salary),
                    'total_gross_salary' 	=>	trim($total_gross_salary),
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

                $result = $shiftpayroll_model->SaveorUpdateShiftpayrollData($data, $where);


                /*
                 * Monthly payroll update
                 */

                $monthlypayroll_model = new Default_Model_Monthlypayroll();

                $emp = $monthlypayroll_model->getPayrollbyEmpId($employee_id);


                if($emp){


                    $whtax_salary = $emp['whtax_salary'];
                    $addtax_salary = $emp['addtax_salary'];
                    $emp_id = $emp['id'];

                       $monthlygross_salary = 0;
                       $contribution_salary = 0;
                       $employeesocial_insurance = 0;
                       $employeehealth_insurance = 0;
                       $employeetotal_insurance = 0;
                       $employersocial_insurance = 0;
                       $employerhealth_insurance = 0;
                       $employertotal_insurance = 0;
                       $whtaxpaided_salary = 0;
                       $progresive_whtax = 0;
                       $bankpaid_salary = 0;
                       $gross_salary = 0;
                       $total_bankpaid_salary = 0;


                    $gross_salary = $total_gross_salary ;
                    $monthlygross_salary = $gross_salary;
                    $contribution_salary = $monthlygross_salary>=105850?105850:($monthlygross_salary<=24000?24000:$monthlygross_salary);
                    $employeesocial_insurance = $contribution_salary * 0.095;
                    $employeehealth_insurance = $monthlygross_salary * 0.017;
                    $employeetotal_insurance = $employeesocial_insurance + $employeehealth_insurance;
                    $employersocial_insurance = $contribution_salary * 0.15;
                    $employerhealth_insurance = $monthlygross_salary * 0.017;
                    $employertotal_insurance = $employersocial_insurance + $employerhealth_insurance;
                    $whtaxpaided_salary = $monthlygross_salary;

                    if($whtaxpaided_salary <= 30000){
                        $progresive_whtax = 0;
                    }else if($whtaxpaided_salary > 130000){
                        $progresive_whtax = ($whtaxpaided_salary - 130000)*0.23 +13000;
                    }else{
                        $progresive_whtax = ($whtaxpaided_salary - 30000)*0.13;
                    }

                    $bankpaid_salary = $whtaxpaided_salary - $employeetotal_insurance - $progresive_whtax;

                    $total_bankpaid_salary = $bankpaid_salary - $whtax_salary + $addtax_salary;

                    $shiftpayroll = 1;


                    $data = array(
                        'gross_salary'          => $gross_salary,
                        'monthlygross_salary' 	=> 	ceil($monthlygross_salary),
                        'contribution_salary' 	=> 	ceil($contribution_salary),
                        'employeesocial_insurance' 	=> 	ceil($employeesocial_insurance),
                        'employeehealth_insurance' 	=> 	ceil($employeehealth_insurance),
                        'employeetotal_insurance' 	=> 	ceil($employeetotal_insurance),
                        'employersocial_insurance' 	=> 	ceil($employersocial_insurance),
                        'employerhealth_insurance' 	=> 	ceil($employerhealth_insurance),
                        'employertotal_insurance' 	=> 	ceil($employertotal_insurance),
                        'whtaxpaided_salary' 	    => 	ceil($whtaxpaided_salary),
                        'progresive_whtax' 	        => 	ceil($progresive_whtax),
                        'bankpaid_salary' 	        => 	ceil($bankpaid_salary),
                        'total_bankpaid_salary' 	=> 	ceil($total_bankpaid_salary),
                        'shiftpayroll'              =>  $shiftpayroll,
                        'modifiedby' 		        => 	ceil($loginUserId),
                        'modifiedon' 		        => 	gmdate("Y-m-d H:i:s")

                    );

                    $where = "id = ".$emp_id;

                    $monthlypayroll_model = new Default_Model_Monthlypayroll();
                    $result = $monthlypayroll_model->SaveorUpdateMonthlypayrollData($data, $where);

                }


                if($id != '')
                    $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Shiftpayroll updated successfully."));
                else
                    $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Shiftpayroll added successfully."));
                $trDb->commit();
                $this->_redirect('/shiftpayroll');

            }
            catch (Exception $e)
            {
                $trDb->rollBack();

                $this->_helper->getHelper("FlashMessenger")->addMessage(array("error"=>"Something went wrong, please try again later."));
                $this->_redirect('/shiftpayroll');
            }
        }
        else
        {
            $messages = $shiftpayrollform->getMessages();

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

        $id = $this->_getParam('id',null);

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $payroll_model = new Default_Model_Shiftpayroll();
        $employee_payroll = array();
        if($id){
            $employee_payroll = $payroll_model->getShiftpayrollDataById($id);
        }


        $file_name = $this->_getParam('file_name', NULL);
        if(!empty($file_name)){
            $file = BASE_PATH.'/downloads/reports/'.$this->_getParam('file_name');
            $status = sapp_Global::downloadReport($file);
        }


        return 0;
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


        $cols_param_arr = array(
            'index'             =>'No',
            'employee_name'     => 'Name',
            'shift1_hours'      => 'shift1 (08~16)',
            'shift21_hours'     => 'shift2 (16~19)',
            'shift22_hours'     => 'shift2 (19~22)',
            'shift23_hours'     => 'shift3 (22~24)',
            'shift31_hours'     => 'shift3 (00~06)',
            'shift32_hours'     => 'shift3 (06~08)',
            'sunday_shift1_hours'   => 'sunday shift1 (08~16)',
            'sunday_shift21_hours'  => 'sunday shift2 (16~19)',
            'sunday_shift22_hours'  => 'sunday shift2 (19~22)',
            'sunday_shift23_hours'  => 'sunday shift2 (22~24)',
            'sunday_shift31_hours'  => 'sunday shift3 (00~06)',
            'sunday_shift32_hours'  => 'sunday shift3 (06~08)',
            'holiday_shift1_hours'  => 'holiday shift1 (08~16)',
            'holiday_shift21_hours' => 'holiday shift2 (16~19)',
            'holiday_shift22_hours' => 'holiday shift2 (19~22)',
            'holiday_shift23_hours' => 'holiday shift2 (22~24)',
            'holiday_shift31_hours' => 'holiday shift3 (00~06)',
            'holiday_shift32_hours' => 'holiday shift3 (01~08)',
            'total_hours'           => 'total hours',
            'gross_salary_hours'    => 'gross salary hours',
            'shift1_salary'         => 'shift1 salary',
            'shift21_salary'        => 'shift2(16~19) salary',
            'shift22_salary'        => 'shift2(19~22) salary',
            'shift23_salary'        => 'shift2(22~24)salary',
            'shift31_salary'        => 'shift3(00~06) salary',
            'shift32_salary'        => 'shift3(06~08) salary',
            'sunday_shift1_salary'  => 'sunday shift(08~16) salary',
            'sunday_shift21_salary' => 'sunday shift2(16~19) salary',
            'sunday_shift22_salary' => 'sunday shift2(19~22) salary',
            'sunday_shift23_salary' => 'sunday shift2(22~24) salary',
            'sunday_shift31_salary' => 'sunday shift3(00~06) salary',
            'sunday_shift32_salary' => 'sunday shift3(06~08) salary',
            'holiday_shift1_salary' => 'holiday shift(08~16) salary',
            'holiday_shift21_salary'=> 'holiday shift2(16~19) salary',
            'holiday_shift22_salary'=> 'holiday shift2(19~22) salary',
            'holiday_shift23_salary'=> 'holiday shift2(22~24)salary',
            'holiday_shift31_salary'=> 'holiday shift3(00~06) salary',
            'holiday_shift32_salary'=> 'holiday shift3(06~08) salary',
            'total_gross_salary'    => 'total gross salary'
        );



        //$cols_param_arr = array('group_name' => 'Group','rolename' => 'Role','user_cnt' => 'Users count');
        $payroll_model = new Default_Model_Shiftpayroll();



        $payroll_data_req = $payroll_model->getShiftpayrollexportData($sort, $by, $searchQuery, $userid, $usergroup, $term,$searchQuery);




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


        $filename = "shiftpayroll (".$term_arr[0].'-'.$term_arr[1].").xlsx";
        $count =0;
        $unique_id = 0;
        $op_count = 0;
        $nonop_count = 0;
        $adm_count = 0;
        $cell_name="";


        $objPHPExcel->getActiveSheet()->getColumnDimension($letters[$count])->setWidth(60);

        foreach ($cols_param_arr as $names)
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


        // Display field/column values in Excel.

        $i = 2;
        foreach($payroll_data_req as $data)
        {
            $count1 =0;
            $unique_id++;
            foreach ($cols_param_arr as $column_key => $column_name)
            {
                // display field/column values
                $cell_name = $letters[$count1].$i;
                if($column_key == 'index'){
                    $value = $unique_id;
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                }else {
                    $value = isset($data[$column_key])?(trim($data[$column_key]) == ''?" ":$data[$column_key]):" ";
                    $value = html_entity_decode($value,ENT_QUOTES,'UTF-8');
                    $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
                }

                $count1++;
            }
            $i++;
        }


        $count1 = 0;
        foreach ($cols_param_arr as $column_key => $column_name)
        {
            // display field/column values
            $cell_name = $letters[$count1].$i;
            if($column_key == 'index'){
                $cell_name = "B".$i;
                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, "Total");
            } else if($column_key != 'employee_name'){

                $value = "=SUM(".$letters[$count1].'2:'.$letters[$count1].($i-1).")";

                $objPHPExcel->getActiveSheet()->SetCellValue($cell_name, $value);
            }

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

    public function viewAction()
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


        $form = new Default_Form_Shiftpayroll();
        $shiftpayroll_model = new Default_Model_Shiftpayroll();
        $usersModel = new Default_Model_Users();
        $user_model = new Default_Model_Usermanagement();

        $form->setAttrib('action',BASE_URL.'shiftpayroll/edit/id/'.$id);
        $form->submit->setLabel('Update');

        $norec_arr = array();
        $elements = $form->getElements();

        $employee_data = $shiftpayroll_model->getEmployeeData();

        if(count($employee_data) == 0){
            $norec_arr['employee_id'] = "This month payroll not created yet";
        }
        $form->employee_id->addMultiOptions(array(''=>'Select Employee Id')+$employee_data);

        $edit_flag = '';
        $edit_order = '';

        try {
            if ($id > 0 && is_numeric($id)) {
                $id = abs($id);

                $data = $shiftpayroll_model->getShiftpayrollForEdit($id);


                if (!empty($data)) {


                    $form->setDefault('id', $id);
                    $form->setDefault('employee_id', $data['employee_id']);
                    $form->setDefault('employee_name', $data['employee_name']);
                    $form->setDefault('shift1_hours',$data['shift1_hours']);
                    $form->setDefault('shift21_hours',$data['shift21_hours']);
                    $form->setDefault('shift22_hours',$data['shift22_hours']);
                    $form->setDefault('shift23_hours',$data['shift23_hours']);
                    $form->setDefault('shift31_hours',$data['shift31_hours']);
                    $form->setDefault('shift32_hours',$data['shift32_hours']);
                    $form->setDefault('sunday_shift1_hours',$data['sunday_shift1_hours']);
                    $form->setDefault('sunday_shift21_hours',$data['sunday_shift21_hours']);
                    $form->setDefault('sunday_shift22_hours',$data['sunday_shift22_hours']);
                    $form->setDefault('sunday_shift23_hours',$data['sunday_shift23_hours']);
                    $form->setDefault('sunday_shift31_hours',$data['sunday_shift31_hours']);
                    $form->setDefault('sunday_shift32_hours',$data['sunday_shift32_hours']);
                    $form->setDefault('holiday_shift1_hours',$data['holiday_shift1_hours']);
                    $form->setDefault('holiday_shift21_hours',$data['holiday_shift21_hours']);
                    $form->setDefault('holiday_shift22_hours',$data['holiday_shift22_hours']);
                    $form->setDefault('holiday_shift23_hours',$data['holiday_shift23_hours']);
                    $form->setDefault('holiday_shift31_hours',$data['holiday_shift31_hours']);
                    $form->setDefault('holiday_shift32_hours',$data['holiday_shift32_hours']);
                    $form->setDefault('total_hours',$data['total_hours']);
                    $form->setDefault('gross_salary_hours',$data['gross_salary_hours']);
                    $form->setDefault('shift1_salary',$data['shift1_salary']);
                    $form->setDefault('shift21_salary',$data['shift21_salary']);
                    $form->setDefault('shift22_salary',$data['shift22_salary']);
                    $form->setDefault('shift23_salary',$data['shift23_salary']);
                    $form->setDefault('shift31_salary',$data['shift31_salary']);
                    $form->setDefault('shift32_salary',$data['shift32_salary']);
                    $form->setDefault('sunday_shift1_salary',$data['sunday_shift1_salary']);
                    $form->setDefault('sunday_shift21_salary',$data['sunday_shift21_salary']);
                    $form->setDefault('sunday_shift22_salary',$data['sunday_shift22_salary']);
                    $form->setDefault('sunday_shift23_salary',$data['sunday_shift23_salary']);
                    $form->setDefault('sunday_shift31_salary',$data['sunday_shift31_salary']);
                    $form->setDefault('sunday_shift32_salary',$data['sunday_shift32_salary']);
                    $form->setDefault('holiday_shift1_salary',$data['holiday_shift1_salary']);
                    $form->setDefault('holiday_shift21_salary',$data['holiday_shift21_salary']);
                    $form->setDefault('holiday_shift22_salary',$data['holiday_shift22_salary']);
                    $form->setDefault('holiday_shift23_salary',$data['holiday_shift23_salary']);
                    $form->setDefault('holiday_shift31_salary',$data['holiday_shift31_salary']);
                    $form->setDefault('holiday_shift32_salary',$data['holiday_shift32_salary']);
                    $form->setDefault('total_gross_salary',$data['total_gross_salary']);



                    $form->employee_id->setAttrib("disabled", "disabled");
                    $form->shift1_hours->setAttrib("disabled", "disabled");
                    $form->shift21_hours->setAttrib("disabled", "disabled");
                    $form->shift22_hours->setAttrib("disabled", "disabled");
                    $form->shift23_hours->setAttrib("disabled", "disabled");
                    $form->shift31_hours->setAttrib("disabled", "disabled");
                    $form->shift32_hours->setAttrib("disabled", "disabled");

                    $form->sunday_shift1_hours->setAttrib("disabled", "disabled");
                    $form->sunday_shift21_hours->setAttrib("disabled", "disabled");
                    $form->sunday_shift22_hours->setAttrib("disabled", "disabled");
                    $form->sunday_shift23_hours->setAttrib("disabled", "disabled");
                    $form->sunday_shift31_hours->setAttrib("disabled", "disabled");
                    $form->sunday_shift32_hours->setAttrib("disabled", "disabled");

                    $form->holiday_shift1_hours->setAttrib("disabled", "disabled");
                    $form->holiday_shift21_hours->setAttrib("disabled", "disabled");
                    $form->holiday_shift22_hours->setAttrib("disabled", "disabled");
                    $form->holiday_shift23_hours->setAttrib("disabled", "disabled");
                    $form->holiday_shift31_hours->setAttrib("disabled", "disabled");
                    $form->holiday_shift32_hours->setAttrib("disabled", "disabled");

                    $form->total_gross_salary->setAttrib("disabled", "disabled");


//                    $form->setDefault('total_gross_salary',$data['total_gross_salary']);


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
            $shiftpayroll_model = new Default_Model_Shiftpayroll();
            $monthlypayroll_model = new Default_Model_Monthlypayroll();
            $shiftpayrolldata = $shiftpayroll_model->getShiftpayrollDataById($id);
            $emp_id = $shiftpayrolldata['employee_id'];

            $result = $shiftpayroll_model->DeletePayrollData($id);

            if($result){

               $employee_data = $monthlypayroll_model->getPayrollbyEmpId($emp_id);

               $emp_id = $employee_data['id'];
               $grossbase_salary =$employee_data['grossbase_salary'];
               $sick_leavedays =$employee_data['sick_leavedays'];
               $standby_hours =$employee_data['standby_hours'];
               $overtime_hours =$employee_data['overtime-hours'];
               $addition_rollposition =$employee_data['addition_rollposition'];
               $weekend_nationaldays =$employee_data['weekend_nationaldays'];
               $annual_leavedays =$employee_data['annual_leavedays'];
               $deductadd_salary =$employee_data['deductadd_salary'];
               $work_days =$employee_data['work_days'];
               $whtax_salary =$employee_data['whtax_salary'];
               $addtax_salary =$employee_data['addtax_salary'];

                   $monthlygross_salary = 0;
                   $contribution_salary = 0;
                   $employeesocial_insurance = 0;
                   $employeehealth_insurance = 0;
                   $employeetotal_insurance = 0;
                   $employersocial_insurance = 0;
                   $employerhealth_insurance = 0;
                   $employertotal_insurance = 0;
                   $whtaxpaided_salary = 0;
                   $progresive_whtax = 0;
                   $bankpaid_salary = 0;
                   $gross_salary = 0;
                   $total_bankpaid_salary = 0;


                $gross_salary = $grossbase_salary + $deductadd_salary;

                if($sick_leavedays){
                    // $work_days = $work_days - $sick_leavedays;
                    $gross_salary = $gross_salary - ($grossbase_salary/21)*$sick_leavedays*0.2;
                }
                if($standby_hours){
                    $gross_salary = $gross_salary - $standby_hours*120;
                }
                if($overtime_hours){
                    $gross_salary = $gross_salary + $overtime_hours*($grossbase_salary/21/8)*0.25;
                }
                if($addition_rollposition){
                    $gross_salary = $gross_salary + $addition_rollposition;
                }
                if($weekend_nationaldays){
                    $gross_salary = $gross_salary + $weekend_nationaldays*4500;
                }

                $monthlygross_salary = $gross_salary * $work_days/21;

                $contribution_salary = $monthlygross_salary>=105850?105850:($monthlygross_salary<=24000?24000:$monthlygross_salary);
                $employeesocial_insurance = $contribution_salary * 0.095;
                $employeehealth_insurance = $monthlygross_salary * 0.017;
                $employeetotal_insurance = $employeesocial_insurance + $employeehealth_insurance;
                $employersocial_insurance = $contribution_salary * 0.15;
                $employerhealth_insurance = $monthlygross_salary * 0.017;
                $employertotal_insurance = $employersocial_insurance + $employerhealth_insurance;
                $whtaxpaided_salary = $monthlygross_salary;

                if($whtaxpaided_salary <= 30000){
                    $progresive_whtax = 0;
                } elseif ($whtaxpaided_salary > 130000){
                    $progresive_whtax = ($whtaxpaided_salary - 130000)*0.23 +13000;
                } else {
                    $progresive_whtax = ($whtaxpaided_salary - 30000)*0.13;
                }

                $bankpaid_salary = $whtaxpaided_salary - $employeetotal_insurance - $progresive_whtax;

                $total_bankpaid_salary = $bankpaid_salary - $whtax_salary + $addtax_salary;

                $shiftpayroll = 0;
                $data = array(
                    'gross_salary' 	        => 	trim($gross_salary),
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
                    'createdby' 		        => 	trim($loginUserId),
                    'modifiedby' 		        => 	trim($loginUserId),
                    'shiftpayroll'              =>  $shiftpayroll,
                    'createdon' 		        => 	gmdate("Y-m-d H:i:s"),
                    'modifiedon' 		        => 	gmdate("Y-m-d H:i:s")

                );

                $where = "id = ".$emp_id;
                $update_result = $monthlypayroll_model->SaveorUpdateMonthlypayrollData($data, $where);

            }


            if($result == 'success')
            {
                $messages['message'] = 'Shiftpayroll deleted successfully.';
                $messages['msgtype'] = 'success';
                $messages['flagtype']= 'process';
            }
            else{
                $messages['message'] = 'Shiftpayroll cannot be deleted.';
                $messages['msgtype'] = 'error';
            }
        }
        else
        {
            $messages['message'] = 'Shiftpayroll cannot be deleted.';
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


    public function getemployeedataAction(){

        $employee_id = $_POST['employee_id'];

        $shiftpayroll_model = new Default_Model_Shiftpayroll();

        $employee_data = $shiftpayroll_model->getEmployeeData($employee_id);

//        $employee_data = array('userfullname'=>$employee_id);

        return $this->_helper->json($employee_data);
    }

}