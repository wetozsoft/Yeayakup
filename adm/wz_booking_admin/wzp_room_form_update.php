<?php
$sub_menu = '780300';
include_once('./_common.php');

$_POST = array_map('trim', $_POST);
if (isset($_REQUEST['rm_ix'])) {
    $rm_ix = (int)$_REQUEST['rm_ix'];
} else {
    $rm_ix = '';
}

if($w == 'd') {

    auth_check($auth[$sub_menu], 'd');

    sql_query(" delete from {$g5['wzp_room_table']} where rm_ix = '{$rm_ix}' ");

    goto_url('./wzp_room_list.php?'.$qstr);

} 
else {

    auth_check($auth[$sub_menu], 'w');
   
    $sql_common = " rm_subject          = '{$_POST['rm_subject']}',
                    rm_size             = '{$_POST['rm_size']}',
                    rm_person_min       = '".(int)$_POST['rm_person_min']."',
                    rm_person_max       = '".(int)$_POST['rm_person_max']."',
                    rm_price_rw         = '".(int)$_POST['rm_price_rw']."',
                    rm_price_rf         = '".(int)$_POST['rm_price_rf']."',
                    rm_price_sw         = '".(int)$_POST['rm_price_sw']."',
                    rm_price_sf         = '".(int)$_POST['rm_price_sf']."',
                    rm_price_fw         = '".(int)$_POST['rm_price_fw']."',
                    rm_price_ff         = '".(int)$_POST['rm_price_ff']."',
                    rm_price_adult      = '".(int)$_POST['rm_price_adult']."',
                    rm_link_url         = '".$_POST['rm_link_url']."',
                    rm_sort             = '".(int)$_POST['rm_sort']."'
                    ";
}

if($w == '') {

    $sql = " insert into {$g5['wzp_room_table']}
                set $sql_common  ";
    sql_query($sql);

    goto_url('./wzp_room_list.php');

} else if($w == 'u') {

    $sql = " update {$g5['wzp_room_table']}
                set $sql_common
                where rm_ix = '{$rm_ix}' ";
    sql_query($sql);

    goto_url('./wzp_room_form.php?w=u&amp;rm_ix='.$rm_ix.'&amp;'.$qstr);

}



?>