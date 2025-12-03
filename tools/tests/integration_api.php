<?php
require_once __DIR__.'/_helper.php';
$base=getenv('TEST_BASE')?:'http://127.0.0.1:8000';
putenv('CI_AUTH_BYPASS=1');
$res=['passed'=>true,'checks'=>[]];
try{
  // Establish session via login
  list($code,$body,$j)=http_json_cookie($base.'/index.php',[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['username'=>'admin','password'=>'password'])]);
  // Basic checks
  list($code,$body,$j)=http_json_cookie($base.'/api/dashboard-stats.php');$res['checks'][]=['endpoint'=>'dashboard-stats','code'=>$code];assert_true($code>=200&&$code<500);
  list($code,$body,$j)=http_json_cookie($base.'/api/doctors.php');$res['checks'][]=['endpoint'=>'doctors GET','code'=>$code,'has_key'=>isset($j['doctors'])];assert_true(isset($j['doctors']));
  $doc=['first_name'=>'Int','last_name'=>'Test','email'=>'int@test.local','phone'=>'0000000000','specialization'=>'General'];
  list($code,$body,$j)=http_json_cookie($base.'/api/doctors.php',[CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>th_json($doc),CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);$res['checks'][]=['endpoint'=>'doctors POST','code'=>$code,'success'=>$j['success']??null];assert_true(($j['success']??false)==true);
  $pidData=['first_name'=>'Int','last_name'=>'Pat','email'=>'int+pat@test.local','phone'=>'1234567890','date_of_birth'=>'1990-01-01','gender'=>'male'];
  list($code,$body,$j)=http_json_cookie($base.'/api/patients.php',[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pidData]);$patientId=$j['patient_id']??null;$res['checks'][]=['endpoint'=>'patients POST','code'=>$code,'patient_id'=>$patientId];assert_true(!empty($patientId));
  $appt=['patient_id'=>$patientId,'doctor_id'=>$j['doctor_id']??1,'appointment_date'=>date('Y-m-d',strtotime('+1 day')),'appointment_time'=>'09:00','reason'=>'Test'];
  list($code,$body,$aj)=http_json_cookie($base.'/api/appointments.php',[CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>th_json($appt),CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);$appointmentId=$aj['appointment_id']??($aj['appointment']['id']??null);$res['checks'][]=['endpoint'=>'appointments POST','code'=>$code,'appointment_id'=>$appointmentId];
  $bill=['patient_id'=>$patientId,'appointment_id'=>$appointmentId,'due_date'=>date('Y-m-d',strtotime('+7 days')),'total_amount'=>50,'items'=>[['item_name'=>'Consult','quantity'=>1,'unit_price'=>50,'total_price'=>50]]];
  list($code,$body,$bj)=http_json_cookie($base.'/api/billing.php',[CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>th_json($bill),CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);if(!is_array($bj)){$bj=json_decode(substr($body,strpos($body,'{')!==false?strpos($body,'{'):0),true);} $billId=$bj['billing_id']??($bj['bill']['id']??null);$res['checks'][]=['endpoint'=>'billing POST','code'=>$code,'billing_id'=>$billId];
  $pay=['billing_id'=>$billId,'amount'=>50,'payment_method'=>'cash','transaction_id'=>'TXN'.rand(1000,9999)];
  list($code,$body,$pj)=http_json_cookie($base.'/api/payments.php',[CURLOPT_CUSTOMREQUEST=>'POST',CURLOPT_POSTFIELDS=>th_json($pay),CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);if(!is_array($pj)){$pj=json_decode(substr($body,strpos($body,'{')!==false?strpos($body,'{'):0),true);} $res['checks'][]=['endpoint'=>'payments POST','code'=>$code,'success'=>$pj['success']??null];
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
