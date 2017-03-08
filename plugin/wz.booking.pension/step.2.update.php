<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

if (isset($_POST['sch_day']) && $_POST['sch_day']) {
    $sch_day = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_POST['sch_day']) ? $_POST['sch_day'] : "";
}

if (!$sch_day) { 
    alert("잘못된 접근입니다.", WZP_STATUS_URL);
} 

if (!isset($_POST['agree1']) || !$_POST['agree1']) {
    alert('예약 및 환불규정에 동의하셔야 예약 하실 수 있습니다.');
}

if (!isset($_POST['agree2']) || !$_POST['agree2']) {
    alert('개인정보 활용에 동의하셔야 예약 하실 수 있습니다.');
}


$bk_name            = isset($_POST['bk_name'])          ? trim($_POST['bk_name'])               : "";
$bk_hp1             = isset($_POST['bk_hp1'])           ? trim($_POST['bk_hp1'])                : "";
$bk_hp2             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp2'])                : "";
$bk_hp3             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp3'])                : "";
$bk_email           = isset($_POST['bk_email'])         ? trim($_POST['bk_email'])              : "";
$bk_memo            = isset($_POST['bk_memo'])          ? trim($_POST['bk_memo'])               : "";
$bk_payment         = isset($_POST['bk_payment'])       ? trim($_POST['bk_payment'])            : "";
$bk_deposit_name    = isset($_POST['bk_deposit_name'])  ? trim($_POST['bk_deposit_name'])       : "";
$bk_bank_account    = isset($_POST['bk_bank_account'])  ? trim($_POST['bk_bank_account'])       : "";

$bk_email           = wz_get_email_address($bk_email);
$bk_hp1             = preg_replace('/[^0-9]/', '', $bk_hp1);
$bk_hp2             = preg_replace('/[^0-9]/', '', $bk_hp2);
$bk_hp3             = preg_replace('/[^0-9]/', '', $bk_hp3);
$bk_hp = '';
if ($bk_hp1 && $bk_hp2 && $bk_hp3) { 
    $bk_hp = $bk_hp1 .'-'. $bk_hp2 .'-'. $bk_hp3;
} 
$bk_memo            = clean_xss_tags($bk_memo);
$bk_payment         = clean_xss_tags($bk_payment);
$bk_deposit_name    = clean_xss_tags($bk_deposit_name);
$bk_bank_account    = clean_xss_tags($bk_bank_account);

// 선택객실정보.
$error_msg      = '';
$bk_cnt_room    = $error = $bk_price = $bk_receipt_price = $bk_misu = $total_room = 0;
$bk_subject     = $bk_receipt_time = '';
unset($arr_room);
unset($rms_ix);
unset($bkr_ix);
$arr_room   = array();
$rms_ix     = array();
$bkr_ix     = array();

// 선택객실정보.
unset($arr_room);
$arr_room   = wz_calculate_room($_POST);
$bk_subject = $arr_room[0]['rm_subject'];
$cnt_room   = count($arr_room);


