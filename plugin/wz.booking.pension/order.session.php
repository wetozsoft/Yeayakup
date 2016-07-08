<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

$uid    = $_POST['uid'];
$od_id  = (int)$_POST['od_id'];

if ($is_member) { // 회원은 저장할 필요가 없음.
    
}
else {
    $sql = "select od_id, bk_time, bk_ip from {$g5['wzp_booking_table']} where od_id = '$od_id' ";
    $bk = sql_fetch($sql);
    $uid2 = md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip']);
    if ($uid = $uid2) { 
        set_session('ss_orderview_uid', $uid2);    
    }     
}

die('{"rescd":"00","restx":""}');
?>