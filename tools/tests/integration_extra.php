<?php
require_once __DIR__.'/_helper.php';
$base=getenv('TEST_BASE')?:'http://127.0.0.1:8000';
$res=['passed'=>true,'checks'=>[]];
try{
  // Login to establish session
  list($c,$b,$j)=http_json_cookie($base.'/index.php',[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['username'=>'admin','password'=>'password'])]);

  // Inventory
  list($code,$body,$ctype)=http_cookie($base.'/api/inventory.php');
  $res['checks'][]=['endpoint'=>'inventory GET','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Laboratory
  list($code,$body,$ctype)=http_cookie($base.'/api/laboratory.php');
  $res['checks'][]=['endpoint'=>'laboratory GET','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Rooms
  list($code,$body,$ctype)=http_cookie($base.'/api/rooms.php');
  $res['checks'][]=['endpoint'=>'rooms GET','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Schedules
  list($code,$body,$ctype)=http_cookie($base.'/api/schedules.php');
  $res['checks'][]=['endpoint'=>'schedules GET','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Reports API (billing)
  list($code,$body,$ctype)=http_cookie($base.'/api/reports-api.php?type=billing');
  $res['checks'][]=['endpoint'=>'reports-api billing','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Reports print
  list($code,$body,$ctype)=http_cookie($base.'/api/reports-print.php?type=billing');
  $res['checks'][]=['endpoint'=>'reports-print billing','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

  // Export CSV
  list($code,$body,$ctype)=http_cookie($base.'/api/export.php?type=billing');
  $res['checks'][]=['endpoint'=>'export billing','code'=>$code,'content_type'=>$ctype];assert_true($code===200);

}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
