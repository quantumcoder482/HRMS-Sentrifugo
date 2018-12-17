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

class Default_EmppayslipsController extends Zend_Controller_Action
{

	private $options;
	public function preDispatch()
	{
	}

	public function init()
	{
		$this->_options= $this->getInvokeArg('bootstrap')->getOptions();
	}

	public function indexAction()
	{

	}

	public function editAction()
	{
		if(defined('EMPTABCONFIGS'))
		{
			$empOrganizationTabs = explode(",",EMPTABCONFIGS);

		if(in_array('emp_payslips',$empOrganizationTabs)){
            $userID="";
			$auth = Zend_Auth::getInstance();
			if($auth->hasIdentity())
			{
				$loginUserId = $auth->getStorage()->read()->id;
			}
			$id = $this->getRequest()->getParam('userid');

			$conText ='';
			$call = $this->_getParam('call');
			if($call == 'ajaxcall')
			{
				$this->_helper->layout->disableLayout();
				$userID = ($this->_getParam('unitId') !='')? $this->_getParam('unitId'):$this->_getParam('userid');
				$conText = ($this->_getParam('context') !='')? $this->_getParam('context'):$this->getRequest()->getParam('context');
			}
		 	if($id == '') $id = $userID;
		 	$Uid = ($id)?$id:$userID;

		    $employeeModal = new Default_Model_Employee();
            try
            {
                if($Uid && is_numeric($Uid) && $Uid>0)
                {
                    $usersModel = new Default_Model_Users();
                    $empdata = $employeeModal->getActiveEmployeeData($Uid);
                    $employeeData = $usersModel->getUserDetailsByIDandFlag($Uid);
                    if($empdata == 'norows')
                    {
                        $this->view->rowexist = "norows";
                        $this->view->empdata = "";
                    }
                    else
                    {
                        $this->view->rowexist = "rows";
                        if(!empty($empdata))
                        {
                            $emppayslipsModel = new Default_Model_Emppayslips();
                            $view = Zend_Layout::getMvcInstance()->getView();
                            $objname = $this->_getParam('objname');
                            $refresh = $this->_getParam('refresh');
                            $dashboardcall = $this->_getParam('dashboardcall',null);
                            $data = array();	$searchQuery = '';	$searchArray = array();		$tablecontent = '';
                            if($refresh == 'refresh')
                            {
                                if($dashboardcall == 'Yes')
                                    $perPage = DASHBOARD_PERPAGE;
                                else
                                    $perPage = PERPAGE;

                                $sort = 'DESC';$by = 'e.date';$pageNo = 1;$searchData = '';$searchQuery = '';	$searchArray = array();
                            }
                            else
                            {
                                $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
                                $by = ($this->_getParam('by')!='')? $this->_getParam('by'):'e.date';
                                if($dashboardcall == 'Yes')
                                    $perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
                                else
                                    $perPage = $this->_getParam('per_page',PERPAGE);

                                $pageNo = $this->_getParam('page', 1);
                                $searchData = $this->_getParam('searchData');
                                $searchData = rtrim($searchData,',');
                            }
                            $dataTmp = $emppayslipsModel->getGrid($sort, $by, $perPage, $pageNo,$searchData,$call,$dashboardcall,$Uid,$conText);


                            array_push($data,$dataTmp);
                            $this->view->dataArray = $data;
                            $this->view->call = $call ;
                            $this->view->employeedata = $employeeData[0];
                            $this->view->id = $id ;
                            $this->view->messages = $this->_helper->flashMessenger->getMessages();
                        }
                        $this->view->empdata = $empdata;
                    }
                }
                else
                {
                    $this->view->rowexist = "norows";
                }
            }
            catch(Exception $e)
            {
                $this->view->rowexist = "norows";
            }
        }else{
            $this->_redirect('error');
        }
        }else{
            $this->_redirect('error');
        }
    }




