<?php
$sub_menu = '780500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '공휴일 목록보기';

$sql_common = " from {$g5['wzp_holiday_table']} ";

$sql_search = " where (1) ";

$is_sch = false; // 검색여부

if ($sch_title) {
    $sql_search .= " and hd_subject like '%".$sch_title."%' ";
    $qstr .= "&sch_title=".$sch_title;
    $is_sch = true;
}

if ($sch_date) {
    $sql_search .= " and hd_date = '".$sch_date."' ";
    $qstr .= "&sch_date=".$sch_date;
    $is_sch = true;
}

if (!$sst) {
    $sst = "hd_ix";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

unset($arr_hd);
$arr_hd = array();
$query = "select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_hd[] = $row;
}
$cnt_hd = count($arr_hd);
if ($res) sql_free_result($res);


include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    전체 <?php echo number_format($total_count); ?>건
</div>

<form name="fsearch" id="fsearch" class="local_sch02 local_sch" method="get">
<div>
    <strong>제목</strong>
    <input type="text" name="sch_title" id="sch_title" value="<?php echo $sch_title;?>" class="frm_input" style="width:170px;" maxlength="50" />
</div>
<div class="sch_last">
    <strong>일자</strong>
    <input type="text" name="sch_date" id="sch_date" value="<?php echo $sch_date;?>" class="frm_input" style="width:100px;" maxlength="10" />
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="btn_add01 btn_add">
    <a href="./wzp_holiday_form.php">새로 등록</a>
</div>

<form name="fitemstocksms" action="./order_list_update.php" method="post" onsubmit="return fitemstocksms_submit(this);">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" style="width:100px;">번호</th> 
        <th scope="col" style="width:auto;">제목</th>
        <th scope="col" style="width:100px;">매년</th>
        <th scope="col" style="width:140px;">일자</th>
        <th scope="col" style="width:100px;">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if ($cnt_hd > 0) {
        for ($z = 0; $z < $cnt_hd; $z++) { 

            $num = number_format($total_count - ($page - 1) * $rows - $z); 
            ?>
            <tr>
                <td class="td_alignc"><span class="sm number"><?php echo $num;?></span></td>
                <td class="td_alignc"><span class="sm number"><?php echo $arr_hd[$z]['hd_subject'];?></span></td>
                <td class="td_alignc"><span class="sm"><?php echo $arr_hd[$z]['hd_loop_year'] ? $arr_hd[$z]['hd_month'].'/'.$arr_hd[$z]['hd_day'] : '';?></span></td>
                <td class="td_alignc">
                    <span class="sm number"><?php echo $arr_hd[$z]['hd_date'];?></span>
                </td>
                <td class="td_mngsmall">
                    <a href="./wzp_holiday_form.php?mode=edit&hd_ix=<?php echo $arr_hd[$z]['hd_ix'].$qstr;?>">수정</a>
                    <a href="./wzp_holiday_form_update.php?mode=del&hd_ix=<?php echo $arr_hd[$z]['hd_ix'].$qstr;?>" onclick="return delete_confirm(this);">삭제</a>
                </td>
            </tr> 
            <?php
        }
    }
    else {
        ?>
        <tr>
            <td class="td_alignc" colspan="4">데이터가 존재하지 않습니다.</td>
        </tr> 
        <?php
    }
    ?>
    </tbody>
    </table>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script type="text/javascript">
<!--
    $(function(){
        $("#sch_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-10:c+10"});
    });
//-->
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>