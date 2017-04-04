<?php
 
namespace GroupON\Methods;
 
use Plenty\Plugin\ConfigRepository;

class GroupOnPickupDataMethod
{
    public function getSupplierID( ConfigRepository $configRepository ):string
    {
        $supplierID = $configRepository->get('GroupON.supplierID');
 
        if(!strlen($supplierID))
        {
            $supplierID = 'Enter Supplier ID';
        }
 
        return $supplierID;
 
    }
    
    public function getToken( ConfigRepository $configRepository ):string
    {
        $token = $configRepository->get('GroupON.token');
 
        if(!strlen($token))
        {
            $token = 'Enter Supplier ID';
        }
 
        return $token;
 
    }
}