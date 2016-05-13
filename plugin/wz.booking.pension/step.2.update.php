<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

if (isset($_POST['sch_day']) && $_POST['sch_day'])
    $sch_day = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_POST['sch_day']) ? $_POST['sch_day'] : "";
else
    alert("잘못된 접근입니다.", WZP_STATUS_URL);

if (!isset($_POST['agree1']) || !$_POST['agree1']) {
    alert('예약 및 환불규정에 동의하셔야 예약 하실 수 있습니다.');
}

if (!isset($_POST['agree2']) || !$_POST['agree2']) {
    alert('개인정보 활용에 동의하셔야 예약 하실 수 있습니다.');
}

$od_id              = isset($_POST['od_id'])            ? trim($_POST['od_id'])                 : "";
$bk_name            = isset($_POST['bk_name'])          ? trim($_POST['bk_name'])               : "";
$bk_hp1             = isset($_POST['bk_hp1'])           ? trim($_POST['bk_hp1'])                : "";
$bk_hp2             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp2'])                : "";
$bk_hp3             = isset($_POST['bk_hp2'])           ? trim($_POST['bk_hp3'])                : "";
$bk_email           = isset($_POST['bk_email'])         ? trim($_POST['bk_email'])              : "";
$bk_memo            = isset($_POST['bk_memo'])          ? trim($_POST['bk_memo'])               : "";
$bk_payment         = isset($_POST['bk_payment'])       ? trim($_POST['bk_payment'])            : "";
$bk_deposit_name    = isset($_POST['bk_deposit_name'])  ? trim($_POST['bk_deposit_name'])       : "";
$bk_bank_account    = isset($_POST['bk_bank_account'])  ? trim($_POST['bk_bank_account'])       : "";

$od_id              = (int)$od_id;
$bk_email           = wz_get_email_address($bk_email);
$bk_hp1             = preg_replace('/[^0-9]/', '', $bk_hp1);
$bk_hp2             = preg_replace('/[^0-9]/', '', $bk_hp2);
$bk_hp3             = preg_replace('/[^0-9]/', '', $bk_hp3);
if ($bk_hp1 && $bk_hp2 && $bk_hp3) { 
    $bk_hp = $bk_hp1 .'-'. $bk_hp2 .'-'. $bk_hp3;
} 
$bk_memo            = clean_xss_tags($bk_memo);
$bk_payment         = clean_xss_tags($bk_payment);
$bk_deposit_name    = clean_xss_tags($bk_deposit_name);
$bk_bank_account    = clean_xss_tags($bk_bank_account);

// 선택객실정보.
$error_msg      = '';
$bk_cnt_room    = $error = $bk_price = $bk_receipt_price = $bk_misu = 0;
$bk_subject     = $bk_receipt_time = '';
unset($arr_room);
unset($rms_ix);
unset($bkr_ix);
$arr_room   = array();
$rms_ix     = array();
$bkr_ix     = array();

$rms_status = '대기'; // 신용카드 결제일경우 '완료' 처리로 예약완료

