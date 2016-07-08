<?php
$sub_menu = '780100';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_token();

$sql_common = "";

$sql = " update {$g5['wzp_pension_table']}
            set pn_bank_info            = '{$_POST['pn_bank_info']}',
                pn_con_notice           = '{$_POST['pn_con_notice']}',
                pn_con_info             = '{$_POST['pn_con_info']}',
                pn_con_checkinout       = '{$_POST['pn_con_checkinout']}',
                pn_con_refund           = '{$_POST['pn_con_refund']}',
                pn_max_booking_day      = '".(int)$_POST['pn_max_booking_day']."',
                pn_max_booking_expire   = '".(int)$_POST['pn_max_booking_expire']."',
                pn_main_calendar_use    = '".(int)$_POST['pn_main_calendar_use']."',
                pn_wating_time          = '".(int)$_POST['pn_wating_time']."',
                pn_bank_use             = '".(int)$_POST['pn_bank_use']."',
                pn_reserv_price_avg     = '".(int)$_POST['pn_reserv_price_avg']."',
                pn_pg_service           = '".$_POST['pn_pg_service']."',
                pn_pg_card_use          = '".$_POST['pn_pg_card_use']."',
                pn_pg_dbank_use         = '".$_POST['pn_pg_dbank_use']."',
                pn_pg_vbank_use         = '".$_POST['pn_pg_vbank_use']."',
                pn_pg_hp_use            = '".$_POST['pn_pg_hp_use']."',
                pn_pg_mid               = '".$_POST['pn_pg_mid']."',
                pn_pg_site_key          = '".$_POST['pn_pg_site_key']."',
                pn_pg_test              = '".(int)$_POST['pn_pg_test']."'
                $sql_common
            ";
sql_query($sql);

goto_url('./wzp_config.php', false);

?>
