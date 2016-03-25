<?php
$sub_menu = '780300';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

$_POST = array_map('trim', $_POST);
if (isset($_REQUEST['bk_ix'])) {
    $bk_ix = (int)$_REQUEST['bk_ix'];
} else {
    $bk_ix = '';
}

if($w == 'd') {

    auth_check($auth[$sub_menu], 'd');

    sql_query(" delete from {$g5['wzp_booking_table']} where bk_ix = '{$bk_ix}' ");

    goto_url('./wzp_booking_list.php?'.$qstr);

} 
else {

    auth_check($auth[$sub_menu], 'w');

    $mb_id              = isset($_POST['mb_id'])            ? trim($_POST['mb_id'])                 : "";
    $bk_name            = isset($_POST['bk_name'])          ? trim($_POST['bk_name'])               : "";
    $bk_hp1             = isset($_POST['bk_hp1'])           ? trim($_POST['bk_hp1'])                : "";
    $bk_hp2             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp2'])                : "";
    $bk_hp3             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp3'])                : "";
    $bk_email           = isset($_POST['bk_email'])         ? trim($_POST['bk_email'])              : "";
    $bk_memo            = isset($_POST['bk_memo'])          ? trim($_POST['bk_memo'])               : "";
    $bk_payment         = isset($_POST['bk_payment'])       ? trim($_POST['bk_payment'])            : "";
    $bk_deposit_name    = isset($_POST['bk_deposit_name'])  ? trim($_POST['bk_deposit_name'])       : "";
    $bk_bank_account    = isset($_POST['bk_bank_account'])  ? trim($_POST['bk_bank_account'])       : "";
    $bk_status          = isset($_POST['bk_status'])        ? trim($_POST['bk_status'])             : "";
    
    $bk_misu            = isset($_POST['bk_misu'])          ? trim($_POST['bk_misu'])               : "";

    $bk_email           = wz_get_email_address($bk_email);
    $bk_hp1             = preg_replace('/[^0-9]/', '', $bk_hp1);
    $bk_hp2             = preg_replace('/[^0-9]/', '', $bk_hp2);
    $bk_hp3             = preg_replace('/[^0-9]/', '', $bk_hp3);
    if ($bk_hp1 && $bk_hp2 && $bk_hp3) { 
        $bk_hp = $bk_hp1 .'-'. $bk_hp2 .'-'. $bk_hp3;
    } 
   
    $bk_misu            = (int)$bk_misu;

    $sql_common = " mb_id               = '{$mb_id}',
                    bk_name             = '{$bk_name}',
                    bk_hp               = '{$bk_hp}',
                    bk_email            = '{$bk_email}',
                    bk_memo             = '{$bk_memo}',
                    bk_status           = '{$bk_status}',
                    bk_misu             = '{$bk_misu}'
                    ";
}

if($w == '') {

    $sql = " insert into {$g5['wzp_booking_table']}
                set $sql_common  ";
                echo $sql;
    sql_query($sql);

    $bk_ix = wz_sql_insert_id();

    goto_url('./wzp_booking_list.php');

} else if($w == 'u') {

    if ($bk_status == '완료') { // 완료일경우 객실상태정보를 완료상태로 변경.
        
        $query = "update {$g5['wzp_room_status_table']} set rms_status = '예약완료' where bk_ix = '{$bk_ix}' ";
        sql_query($query);

    } 
    else if ($bk_status == '대기') { // 대기일경우 객실예약상태를 대기상태로 변경.
        
        $query = "update {$g5['wzp_room_status_table']} set rms_status = '예약대기' where bk_ix = '{$bk_ix}' ";
        sql_query($query);

    } 

    $sql = " update {$g5['wzp_booking_table']}
                set $sql_common 
                where bk_ix = '{$bk_ix}' ";
    sql_query($sql);

    goto_url('./wzp_booking_view.php?w=u&amp;bk_ix='.$bk_ix.'&amp;'.$qstr);

} else if($w == 'kd') { // 객실개별정보 삭제

    $bkr_ix = (int)$_GET['bkr_ix'];

    $query = "select * from {$g5['wzp_booking_room_table']} where bkr_ix = '$bkr_ix'";
    $bkr = sql_fetch($query);
    $bkr_misu = $bkr['bkr_price'] + $bkr['bkr_price_adult'];
    $bk_ix = $bkr['bk_ix'];

    $query = "delete from {$g5['wzp_room_status_table']} where bk_ix = '{$bkr['bk_ix']}' and rm_ix = '{$bkr['rm_ix']}' ";
    sql_query($query);

    $query = "delete from {$g5['wzp_booking_room_table']} where bkr_ix = '{$bkr['bkr_ix']}' ";
    sql_query($query);  

    // 삭제후 금액 재계산.
    $query = "select sum(bkr_price + bkr_price_adult) as bkr_price, count(*) as cnt, bkr_subject from {$g5['wzp_booking_room_table']} where bk_ix = '$bk_ix'";
    $row = sql_fetch($query);

    $query = "update {$g5['wzp_booking_table']} set 
                    bk_subject = '".$row['bkr_subject']. ($row['cnt']>1 ? ' 외'.($row['cnt']-1).'건' : '') ."', 
                    bk_cnt_room = '".$row['cnt']."', 
                    bk_price = '".$row['bkr_price']."',
                    bk_misu = bk_misu - ".$bkr_misu."
            where bk_ix = '{$bk_ix}' ";
    sql_query($query);

    goto_url('./wzp_booking_view.php?w=u&amp;bk_ix='.$bk_ix.'&amp;'.$qstr);

}
?>