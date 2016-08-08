<?php
$sub_menu = '780300';
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택삭제") {

    auth_check($auth[$sub_menu], 'd');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " delete from {$g5['wzp_room_extend_price_table']} where rmp_ix = '".(int)$_POST['rmp_ix'][$k]."' ";
        sql_query($sql);
    }

}

goto_url('./wzp_price_list.php?'.$qstr);
?>
