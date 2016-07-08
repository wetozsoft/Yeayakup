<?php
$sub_menu = '780300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$_POST = array_map('trim', $_POST);
if (isset($_REQUEST['bk_ix'])) {
    $bk_ix = (int)$_REQUEST['bk_ix'];
} else {
    $bk_ix = '';
}


if ($mode == 'pay') { 

    if (!$bk_ix) { 
        alert("잘못된 접근입니다.");
    }
    
    $bk_status          = isset($_POST['bk_status'])        ? trim($_POST['bk_status'])             : "";
    $bk_misu            = isset($_POST['bk_misu'])          ? trim($_POST['bk_misu'])               : "";
    $bk_receipt_price   = isset($_POST['bk_receipt_price']) ? trim($_POST['bk_receipt_price'])      : "";
    $bk_cancel          = isset($_POST['bk_cancel'])        ? trim($_POST['bk_cancel'])             : "";

    $bk_misu            = (int)$bk_misu;
    $bk_receipt_price   = (int)$bk_receipt_price;

    $sql_common = " bk_status           = '{$bk_status}',
                    bk_misu             = '{$bk_misu}',
                    bk_receipt_price    = '{$bk_receipt_price}'
                    ";

    $query = "update {$g5['wzp_room_status_table']} set rms_status = '{$bk_status}' where bk_ix = '{$bk_ix}' ";
    sql_query($query);


    $sql = " select * from {$g5['wzp_booking_table']} where bk_ix = '$bk_ix' ";
    $bk = sql_fetch($sql);
    $tno = $bk['bk_tno'];

    // pg 결제처리.
    $pg_res_cd = '';

    if ($bk_cancel) { 

        include_once(WZP_PLUGIN_PATH.'/gender/'.$bk['bk_pg'].'/pg_hub_cancel_adm.php');

        if($pg_res_cd == '') {
            $sql_common .= ", bk_pg_price = 0, bk_pg_cancel = 1";
        }
        else {
            die($pg_res_msg);
        }
    }

    $sql = " update {$g5['wzp_booking_table']}
                set $sql_common 
                where bk_ix = '{$bk_ix}' ";
    sql_query($sql);

} 
else if ($mode == 'info') { 

   if (!$bk_ix) { 
        alert("잘못된 접근입니다.");
    }
    
    $bk_name            = isset($_POST['bk_name'])          ? trim($_POST['bk_name'])               : "";
    $bk_hp1             = isset($_POST['bk_hp1'])           ? trim($_POST['bk_hp1'])                : "";
    $bk_hp2             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp2'])                : "";
    $bk_hp3             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp3'])                : "";
    $bk_email           = isset($_POST['bk_email'])         ? trim($_POST['bk_email'])              : "";
    $bk_memo            = isset($_POST['bk_memo'])          ? trim($_POST['bk_memo'])               : "";
        
    $bk_email           = wz_get_email_address($bk_email);
    $bk_hp1             = preg_replace('/[^0-9]/', '', $bk_hp1);
    $bk_hp2             = preg_replace('/[^0-9]/', '', $bk_hp2);
    $bk_hp3             = preg_replace('/[^0-9]/', '', $bk_hp3);
    if ($bk_hp1 && $bk_hp2 && $bk_hp3) { 
        $bk_hp = $bk_hp1 .'-'. $bk_hp2 .'-'. $bk_hp3;
    } 

    $sql_common = " bk_name             = '{$bk_name}',
                    bk_hp               = '{$bk_hp}',
                    bk_email            = '{$bk_email}',
                    bk_memo             = '{$bk_memo}'
                    ";

    $sql = " update {$g5['wzp_booking_table']}
                set $sql_common 
                where bk_ix = '{$bk_ix}' ";
    sql_query($sql);

} 
else if($mode == 'kd') { // 객실개별정보 삭제

    $bkr_ix = (int)$_GET['bkr_ix'];

    $query = "select * from {$g5['wzp_booking_room_table']} where bkr_ix = '$bkr_ix'";
    $bkr = sql_fetch($query);
    $bkr_misu = $bkr['bkr_price'] + $bkr['bkr_price_adult'];
    $bk_ix = $bkr['bk_ix'];

    $query = "select bk_reserv_price, bk_misu, bk_status from {$g5['wzp_booking_table']} where bk_ix = '$bk_ix'";
    $bk = sql_fetch($query);
    $bk_misu    = $bk['bk_misu'];
    $bk_status  = $bk['bk_status'];
    if ($bk_status != '완료') { 
        $bk_misu = $bk_misu - $bkr_misu;
    } 

    $query = "delete from {$g5['wzp_room_status_table']} where bk_ix = '{$bkr['bk_ix']}' and rm_ix = '{$bkr['rm_ix']}' ";
    sql_query($query);

    $query = "delete from {$g5['wzp_booking_room_table']} where bkr_ix = '{$bkr['bkr_ix']}' ";
    sql_query($query);  

    // 삭제후 금액 재계산.
    $query = "select sum(bkr_price + bkr_price_adult) as bkr_price, count(*) as cnt, bkr_subject from {$g5['wzp_booking_room_table']} where bk_ix = '$bk_ix'";
    $row = sql_fetch($query);

    $bk_reserv_price    = round(($row['bkr_price'] / 100) * ($wzpconfig['pn_reserv_price_avg'] ? $wzpconfig['pn_reserv_price_avg'] : 100));

    $query = "update {$g5['wzp_booking_table']} set 
                    bk_subject = '".$row['bkr_subject']. ($row['cnt']>1 ? ' 외'.($row['cnt']-1).'건' : '') ."', 
                    bk_cnt_room = '".$row['cnt']."', 
                    bk_price = '".$row['bkr_price']."',
                    bk_reserv_price = '".$bk_reserv_price."',
                    bk_misu = '".$bk_misu."'
            where bk_ix = '{$bk_ix}' ";
    sql_query($query);


}

goto_url('./wzp_booking_view.php?w=u&amp;bk_ix='.$bk_ix.'&amp;'.$qstr);
?>