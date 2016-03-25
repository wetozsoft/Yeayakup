<?php
$sub_menu = '780300';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '객실정보 관리';

$rm_ix = (int)$_GET['rm_ix'];

if ($w == 'u') {
    $html_title = '객실정보 수정';

    $sql = " select * from {$g5['wzp_room_table']} where rm_ix = '$rm_ix' ";
    $rm = sql_fetch($sql);
    if (!$rm['rm_ix']) alert('등록된 자료가 없습니다.', 'wzp_room_list.php');

}
else {
    $html_title = '객실정보 입력';
}

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<style>
.tbl_type,.tbl_type th,.tbl_type td{border:0;}
.tbl_type{width:100%;border-top:1px solid #dcdcdc;border-bottom:1px solid #dcdcdc;border-collapse:collapse}
.tbl_type caption{display:none}
.tbl_type tfoot{background-color:#f5f7f9;font-weight:bold}
.tbl_type th{padding:7px 0 4px;border:1px solid #dcdcdc;background-color:#f5f7f9;color:#666;font-weight:bold;text-align:center;}
.tbl_type td{padding:6px 6px;border:1px solid #e5e5e5;color:#4c4c4c}
.frm_input.number {text-align:right;padding-right:3px;}
</style>


<form name="frm" action="./wzp_room_form_update.php" method="post" onsubmit="return getAction(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="rm_ix" value="<?php echo $rm_ix; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

    <section id="anc_spp_pay" class="cbox">
        
        <div style="height:20px;"></div>
        <h2>객실상세정보</h2>
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption>객실상세정보</caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">객실명</th>
                <td>
                    <input type="text" name="rm_subject" id="rm_subject" value="<?php echo $rm['rm_subject'];?>" required class="required frm_input"  maxlength="100" size="30" />
                </td>
            </tr>
            <tr>
                <th scope="row">크기</th>
                <td>
                    <input type="text" name="rm_size" id="rm_size" value="<?php echo $rm['rm_size'];?>" required class="required frm_input"  maxlength="20" size="10" />
                </td>
            </tr>
            <tr>
                <th scope="row">인원</th>
                <td>
                    최소 : <input type="text" name="rm_person_min" id="rm_person_min" value="<?php echo $rm['rm_person_min'];?>" required class="required frm_input"  maxlength="10" size="5" /> 명 / 
                    최대 : <input type="text" name="rm_person_max" id="rm_person_max" value="<?php echo $rm['rm_person_max'];?>" required class="required frm_input"  maxlength="10" size="5" /> 명
                </td>
            </tr>
            <tr>
                <th scope="row">링크URL</th>
                <td>
                    <input type="text" name="rm_link_url" id="rm_link_url" value="<?php echo $rm['rm_link_url'];?>" class="frm_input"  maxlength="120" size="80" />
                </td>
            </tr>
            <tr>
                <th scope="row">순서</th>
                <td>
                    <input type="text" name="rm_sort" id="rm_sort" value="<?php echo $rm['rm_sort'];?>" required class="required frm_input"  maxlength="10" size="7" />
                </td>
            </tr>
            </tbody>
            </table>
        </div>
        
        <div style="height:20px;"></div>
        <h2>객실요금정보</h2>
        <div class="tbl_frm01 tbl_wrap">
            <table cellspacing="0" border="1" class="tbl_type" style="width:900px;" id="tbl_kinds">
            <caption></caption>
            <colgroup>
                <col width="25%"/>
                <col width="35%"/>
                <col width="40%" />
            </colgroup>
            <tbody>
            <tr>
                <th scope="row"></th>
                <th scope="row">주중 (일 ~ 목)</th>
                <th scope="row">주말 (금, 토)</th>
            </tr>
            <tr>
                <th scope="row"><?php echo wz_season_type_str('');?></th>
                <td>
                    <input type="text" name="rm_price_rw" id="rm_price_rw" value="<?php echo $rm['rm_price_rw'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
                <td>
                    <input type="text" name="rm_price_rf" id="rm_price_rf" value="<?php echo $rm['rm_price_rf'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo wz_season_type_str('S');?></th>
                <td>
                    <input type="text" name="rm_price_sw" id="rm_price_sw" value="<?php echo $rm['rm_price_sw'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
                <td>
                    <input type="text" name="rm_price_sf" id="rm_price_sf" value="<?php echo $rm['rm_price_sf'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo wz_season_type_str('F');?></th>
                <td>
                    <input type="text" name="rm_price_fw" id="rm_price_fw" value="<?php echo $rm['rm_price_fw'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
                <td>
                    <input type="text" name="rm_price_ff" id="rm_price_ff" value="<?php echo $rm['rm_price_ff'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo wz_season_type_str('H');?><br />(공휴일)</th>
                <td colspan="3">
                    <input type="text" name="rm_price_hs" id="rm_price_hs" value="<?php echo $rm['rm_price_hs'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                    (시즌관리 화면에서 특정일 설정 가능, 성수기요금보다 우선순위로 요금이 적용됩니다.)
                </td>
            </tr>
            <tr>
                <th scope="row">성인추가요금</th>
                <td colspan="3">
                    최소인원 초과시 1인당 <input type="text" name="rm_price_adult" id="rm_price_adult" value="<?php echo $rm['rm_price_adult'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원 추가.
                </td>
            </tr>
            </tbody>
            </table>
        </div>
    </section>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <a href="./wzp_room_list.php?<?php echo $qstr; ?>">목록</a>
    </div>

</form>

<script>
function getAction(f) {
    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>