if ($cnt_room > 0) { 

    sql_query("LOCK TABLE {$g5['wzp_room_status_table']} READ, LOCK TABLE {$g5['wzp_room_status_table']} WRITE ", false);

    for ($z = 0; $z < $cnt_room; $z++) { 

        $bk_day = $arr_room[$z]['bk_day'];
        $rm_ix  = $arr_room[$z]['rm_ix'];
        
        // 예약날짜만큼 루프
        for ($j=0;$j<$bk_day;$j++) { 
            $rms_date   = wz_get_addday($sch_day, $j);
            $rms_year   = substr($rms_date, 0, 4);
            $rms_month  = substr($rms_date, 5, 2);
            $rms_day    = substr($rms_date, 8);
            
            // 예약이 가능한 날짜 확인.
            $query = " select rms_ix, rms_status from {$g5['wzp_room_status_table']} where rm_ix = '$rm_ix' and rms_date = '$rms_date' and rms_status <> '취소' ";
            $rms = sql_fetch($query);
            if ($rms['rms_status'] == '완료' || $rms['rms_status'] == '예약완료') { // 이미 예약중인 날짜라면.
                $error_msg .= '\"'.$arr_room[$z]['rm_subject'].'\" 의 '.wz_get_hangul_date($rms_date).' 예약이 이미 완료된 예약객실로 예약이 불가능합니다.\\n잔여객실 확인 후 다시 예약바랍니다.\\n';
                $error++;
            }
            else if ($rms['rms_status'] == '대기' || $rms['rms_status'] == '예약대기') { // 이미 예약중인 날짜라면.
                $error_msg .= '\"'.$arr_room[$z]['rm_subject'].'\" 의 '.wz_get_hangul_date($rms_date).' 예약이 이미 진행중입니다.\\n잔여객실 확인 후 다시 예약바랍니다.\\n';
                $error++;
            }
            
            // 오류가 없는경우에만 예약처리.
            if (!$error) { 

                $query = "insert into {$g5['wzp_room_status_table']} set 
                        rm_ix       = '$rm_ix', 
                        rms_year    = '$rms_year', 
                        rms_month   = '$rms_month', 
                        rms_day     = '$rms_day', 
                        rms_date    = '$rms_date',
                        rms_status  = '대기' ";
                $result = sql_query($query, false);

                $rms_ix[] = (!defined('G5_MYSQLI_USE') ? mysql_insert_id() : sql_insert_id());
                if (!$result) { 
                    $error_msg .= '\"'.$arr_room[$z]['rm_subject'].'\" 의 '.wz_get_hangul_date($rms_date).' 날짜 예약오류.\\n';
                    $error++;
                }
            }
        }

        // 객실예약룸정보
        if (!$error) {
            $query = "insert into {$g5['wzp_booking_room_table']} set 
                        rm_ix           = '$rm_ix', 
                        bkr_subject     = '{$arr_room[$z]['rm_subject']}', 
                        bkr_price       = '{$arr_room[$z]['price_room']}',
                        bkr_cnt_adult   = '{$arr_room[$z]['bk_cnt_adult']}', 
                        bkr_price_adult = '{$arr_room[$z]['price_person']}', 
                        bkr_frdate      = '$sch_day', 
                        bkr_todate      = '".wz_get_addday($sch_day, $bk_day)."', 
                        bkr_day         = '$bk_day' ";
            $result = sql_query($query, false);
            $bkr_ix[] = (!defined('G5_MYSQLI_USE') ? mysql_insert_id() : sql_insert_id());
            if (!$result) {
                $error_msg .= '객실룸정보 등록오류.\\n';
                $error++;
            }
            else {
                $bk_cnt_room++;
                if (!$bk_subject) { 
                    $bk_subject = $arr_room[$z]['rm_subject'];   
                }
            }
        }

        $total_room += $arr_room[$z]['price_room'] + $arr_room[$z]['price_person'];
    }

    sql_query("UNLOCK TABLES ", false);
    
} 

// 실제결제되어야할 금액.
$bk_price           = $total_room;
$bk_reserv_price    = round(($bk_price / 100) * ($wzpconfig['pn_reserv_price_avg'] ? $wzpconfig['pn_reserv_price_avg'] : 100));

$od_pay_price       = $bk_reserv_price; // pg 단에서 결제금액 일치여부확인을 위한 변수
$bk_subject         = $bk_subject . ($bk_cnt_room>1 ? ' 외'.($bk_cnt_room-1).'건' : '');
$bk_status          = '대기';
$bk_pg_price        = 0; // pg를 통해 결제된 금액.
$bk_tno = $bk_app_no = '';

if ($bk_payment == '무통장') {
    $bk_misu            = $bk_price;
    $bk_receipt_price   = 0;
    $bk_receipt_time    = '0000-00-00 00:00:00';
} 

@include_once(WZP_PLUGIN_PATH.'/gender/pg.pay_exec.php');

if (G5_IS_MOBILE)
    $bk_mobile = '1';
else
    $bk_mobile = '0';

$od_id = get_session('ss_order_id');

