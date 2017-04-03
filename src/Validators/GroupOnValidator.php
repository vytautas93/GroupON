<?php
 
namespace GroupON\Validators;
 
use Plenty\Validation\Validator;
 
/**
 *  Validator Class
 */
class GroupOnValidator extends Validator
{
    protected function defineAttributes()
    {
        $this->addString('supplierID', true);
        $this->addString('token', true);
    }
}