<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 회원인 경우
$sql_common = '';
if ($is_member) {
    $sql_common = " from {$g5['wzp_booking_table']} where mb_id = '{$member['mb_id']}' ";
}
else if ($_POST['user_nm'] && $_POST['user_hp']) { // 비회원인 경우 예약자명과 핸드폰번호가 넘어왔다면
    $user_nm = clean_xss_tags($_POST['user_nm']);
    $user_hp = str_replace('-', '', clean_xss_tags($_POST['user_hp']));
    set_session("ss_guest_token", md5($user_nm.$user_hp));
    $sql_common = " from {$g5['wzp_booking_table']} where bk_name = '$user_nm' and replace(bk_hp, '-', '') = '$user_hp' ";
}
else { // 그렇지 않다면 로그인으로 가기
    goto_url(WZP_STATUS_URL.'&mode=ordercheck');
}

$qstr = 'bo_table='.$bo_table.'&mode=orderlist';
if (isset($_REQUEST['page'])) { // 리스트 페이지
    $page = (int)$_REQUEST['page'];
    if ($page)
        $qstr .= '&amp;page=' . urlencode($page);
} else {
    $page = '';
}

// 객실정보
$querylimit = "";
if ($is_member) { 
    $query = " select count(*) as cnt {$sql_common} ";
    $row = sql_fetch($query);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함
    $querylimit = " limit {$from_record}, {$rows} ";
}

unset($arr_order);
$arr_order = array();
$query = "select * {$sql_common} order by bk_ix desc {$querylimit} ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_order[] = $row;
}
$cnt_order = count($arr_order);
sql_free_result($res);

if (!$is_member && !$cnt_order) { // 비회원일경우 alert 창 처리.
    alert("존재하지 않는 예약자 정보입니다.", WZP_STATUS_URL.'&mode=ordercheck');
} 
?>

<div class="ord-list">
    
    <h3>- 예약객실안내</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
        <caption></caption>
        <colgroup>
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약번호</th>
            <th scope="col">객실명</th>
            <th scope="col">결제금액</th>
            <th scope="col">결제상태</th>
            <th scope="col">결제방식</th>
            <th scope="col">핸드폰번호</th>
            <th scope="col">예약상태</th>
        </tr>
        <?php 
        if ($cnt_order > 0) { 
            for ($z = 0; $z < $cnt_order; $z++) { 

            $uid = md5($arr_order[$z]['od_id'].$arr_order[$z]['bk_time'].$arr_order[$z]['bk_ip']);
            ?>
            <tr>
                <td><a href="<?php echo WZP_STATUS_HTTPS_URL;?>&mode=orderdetail&ix=<?php echo $arr_order[$z]['bk_ix'];?>&uid=<?php echo $uid;?>" class="linker" title="예약번호 <?php echo $arr_order[$z]['od_id'];?> 의 상세정보 확인"><?php echo $arr_order[$z]['od_id'];?></a></td>
                <td><?php echo $arr_order[$z]['bk_subject'];?></td>
                <td style="text-align:right"><?php echo number_format($arr_order[$z]['bk_price']);?> 원</td>
                <td><?php echo $arr_order[$z]['bk_misu'] ? '미확인' : '결제완료';?></td>
                <td><?php echo $arr_order[$z]['bk_payment'];?></td>
                <td><?php echo $arr_order[$z]['bk_hp'];?></td>
                <td><?php echo $arr_order[$z]['bk_status'];?></td>
            </tr>
            <?php 
            }
        } 
        else {
            ?>
            <tr>
                <td colspan="7" style="text-align:center">
                    예약내역이 존재하지 않습니다.
                </td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>

    <?php 
    if ($querylimit) { 
        echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page=');    
    } 
    ?>

    <h3>- 기본예약안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_info'];?></div></div>

    <h3>- 입/퇴실 안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_checkinout'];?></div></div>

    <h3>- 환불규정</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_refund'];?></div></div>

</div>


