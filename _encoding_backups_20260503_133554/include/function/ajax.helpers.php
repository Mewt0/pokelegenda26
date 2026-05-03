<?php
// include/function/ajax.helpers.php (Р С•Р С—РЎвЂ Р С‘Р С•Р Р…Р В°Р В»РЎРЉР Р…Р С•)
if (!function_exists('is_ajax')) {
  function is_ajax(){
    return (!empty($_GET['ajax']) && $_GET['ajax']=='1')
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest');
  }
}
if (!function_exists('jexit')) {
  function jexit($arr){ header('Content-Type: application/json; charset=UTF-8'); echo json_encode($arr); exit; }
}
if (!function_exists('resp_ok')) {
  function resp_ok($isAjax, $redirect=null, $msg=null){
    if ($isAjax) jexit(array('ok'=>true,'redirect'=>$redirect,'msg'=>$msg));
    if ($redirect) die('<script>location.href='.json_encode($redirect).';</script>');
    if ($msg) die('<script>alert('.json_encode($msg).');</script>');
    exit;
  }
}
if (!function_exists('resp_err')) {
  function resp_err($isAjax, $msg='Р С›РЎв‚¬Р С‘Р В±Р С”Р В°'){
    if ($isAjax) jexit(array('ok'=>false,'msg'=>$msg));
    // РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р в„– РЎР‚Р ВµР В¶Р С‘Р С (РЎвЂћРЎР‚Р ВµР в„–Р СРЎвЂ№/РЎРѓР С”РЎР‚Р С‘Р С—РЎвЂљРЎвЂ№)
    die('<script>alert('.json_encode($msg).');</script>');
  }
}
