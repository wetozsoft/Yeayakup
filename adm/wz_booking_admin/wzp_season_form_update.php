<?php
$sub_menu = '780200';
include_once('./_common.php');

$_POST = array_map('trim', $_POST);
if (isset($_REQUEST['se_ix'])) {
    $se_ix = (int)$_REQUEST['se_ix'];
} else {
    $se_ix = '';
}

if($w == 'd') {

    auth_check($auth[$sub_menu], 'd');

    sql_query(" delete from {$g5['wzp_season_table']} where se_ix = '{$se_ix}' ");

    goto_url('./wzp_season_list.php?'.$qstr);

} 
else {

    auth_check($auth[$sub_menu], 'w');

    $se_frdate = $_POST['se_frdate_m'] .'-'. $_POST['se_frdate_d'];
    $se_todate = $_POST['se_todate_m'] .'-'. $_POST['se_todate_d'];

    if(!$_POST['se_type'])
        alert('시즌타입을 선택해 주십시오.');
   
    $sql_common = " se_type             = '{$_POST['se_type']}',
                    se_frdate           = '{$se_frdate}',
                    se_todate           = '{$se_todate}'
                    ";
}

if($w == '') {

    $query = "select se_ix, se_type from {$g5['wzp_season_table']} where se_frdate <= '".$se_todate."' AND se_todate >= '".$se_frdate."' limit 1";
    $se = sql_fetch($query);
    if ($se['se_ix']) { 
        alert("이미 ".wz_season_type_str($se['se_type'])." 기간 으로 등록된 기간입니다. ");
    }

    $sql = " insert into {$g5['wzp_season_table']}
                set $sql_common  ";
    sql_query($sql);

    goto_url('./wzp_season_list.php');

} else if($w == 'u') {

    $query = "select se_ix, se_type from {$g5['wzp_season_table']} where se_frdate <= '".$se_todate."' AND se_todate >= '".$se_frdate."' and se_ix <> '".$se_ix."' limit 1";
    $se = sql_fetch($query);
    if ($se['se_ix']) { 
        alert("이미 ".wz_season_type_str($se['se_type'])." 기간 으로 등록된 기간입니다. ");
    }

    $sql = " update {$g5['wzp_season_table']}
                set $sql_common
                where se_ix = '{$se_ix}' ";
    sql_query($sql);

    goto_url('./wzp_season_form.php?w=u&amp;se_ix='.$se_ix.'&amp;'.$qstr);

}



?>