if (!$error) { 
    $query = "insert into {$g5['wzp_booking_table']} set 
                od_id               = '{$od_id}',
                mb_id               = '{$member['mb_id']}',
                bk_name             = '{$bk_name}',
                bk_subject          = '{$bk_subject}',
                bk_cnt_room         = '{$bk_cnt_room}',
                bk_hp               = '{$bk_hp}',
                bk_email            = '{$bk_email}',
                bk_memo             = '{$bk_memo}',
                bk_payment          = '{$bk_payment}',
                bk_deposit_name     = '{$bk_deposit_name}',
                bk_bank_account     = '{$bk_bank_account}',
                bk_price            = '{$bk_price}',
                bk_reserv_price     = '{$bk_reserv_price}',
                bk_receipt_price    = '{$bk_receipt_price}',
                bk_pg_price         = '{$bk_pg_price}',
                bk_misu             = '{$bk_misu}',
                bk_receipt_time     = '{$bk_receipt_time}',
                bk_mobile           = '{$bk_mobile}',
                bk_time             = '".G5_TIME_YMDHIS."',
                bk_ip               = '{$_SERVER['REMOTE_ADDR']}',
                bk_pg               = '{$wzpconfig['pn_pg_service']}',
                bk_tno              = '{$bk_tno}',
                bk_app_no           = '{$bk_app_no}',
                bk_status           = '{$bk_status}'
    ";   
    $result = sql_query($query, false);
    if (!$result) { 
        $error_msg .= '예약정보 등록오류.\\n';
        $error++;
    }
    else {
        $bk_ix = (!defined('G5_MYSQLI_USE') ? mysql_insert_id() : sql_insert_id());
    }
} 

if (!$error) { 
    if (is_array($rms_ix)) { // 객실상태정보에 예약키 적용.
        $rms_ix_list = implode(',', $rms_ix);
        $query = "update {$g5['wzp_room_status_table']} set bk_ix = '$bk_ix', rms_status = '$bk_status' where rms_ix in (".$rms_ix_list.") ";
        sql_query($query);
    }
    if (is_array($bkr_ix)) { // 객실예약룸정보 예약키 적용.
        $bkr_ix_list = implode(',', $bkr_ix);
        $query = "update {$g5['wzp_booking_room_table']} set bk_ix = '$bk_ix' where bkr_ix in (".$bkr_ix_list.") ";
        sql_query($query);
    }

    $uid = md5($od_id.G5_TIME_YMDHIS.$_SERVER['REMOTE_ADDR']);
    set_session('ss_orderview_uid', $uid);
    goto_url(WZP_STATUS_URL.'&mode=step3&od_id='.$od_id.'&amp;uid='.$uid);
}
else {
    
    $cancel_msg = '예약정보 등록오류';
    @include_once(WZP_PLUGIN_PATH.'/gender/pg.pay_cancel.php');

    if (is_array($rms_ix)) { // 객실상태정보 삭제.
        $rms_ix_list = implode(',', $rms_ix);
        $query = "delete from {$g5['wzp_room_status_table']} where rms_ix in (".$rms_ix_list.") ";
        sql_query($query);
    }
    if (is_array($bkr_ix)) { // 객실예약룸정보 삭제.
        $bkr_ix_list = implode(',', $bkr_ix);
        $query = "delete from {$g5['wzp_booking_room_table']} where bkr_ix in (".$bkr_ix_list.") ";
        sql_query($query);
    }
    alert($error_msg, WZP_STATUS_URL);
} 
?>

<html>
    <head>
        <title>예약정보 기록</title>
        <script>
            // 결제 중 새로고침 방지 샘플 스크립트 (중복결제 방지)
            function noRefresh()
            {
                /* CTRL + N키 막음. */
                if ((event.keyCode == 78) && (event.ctrlKey == true))
                {
                    event.keyCode = 0;
                    return false;
                }
                /* F5 번키 막음. */
                if(event.keyCode == 116)
                {
                    event.keyCode = 0;
                    return false;
                }
            }

            document.onkeydown = noRefresh ;
        </script>
    </head>
</html>