if (is_array($_POST['rm_ix'])) {
    $cnt_chk = count($_POST['rm_ix']);
    for ($z = 0; $z < $cnt_chk; $z++) {
        $rmix           = (int)$_POST['rm_ix'][$z]; // 객실키
        $bkday          = (int)$_POST['bk_day'][$z]; // 예약일수
        $bkcnt_adult    = (int)$_POST['bk_cnt_adult'][$z]; // 예약인원
        $rms_price      = 0;
        if ($rm_ix) { 
            $query = "select * from {$g5['wzp_room_table']} where rm_ix = '$rmix' ";   
            $rm = sql_fetch($query);

            $add_person_adult = $add_person_adult_price = 0;
            if ($bkcnt_adult > $rm['rm_person_min']) { 
                $add_person_adult           = $bkcnt_adult - $rm['rm_person_min'];
                $add_person_adult_price     = ($rm['rm_price_adult'] * $add_person_adult) * $bkday;
            }          

            // 예약날짜만큼 루프
            if (!$error) { 
                for ($j=0;$j<$bkday;$j++) { 
                    $rms_date   = wz_get_addday($sch_day, $j);
                    $rms_year   = substr($rms_date, 0, 4);
                    $rms_month  = substr($rms_date, 5, 2);
                    $today_type = wz_get_type($rms_date);
                    $rms_price  += wz_calculate($rmix, $today_type);
                    
                    $query = " select rms_ix, rms_status from {$g5['wzp_room_status_table']} where rm_ix = '$rmix' and rms_date = '$rms_date' ";
                    $rms = sql_fetch($query);
                    if ($rms['rms_status'] == '완료') { // 이미 예약중인 날짜라면.
                        $error_msg .= '\"'.$rm['rm_subject'].'\" 의 '.$rms_date.' 예약이 이미 완료된 예약객실로 예약이 불가능합니다.\\n';
                        $error++;
                    }
                    else if ($rms['rms_status'] == '대기') { // 이미 예약중인 날짜라면.
                        $error_msg .= '\"'.$rm['rm_subject'].'\" 의 '.$rms_date.' 예약이 이미 진행중입니다.\\n';
                        $error++;
                    }
                    else {
                        $query = "insert into {$g5['wzp_room_status_table']} set 
                                rm_ix       = '$rmix', 
                                rms_year    = '$rms_year', 
                                rms_month   = '$rms_month', 
                                rms_date    = '$rms_date',
                                rms_status  = '$rms_status' ";
                        $result = sql_query($query, false);
                        $rms_ix[] = (!defined('G5_MYSQLI_USE') ? mysql_insert_id() : sql_insert_id());
                        if (!$result) { 
                            $error_msg .= '\"'.$rm['rm_subject'].'\" 의 '.$rms_date.' 날짜 예약오류.\\n';
                            $error++;
                        }
                    }
                }     
            }

            // 객실예약룸정보
            if (!$error) {
                $query = "insert into {$g5['wzp_booking_room_table']} set 
                            rm_ix           = '$rmix', 
                            bkr_subject     = '{$rm['rm_subject']}', 
                            bkr_price       = '$rms_price',
                            bkr_cnt_adult   = '$bkcnt_adult', 
                            bkr_price_adult = '$add_person_adult_price', 
                            bkr_frdate      = '$sch_day', 
                            bkr_todate      = '".wz_get_addday($sch_day, $bkday)."', 
                            bkr_day         = '$bkday' ";
                $result = sql_query($query, false);
                $bkr_ix[] = (!defined('G5_MYSQLI_USE') ? mysql_insert_id() : sql_insert_id());
                if (!$result) {
                    $error_msg .= '객실룸정보 등록오류.\\n';
                    $error++;
                }
                else {
                    if (!$bk_subject) { 
                        $bk_subject = $rm['rm_subject'];   
                    }
                }
            }

            $bk_cnt_room++;
            $bk_price += $rms_price + $add_person_adult_price;
        }
    }
}

$bk_subject = $bk_subject . ($bk_cnt_room>1 ? ' 외'.($bk_cnt_room-1).'건' : '');
if ($bk_payment == '무통장') {
    $bk_misu            = $bk_price;
    $bk_receipt_price   = 0;
    $bk_receipt_time    = '0000-00-00 00:00:00';
} 

if (G5_IS_MOBILE)
    $bk_mobile = '1';
else
    $bk_mobile = '0';

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
                bk_receipt_price    = '{$bk_receipt_price}',
                bk_misu             = '{$bk_misu}',
                bk_receipt_time     = '{$bk_receipt_time}',
                bk_mobile           = '{$bk_mobile}',
                bk_time             = '".G5_TIME_YMDHIS."',
                bk_ip               = '{$_SERVER['REMOTE_ADDR']}'
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
        $query = "update {$g5['wzp_room_status_table']} set bk_ix = '$bk_ix' where rms_ix in (".$rms_ix_list.") ";
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