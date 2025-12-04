<?php
$base=isset($argv[1])?$argv[1]:'http://127.0.0.1:8000';
putenv('TEST_BASE='.$base);
require_once __DIR__.'/tests/_helper.php';
$h=new TestHarness();
$h->start();
$tests=[
  __DIR__.'/tests/unit_validation.php',
  __DIR__.'/tests/unit_auth.php',
  __DIR__.'/tests/unit_auth_roles.php',
  __DIR__.'/tests/integration_api.php',
  __DIR__.'/tests/integration_extra.php',
  __DIR__.'/tests/e2e_workflow.php',
  __DIR__.'/tests/performance.php',
  __DIR__.'/tests/security.php'
];
foreach($tests as $t){
  $name=basename($t);
  $ok=false;$det=[];
  try{
    $det=require $t;
    $ok=is_array($det)?($det['passed']??false):false;
    $h->record($name,$ok,$det);
  }catch(Throwable $e){
    $h->record($name,false,['error'=>$e->getMessage()]);
  }
}
$h->end();
$h->output();
?>
