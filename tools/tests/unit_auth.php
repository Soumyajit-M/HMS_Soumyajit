<?php
require_once __DIR__.'/_helper.php';
require_once __DIR__.'/../../classes/Auth.php';
$res=['passed'=>true,'cases'=>[]];
try{
  $a=new Auth();
  $r=$a->login('nonexistent','bad');$res['cases'][]=['case'=>'login invalid','success'=>$r['success']??null];assert_true(!$r['success']);
  $logged=$a->isLoggedIn();$res['cases'][]=['case'=>'isLoggedIn after invalid','value'=>$logged];assert_true(!$logged);
  $r=$a->logout();$res['cases'][]=['case'=>'logout','success'=>$r['success']??null];assert_true($r['success']);
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