	public function viewAction()
	{
        if(defined('EMPTABCONFIGS'))
        {
            $empOrganizationTabs = explode(",",EMPTABCONFIGS);

            if(in_array('emp_payslips',$empOrganizationTabs)){
                $userID="";
                $auth = Zend_Auth::getInstance();
                if($auth->hasIdentity())
                {
                    $loginUserId = $auth->getStorage()->read()->id;
                }
                $id = $this->getRequest()->getParam('userid');

                $conText ='';
                $call = $this->_getParam('call');
                if($call == 'ajaxcall')
                {
                    $this->_helper->layout->disableLayout();
                    $userID = ($this->_getParam('unitId') !='')? $this->_getParam('unitId'):$this->_getParam('userid');
                    $conText = ($this->_getParam('context') !='')? $this->_getParam('context'):$this->getRequest()->getParam('context');
                }
                if($id == '') $id = $userID;
                $Uid = ($id)?$id:$userID;

                $employeeModal = new Default_Model_Employee();
                try
                {
                    if($Uid && is_numeric($Uid) && $Uid>0)
                    {
                        $usersModel = new Default_Model_Users();
                        $empdata = $employeeModal->getActiveEmployeeData($Uid);
                        $employeeData = $usersModel->getUserDetailsByIDandFlag($Uid);
                        if($empdata == 'norows')
                        {
                            $this->view->rowexist = "norows";
                            $this->view->empdata = "";
                        }
                        else
                        {
                            $this->view->rowexist = "rows";
                            if(!empty($empdata))
                            {
                                $emppayslipsModel = new Default_Model_Emppayslips();
                                $view = Zend_Layout::getMvcInstance()->getView();
                                $objname = $this->_getParam('objname');
                                $refresh = $this->_getParam('refresh');
                                $dashboardcall = $this->_getParam('dashboardcall',null);
                                $data = array();	$searchQuery = '';	$searchArray = array();		$tablecontent = '';
                                if($refresh == 'refresh')
                                {
                                    if($dashboardcall == 'Yes')
                                        $perPage = DASHBOARD_PERPAGE;
                                    else
                                        $perPage = PERPAGE;

                                    $sort = 'DESC';$by = 'e.date';$pageNo = 1;$searchData = '';$searchQuery = '';	$searchArray = array();
                                }
                                else
                                {
                                    $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
                                    $by = ($this->_getParam('by')!='')? $this->_getParam('by'):'e.date';
                                    if($dashboardcall == 'Yes')
                                        $perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
                                    else
                                        $perPage = $this->_getParam('per_page',PERPAGE);

                                    $pageNo = $this->_getParam('page', 1);
                                    $searchData = $this->_getParam('searchData');
                                    $searchData = rtrim($searchData,',');
                                }
                                $dataTmp = $emppayslipsModel->getGrid($sort, $by, $perPage, $pageNo,$searchData,$call,$dashboardcall,$Uid,$conText);


                                array_push($data,$dataTmp);
                                $this->view->dataArray = $data;
                                $this->view->call = $call ;
                                $this->view->employeedata = $employeeData[0];
                                $this->view->id = $id ;
                                $this->view->messages = $this->_helper->flashMessenger->getMessages();
                            }
                            $this->view->empdata = $empdata;
                        }
                    }
                    else
                    {
                        $this->view->rowexist = "norows";
                    }
                }
                catch(Exception $e)
                {
                    $this->view->rowexist = "norows";
                }
            }else{
                $this->_redirect('error');
            }
        }else{
            $this->_redirect('error');
        }
	}



	public function viewpopupAction(){

        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
        }
        $this->_helper->layout->disableLayout();

        $payslip_model = new Default_Model_Emppayslips();

        $id = $this->getRequest()->getParam('id');

        $filename = "";
        if($id){
            $payslipdata = $payslip_model->getpdffilepath($id);
            $filename = $payslipdata['filename'];
        }
        if($filename == ""){
            $this->_redirect(BASE_URL.'emppayslips/edit/userid/'.$id);
        }

        $base_url = parse_url(BASE_URL);

        $filepath = BASE_PATH.'/downloads/payslips/'.$filename;


        $status = sapp_Global::downloadReport($filepath);
        $this->_redirect(BASE_URL.'emppayslips/edit/userid/'.$id);

//        sapp_Global::
//        this->_redirect($filepath);
//        echo "<script>windows.location.href(".$filepath.")</script>";

    }

}
?>
