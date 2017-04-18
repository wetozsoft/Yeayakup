<?php
$sub_menu = '780500';
include_once('./_common.php');


$hd_ix              = isset($_REQUEST['hd_ix'])        ? (int)($_REQUEST['hd_ix'])            : "";
$hd_subject         = isset($_POST['hd_subject'])      ? trim($_POST['hd_subject'])           : "";
$hd_date            = isset($_POST['hd_date'])         ? trim($_POST['hd_date'])              : "";
$hd_loop_year       = isset($_POST['hd_loop_year'])    ? trim($_POST['hd_loop_year'])         : 0;

$hd_date            = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $hd_date) ? $hd_date : '';

if ($mode == 'del') { 
    $query = "delete from {$g5['wzp_holiday_table']} where hd_ix = '{$hd_ix}' ";
    sql_query($query);
    goto_url('./wzp_holiday_list.php?'.$qstr);
} 
else {
    if (!$hd_date) { 
        alert("일자 정보가 전달되지 않았습니다.");
    } 

    $hd_year   = substr($hd_date,0,4);
    $hd_month  = substr($hd_date,5,2);
    $hd_day    = substr($hd_date,8);

    $query = "select hd_ix from {$g5['wzp_holiday_table']} where hd_date = '{$hd_date}' or (hd_loop_year = 1 and hd_month = '{$hd_month}' and hd_day = '{$hd_day}') ";
    $hd = sql_fetch($query);
    if ($hd['hd_ix']) { // 이미 존재하면 업데이트
        $query = "update {$g5['wzp_holiday_table']} set hd_subject = '$hd_subject', hd_loop_year = '$hd_loop_year' where hd_ix = '{$hd['hd_ix']}' ";
        sql_query($query);
        goto_url('./wzp_holiday_list.php');
    } 
    else { // 아니면 새로 저장.
        $query = "insert into {$g5['wzp_holiday_table']} set 
                        hd_year = '$hd_year', 
                        hd_month = '$hd_month', 
                        hd_loop_year = '$hd_loop_year', 
                        hd_day = '$hd_day', 
                        hd_date = '$hd_date', 
                        hd_subject = '$hd_subject'
                ";
        sql_query($query);
        goto_url('./wzp_holiday_list.php');
    }
}
?>