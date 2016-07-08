<?php
$sub_menu = '780300';
include_once('./_common.php');

$_POST = array_map('trim', $_POST);
if (isset($_REQUEST['rmp_ix'])) {
    $rmp_ix = (int)$_REQUEST['rmp_ix'];
} else {
    $rmp_ix = '';
}

if($w == 'd') {

    auth_check($auth[$sub_menu], 'd');

    sql_query(" delete from {$g5['wzp_room_extend_price_table']} where rmp_ix = '{$rmp_ix}' ");

    goto_url('./wzp_price_list.php?'.$qstr);

} 
else {

    auth_check($auth[$sub_menu], 'w');

    $rm_ix              = isset($_POST['rm_ix'])            ? trim($_POST['rm_ix'])                 : "";
    $rmp_price          = isset($_POST['rmp_price'])        ? trim($_POST['rmp_price'])             : "";
    $rmp_date           = isset($_POST['rmp_date'])         ? trim($_POST['rmp_date'])              : "";

    $rm_ix              = (int)$rm_ix;
    $rmp_price          = (int)$rmp_price;
    $rmp_date           = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $rmp_date) ? $rmp_date : '';

    $rmp_year   = substr($rmp_date,0,4);
    $rmp_month  = substr($rmp_date,5,2);
    $rmp_day    = substr($rmp_date,8);
   
    $sql_common = " rm_ix = '$rm_ix', 
                    rmp_year = '$rmp_year', 
                    rmp_month = '$rmp_month', 
                    rmp_day = '$rmp_day', 
                    rmp_date = '$rmp_date', 
                    rmp_price = '$rmp_price'
                    ";
}

if($w == '') {

    $sql = " insert into {$g5['wzp_room_extend_price_table']}
                set $sql_common  ";
    sql_query($sql);

    goto_url('./wzp_price_list.php');

} else if($w == 'u') {

    $sql = " update {$g5['wzp_room_extend_price_table']}
                set $sql_common
                where rmp_ix = '{$rmp_ix}' ";
    sql_query($sql);

    goto_url('./wzp_price_form.php?w=u&amp;rmp_ix='.$rmp_ix.'&amp;'.$qstr);

}



?>