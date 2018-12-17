<?php
/*********************************************************************************
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2014 Sapplica
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

class Default_Model_Shiftpayroll extends Zend_Db_Table_Abstract
{
    protected $_name = 'main_shiftpayrolls';
    protected $_primary = 'id';

    /**
     * This function gives data for grid view.
     * @parameters
     * @param sort          = ascending or descending
     * @param by            = name of field which to be sort
     * @param pageNo        = page number
     * @param perPage       = no.of records per page
     * @param searchQuery   = search string
     *
     * @return  ResultSet;
     */

    public function getShiftpayrollData($sort, $by, $pageNo, $perPage,$searchQuery,$userid,$usergroup,$term,$searchQuery)
    {

        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = "r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."'";


        if($searchQuery)
            $where = $searchQuery;



//        if($usergroup == MANAGEMENT_GROUP || $usergroup == HR_GROUP)
//            $where .= "";
//        else if((  $usergroup == MANAGER_GROUP ) && $req_type == 1)
//            $where .= " AND (r.createdby = ".$userid." or (".$userid." in (approver1,approver2,approver3) and 'Initiated' in (case when approver1 = ".$userid." then appstatus1 when approver2 = ".$userid." then appstatus2 when approver3 = ".$userid." then appstatus3 end)) )";
//        else if($usergroup == MANAGER_GROUP && $req_type == 2)
//            $where .= " AND r.createdby = ".$userid." ";
//        else if($usergroup == MANAGER_GROUP && $req_type == 3)
//            $where .= " AND r.createdby = ".$userid." ";


        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $payrolldata = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('r'=>"main_shiftpayrolls"),array('id'=>'id','employee_id','employee_name','shift1_hours','shift21_hours','shift22_hours','shift23_hours','shift31_hours','shift32_hours',
                'sunday_shift1_hours','sunday_shift21_hours','sunday_shift22_hours','sunday_shift23_hours','sunday_shift31_hours','sunday_shift32_hours','holiday_shift1_hours',
                'holiday_shift21_hours','holiday_shift22_hours','holiday_shift23_hours','holiday_shift31_hours','holiday_shift32_hours','total_hours','gross_salary_hours',
                'shift1_salary','shift21_salary','shift22_salary','shift23_salary','shift31_salary','shift32_salary','sunday_shift1_salary','sunday_shift21_salary','sunday_shift22_salary',
                'sunday_shift23_salary','sunday_shift31_salary','sunday_shift32_salary','holiday_shift1_salary','holiday_shift21_salary','holiday_shift22_salary','holiday_shift23_salary',
                'holiday_shift31_salary','holiday_shift32_salary','total_gross_salary'
            ))
            ->where($where)
            ->order("$by $sort")
            ->limitPage($pageNo, $perPage);


        return $payrolldata;
    }



    public function getShiftpayrollexportData($sort,$by,$searchQuery,$userid,$usergroup,$term,$searchQuery)
    {

        $db = Zend_Db_Table::getDefaultAdapter();


        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = "r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."'";


        if($searchQuery)
            $where = $searchQuery;



//        if($usergroup == MANAGEMENT_GROUP || $usergroup == HR_GROUP)
//            $where .= "";
//        else if((  $usergroup == MANAGER_GROUP ) && $req_type == 1)
//            $where .= " AND (r.createdby = ".$userid." or (".$userid." in (approver1,approver2,approver3) and 'Initiated' in (case when approver1 = ".$userid." then appstatus1 when approver2 = ".$userid." then appstatus2 when approver3 = ".$userid." then appstatus3 end)) )";
//        else if($usergroup == MANAGER_GROUP && $req_type == 2)
//            $where .= " AND r.createdby = ".$userid." ";
//        else if($usergroup == MANAGER_GROUP && $req_type == 3)
//            $where .= " AND r.createdby = ".$userid." ";


        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $payrolldata = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('r'=>"main_shiftpayrolls"),array('id'=>'id','employee_id','employee_name','shift1_hours','shift21_hours','shift22_hours','shift23_hours','shift31_hours','shift32_hours',
                'sunday_shift1_hours','sunday_shift21_hours','sunday_shift22_hours','sunday_shift23_hours','sunday_shift31_hours','sunday_shift32_hours','holiday_shift1_hours',
                'holiday_shift21_hours','holiday_shift22_hours','holiday_shift23_hours','holiday_shift31_hours','holiday_shift32_hours','total_hours','gross_salary_hours',
                'shift1_salary','shift21_salary','shift22_salary','shift23_salary','shift31_salary','shift32_salary','sunday_shift1_salary','sunday_shift21_salary','sunday_shift22_salary',
                'sunday_shift23_salary','sunday_shift31_salary','sunday_shift32_salary','holiday_shift1_salary','holiday_shift21_salary','holiday_shift22_salary','holiday_shift23_salary',
                'holiday_shift31_salary','holiday_shift32_salary','total_gross_salary'
            ))
            ->where($where)
            ->order("$by $sort");

        return $db->fetchAll($payrolldata);
    }



    public function getDepartmentCount($req_type, $term)
    {

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userid = $auth->getStorage()->read()->id;
            $usergroup = $auth->getStorage()->read()->group_id;
        }

        $db = Zend_Db_Table::getDefaultAdapter();

        if($req_type==2)
        {
            $where = "r.department = 'NON-OP' ";
        }
        if($req_type==3)
        {
            $where = "r.department = 'ADM' ";
        }
        if($req_type==1)
        {
            $where = "r.department = 'OP' ";
        }

        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where .= "AND r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."'";

