<?php
$sub_menu = '780400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$sql_common = " from {$g5['wzp_booking_table']} ";

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

if ($sfs) { 
    switch ($sfs) {
        case "완료":
            $sql_search .= " and (bk_status = '예약완료' or bk_status = '완료') ";
            break;
        case "취소":
            $sql_search .= " and (bk_status = '예약취소' or bk_status = '취소') ";
            break;
        default :
            $sql_search .= " and (bk_status = '예약대기' or bk_status = '대기') ";
            break;
    }
    $qstr .= "&sfs=".$sfs;
} 

if ($sch_frdate1 && $sch_todate1) { 
    $sql_search .= " and bk_ix in (select bk_ix from {$g5['wzp_booking_room_table']} where bkr_frdate <= '{$sch_todate1}' AND bkr_todate > '{$sch_frdate1}') ";
    $qstr .= "&sch_frdate1=".$sch_frdate1."&sch_todate1=".$sch_todate1;
} 

if (!$sst) {
    $sst = "bk_ix";
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

$g5['title'] = '예약정보 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$colspan = 13;
?>

<style>
    .linker {text-decoration:underline;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    전체 <?php echo number_format($total_count) ?>개
</div>

<form name="fsearch" id="fsearch" class="local_sch02 local_sch" method="get" onsubmit="return getSearch(this);">

<div>
    <strong>숙박일</strong>
    <input type="text" name="sch_frdate1" id="sch_frdate1" value="<?php echo $sch_frdate1;?>" class="frm_input" style="width:100px;" maxlength="10" /> ~ 
    <input type="text" name="sch_todate1" id="sch_todate1" value="<?php echo $sch_todate1;?>" class="frm_input" style="width:100px;" maxlength="10" />
</div>
<div>
    <strong>예약상태</strong>
<select name="sfs" id="sfs">
    <option value=""<?php echo get_selected($_GET['sfs'], ""); ?>>전체</option>
    <option value="대기"<?php echo get_selected($_GET['sfs'], "대기"); ?>>대기</option>
    <option value="완료"<?php echo get_selected($_GET['sfs'], "완료"); ?>>완료</option>
    <option value="취소"<?php echo get_selected($_GET['sfs'], "취소"); ?>>취소</option>
</select>
</div>
<div class="sch_last">
    <strong>단어검색</strong>
    
<select name="sfl" id="sfl">
    <option value="bk_name"<?php echo get_selected($_GET['sfl'], "bk_name"); ?>>예약자명</option>
    <option value="bk_hp"<?php echo get_selected($_GET['sfl'], "bk_hp"); ?>>핸드폰</option>
    <option value="bk_email"<?php echo get_selected($_GET['sfl'], "bk_email"); ?>>이메일</option>
    <option value="bk_memo"<?php echo get_selected($_GET['sfl'], "bk_memo"); ?>>요청사항</option>
    <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>예약자아이디</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" class="frm_input">
<input type="submit" value="검색" class="btn_submit">
<input type="button" value="엑셀다운로드" class="btn_submit" onclick="getExcel(document.forms.fsearch);">
</div>
</form>

<form name="frm" id="frm" method="post" action="./wzp_booking_list_update.php" onsubmit="return getAction(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">

<input type="hidden" name="sfs" value="<?php echo $sfs ?>">
<input type="hidden" name="sch_frdate1" value="<?php echo $sch_frdate1 ?>">
<input type="hidden" name="sch_todate1" value="<?php echo $sch_todate1 ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th width="40px" scope="col" rowspan="2">
            <label for="chkall" class="sound_only">현재 페이지 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th width="120px" scope="col">예약번호</th>
        <th width="auto" scope="col">객실명</th>
        <th width="100px" scope="col">성명</th>
        <th width="80px" scope="col">총이용요금</th>
        <th width="230px" scope="col">결제상태</th>
        <th width="90px" scope="col">결제방식</th>
        <th width="100px" scope="col">핸드폰번호</th>
        <th width="100px" scope="col">날짜</th>
        <th width="50px" scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        $query2 = "select * from {$g5['wzp_booking_room_table']} where bk_ix = '{$row['bk_ix']}' order by rm_ix asc "; // 객실정보
        $result2 = sql_query($query2);

        $bg  = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <input type="hidden" name="bk_ix[<?php echo $i ?>]" value="<?php echo $row['bk_ix'] ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_alignc"><a href="./wzp_booking_view.php?bk_ix=<?php echo $row['bk_ix']; ?>&amp;<?php echo $qstr; ?>" class="linker"><?php echo $row['od_id']; ?></a></td>
        <td>
            <?php
            for ($z=0; $row2=sql_fetch_array($result2); $z++) {
                echo '<div><strong>'.$row2['bkr_subject'].'</strong> <span class="use-dt">'.wz_get_hangul_date_md($row2['bkr_frdate']).'('.get_yoil($row2['bkr_frdate']).') ~ '.wz_get_hangul_date_md($row2['bkr_todate']).'('.get_yoil($row2['bkr_todate']).')</span></div>';
            } 
            ?>
        </td>
        <td class="td_alignc"><?php echo $row['bk_name']; ?></td>
        <td class="td_alignc"><?php echo number_format($row['bk_price']); ?></td>
        <td class="td_alignc">
            예약금 (<?php echo ($row['bk_reserv_price'] <= ($row['bk_price'] - $row['bk_misu']) ? '결제완료' : '<font color="red">미결제</font>'); ?>)&nbsp;/&nbsp;
            총금액 (<?php echo $row['bk_misu'] ? '<font color="red">미결제</font>' : '결제완료'; ?>)
        </td>
        <td class="td_alignc"><?php echo $row['bk_payment']; ?></td>
        <td class="td_alignc"><?php echo $row['bk_hp']; ?></td>
        <td class="td_alignc">
            <?php 
            if ($row['bk_status'] == '대기') { 
                echo wz_convert_time_last($row['bk_time']).' 경과';
            } 
            else {
                echo substr($row['bk_time'], 0, 10); 
            }
            ?>
        </td>
        <td class="td_alignc"><?php echo $row['bk_status']; ?></td>
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
    <input type="submit" name="act_button" value="선택예약완료" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택예약취소" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택예약대기" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

</form>

<script type="text/javascript">
<!--
    $(function(){
        $("#sch_frdate1, #sch_todate1").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-5:c+2"});
    });
    function getAction(f)
    {
        if (!is_checked("chk[]")) {
            alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if(document.pressed == "선택삭제") {
            if(!confirm("선택한 예약정보를 삭제처리 하시겠습니까?")) {
                return false;
            }
        }
        else if(document.pressed == "선택예약완료") {
            if(!confirm("선택한 예약정보를 예약완료처리 하시겠습니까?")) {
                return false;
            }
        }
        else if(document.pressed == "선택예약취소") {
            if(!confirm("선택한 예약정보를 예약취소처리 하시겠습니까?")) {
                return false;
            }
        }
        else if(document.pressed == "선택예약대기") {
            if(!confirm("선택한 예약정보를 예약대기처리 하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }
    function getSearch(f) {
        f.action = "./wzp_booking_list.php";
    }
    function getExcel(f) {
        f.action = "./wzp_booking_excel.php";
        f.target = "_self";
        f.submit();
    }
//-->
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>