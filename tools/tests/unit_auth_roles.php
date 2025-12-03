<?php
require_once __DIR__.'/_helper.php';
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../classes/Auth.php';
$res=['passed'=>true,'cases'=>[]];
try{
  $a=new Auth();
  $uname='doctor_'.substr(md5(uniqid('',true)),0,8);
  $data=[
    'username'=>$uname,
    'email'=>$uname.'@test.local',
    'password'=>'P@ssw0rd!',
    'role'=>'doctor',
    'first_name'=>'Role','last_name'=>'Test','phone'=>'1234567890','address'=>'N/A'
  ];
  $reg=$a->register($data);$res['cases'][]=['case'=>'register doctor user','success'=>$reg['success']??null];assert_true(($reg['success']??false)==true);
  $login=$a->login($uname,'P@ssw0rd!');$res['cases'][]=['case'=>'login doctor','success'=>$login['success']??null];assert_true(($login['success']??false)==true);
  $hasDoc=$a->hasRole('doctor');$res['cases'][]=['case'=>'hasRole doctor','value'=>$hasDoc];assert_true($hasDoc===true);
  $hasAdmin=$a->hasRole('admin');$res['cases'][]=['case'=>'hasRole admin','value'=>$hasAdmin];assert_true($hasAdmin===false);
  $any=$a->hasAnyRole(['admin','staff']);$res['cases'][]=['case'=>'hasAnyRole admin/staff','value'=>$any];assert_true($any===false);
  $a->logout();
  // cleanup user
  $db=new Database();$conn=$db->getConnection();
  $stmt=$conn->prepare('DELETE FROM users WHERE username = :u');$stmt->bindParam(':u',$uname);$stmt->execute();
}catch(Throwable $e){$res=['passed'=>false,'error'=>$e->getMessage()];}
return $res;
?>
