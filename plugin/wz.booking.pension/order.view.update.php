<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

if (isset($_REQUEST['ix']) && $_REQUEST['ix'])
    $bk_ix = (int)$_REQUEST['ix'];
else
    alert("잘못된 접근입니다.", WZP_STATUS_URL);

$uid = isset($_REQUEST['uid']) ? trim($_REQUEST['uid']) : "";

if ($w == 'c') { // 예약정보취소
    
    $query = "select bk_status from {$g5['wzp_booking_table']} where bk_ix = '{$bk_ix}' ";
    $row = sql_fetch($query);

    if ($row['bk_status'] == '완료') { 
        alert("예약이 완료된 정보이므로 취소가 불가능합니다.", WZP_STATUS_URL.'&mode=orderdetail&ix='.$bk_ix.'&uid='.$uid);
    } 
    else {

        $query = "delete from {$g5['wzp_room_status_table']} where bk_ix = '$bk_ix' "; // 객실상태정보 삭제
        sql_query($query);

        $query = "delete from {$g5['wzp_booking_room_table']} where bk_ix = '$bk_ix' "; // 객실예약룸정보 삭제
        sql_query($query);  

        $query = "update {$g5['wzp_booking_table']} set 
                        bk_status = '취소', 
                        bk_cnt_room = '0'
                where bk_ix = '{$bk_ix}' ";
        sql_query($query);
    }

    goto_url(WZP_STATUS_URL.'&mode=step3&od_id='.$od_id.'&amp;uid='.$uid);
} 
?>