<?php
$sub_menu = '780500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '공휴일 등록/수정';
$hd_ix       = (int)$_GET['hd_ix'];

if ($mode == 'edit') { 
    $sql = " select * from {$g5['wzp_holiday_table']} where hd_ix = '$hd_ix' ";
    $hd = sql_fetch($sql);
    if (!$hd['hd_ix']) alert('등록된 자료가 없습니다.', 'wzp_holiday_list.php');    
} 

$qstr .= "&sch_title=".$sch_title."&hd_date=".$hd_date;


include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<form method="post" name="frm" id="frm" action="./wzp_holiday_form_update.php?<?php echo $qstr;?>" onsubmit="return getAction(this);">
<input type="hidden" name="mode" value="<?php echo $mode ?>">
<input type="hidden" name="hd_ix" value="<?php echo $hd_ix ?>">

<div class="tbl_frm01 tbl_wrap">

    <table cellpadding="0" cellspacing="0" border="0">
    <colgroup>
        <col width="130px">
        <col>
        <col width="130px">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <td class="head">제목</td>
        <td class="head">
            <input type="text" name="hd_subject" id="hd_subject" value="<?php echo $hd['hd_subject'];?>" class="frm_input" style="width:360px;" maxlength="100" />
        </td>
    </tr> 
    <tr>
        <td class="head">일자</td>
        <td class="head">
            <input type="text" name="hd_date" id="hd_date" value="<?php echo $hd['hd_date'];?>" class="frm_input required" required style="width:100px;" maxlength="10" />
            <label><input type="checkbox" name="hd_loop_year" id="hd_loop_year" value="1" <?php echo $hd['hd_loop_year'] ? 'checked=checked' : '';?> /> 매년적용</label>
        </td>
    </tr>
    </tbody>
    </table>

</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="<?php echo $mode == 'edit' ? '수정' : '등록';?>" class="btn_submit" accesskey="s">
    <?php if ($mode == 'edit' && $member['mb_id'] == $hd['mb_id']) {?>
    <input type="button" value="삭제" class="btn_submit">
    <?php } ?>
    <a href="./wzp_holiday_list.php?sst=&amp;sod=&amp;sfl=&amp;stx=&amp;page=0">목록</a>
</div>

</form>

<script type="text/javascript">
<!--
    function getAction(f) {

        return true;
    }
    $(function(){
        $("#hd_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-0:c+10"});
    });
//-->
</script>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>