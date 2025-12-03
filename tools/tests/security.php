<?php
require_once __DIR__.'/_helper.php';
$base=getenv('TEST_BASE')?:'http://127.0.0.1:8000';
$res=['passed'=>true,'checks'=>[]];
try{
  putenv('CI_AUTH_BYPASS=0');
  list($code,$body,$j)=http_json($base.'/api/patients.php');$res['checks'][]=['endpoint'=>'patients unauth GET','message'=>$j['message']??null];assert_true(($j['success']??false)==false);
  putenv('CI_AUTH_BYPASS=1');
  $inj=['first_name'=>'John','last_name'=>'Doe','email'=>'x@test.local','phone'=>'1234567890','date_of_birth'=>'1990-01-01','gender'=>'male','address'=>'123','emergency_contact_name'=>'x','emergency_contact_phone'=>'1234567890','emergency_contact_email'=>'z@test','blood_type'=>'A+','allergies'=>'none','medical_history'=>'none','insurance_provider'=>'y','insurance_number'=>'1 OR 1=1'];
  list($code,$body,$j)=http_json($base.'/api/patients.php',[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$inj]);$res['checks'][]=['endpoint'=>'patients POST inj','code'=>$code,'success'=>$j['success']??null];assert_true(isset($j['success']));
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
