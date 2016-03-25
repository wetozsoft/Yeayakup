<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


$bk_ix = (int)$_GET['ix'];

$wherequery = "";
if ($is_member && !$is_admin) { 
    $wherequery = " and mb_id = '{$member['mb_id']}' ";
} 
$query = "select * from {$g5['wzp_booking_table']} where bk_ix = '$bk_ix' {$wherequery} ";
$bk = sql_fetch($query);

if ($_GET['uid'] != md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip'])) { // 유효성검사
    alert("잘못된 접근입니다.", WZP_STATUS_URL.'&mode=orderlist');
} 

$is_done = true;
if ($bk['bk_status'] != '완료') {
    $is_done = false;
}

if (!$is_member) { // 비회원일경우.
    $tm_guest_token = md5($bk['bk_name'].str_replace('-', '', $bk['bk_hp']));
    if ($tm_guest_token != get_session("ss_guest_token")) { 
        set_session("ss_guest_token", "");
        alert("잘못된 접근입니다.", WZP_STATUS_URL.'&mode=orderlist');
    } 
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

$action_url = https_url(G5_PLUGIN_DIR.'/wz.booking.pension/order.view.update.php', true);    
?>

<div class="st3-form">
    
    <h3>- 예약자정보</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="15%">
            <col width="35%">
            <col width="15%">
            <col width="35%">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약번호</th>
            <td><?php echo $bk['od_id'];?></td>
            <th scope="col">예약상태</th>
            <td>
                <?php
                echo $bk['bk_status'];
                if (!$is_done && $bk['bk_status'] != '취소') { 
                    echo '&nbsp;<input type="button" class="btn_action" value="예약취소" onclick="getCancel(\''.$bk_ix.'\');" />';
                } 
                ?>
            </td>
        </tr>
        <tr>
            <th scope="col">예약자명</th>
            <td colspan="3"><?php echo $bk['bk_name'];?></td>
        </tr>
        <tr>
            <th scope="col">핸드폰</th>
            <td><?php echo $bk['bk_hp'];?></td>
            <th scope="col">이메일</th>
            <td><?php echo $bk['bk_email'];?></td>
        </tr>
        <tr>
            <th scope="col">요청사항</th>
            <td colspan="3"><?php echo conv_content($bk['bk_memo'],0);?></td>
        </tr>
        <tr>
            <th scope="col">예약등록일</th>
            <td colspan="3"><?php echo $bk['bk_time'];?></td>
        </tr>
        </tbody>
    </table>

    <h3>- 결제정보</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="15%">
            <col width="85%">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">이용금액</th>
            <td colspan="3">
                <strong><?php echo number_format($bk['bk_price']);?> 원</strong>
                (<?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?>)
            </td>
        </tr>
        <tr>
            <th scope="col">미수금</th>
            <td colspan="3"><?php echo number_format($bk['bk_misu']);?> 원</td>
        </tr>
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
        </tbody>
    </table>

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
        else {
            ?>
            <tr>
                <td style="text-align:center;" colspan="7">예약된 객실내역이 없습니다.</td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>

    <?php if ($is_member) {?>
    <div class="action">
        <a href="<?php echo WZP_STATUS_HTTPS_URL;?>&mode=orderlist" class="btn_submit before">목록으로</a>
    </div>
    <?php } ?>
    
</div>

<script type="text/javascript">
<!--
    <?php if (!$is_done) { 
    ?>
    function getCancel(ix) {
        if (confirm("예약내역을 취소 하시겠습니까?")) {
            location.href = "<?php echo $action_url?>?bo_table=<?php echo $bo_table?>&w=c&uid=<?php echo $uid?>&ix="+ix;
        }
    }
    <?php } ?>
//-->
</script>

