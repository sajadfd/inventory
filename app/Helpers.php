<?php


use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;

if (!function_exists('t')) {

    // this function for merege file and key
    function t($key, $arr = []): \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator|\Illuminate\Contracts\Foundation\Application|null
    {
        $currntLocale = LaravelLocalization::getCurrentLocale();
        if (count($arr) > 0)
            return trans($currntLocale . '.' . $key, $arr);
        return trans($currntLocale . '.' . $key);
    }// end of t
}// end of if

if (!function_exists('multipleHash')) {
    function multipleHash($token = false): string
    {
        if ($token) {
            $hashTypes = [1 => 'md5', 2 => 'bcrypt', 3 => 'argon2', 4 => 'sha-256', 5 => 'sha-1'];
            $index = rand(1, 5);
            $alg = $hashTypes[$index] ? $hashTypes[$index] : 'bcrypt';
            return $alg;
        } else {
            return 'bcrypt';
        }//end of if

    } // /multipleHash
}// end of if

if (!function_exists('checkValueExists')) {
    function checkValueExists($array, $value): bool
    {
        foreach ($array as $item) {
            if (is_array($item) || is_object($item)) {
                // Recursively check nested arrays or objects
                if (checkValueExists((array)$item, $value)) {
                    return true;
                }
            } elseif ($item === $value) {
                return true;
            }
        }

        return false;
    }// /$array, $value
}// /checkValueExists

if(!function_exists('isValidPhoneNumber')){
    function isValidPhoneNumber($phone,$code): bool{

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        try {

            if($code){
                $phoneNumber = $phoneNumberUtil->parse($phone, $code);

                if ($phoneNumberUtil->isValidNumber($phoneNumber)) {
                    // Phone number is valid
                    $formattedNumber = $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
                    $numberType = $phoneNumberUtil->getNumberType($phoneNumber);

                    error_log('$formattedNumber : '.$formattedNumber);
                    error_log('$numberType : '.$numberType);

                    return true ;
                    // Use the $formattedNumber and $numberType as needed
                } else {
                    return false ;
                }
            }else{
                return false ;
            }// /if
        } catch (\libphonenumber\NumberParseException $e) {
            error_log($e->getMessage());
            return false ;
            // Error occurred while parsing the phone number
        }// /try catch
    }// /isValidPhoneNumber

}// /if

