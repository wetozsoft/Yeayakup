<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 객실정보
unset($arr_room);
$arr_room = array();
$query = "select * from {$g5['wzp_room_table']} order by rm_sort asc ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_room[] = $row;
}
$cnt_room = count($arr_room);
sql_free_result($res);

$exclude = array('req_tx', 'res_cd', 'tran_cd', 'ordr_idxx', 'good_mny', 'good_name', 'buyr_name', 'buyr_tel1', 'buyr_tel2', 'buyr_mail', 'enc_info', 'enc_data', 'use_pay_method', 'rcvr_name', 'rcvr_tel1', 'rcvr_tel2', 'rcvr_mail', 'rcvr_zipx', 'rcvr_add1', 'rcvr_add2', 'param_opt_1', 'param_opt_2', 'param_opt_3');

    $sql = " select * from {$g5['wzp_booking_data_table']} where od_id = '6160706122316865' ";
    $row = sql_fetch($sql);

    $data = unserialize(base64_decode($row['dt_data']));
    $order_action_url = https_url(G5_PLUGIN_DIR.'/wz.booking.pension/step.2.update.php', true);

    echo '<form name="forderform" method="post" action="'.$order_action_url.'" autocomplete="off">'.PHP_EOL;

    $field = '';
    foreach($data as $key=>$value) {
        if(in_array($key, $exclude))
            continue;

        if(is_array($value)) {
            foreach($value as $k=>$v) {
                $field .= '<input type="hidden" name="'.$key.'['.$k.']" value="'.$v.'">'.PHP_EOL;
            }
        } else {
            $field .= '<input type="hidden" name="'.$key.'" value="'.$value.'">'.PHP_EOL;
        }
    }
    echo $field;

    foreach($_POST as $key=>$value) {
        echo '<input type="hidden" name="'.$key.'" value="'.$value.'">'.PHP_EOL;
    }

    echo '</form>'.PHP_EOL;
?>

<div class="st3-form">
    
    <h3>- 객실안내</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
        <caption></caption>
        <colgroup>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th scope="col" rowspan="2">객실명</th>
            <th scope="col" rowspan="2">사이즈</th>
            <th scope="col" colspan="2">인원</th>
            <th scope="col" colspan="2">비수기</th>
            <th scope="col" colspan="2">준성수기</th>
            <th scope="col" colspan="2">성수기</th>
        </tr>
        <tr>
            <th scope="col">기준</th>
            <th scope="col">최대</th>
            <th scope="col">주중</th>
            <th scope="col">주말</th>
            <th scope="col">주중</th>
            <th scope="col">주말</th>
            <th scope="col">주중</th>
            <th scope="col">주말</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        if ($cnt_room > 0) { 
            for ($z = 0; $z < $cnt_room; $z++) { 
            ?>
            <tr>
                <td><?php echo $arr_room[$z]['rm_subject'];?></td>
                <td><?php echo $arr_room[$z]['rm_size'];?></td>
                <td><?php echo $arr_room[$z]['rm_person_min'];?></td>
                <td><?php echo $arr_room[$z]['rm_person_max'];?></td>
                <td><?php echo number_format($arr_room[$z]['rm_price_rw']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['rm_price_rf']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['rm_price_sw']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['rm_price_sf']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['rm_price_fw']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['rm_price_ff']);?> 원</td>
            </tr>
            <?php 
            }
        } 
        ?>
        </tbody>
    </table>

    <h3>- 기본예약안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_info'];?></div></div>

    <h3>- 입/퇴실 안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_checkinout'];?></div></div>

    <h3>- 환불규정</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_refund'];?></div></div>

</div>