//		if($usergroup == MANAGEMENT_GROUP  || $usergroup == HR_GROUP)
//			$where .= "";
//		else if((  $usergroup == MANAGER_GROUP) && $req_type == 1)
//			$where .= " AND (r.createdby = ".$userid." or (".$userid." in (approver1,approver2,approver3) and 'Initiated' in (case when approver1 = ".$userid." then appstatus1 when approver2 = ".$userid." then appstatus2 when approver3 = ".$userid." then appstatus3 end)) )";
//		else if($usergroup == MANAGER_GROUP && $req_type == 2)
//			$where .= " AND r.createdby = ".$userid." ";
//		else if($usergroup == MANAGER_GROUP && $req_type == 3)
//			$where .= " AND r.createdby = ".$userid." ";


        $shiftpayrollsData = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('r'=>$this->_name), array("count"=>"count(*)"))
            ->where($where);
        return $db->fetchRow($shiftpayrollsData);
    }

    public function getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$loginUserId,$loginuserGroup,$objName,$dashboardcall,$term)
    {
        $searchQuery = '';
        $searchArray = array();
        $data = array();
        if($searchData != '' && $searchData!='undefined')
        {
            $searchValues = json_decode($searchData);
            if(count($searchValues) >0)
            {
                foreach($searchValues as $key => $val)
                {
                    if($key == 'onboard_date' || $key == 'r.createdon')
                    {
                        $searchQuery .= " date(".$key.") = '".  sapp_Global::change_date($val,'database')."' AND ";
                    }
                    else
                        $searchQuery .= " ".$key." like '%".$val."%' AND ";
                    $searchArray[$key] = $val;
                }
                $searchQuery = rtrim($searchQuery," AND");
            }
        }

        $tableFields = array('action'           => 'Action',
            'employee_name'     => 'Employee Name',
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

        $tablecontent = $this->getShiftpayrollData($sort, $by, $pageNo, $perPage,$searchQuery,$loginUserId,$loginuserGroup,$term);

        $dataTmp = array(
            'sort' => $sort,
            'by' => $by,
            'pageNo' => $pageNo,
            'perPage' => $perPage,
            'tablecontent' => $tablecontent,
            'objectname' => $objName,
            'extra' => array(),
            'tableheader' => $tableFields,
            'jsGridFnName' => 'getAjaxgridData',
            'jsFillFnName' => '',
            'add' =>'add',
            'term' => $term,
            'menuName' => 'Calc Payroll for Shift AMD',
            'searchArray' => $searchArray,
            'call'=>$call,
            'dashboardcall'=>$dashboardcall,
            'search_filters' => array(),
//                    'r.createdon' =>array('type'=>'datepicker'),
//                    'onboard_date'=>array('type'=>'datepicker'),
//                    'req_status' => array(
//                        'type' => 'select',
//                        'filter_data' => $status_arr,
//                    ),
//                    'appstatus1' => array(
//                        'type' => 'select',
//                        'filter_data' => $status_arr,
//                    ),
//                    'appstatus2' => array(
//                        'type' => 'select',
//                        'filter_data' => $status_arr,
//                    ),
//                    'appstatus3' => array(
//                        'type' => 'select',
//                        'filter_data' => $status_arr,
//                    ),
//                ),

        );
        return $dataTmp;
    }
    public function getShiftpayrollForEdit($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $query = "select p.* from ".$this->_name." p where p.id =".$id;
        $result = $db->query($query);
        $row = $result->fetch();
        return $row;
    }

    public function getCreatedpayrollbyMonth($term)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userid = $auth->getStorage()->read()->id;
            $usergroup = $auth->getStorage()->read()->group_id;
        }
        $db = Zend_Db_Table::getDefaultAdapter();


        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = "r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."'";

        $termpayrollsData = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('r'=>$this->_name), array("count"=>"count(*)"))
            ->where($where);
        return $db->fetchRow($termpayrollsData);

    }

    public function CreateNewShiftpayroll($loginUserId,$loginuserGroup){
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userid = $auth->getStorage()->read()->id;
            $usergroup = $auth->getStorage()->read()->group_id;
        }
        $db = Zend_Db_Table::getDefaultAdapter();

        $query = "select r.id, r.employeeId, r.date_of_joining, r.businessunit_name, r.userfullname, p.salary
                  from main_employees_summary r 
                  left join main_empsalarydetails p on p.user_id = r.user_id
                  where r.businessunit_name != 'NULL'
                  order by r.id";
        $result = $db->query($query);
        $employeedata = $result->fetchAll();

        $nowDate = new Zend_date();
        $create_date = $nowDate->get('YYYY-MM-dd');


        // make shiftpayroll

        $count = 0;
        foreach($employeedata as $emp){

            $work_days = 21;
            $salary = sapp_Global::_decrypt($emp['salary']);
            $shiftgross_salary = $salary * $work_days/21;
            $contribution_salary = $shiftgross_salary>=105850?105850:($shiftgross_salary<=24000?24000:$shiftgross_salary);
            $employeesocial_insurance = $contribution_salary * 0.095;
            $employeehealth_insurance = $shiftgross_salary * 0.017;
            $employeetotal_insurance = $employeesocial_insurance + $employeehealth_insurance;
            $employersocial_insurance = $contribution_salary * 0.15;
            $employerhealth_insurance = $shiftgross_salary * 0.017;
            $employertotal_insurance = $employersocial_insurance + $employerhealth_insurance;
            $whtaxpaided_salary = $shiftgross_salary;

            if($whtaxpaided_salary <= 30000){
                $progresive_whtax = 0;
            }else if($whtaxpaided_salary > 130000){
                $progresive_whtax = ($whtaxpaided_salary - 130000)*0.23 +13000;
            }else{
                $progresive_whtax = ($whtaxpaided_salary - 30000)*0.13;
            }

            $bankpaid_salary = $whtaxpaided_salary - $employeetotal_insurance - $progresive_whtax;


            $data = array(
                'employee_id' 	    =>	trim($emp['employeeId']),
                'employee_name' 	=>	trim($emp['userfullname']),
                'department' 		=>	trim($emp['businessunit_name']),
                'starting_date'	    =>	$emp['date_of_joining'],
//                'comments'		    =>	trim($comments),
//                'sick_leavedays'    => 	trim($sick_leavedays),
//                'standby_hours'	    =>	trim($standby_hours),
//                'overtime_hours'	=>	trim($overtime_hours),
//                'addition_rollposition' => 	trim($addition_rollposition),
//                'annual_leavedays'      => 	trim($annual_leavedays),
//                'weekend_nationaldays' 	=> 	trim($weekend_nationaldays),
//                'contract_date' 		=> 	sapp_Global::change_date(trim($contract_date),'database'),
//                'daily_allowance' 		=> 	trim($daily_allowance),
                'grossbase_salary' 	    => 	trim($salary),
//                'deductadd_salary' 	    => 	trim($deductadd_salary),
                'gross_salary' 	        => 	trim($salary),
                'work_days' 	        => 	trim($work_days),
                'shiftgross_salary' 	=> 	trim($shiftgross_salary),
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
//                'whtax_salary' 	            => 	trim($whtax_salary),
                'createdby' 		        => 	trim($loginUserId),
//                'modifiedby' 		        => 	trim($loginUserId),
                'createdon' 		        => 	$create_date,
//                'modifiedon' 		        => 	gmdate("Y-m-d H:i:s")

            );

            $this->insert($data);
            $id=$this->getAdapter()->lastInsertId($this->_name);
            if($id){
                $count++;
            }
        }
        return $count;



    }

    /**
     * This function will return all data of requisition by passing its primay key id.
     * @parameters
     * @param Integer $id   =  id of shiftpayroll (primary key)
     *
     * @return Array  Array of shiftpayroll's data.
     */


    public function getShiftpayrollDataById($id)
    {
        $row = $this->fetchRow("id = '".$id."'");
        if (!$row)
        {
            throw new Exception("Problem in shiftpayroll model");
        }
        return $row->toArray();
    }

    /**
     * This function is used to save/update data in database.
     * @parameters
     * @param data  =  array of form data.
     * @param where =  where condition in case of update.
     *
     * @return {String}  Primary id when new record inserted,'update' string when a record updated.
     */
    public function SaveorUpdateShiftpayrollData($data, $where)
    {

        if($where != '')
        {
            $this->update($data, $where);
            return 'update';
        }

        else
        {
            $this->insert($data);
            $id=$this->getAdapter()->lastInsertId($this->_name);
            return $id;
        }
    }

    Public function DeletePayrollData($id)
    {
        $where = array('id=?'=>$id);
        $this->delete($where);
        return 'success';
    }

    public function getEmployeeData($employee_id = ''){

        $db = Zend_Db_Table::getDefaultAdapter();

        if($employee_id != ''){
            $employeedata = $db->query("select userfullname from main_employees_summary where employeeId = '".$employee_id."'");
            return $employeedata->fetch();
        } else {
            $query = "select * from main_employees_summary where businessunit_name != 'NULL' order by id";
            $result = $db->query($query);
            $data = $result->fetchAll();

            $options_arr = Array();
            foreach($data as $option){
                $options_arr[$option['employeeId']] = $option['employeeId'];
            }

            return $options_arr;
        }

    }



}//end of class