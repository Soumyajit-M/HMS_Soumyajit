<?php
require_once __DIR__.'/_helper.php';
$base=getenv('TEST_BASE')?:'http://127.0.0.1:8000';
$res=['passed'=>true,'output'=>''];
try{
  putenv('CI_AUTH_BYPASS=0');
  $cmd='php "'.str_replace('"','""',__DIR__.'/../full_smoke_test.php').'"';
  $out=shell_exec($cmd.' 2>&1');
  $res['output']=$out;
  $ok=(strpos($out,'Smoke test completed')!==false);
  if(!$ok){$res['passed']=false;}
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
