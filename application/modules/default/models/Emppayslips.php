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

class Default_Model_Emppayslips extends Zend_Db_Table_Abstract
{	
    protected $_name = 'main_emppayslip';
    protected $_primary = 'id';


    public function getEmpPayslipData($sort, $by, $pageNo, $perPage, $id)
    {
        $where = " e.user_id = ".$id;
//
//        if($searchQuery)
//            $where .= " AND ".$searchQuery;
        $db = Zend_Db_Table::getDefaultAdapter();

        $empPayslipData = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('e' => 'main_emppayslip'),array('id'=>'e.id','period','date','bankname','accountnumber','salary_net'))
            ->where($where)
            ->order("$by $sort")
            ->limitPage($pageNo, $perPage);
        return $empPayslipData;
    }

    public function getpdffilepath($id){
        $where = "e.id = ".$id;
        $db = Zend_Db_Table::getDefaultAdapter();

        $query = "select filename from main_emppayslip where id=".$id;
        $result = $db->query($query);
        return $result->fetch();

    }


    public function addUpdatePayslip($data){

        $date_arr = explode('-', (string)$data['date']);
        $start_date_string = $date_arr[0].'-'.$date_arr[1].'-01';
        $end_date_string = $date_arr[0].'-'.$date_arr[1].'-15';

        $start_date = date("Y-m-d",strtotime($start_date_string));
        $end_date = date("Y-m-d",strtotime($end_date_string));

        $where = " AND p.date >= '".$start_date."' AND p.date <= '".$end_date."'";

        $db = Zend_Db_Table::getDefaultAdapter();

        $query = "select p.* from ".$this->_name." p where p.user_id = '".$data['user_id']."'".$where;

        $result = $db->query($query);
        $row = $result->fetch();

        if($row['id'] != '')
        {
            $where = "id = ".$row['id'];
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

    public function getGrid($sort, $by, $perPage, $pageNo,$searchData,$call,$dashboardcall,$Uid,$conText)
    {

        $objName = 'emppayslips';

        $tableFields = array('action'=>'Action','period'=>'Period','date'=>'Created Date','bankname'=>'Bank Name','accountnumber'=>'Account Number', 'salary_net'=>'Net Salary');

        $tablecontent = $this->getEmpPayslipData($sort, $by, $pageNo, $perPage, $Uid);


        $bool_arr = array('' => 'All',1 => 'Yes',2 => 'No');
        $dataTmp = array('userid'=>$Uid,
            'sort' => $sort,
            'by' => $by,
            'pageNo' => $pageNo,
            'perPage' => $perPage,
            'tablecontent' => $tablecontent,
            'objectname' => $objName,
            'extra' => array(),
            'tableheader' => $tableFields,
            'jsGridFnName' => 'getEmployeeAjaxgridData',
            'jsFillFnName' => '',
//            'add'=>'add',
            'menuName'=>'Pay slips',
            'formgrid'=>'true',
            'unitId'=>$Uid,
            'call'=>$call,
            'context'=>$conText

//            'search_filters' => array(
//                'active_company' => array(
//                    'type' => 'select',
//                    'filter_data' => $bool_arr,
//                ),
//                'start_date'=>array('type'=>'datepicker'),
//                'end_date'=>array('type'=>'datepicker')
//            )
        );
        return $dataTmp;
    }




}