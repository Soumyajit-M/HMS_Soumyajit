<?php
require_once __DIR__.'/_helper.php';
require_once __DIR__.'/../../classes/Validation.php';
$res=['passed'=>true,'cases'=>[]];
try{
  $r=Validation::validateEmail('');$res['cases'][]=['case'=>'email empty','valid'=>$r['valid']??false];assert_true(!$r['valid']);
  $r=Validation::validateEmail('x@');$res['cases'][]=['case'=>'email bad','valid'=>$r['valid']??false];assert_true(!$r['valid']);
  $r=Validation::validateEmail('a@b.com');$res['cases'][]=['case'=>'email ok','valid'=>$r['valid']??false];assert_true($r['valid']);
  $r=Validation::validatePhone('');$res['cases'][]=['case'=>'phone empty','valid'=>$r['valid']??false];assert_true(!$r['valid']);
  $r=Validation::validatePhone('123');$res['cases'][]=['case'=>'phone short','valid'=>$r['valid']??false];assert_true(!$r['valid']);
  $r=Validation::validatePhone('1234567890');$res['cases'][]=['case'=>'phone ok','valid'=>$r['valid']??false];assert_true($r['valid']);
  $r=Validation::validateRequired('','name');$res['cases'][]=['case'=>'required empty','valid'=>$r['valid']??false];assert_true(!$r['valid']);
  $r=Validation::validateRequired('ok','name');$res['cases'][]=['case'=>'required ok','valid'=>$r['valid']??false];assert_true($r['valid']);
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
