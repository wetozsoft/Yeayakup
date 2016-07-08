<?php
$sub_menu = '780310';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '객실개별요금 관리';

$rmp_ix = (int)$_GET['rmp_ix'];

if ($w == 'u') {
    $html_title = '객실개별요금 수정';

    $sql = " select * from {$g5['wzp_room_extend_price_table']} where rmp_ix = '$rmp_ix' ";
    $rmp = sql_fetch($sql);
    if (!$rmp['rmp_ix']) alert('등록된 자료가 없습니다.', 'wzp_room_list.php');

}
else {
    $html_title = '객실개별요금 입력';
}

$sql = " select * from {$g5['wzp_room_table']} order by rm_sort asc ";
$rm = sql_query($sql);

include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
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


<form name="frm" action="./wzp_price_form_update.php" method="post" onsubmit="return getAction(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="rmp_ix" value="<?php echo $rmp_ix; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

    <section id="anc_spp_pay" class="cbox">
        
        <div style="height:20px;"></div>
        <h2>객실개별요금정보</h2>
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption>객실개별요금정보</caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">객실</th>
                <td>
                    <select name="rm_ix" id="rm_ix" required>
                    <option value="">선택</option>
                    <?php
                    for ($i=0; $row=sql_fetch_array($rm); $i++) {
                        $selected = '';
                        if ($rmp['rm_ix'] == $row['rm_ix']) { 
                            $selected = 'selected=selected';
                        } 
                        echo '<option value="'.$row['rm_ix'].'" '.$selected.'>'.$row['rm_subject'].'</option>';
                    }
                    ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">일자</th>
                <td>
                    <input type="text" name="rmp_date" id="rmp_date" value="<?php echo $rmp['rmp_date'];?>" required class="required frm_input"  maxlength="20" size="10" />
                </td>
            </tr>
            <tr>
                <th scope="row">요금</th>
                <td>
                    <input type="text" name="rmp_price" id="rmp_price" value="<?php echo $rmp['rmp_price'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
                </td>
            </tr>
            </tbody>
            </table>
        </div>
    </section>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <a href="./wzp_price_list.php?<?php echo $qstr; ?>">목록</a>
    </div>

</form>

<script>
function getAction(f) {
    return true;
}
$(function(){
    $("#rmp_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-2:c+5"});
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>