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

/**
 * This form is used in Requisition screen.
 * @author K.Rama Krishna 
 */
class Default_Form_Monthlypayroll extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('id', 'formid');
        $this->setAttrib('name', 'monthlypayroll');
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserRole = $auth->getStorage()->read()->emprole;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }
        $id = new Zend_Form_Element_Hidden('id');


        /**
         *  Define Form Elements
         *
         */
        $employee_id = new Zend_Form_Element_Select('employee_id');
        $employee_id->setAttrib('title', 'Employee Code.');
        $employee_id->setAttrib('class', 'formDataElement');
        $employee_id->setAttrib('onChange', 'getEmployeeData(this)');
        $employee_id->addMultiOptions(array(''=>'Select Employee','AMD006'=>'AMD006'));



        $starting_date = new Zend_Form_Element_Text('starting_date');
        $starting_date->setAttrib('maxLength', 10);
        $starting_date->setAttrib('title', 'Starting Date.');
        $starting_date->addFilter(new Zend_Filter_StringTrim());


        $employee_name = new Zend_Form_Element_Text('employee_name');
        $employee_name->setAttrib('maxLength', 30);
        $employee_name->setAttrib('title', 'Employee Name.');
        $employee_name->addFilter(new Zend_Filter_StringTrim());


        $department = new Zend_Form_Element_Text('department');
        $department->setAttrib('maxLength', 10);
        $department->setAttrib('title', 'Department.');
        $department->addFilter(new Zend_Filter_StringTrim());


        $comments = new Zend_Form_Element_Textarea('comments');
        $comments->setAttrib('rows', 10);
        $comments->setAttrib('cols', 50);
        $comments->setAttrib('maxlength', 400);
        $comments->setAttrib('title', 'Comments.');


        $contract_date = new Zend_Form_Element_Text('contract_date');
        $contract_date->setAttrib('title', 'Date of Contract.');
        $contract_date->setAttrib('maxLength', 10);
        $contract_date->setAttrib('readonly', 'readonly');
        $contract_date->setAttrib('onfocus', 'this.blur()');


        $sick_leavedays = new Zend_Form_Element_Text('sick_leavedays');
        $sick_leavedays->setAttrib('maxLength', 10);
        $sick_leavedays->setAttrib('title', 'Sick leavedays.');
        $sick_leavedays->addFilter(new Zend_Filter_StringTrim());

        $standby_hours = new Zend_Form_Element_Text('standby_hours');
        $standby_hours->setAttrib('maxLength', 10);
        $standby_hours->setAttrib('title', 'Standby in hours.');
        $standby_hours->addFilter(new Zend_Filter_StringTrim());

        $overtime_hours = new Zend_Form_Element_Text('overtime_hours');
        $overtime_hours->setAttrib('maxLength', 10);
        $overtime_hours->setAttrib('title', 'Overtime in hours.');
        $overtime_hours->addFilter(new Zend_Filter_StringTrim());

        $addition_rollposition = new Zend_Form_Element_Text('addition_rollposition');
        $addition_rollposition->setAttrib('maxLength', 10);
        $addition_rollposition->setAttrib('title', 'Addition Role/Position.');
        $addition_rollposition->addFilter(new Zend_Filter_StringTrim());

        $annual_leavedays = new Zend_Form_Element_Text('annual_leavedays');
        $annual_leavedays->setAttrib('maxLength', 10);
        $annual_leavedays->setAttrib('title', 'Annual leave days.');
        $annual_leavedays->addFilter(new Zend_Filter_StringTrim());

        $weekend_nationaldays = new Zend_Form_Element_Text('weekend_nationaldays');
        $weekend_nationaldays->setAttrib('maxLength', 10);
        $weekend_nationaldays->setAttrib('title', 'Weekend/National holidays.');
        $weekend_nationaldays->addFilter(new Zend_Filter_StringTrim());

        $daily_allowance = new Zend_Form_Element_Text('daily_allowance');
        $daily_allowance->setAttrib('maxLength', 10);
        $daily_allowance->setAttrib('title', 'Daily allowance');
        $daily_allowance->addFilter(new Zend_Filter_StringTrim());

        $grossbase_salary = new Zend_Form_Element_Text('grossbase_salary');
        $grossbase_salary->setAttrib('maxLength', 10);
        $grossbase_salary->setAttrib('title', 'Gross Base Salary.');
        $grossbase_salary->addFilter(new Zend_Filter_StringTrim());

        $deductadd_salary = new Zend_Form_Element_Text('deductadd_salary');
        $deductadd_salary->setAttrib('maxLength', 10);
        $deductadd_salary->setAttrib('title', 'Deduction/Adds from gross salary.');
        $deductadd_salary->addFilter(new Zend_Filter_StringTrim());

        $gross_salary = new Zend_Form_Element_Text('gross_salary');
        $gross_salary->setAttrib('maxLength', 10);
        $gross_salary->setAttrib('title', 'Gross Salary');
        $gross_salary->addFilter(new Zend_Filter_StringTrim());

        $work_days = new Zend_Form_Element_Text('work_days');
        $work_days->setAttrib('maxLength', 10);
        $work_days->setAttrib('title', 'Work days.');
        $work_days->addFilter(new Zend_Filter_StringTrim());

        $monthlygross_salary = new Zend_Form_Element_Text('monthlygross_salary');
        $monthlygross_salary->setAttrib('maxLength', 10);
        $monthlygross_salary->setAttrib('title', 'Monthly Gross Salary for days worked.');
        $monthlygross_salary->addFilter(new Zend_Filter_StringTrim());

        $contribution_salary = new Zend_Form_Element_Text('contribution_salary');
        $contribution_salary->setAttrib('maxLength', 10);
        $contribution_salary->setAttrib('title', 'Salary which contribution are calculated.');
        $contribution_salary->addFilter(new Zend_Filter_StringTrim());

        $employeesocial_insurance = new Zend_Form_Element_Text('employeesocial_insurance');
        $employeesocial_insurance->setAttrib('maxLength', 10);
        $employeesocial_insurance->setAttrib('title', 'Employee social insurance.');
        $employeesocial_insurance->addFilter(new Zend_Filter_StringTrim());

        $employeehealth_insurance = new Zend_Form_Element_Text('employeehealth_insurance');
        $employeehealth_insurance->setAttrib('maxLength', 10);
        $employeehealth_insurance->setAttrib('title', 'Employee health insurance.');
        $employeehealth_insurance->addFilter(new Zend_Filter_StringTrim());

        $employeetotal_insurance = new Zend_Form_Element_Text('employeetotal_insurance');
        $employeetotal_insurance->setAttrib('maxLength', 10);
        $employeetotal_insurance->setAttrib('title', 'Total of employee insurance.');
        $employeetotal_insurance->addFilter(new Zend_Filter_StringTrim());

        $employersocial_insurance = new Zend_Form_Element_Text('employersocial_insurance');
        $employersocial_insurance->setAttrib('maxLength', 10);
        $employersocial_insurance->setAttrib('title', 'Employer social insurance.');
        $employersocial_insurance->addFilter(new Zend_Filter_StringTrim());

        $employerhealth_insurance = new Zend_Form_Element_Text('employerhealth_insurance');
        $employerhealth_insurance->setAttrib('maxLength', 10);
        $employerhealth_insurance->setAttrib('title', 'Employer health insurance.');
        $employerhealth_insurance->addFilter(new Zend_Filter_StringTrim());

        $employertotal_insurance = new Zend_Form_Element_Text('employertotal_insurance');
        $employertotal_insurance->setAttrib('maxLength', 10);
        $employertotal_insurance->setAttrib('title', 'Total of employer insurance.');
        $employertotal_insurance->addFilter(new Zend_Filter_StringTrim());

        $whtaxpaided_salary = new Zend_Form_Element_Text('whtaxpaided_salary');
        $whtaxpaided_salary->setAttrib('maxLength', 10);
        $whtaxpaided_salary->setAttrib('title', 'Salary which withholding taxs are calculated.');
        $whtaxpaided_salary->addFilter(new Zend_Filter_StringTrim());

        $progresive_whtax = new Zend_Form_Element_Text('progresive_whtax');
        $progresive_whtax->setAttrib('maxLength', 10);
        $progresive_whtax->setAttrib('title', 'Progresive withholding tax.');
        $progresive_whtax->addFilter(new Zend_Filter_StringTrim());

        $bankpaid_salary = new Zend_Form_Element_Text('bankpaid_salary');
        $bankpaid_salary->setAttrib('maxLength', 10);
        $bankpaid_salary->setAttrib('title', 'Net salary to be paid through bank account.');
        $bankpaid_salary->addFilter(new Zend_Filter_StringTrim());

        $whtax_salary = new Zend_Form_Element_Text('whtax_salary');
        $whtax_salary->setAttrib('maxLength', 10);
        $whtax_salary->setAttrib('title', 'Withhold from Net Salary.');
        $whtax_salary->addFilter(new Zend_Filter_StringTrim());


        /**
         *  Validate all Form Elements
         *
         */


        $employee_id ->setRequired(true);
        $employee_id->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $starting_date->setRequired(true);
        $starting_date->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employee_name->setRequired(true);
        $employee_name->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $department->setRequired(true);
        $department->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $grossbase_salary->setRequired(true);
        $grossbase_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Emplyoee ID.'));


        $gross_salary->setRequired(true);
        $gross_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $contract_date->setRequired(true);
        $contract_date->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $work_days->setRequired(true);
        $work_days->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));
        $work_days->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $work_days->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));


        $monthlygross_salary->setRequired(true);
        $monthlygross_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $contribution_salary->setRequired(true);
        $contribution_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employeesocial_insurance->setRequired(true);
        $employeesocial_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employeehealth_insurance->setRequired(true);
        $employeehealth_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employeetotal_insurance->setRequired(true);
        $employeetotal_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employersocial_insurance->setRequired(true);
        $employersocial_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employerhealth_insurance->setRequired(true);
        $employerhealth_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employertotal_insurance->setRequired(true);
        $employertotal_insurance->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $whtaxpaided_salary->setRequired(true);
        $whtaxpaided_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $progresive_whtax->setRequired(true);
        $progresive_whtax->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $bankpaid_salary->setRequired(true);
        $bankpaid_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));


        $sick_leavedays->addValidator("regex",true,array(
                'pattern'=>'/^([0-9]+?)+$/',
                'messages'=>array(
                    'regexNotMatch'=>'Please enter only numbers.'
                )
        ));
        $sick_leavedays->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $standby_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $standby_hours->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $overtime_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $overtime_hours->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $weekend_nationaldays->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $weekend_nationaldays->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $annual_leavedays->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $annual_leavedays->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $daily_allowance->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $daily_allowance->addValidator("greaterThan",true,array(
            'min'=>0,
            'messages'=>array(
                'notGreaterThan'=>'No.of positions cannot be zero.'
            )
        ));

        $deductadd_salary->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));




        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
        $submit->setLabel('Save');

        $idval = Zend_Controller_Front::getInstance()->getRequest()->getParam('id',null);
        $bunit_val = Zend_Controller_Front::getInstance()->getRequest()->getParam('business_unit',null);
        

        
        $this->addElements(array($id,$submit,$employee_id, $starting_date, $employee_name, $department, $comments, $sick_leavedays, $standby_hours, $overtime_hours, $addition_rollposition, $annual_leavedays,
                $weekend_nationaldays, $daily_allowance, $gross_salary, $grossbase_salary, $deductadd_salary, $contract_date, $work_days, $monthlygross_salary, $contribution_salary,
                $employeesocial_insurance, $employeehealth_insurance, $employeetotal_insurance, $employersocial_insurance, $employerhealth_insurance, $employertotal_insurance,
                $whtax_salary, $whtaxpaided_salary, $progresive_whtax, $bankpaid_salary));
        $this->setElementDecorators(array('ViewHelper'));

    }
}