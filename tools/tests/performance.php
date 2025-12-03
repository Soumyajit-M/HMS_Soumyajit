<?php
require_once __DIR__.'/_helper.php';
$base=getenv('TEST_BASE')?:'http://127.0.0.1:8000';
putenv('CI_AUTH_BYPASS=1');
function perf_run($urls,$concurrency=5){
  $mh=curl_multi_init();$chs=[];$times=[];$start=microtime(true);
  $pending=$urls;for($i=0;$i<$concurrency&&count($pending)>0;$i++){ $u=array_shift($pending);$ch=curl_init($u);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);curl_multi_add_handle($mh,$ch);$chs[(int)$ch]=$ch;}
  do{curl_multi_exec($mh,$active);$info=curl_multi_info_read($mh);if($info && $info['handle']){ $ch=$info['handle'];$times[]=[curl_getinfo($ch,CURLINFO_EFFECTIVE_URL),curl_getinfo($ch,CURLINFO_TOTAL_TIME)];curl_multi_remove_handle($mh,$ch);curl_close($ch);unset($chs[(int)$ch]);if(count($pending)>0){$u=array_shift($pending);$ch=curl_init($u);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);curl_multi_add_handle($mh,$ch);$chs[(int)$ch]=$ch;}}
    usleep(10000);
  }while($active||count($chs)>0);
  curl_multi_close($mh);$end=microtime(true);
  $durms=round(($end-$start)*1000,2);$lat=array_map(function($x){return round($x[1]*1000,2);},$times);
  sort($lat);$sum=array_sum($lat);$avg=round($sum/max(count($lat),1),2);$p95=$lat?($lat[(int)floor(0.95*(count($lat)-1))]):0;$max=$lat?max($lat):0;$min=$lat?$lat[0]:0;
  return ['count'=>count($lat),'total_ms'=>$durms,'avg_ms'=>$avg,'p95_ms'=>$p95,'min_ms'=>$min,'max_ms'=>$max];
}
$res=['passed'=>true,'metrics'=>[]];
try{
  $urls=[];$n=20;for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/dashboard-stats.php';}
  $res['metrics']['dashboard']=perf_run($urls,5);
  $urls=[];for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/doctors.php';}
  $res['metrics']['doctors']=perf_run($urls,5);
  $urls=[];for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/patients.php';}
  $res['metrics']['patients']=perf_run($urls,5);
  $urls=[];for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/reports-api.php?type=billing';}
  $res['metrics']['reports_api_billing']=perf_run($urls,5);
  $urls=[];for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/export.php?type=billing';}
  $res['metrics']['export_billing']=perf_run($urls,5);
  $urls=[];for($i=0;$i<$n;$i++){ $urls[]=$base.'/api/reports-print.php?type=billing';}
  $res['metrics']['reports_print_billing']=perf_run($urls,5);
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
