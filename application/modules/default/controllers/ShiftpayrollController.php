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
        if (!isset($session)) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                echo Zend_Json::encode(array('login' => 'failed'));
                die();
            } else {
                $this->_redirect('');
            }
        }

    }

    public function init()
    {
        $this->_options = $this->getInvokeArg('bootstrap')->getOptions();
    }

    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        $this->view->message = "this is no shift  payroll page!!";

    }


}