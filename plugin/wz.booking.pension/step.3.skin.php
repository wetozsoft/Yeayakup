<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$od_id = (int)$_GET['od_id'];

if (!$is_member) {
    if (get_session('ss_orderview_uid') != $_GET['uid'])
        alert("직접 링크로는 예약 조회가 불가합니다.\\n\\n예약확인 화면을 통하여 조회하시기 바랍니다.", WZP_STATUS_URL.'&mode=ordercheck');
}

$sql = "select * from {$g5['wzp_booking_table']} where od_id = '$od_id' ";
if($is_member && !$is_admin)
    $sql .= " and mb_id = '{$member['mb_id']}' ";
$bk = sql_fetch($sql);
if (!$bk['od_id'] || (!$is_member && md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip']) != get_session('ss_orderview_uid'))) {
    alert("조회하실 예약정보가 없습니다.", WZP_STATUS_URL);
}

// 객실예약정보
unset($arr_room);
$arr_room = array();
$query = "select * from {$g5['wzp_booking_room_table']} where bk_ix = '{$bk['bk_ix']}' order by bkr_ix asc ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_room[] = $row;
}
$cnt_room = count($arr_room);
sql_free_result($res);
?>

<div class="st3-form">
    
    <h3>- 객실예약현황</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
        <caption></caption>
        <colgroup>
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">객실명</th>
            <th scope="col">이용일자</th>
            <th scope="col">기간</th>
            <th scope="col">인원</th>
            <th scope="col">객실요금</th>
            <th scope="col">추가요금</th>
            <th scope="col">합계</th>
        </tr>
        <?php 
        $total_price = 0;
        if ($cnt_room > 0) { 
            for ($z = 0; $z < $cnt_room; $z++) { 
            ?>
            <tr>
                <td><?php echo $arr_room[$z]['bkr_subject'];?></td>
                <td><?php echo wz_get_hangul_date($arr_room[$z]['bkr_frdate']).'('.get_yoil($arr_room[$z]['bkr_frdate']).') ~ '.wz_get_hangul_date($arr_room[$z]['bkr_todate']).'('.get_yoil($arr_room[$z]['bkr_todate']).')';?></td>
                <td><?php echo $arr_room[$z]['bkr_day'].'박'.($arr_room[$z]['bkr_day']+1).'일';?></td>
                <td><?php echo $arr_room[$z]['bkr_cnt_adult'];?> 명</td>
                <td><?php echo number_format($arr_room[$z]['bkr_price']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['bkr_price_adult']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['bkr_price'] + $arr_room[$z]['bkr_price_adult']);?> 원</td>
            </tr>
            <?php 
            }
        } 
        ?>
        </tbody>
    </table>

    <h3>- 최종결제금액</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="150px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">총결제금액</th>
            <td>
                <strong><?php echo number_format($bk['bk_price']);?> 원</strong>
            </td>
        </tr>
    </table>

    <h3>- 예약자정보</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="150px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약자명</th>
            <td><?php echo $bk['bk_name'];?></td>
        </tr>
        <tr>
            <th scope="col">핸드폰</th>
            <td><?php echo $bk['bk_hp'];?></td>
        </tr>
        <tr>
            <th scope="col">이메일</th>
            <td><?php echo $bk['bk_email'];?></td>
        </tr>
        <tr>
            <th scope="col">요청사항</th>
            <td><?php echo conv_content($bk['bk_memo'],0);?></td>
        </tr>
    </table>

    <h3>- 결제방법</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="150px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">무통장입금</th>
            <td>
                <div style="margin:3px 0 3px">
                    입금자명 : <?php echo $bk['bk_deposit_name'];?>
                </div>
                <div style="margin:5px 0 3px">
                    입금계좌 : <?php echo $bk['bk_bank_account'];?>
                </div>
            </td>
        </tr>
    </table>    

    <div class="action">
        <a href="<?php echo G5_URL;?>" class="btn_submit next">홈으로</a>
    </div>

    </form>

</div>


