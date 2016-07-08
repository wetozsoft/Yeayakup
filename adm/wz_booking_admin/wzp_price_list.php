<?php
$sub_menu = '780310';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$sql_common = " from {$g5['wzp_room_extend_price_table']} ";

$sql_search = " where (1) ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst = "rmp_date asc, rmp_ix";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);


$g5['title'] = '객실개별요금 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$colspan = 5;
?>

<div class="btn_add01 btn_add">
    <a href="./wzp_price_form.php" id="bo_add">개별요금 추가</a>
</div>

<form name="frm" id="frm" method="post" action="./wzp_price_list_update.php" onsubmit="return getAction(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th width="40px" scope="col">
            <label for="chkall" class="sound_only">현재 페이지 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th width="auto" scope="col">객실명</th>
        <th width="120px" scope="col">날짜</th>
        <th width="150px" scope="col">금액</th>
        <th width="80px" scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        $sql = " select rm_subject from {$g5['wzp_room_table']} where rm_ix = '{$row['rm_ix']}' ";
        $rm = sql_fetch($sql);
        $rm_subject = $rm['rm_subject'];

        $bg  = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <input type="hidden" name="rmp_ix[<?php echo $i ?>]" value="<?php echo $row['rmp_ix'] ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_alignc"><?php echo $rm_subject; ?></td>
        <td class="td_alignc"><?php echo $row['rmp_date']; ?></td>
        <td class="td_alignc"><?php echo number_format($row['rmp_price']); ?></td>
        <td class="td_mngsmall">
            <a href="./wzp_price_form.php?w=u&amp;rmp_ix=<?php echo $row['rmp_ix']; ?>&amp;<?php echo $qstr; ?>">수정</a>&nbsp;
            <a href="./wzp_price_form_update.php?w=d&amp;rmp_ix=<?php echo $row['rmp_ix']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();">삭제</a> 
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
            if(!confirm("선택한 요금정보를 삭제처리 하시겠습니까?")) {
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