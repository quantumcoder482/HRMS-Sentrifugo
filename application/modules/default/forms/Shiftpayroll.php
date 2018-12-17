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
 * @author Wu.Jinhe
 */
class Default_Form_Shiftpayroll extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('id', 'formid');
        $this->setAttrib('name', 'shiftpayroll');
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $loginUserId = $auth->getStorage()->read()->id;
            $loginuserRole = $auth->getStorage()->read()->emprole;
            $loginuserGroup = $auth->getStorage()->read()->group_id;
        }

        /**
         *  Define Form Elements
         *
         */
        $id = new Zend_Form_Element_Hidden('id');

        $employee_id = new Zend_Form_Element_Select('employee_id');
        $employee_id->setAttrib('title', 'Employee Code.');
        $employee_id->setAttrib('class', 'formDataElement');
        $employee_id->setAttrib('onChange', 'getEmployeeName(this)');
        $employee_id->addMultiOptions(array(''=>'Select Employee'));

//        $employee_id = new Zend_Form_Element_Text('employee_id');
//        $employee_id->setAttrib('maxLength', 20);
//        $employee_id->setAttrib('title', 'Employee ID.');
//        $employee_id->addFilter(new Zend_Filter_StringTrim());

        $employee_name = new Zend_Form_Element_Text('employee_name');
        $employee_name->setAttrib('maxLength', 30);
        $employee_name->setAttrib('title', 'Employee Name.');
        $employee_name->addFilter(new Zend_Filter_StringTrim());

        $shift1_hours = new Zend_Form_Element_Text('shift1_hours');
        $shift1_hours->setAttrib('maxLength', 10);
        $shift1_hours->setAttrib('title', 'Shift1 (08~16)');
        $shift1_hours->addFilter(new Zend_Filter_StringTrim());

        $shift21_hours = new Zend_Form_Element_Text('shift21_hours');
        $shift21_hours->setAttrib('maxLength', 10);
        $shift21_hours->setAttrib('title', 'Shift2 (16~19)');
        $shift21_hours->addFilter(new Zend_Filter_StringTrim());

        $shift22_hours = new Zend_Form_Element_Text('shift22_hours');
        $shift22_hours->setAttrib('maxLength', 10);
        $shift22_hours->setAttrib('title', 'Shift2 (19~22) 20%');
        $shift22_hours->addFilter(new Zend_Filter_StringTrim());

        $shift23_hours = new Zend_Form_Element_Text('shift23_hours');
        $shift23_hours->setAttrib('maxLength', 10);
        $shift23_hours->setAttrib('title', 'Shift2 (22~24) 50%');
        $shift23_hours->addFilter(new Zend_Filter_StringTrim());

        $shift31_hours = new Zend_Form_Element_Text('shift31_hours');
        $shift31_hours->setAttrib('maxLength', 10);
        $shift31_hours->setAttrib('title', 'Shift3 (00~06) 50%');
        $shift31_hours->addFilter(new Zend_Filter_StringTrim());

        $shift32_hours = new Zend_Form_Element_Text('shift32_hours');
        $shift32_hours->setAttrib('maxLength', 10);
        $shift32_hours->setAttrib('title', 'Shift3 (06~08)');
        $shift32_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift1_hours = new Zend_Form_Element_Text('sunday_shift1_hours');
        $sunday_shift1_hours->setAttrib('maxLength', 10);
        $sunday_shift1_hours->setAttrib('title', 'Sunday Shift1 (08~16) 25%');
        $sunday_shift1_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift21_hours = new Zend_Form_Element_Text('sunday_shift21_hours');
        $sunday_shift21_hours->setAttrib('maxLength', 10);
        $sunday_shift21_hours->setAttrib('title', 'Sunday Shift2 (16~19) 25%');
        $sunday_shift21_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift22_hours = new Zend_Form_Element_Text('sunday_shift22_hours');
        $sunday_shift22_hours->setAttrib('maxLength', 10);
        $sunday_shift22_hours->setAttrib('title', 'Sunday Shift2 (19~22) 45%');
        $sunday_shift22_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift23_hours = new Zend_Form_Element_Text('sunday_shift23_hours');
        $sunday_shift23_hours->setAttrib('maxLength', 10);
        $sunday_shift23_hours->setAttrib('title', 'Sunday Shift2 (22~24) 75%');
        $sunday_shift23_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift31_hours = new Zend_Form_Element_Text('sunday_shift31_hours');
        $sunday_shift31_hours->setAttrib('maxLength', 10);
        $sunday_shift31_hours->setAttrib('title', 'Sunday Shift3 (00~06) 75%');
        $sunday_shift31_hours->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift32_hours = new Zend_Form_Element_Text('sunday_shift32_hours');
        $sunday_shift32_hours->setAttrib('maxLength', 10);
        $sunday_shift32_hours->setAttrib('title', 'Sunday Shift3 (06~08) 25%');
        $sunday_shift32_hours->addFilter(new Zend_Filter_StringTrim());


        $holiday_shift1_hours = new Zend_Form_Element_Text('holiday_shift1_hours');
        $holiday_shift1_hours->setAttrib('maxLength', 10);
        $holiday_shift1_hours->setAttrib('title', 'Holiday Shift1 (08~16) 25%');
        $holiday_shift1_hours->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift21_hours = new Zend_Form_Element_Text('holiday_shift21_hours');
        $holiday_shift21_hours->setAttrib('maxLength', 10);
        $holiday_shift21_hours->setAttrib('title', 'Holiday Shift2 (16~19) 25%');
        $holiday_shift21_hours->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift22_hours = new Zend_Form_Element_Text('holiday_shift22_hours');
        $holiday_shift22_hours->setAttrib('maxLength', 10);
        $holiday_shift22_hours->setAttrib('title', 'Holiday Shift2 (19~22) 45%');
        $holiday_shift22_hours->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift23_hours = new Zend_Form_Element_Text('holiday_shift23_hours');
        $holiday_shift23_hours->setAttrib('maxLength', 10);
        $holiday_shift23_hours->setAttrib('title', 'Holiday Shift2 (22~24) 75%');
        $holiday_shift23_hours->addFilter(new Zend_Filter_StringTrim());


        $holiday_shift31_hours = new Zend_Form_Element_Text('holiday_shift31_hours');
        $holiday_shift31_hours->setAttrib('maxLength', 10);
        $holiday_shift31_hours->setAttrib('title', 'Holiday Shift3 (00~06) 75%');
        $holiday_shift31_hours->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift32_hours = new Zend_Form_Element_Text('holiday_shift32_hours');
        $holiday_shift32_hours->setAttrib('maxLength', 10);
        $holiday_shift32_hours->setAttrib('title', 'Holiday Shift3 (06~08) 25%');
        $holiday_shift32_hours->addFilter(new Zend_Filter_StringTrim());

        $total_hours = new Zend_Form_Element_Text('total_hours');
        $total_hours->setAttrib('maxLength', 10);
        $total_hours->setAttrib('title', 'Total Hours');
        $total_hours->addFilter(new Zend_Filter_StringTrim());

        $gross_salary_hours = new Zend_Form_Element_Text('gross_salary_hours');
        $gross_salary_hours->setAttrib('maxLength', 10);
        $gross_salary_hours->setAttrib('title', 'Gross Salary per hour');
        $gross_salary_hours->addFilter(new Zend_Filter_StringTrim());

        $shift1_salary = new Zend_Form_Element_Text('shift1_salary');
        $shift1_salary->setAttrib('maxLength', 10);
        $shift1_salary->setAttrib('title', 'Shift1 (08~16)');
        $shift1_salary->addFilter(new Zend_Filter_StringTrim());

        $shift21_salary = new Zend_Form_Element_Text('shift21_salary');
        $shift21_salary->setAttrib('maxLength', 10);
        $shift21_salary->setAttrib('title', 'Shift2 (16~19)');
        $shift21_salary->addFilter(new Zend_Filter_StringTrim());

        $shift22_salary = new Zend_Form_Element_Text('shift22_salary');
        $shift22_salary->setAttrib('maxLength', 10);
        $shift22_salary->setAttrib('title', 'Shift2 (19~22) 20%');
        $shift22_salary->addFilter(new Zend_Filter_StringTrim());

        $shift23_salary = new Zend_Form_Element_Text('shift23_salary');
        $shift23_salary->setAttrib('maxLength', 10);
        $shift23_salary->setAttrib('title', 'Shift2 (22~24) 50%');
        $shift23_salary->addFilter(new Zend_Filter_StringTrim());

        $shift31_salary = new Zend_Form_Element_Text('shift31_salary');
        $shift31_salary->setAttrib('maxLength', 10);
        $shift31_salary->setAttrib('title', 'Shift3 (00~06) 50%');
        $shift31_salary->addFilter(new Zend_Filter_StringTrim());

        $shift32_salary = new Zend_Form_Element_Text('shift32_salary');
        $shift32_salary->setAttrib('maxLength', 10);
        $shift32_salary->setAttrib('title', 'Shift3 (06~08)');
        $shift32_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift1_salary = new Zend_Form_Element_Text('sunday_shift1_salary');
        $sunday_shift1_salary->setAttrib('maxLength', 10);
        $sunday_shift1_salary->setAttrib('title', 'Sunday Shift1 (08~16) 25%');
        $sunday_shift1_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift21_salary = new Zend_Form_Element_Text('sunday_shift21_salary');
        $sunday_shift21_salary->setAttrib('maxLength', 10);
        $sunday_shift21_salary->setAttrib('title', 'Sunday Shift2 (16~19) 25%');
        $sunday_shift21_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift22_salary = new Zend_Form_Element_Text('sunday_shift22_salary');
        $sunday_shift22_salary->setAttrib('maxLength', 10);
        $sunday_shift22_salary->setAttrib('title', 'Sunday Shift2 (19~22) 45%');
        $sunday_shift22_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift23_salary = new Zend_Form_Element_Text('sunday_shift23_salary');
        $sunday_shift23_salary->setAttrib('maxLength', 10);
        $sunday_shift23_salary->setAttrib('title', 'Sunday Shift2 (22~24) 75%');
        $sunday_shift23_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift31_salary = new Zend_Form_Element_Text('sunday_shift31_salary');
        $sunday_shift31_salary->setAttrib('maxLength', 10);
        $sunday_shift31_salary->setAttrib('title', 'Sunday Shift3 (00~06) 75%');
        $sunday_shift31_salary->addFilter(new Zend_Filter_StringTrim());

        $sunday_shift32_salary = new Zend_Form_Element_Text('sunday_shift32_salary');
        $sunday_shift32_salary->setAttrib('maxLength', 10);
        $sunday_shift32_salary->setAttrib('title', 'Sunday Shift3 (06~08) 25%');
        $sunday_shift32_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift1_salary = new Zend_Form_Element_Text('holiday_shift1_salary');
        $holiday_shift1_salary->setAttrib('maxLength', 10);
        $holiday_shift1_salary->setAttrib('title', 'Holiday Shift1 (08~16) 25%');
        $holiday_shift1_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift21_salary = new Zend_Form_Element_Text('holiday_shift21_salary');
        $holiday_shift21_salary->setAttrib('maxLength', 10);
        $holiday_shift21_salary->setAttrib('title', 'Holiday Shift2 (16~19) 25%');
        $holiday_shift21_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift22_salary = new Zend_Form_Element_Text('holiday_shift22_salary');
        $holiday_shift22_salary->setAttrib('maxLength', 10);
        $holiday_shift22_salary->setAttrib('title', 'Holiday Shift2 (19~22) 45%');
        $holiday_shift22_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift23_salary = new Zend_Form_Element_Text('holiday_shift23_salary');
        $holiday_shift23_salary->setAttrib('maxLength', 10);
        $holiday_shift23_salary->setAttrib('title', 'Holiday Shift2 (22~24) 75%');
        $holiday_shift23_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift31_salary = new Zend_Form_Element_Text('holiday_shift31_salary');
        $holiday_shift31_salary->setAttrib('maxLength', 10);
        $holiday_shift31_salary->setAttrib('title', 'Holiday Shift3 (00~06) 75%');
        $holiday_shift31_salary->addFilter(new Zend_Filter_StringTrim());

        $holiday_shift32_salary = new Zend_Form_Element_Text('holiday_shift32_salary');
        $holiday_shift32_salary->setAttrib('maxLength', 10);
        $holiday_shift32_salary->setAttrib('title', 'Holiday Shift3 (06~08) 25%');
        $holiday_shift32_salary->addFilter(new Zend_Filter_StringTrim());

        $total_gross_salary = new Zend_Form_Element_Text('total_gross_salary');
        $total_gross_salary->setAttrib('maxLength', 10);
        $total_gross_salary->setAttrib('title', 'Total Gross Salary.');
        $total_gross_salary->addFilter(new Zend_Filter_StringTrim());


        /**
         *  Validate all Form Elements
         *
         */


        $employee_id ->setRequired(true);
        $employee_id->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));

        $employee_name->setRequired(true);
        $employee_name->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));




