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

class Default_Model_Monthlypayroll extends Zend_Db_Table_Abstract
{
    protected $_name = 'main_monthlypayrolls';
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

    public function getMonthlypayrollData($sort, $by, $pageNo, $perPage,$searchQuery,$userid,$usergroup,$req_type,$term,$searchQuery)
    {

        if($req_type == 3)
            $where = " r.department = 'ADM' ";
        else if($req_type == 2)
        {
            $where = " r.department = 'NON-OP' ";
        }
        else
            $where = " r.department = 'OP' ";


        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where .= "AND r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."'";


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
            ->from(array('r'=>"main_monthlypayrolls"),array('id'=>'id','employee_id','starting_date','employee_name','department','comments',
                'grossbase_salary','sick_leavedays','standby_hours','overtime_hours','addition_rollposition','annual_leavedays','weekend_nationaldays',
                'daily_allowance','deductadd_salary','gross_salary','work_days','monthlygross_salary','contribution_salary','employeesocial_insurance',
                'employeehealth_insurance','employeetotal_insurance','employersocial_insurance','employerhealth_insurance','employertotal_insurance',
                'whtaxpaided_salary','progresive_whtax','bankpaid_salary','addtax_salary','whtax_salary','total_bankpaid_salary'
            ))
            ->where($where)
            ->order("$by $sort")
            ->limitPage($pageNo, $perPage);


        return $payrolldata;
    }



    public function getMonthlypayrollexportData($sort,$by,$searchQuery,$userid,$usergroup,$req_type,$term,$searchQuery)
    {

        $db = Zend_Db_Table::getDefaultAdapter();

        $date_arr = explode('-', (string)$term);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = " r.createdon >= '".$start_date."' AND r.createdon <= '".$end_date."' ";


        if($req_type == 3)
            $where .= "AND r.department = 'ADM' ";
        else if($req_type == 2)
        {
            $where .= "AND r.department = 'NON-OP' ";
        }
        else if($req_type == 1){
            $where .= "AND r.department = 'OP' ";
        }
        else
            $where .= "";


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
            ->from(array('r'=>"main_monthlypayrolls"),array('id'=>'id','employee_id','starting_date','employee_name','department','comments',
                'grossbase_salary','sick_leavedays','standby_hours','overtime_hours','addition_rollposition','annual_leavedays','weekend_nationaldays',
                'daily_allowance','deductadd_salary','gross_salary','work_days','monthlygross_salary','contribution_salary','employeesocial_insurance',
                'employeehealth_insurance','employeetotal_insurance','employersocial_insurance','employerhealth_insurance','employertotal_insurance',
                'whtaxpaided_salary','progresive_whtax','bankpaid_salary','addtax_salary','whtax_salary','total_bankpaid_salary'
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


    	$monthlypayrollsData = $this->select()
		->setIntegrityCheck(false)
		->from(array('r'=>$this->_name), array("count"=>"count(*)"))
		->where($where);
		return $db->fetchRow($monthlypayrollsData);
    }
	
	public function getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$loginUserId,$loginuserGroup,$reqtype,$objName,$dashboardcall,$term)
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
                            'employee_id'       => 'Employee Unique Number',
                            'starting_date'     => 'Starting Date',
                            'employee_name'     => 'Employee Name',
                            'department'        => 'DEP',
                            'comments'          => 'Comments',
                            'grossbase_salary'  => 'Gross Base Salary',
                            'sick_leavedays'    => 'Sick Leave in Days',
                            'standby_hours'     => 'Standby in Hours',
                            'overtime_hours'    => 'Overtime in hours',
                            'addition_rollposition'     => 'Additional Role/Position',
                            'annual_leavedays'         => 'Annual leave Days',
                            'weekend_nationaldays'     => 'Weekend/National Holidays',
                            'daily_allowance'          => 'Daily Allowance',
                            'deductadd_salary'         => 'Deduct & Add Salary',
                            'gross_salary'             => 'Gross Salary',
                            'work_days'                => 'Work Days',
                            'monthlygross_salary'      => 'Monthly Gross Salary',
                            'contribution_salary'      => 'Contribution Salary',
                            'employeesocial_insurance' => 'Employee Social Insurance',
                            'employeehealth_insurance' => 'Employee Health Insurance',
                            'employeetotal_insurance'  => 'Employee Total Insurance',
                            'employersocial_insurance' => 'Employer Social Insurance',
                            'employerhealth_insurance' => 'Employer Health Insurance',
                            'employertotal_insurance'  => 'Employer Total Insurance',
                            'whtaxpaided_salary'       => 'Salary withholding taxs are calculated',
                            'progresive_whtax'         => 'Progresive withholding Tax',
                            'bankpaid_salary'          => 'All Salary paid through Bank',
                            'addtax_salary'            => 'Adds to Net Salary',
                            'whtax_salary'             => 'Withhold from Net Salary',
                            'total_bankpaid_salary'    => 'Total Net Salary',

            );
		$tablecontent = $this->getMonthlypayrollData($sort, $by, $pageNo, $perPage,$searchQuery,$loginUserId,$loginuserGroup,$reqtype,$term);

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
//                'add' =>'add',
                'term' => $term,
				'menuName' => 'Monthly Payroll for AMD',
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
    public function getMonthlypayrollForEdit($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $query = "select p.* from ".$this->_name." p where p.id =".$id;
        $result = $db->query($query);
        $row = $result->fetch();
        return $row;
    }

