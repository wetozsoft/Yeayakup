<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');


$uid    = isset($_REQUEST['uid']) ? trim($_REQUEST['uid']) : "";
$od_id  = (int)$_REQUEST['od_id'];

if (!$od_id)
    die('{"rescd":"99","restx":"잘못된 접근입니다."}');

if ($mode == 'cancel') { // 예약정보취소

    if (!$is_member) {
        if (get_session('ss_orderview_uid') != $uid)
            die('{"rescd":"98","restx":"잘못된 접근입니다."}');
    }

    $sql = "select * from {$g5['wzp_booking_table']} where od_id = '$od_id' ";
    if($is_member)
        $sql .= " and mb_id = '{$member['mb_id']}' ";
    $bk = sql_fetch($sql);
    if (!$bk['od_id'] || (!$is_member && md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip']) != get_session('ss_orderview_uid'))) {
        die('{"rescd":"97","restx":"조회하실 예약정보가 없습니다."}');
    }

    if ($bk['bk_status'] == '완료') { 
        die('{"rescd":"96","restx":"예약이 완료된 정보이므로 취소가 불가능합니다."}');
    } 
    else {

        // 객실예약정보 변경
        $query = " update {$g5['wzp_booking_table']} set bk_status = '취소' where bk_ix = '{$bk['bk_ix']}' ";
        sql_query($query);

        // 객실상태정보 변경
        $query = " update {$g5['wzp_room_status_table']} set rms_status = '취소' where bk_ix = '{$bk['bk_ix']}' ";
        sql_query($query);

    }
} 

die('{"rescd":"00","restx":""}');
?>