//        $gross_salary->setRequired(true);
//        $monthlygross_salary->addValidator('NotEmpty', false, array('messages' => 'Please select Employee ID.'));



        $shift1_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $shift21_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $shift22_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $shift23_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $shift31_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $shift32_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $sunday_shift1_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $sunday_shift21_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $sunday_shift22_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $sunday_shift23_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $sunday_shift31_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $sunday_shift32_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $holiday_shift1_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $holiday_shift21_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $holiday_shift22_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $holiday_shift23_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $holiday_shift31_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));

        $holiday_shift32_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $total_hours->addValidator("regex",true,array(
            'pattern'=>'/^([0-9]+?)+$/',
            'messages'=>array(
                'regexNotMatch'=>'Please enter only numbers.'
            )
        ));
        $gross_salary_hours->addValidator("regex",true,array(
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



        $this->addElements(array($id, $submit, $employee_id, $employee_name, $shift1_hours,$shift21_hours,$shift22_hours,$shift23_hours,$shift31_hours,$shift32_hours,
            $sunday_shift1_hours,$sunday_shift21_hours,$sunday_shift22_hours,$sunday_shift23_hours,$sunday_shift31_hours,$sunday_shift32_hours,$holiday_shift1_hours,
            $holiday_shift21_hours,$holiday_shift22_hours,$holiday_shift23_hours,$holiday_shift31_hours,$holiday_shift32_hours,$total_hours,$gross_salary_hours,
            $shift1_salary,$shift21_salary,$shift22_salary,$shift23_salary,$shift31_salary,$shift32_salary,$sunday_shift1_salary,$sunday_shift21_salary,$sunday_shift22_salary,
            $sunday_shift23_salary,$sunday_shift31_salary,$sunday_shift32_salary,$holiday_shift1_salary,$holiday_shift21_salary,$holiday_shift22_salary,$holiday_shift23_salary,
            $holiday_shift31_salary,$holiday_shift32_salary,$total_gross_salary));
        $this->setElementDecorators(array('ViewHelper'));

    }
}