    public function getPayrollbyEmpId($emp_id)
    {
        $Date = new Zend_date();
        $nowDate = $Date->get('YYYY-MM-dd');

        $date_arr = explode('-', (string)$nowDate);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = " AND p.createdon >= '".$start_date."' AND p.createdon <= '".$end_date."'";


        $db = Zend_Db_Table::getDefaultAdapter();
        $query = "select p.* from ".$this->_name." p where p.employee_id = '".$emp_id."'".$where;
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

    public function CreateNewMonthlypayroll($loginUserId,$loginuserGroup){
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
/*
        $start_date = $nowDate->get('YYYY-MM').'-01';
        $end_date = $nowDate->get('YYYY-MM').'-15';

        $query = "select employee_id, total_gross_salary
                  from main_shiftpayrolls
                  order by id
                  where createdon >= {$start_date} AND createdon <= {$end_date}";

        $result = $db->query($query);
        $employee_shiftpayrolls = $result->fetchAll();
*/
        // make monthlypayroll

        $count = 0;
        foreach($employeedata as $emp){

            $salary = null;
/*
            if($employee_shiftpayrolls){
                foreach($employee_shiftpayrolls as $emp_shiftpayroll){
                    if(trim($emp_shiftpayroll['employee_id']) == trim($emp['employeeId'])){
                        $salary = $emp_shiftpayroll['total_gross_salary'];
                    }
                }
            }
*/
            $work_days = 21;
            if($salary == null)
            {
                $salary = sapp_Global::_decrypt($emp['salary']);
            }

            $monthlygross_salary = $salary * $work_days/21;
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
            $total_bankpaid_salary = $bankpaid_salary;

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
                'total_bankpaid_salary'     => 	trim($total_bankpaid_salary),
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

    public function getEmpBankData($emp_id){

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $userid = $auth->getStorage()->read()->id;
            $usergroup = $auth->getStorage()->read()->group_id;
        }
        $db = Zend_Db_Table::getDefaultAdapter();

        $query = "select p.bankname, p.accountnumber, p.user_id
                  from main_employees_summary r 
                  left join main_empsalarydetails p on p.user_id = r.user_id
                  where r.employeeId = '{$emp_id}'
                  ";
        $result = $db->query($query);
        $empbankdata = $result->fetch();
        return $empbankdata;
    }



    /**
     * This function will return all data of requisition by passing its primay key id.
     * @parameters
     * @param Integer $id   =  id of monthlypayroll (primary key)
     * 
     * @return Array  Array of monthlypayroll's data.
     */


    public function getMonthlypayrollDataById($id)
    {
    	$row = $this->fetchRow("id = '".$id."'");
        if (!$row) 
        {
            throw new Exception("Problem in monthlypayroll model");
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
    public function SaveorUpdateMonthlypayrollData($data, $where)
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


}//end of class