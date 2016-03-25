<?php
$sub_menu = '780200';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

auth_check($auth[$sub_menu], "r");

$sql = " select * from {$g5['wzp_season_table']} order by se_frdate asc ";
$result = sql_query($sql);

$g5['title'] = '시즌정보 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$colspan = 5;
?>

<form name="frm" id="frm" method="post" action="./wzp_season_list_update.php" onsubmit="return getAction(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">

<div class="btn_add01 btn_add">
    <a href="./wzp_season_form.php" id="bo_add">시즌정보 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th width="30px" scope="col">
            <label for="chkall" class="sound_only">현재 페이지 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">시즌타입</th>
        <th scope="col">시작일</th>
        <th scope="col">종료일</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        $bg  = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <input type="hidden" name="se_ix[<?php echo $i ?>]" value="<?php echo $row['se_ix'] ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td style="text-align:center;"><?php echo wz_season_type_str($row['se_type']); ?></td>
        <td style="text-align:center;"><?php echo $row['se_frdate']; ?></td>
        <td style="text-align:center;"><?php echo $row['se_todate']; ?></td>
        <td class="td_mngsmall">
            <a href="./wzp_season_form.php?w=u&amp;se_ix=<?php echo $row['se_ix']; ?>&amp;<?php echo $qstr; ?>">수정</a>&nbsp;
            <a href="./wzp_season_form_update.php?w=d&amp;se_ix=<?php echo $row['se_ix']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();">삭제</a> 
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
</div>

</form>

<script type="text/javascript">
<!--
    function getAction(f)
    {
        if (!is_checked("chk[]")) {
            alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if(document.pressed == "선택삭제") {
            if(!confirm("선택한 시즌정보를 삭제처리 하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }
//-->
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>