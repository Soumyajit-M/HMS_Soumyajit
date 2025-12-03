<?php
function th_now(){return microtime(true);} 
function th_out($s){echo $s;} 
function th_json($v){return json_encode($v);} 
function http_json($url,$opts=[]){$ch=curl_init($url);$def=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>10,CURLOPT_HTTPHEADER=>['Accept: application/json']];foreach($opts as $k=>$v){$def[$k]=$v;}curl_setopt_array($ch,$def);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return [$code,$body,json_decode($body,true)];}
function th_cookie(){static $p=null; if($p===null){$p=__DIR__.'/cookies.txt'; @unlink($p);} return $p;}
function http_json_cookie($url,$opts=[]){$ch=curl_init($url);$def=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>10,CURLOPT_COOKIEJAR=>th_cookie(),CURLOPT_COOKIEFILE=>th_cookie(),CURLOPT_HTTPHEADER=>['Accept: application/json']];foreach($opts as $k=>$v){$def[$k]=$v;}curl_setopt_array($ch,$def);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return [$code,$body,json_decode($body,true)];}
function http_cookie($url,$opts=[]){$ch=curl_init($url);$def=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIEJAR=>th_cookie(),CURLOPT_COOKIEFILE=>th_cookie()];foreach($opts as $k=>$v){$def[$k]=$v;}curl_setopt_array($ch,$def);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$ctype=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);curl_close($ch);return [$code,$body,$ctype];}
class TestHarness{
  public $results=[];public $start=0;public $end=0;
  function start(){$this->start=th_now();}
  function end(){
    $this->end=th_now();
    $this->results['summary']['duration_ms']=round(($this->end-$this->start)*1000,2);
  }
  function record($name,$passed,$details=[]){
    $this->results['tests'][]=['name'=>$name,'passed'=>$passed,'details'=>$details];
  }
  function summary(){
    $t=$this->results['tests']??[];$p=0;foreach($t as $x){if($x['passed']){$p++;}}
    $this->results['summary']=['total'=>count($t),'passed'=>$p,'failed'=>count($t)-$p];
    return $this->results['summary'];
  }
  function output(){
    $this->summary();
    th_out(th_json($this->results));
  }
}
function assert_true($cond,$msg=''){if(!$cond)throw new Exception($msg?:'assert_true failed');}
function assert_eq($a,$b,$msg=''){if($a!=$b)throw new Exception($msg?:('assert_eq failed: '.var_export($a,true).' != '.var_export($b,true)));}
?>
