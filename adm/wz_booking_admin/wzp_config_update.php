<?php
$sub_menu = '780100';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');

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
                pn_main_calendar_use    = '".(int)$_POST['pn_main_calendar_use']."'
                $sql_common
            ";
sql_query($sql);

goto_url('./wzp_config.php', false);

?>
