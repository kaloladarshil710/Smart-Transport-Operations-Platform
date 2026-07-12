<?php
/** Driver input validation and compliance checks. */
declare(strict_types=1);

function validateDriverInput(array $input, ?int $driverId = null): array
{
    $errors=[]; $required=['full_name'=>'Driver name','email'=>'Email','phone'=>'Phone','license_number'=>'License number','license_expiry'=>'License expiry','region_id'=>'Region'];
    foreach ($required as $field=>$label) if (trim((string)($input[$field] ?? ''))==='') $errors[$field]="$label is required.";
    if (!empty($input['email']) && !filter_var((string)$input['email'],FILTER_VALIDATE_EMAIL)) $errors['email']='Enter a valid email address.';
    if (!empty($input['license_expiry']) && strtotime((string)$input['license_expiry'])===false) $errors['license_expiry']='Enter a valid license expiry date.';
    if (!empty($input['date_of_birth']) && strtotime((string)$input['date_of_birth']) > strtotime('-18 years')) $errors['date_of_birth']='Drivers must be at least 18 years old.';
    if (isset($input['safety_score']) && ($input['safety_score']!=='' && ((float)$input['safety_score']<0 || (float)$input['safety_score']>100))) $errors['safety_score']='Safety score must be between 0 and 100.';
    if (!driverFieldIsUnique('email',(string)($input['email'] ?? ''),$driverId)) $errors['email']='This email is already assigned to a driver.';
    if (!driverFieldIsUnique('phone',(string)($input['phone'] ?? ''),$driverId)) $errors['phone']='This phone number is already assigned to a driver.';
    if (!driverFieldIsUnique('license_number',(string)($input['license_number'] ?? ''),$driverId)) $errors['license_number']='This license number is already assigned to a driver.';
    return $errors;
}
