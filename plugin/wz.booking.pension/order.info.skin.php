<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$od_id = $_GET['od_id'];
$od_id = preg_match("/^[0-9]+$/", $od_id) ? $od_id : '';

if (!$is_member) {
    if (get_session('ss_orderview_uid') != $_GET['uid'])
        alert("직접 링크로는 예약 조회가 불가합니다.\\n\\n예약확인 화면을 통하여 조회하시기 바랍니다.", WZP_STATUS_URL.'&mode=ordercheck');
}

$sql = "select * from {$g5['wzp_booking_table']} where od_id = '$od_id' ";
if($is_member)
    $sql .= " and mb_id = '{$member['mb_id']}' ";
$bk = sql_fetch($sql);
if (!$bk['od_id'] || (!$is_member && md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip']) != get_session('ss_orderview_uid'))) {
    alert("조회하실 예약정보가 없습니다.", WZP_STATUS_URL);
}

$disp_bank = true;
$app_no_subj = '';
$disp_bank = true;
$disp_receipt = false;
if($bk['bk_payment'] == '신용카드' || $bk['bk_payment'] == 'KAKAOPAY') {
    $app_no_subj = '승인번호';
    $app_no = $bk['bk_app_no'];
    $disp_bank = false;
    $disp_receipt = true;
} else if($bk['bk_payment'] == '간편결제') {
    $app_no_subj = '승인번호';
    $app_no = $bk['bk_app_no'];
    $disp_bank = false;
    switch($bk['bk_pg']) {
        case 'kcp':
            $easy_pay_name = 'PAYCO';
            break;
        default:
            break;
    }
} else if($bk['bk_payment'] == '휴대폰') {
    $app_no_subj = '휴대폰번호';
    $app_no = $bk['bk_bank_account'];
    $disp_bank = false;
    $disp_receipt = true;
} else if($bk['bk_payment'] == '가상계좌' || $bk['bk_payment'] == '계좌이체') {
    $app_no_subj = '거래번호';
    $app_no = $bk['bk_tno'];
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
if ($res) sql_free_result($res);

$uid = md5($bk['od_id'].$bk['bk_time'].$bk['bk_ip']);
$action_url = https_url(G5_PLUGIN_DIR.'/wz.booking.pension/order.view.update.php', true);   

// LG 현금영수증 JS
if($bk['bk_pg'] == 'lg') {
    if($wzpconfig['pn_pg_test']) {
        echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr:7085/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
    } else {
        echo '<script language="JavaScript" src="http://pgweb.uplus.co.kr/WEB_SERVER/js/receipt_link.js"></script>'.PHP_EOL;
    }
}
?>

<div class="pay-bank-notice">
    <?php if ($bk['bk_status'] == '대기') { ?>
    <strong>예약신청이 완료되었습니다.</strong>
    <ul class="desc">
        <li><strong><?php echo date("Y년m월d일 H시", strtotime($bk['bk_time']." + ".$wzpconfig['pn_wating_time']." hours"));?>까지</strong> 입금을 완료하지 않을경우 자동취소 됩니다.</li>
        <li>인터넷 예약 특성상 입금시간이 지체되면 예약이 중복될수 있어 빠른입금 부탁드립니다.</li>
        <li>입금완료 후 미리 준비할 수 있도록 이용전 통화하시는것이 좋습니다.</li>
    </ul>
    <?php } else if ($bk['bk_status'] == '취소') { ?>
    <strong>예약이 취소되었습니다.</strong>
    <ul class="desc">
        <li>환불수수료는 규정 및 이용안내 를 참고해주세요.</li>
    </ul>
    <?php } else { ?>
    <strong>예약이 완료되었습니다.</strong>
    <ul class="desc">
        <li>예약취소는 전화문의바랍니다.</li>
        <li>환불수수료는 규정 및 이용안내 를 참고해주세요.</li>
    </ul>
    <?php } ?>
</div>


<h3>- 객실예약현황</h3>
<table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
    <caption></caption>
    <colgroup>
        <col>
    </colgroup>
    <thead>
    <tr>
        <th scope="col">객실명</th>
        <th scope="col">이용일자</th>
        <th scope="col">기간</th>
        <th scope="col">인원</th>
        <th scope="col">객실요금</th>
        <th scope="col">추가요금</th>
        <th scope="col">합계</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    $total_price = $total_room = $total_person = 0;
    if ($cnt_room > 0) { 
        for ($z = 0; $z < $cnt_room; $z++) { 
        ?>
        <tr>
            <td><?php echo $arr_room[$z]['bkr_subject'];?></td>
            <td><?php echo wz_get_hangul_date_md($arr_room[$z]['bkr_frdate']).'('.get_yoil($arr_room[$z]['bkr_frdate']).') ~ '.wz_get_hangul_date_md($arr_room[$z]['bkr_todate']).'('.get_yoil($arr_room[$z]['bkr_todate']).')';?></td>
            <td><?php echo $arr_room[$z]['bkr_day'].'박'.($arr_room[$z]['bkr_day']+1).'일';?></td>
            <td><?php echo $arr_room[$z]['bkr_cnt_adult'];?> 명</td>
            <td><?php echo number_format($arr_room[$z]['bkr_price']);?> 원</td>
            <td><?php echo number_format($arr_room[$z]['bkr_price_adult']);?> 원</td>
            <td><?php echo number_format($arr_room[$z]['bkr_price'] + $arr_room[$z]['bkr_price_adult']);?> 원</td>
        </tr>
        <?php 
        $total_room     += $arr_room[$z]['bkr_price'];
        $total_person   += $arr_room[$z]['bkr_price_adult'];
        }
    } 
    ?>
    </tbody>
    <thead>
    <tr>
        <th colspan="4">합계</th>
        <th><?php echo number_format($total_room);?></th>
        <th><?php echo number_format($total_person);?></th>
        <th><?php echo number_format($total_room + $total_person);?> 원</th>
    </tr>  
    </thead>
</table>

<h3>- 결제정보</h3>
<table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
    <caption></caption>
    <colgroup>
        <col width="10%"/>
        <col width="30%"/>
        <col width="30%"/>
        <col width="30%"/>
    </colgroup>
    <thead>
    <tr>
        <th scope="col">비고</th>
        <th scope="col">예약금</th>
        <th scope="col">잔금</th>
        <th scope="col">총이용금액</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>금액</td>
        <td><?php echo number_format($bk['bk_reserv_price']);?> 원</td>
        <td><?php echo number_format($bk['bk_price'] - $bk['bk_reserv_price']);?> 원</td>
        <td><strong><?php echo number_format($bk['bk_price']);?> 원</strong></td>
    </tr>
    <tr>
        <td>상태</td>
        <td><?php echo ($bk['bk_reserv_price'] <= ($bk['bk_price'] - $bk['bk_misu']) ? '결제완료' : '미결제');?></td>
        <td><?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?></td>
        <td><strong><?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?></strong></td>
    </tr>
    </tbody>
</table>

<h3>- 예약금 결제방법</h3>
<table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
    <caption></caption>
    <colgroup>
        <col width="150px">
        <col width="auto">
    </colgroup>
    <tbody>

    <tr>
        <th>결제방법</th>
        <td>
            <?php echo $bk['bk_payment'];?>
        </td>
    </tr> 

    <?php if($app_no_subj) { // 승인번호, 휴대폰번호, 거래번호?>
    <tr>
        <th><?php echo $app_no_subj; ?></th>
        <td>
            <?php echo $app_no; ?>
        </td>
    </tr>
    <?php } ?>

    <?php if($disp_bank) {?>
    <tr>
        <th>입금정보</th>
        <td>
            <?php
            if ($bk['bk_deposit_name']) { 
                echo ' 입금자명 : '.get_text($bk['bk_deposit_name']);    
            } 
            if ($bk['bk_bank_account']) { 
                echo ' 입금계좌 : '.get_text($bk['bk_bank_account']);    
            } 
            ?>
        </td>
    </tr>
    <?php } ?>

    <?php if($disp_receipt) {?>
    <tr>
        <th>영수증</th>
        <td>
            <?php
            if($bk['bk_payment'] == '휴대폰')
            {
                if($bk['bk_pg'] == 'kcp') {
                    include_once(WZP_PLUGIN_PATH.'/gender/kcp/config.php');
                    $hp_receipt_script = 'window.open(\''.$g_receipt_url_bill.'mcash_bill&tno='.$bk['bk_tno'].'&order_no='.$bk['od_id'].'&trade_mony='.$bk['bk_receipt_price'].'\', \'winreceipt\', \'width=500,height=690,scrollbars=yes,resizable=yes\');';
                }
                else if($bk['bk_pg'] == 'lg') {
                    include_once(WZP_PLUGIN_PATH.'/gender/lg/config.php');
                    $LGD_TID      = $bk['bk_tno'];
                    $LGD_MERTKEY  = $wzpconfig['pn_pg_site_key'];
                    $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

                    $hp_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
                }
            ?>
            <a href="javascript:;" onclick="<?php echo $hp_receipt_script; ?>">영수증 출력</a>
            <?php
            }

            if($bk['bk_payment'] == '신용카드')
            {
                if($bk['bk_pg'] == 'kcp') {
                    include_once(WZP_PLUGIN_PATH.'/gender/kcp/config.php');
                    $card_receipt_script = 'window.open(\''.$g_receipt_url_bill.'card_bill&tno='.$bk['bk_tno'].'&order_no='.$bk['od_id'].'&trade_mony='.$bk['bk_receipt_price'].'\', \'winreceipt\', \'width=470,height=815,scrollbars=yes,resizable=yes\');';
                }
                else if($bk['bk_pg'] == 'lg') {
                    include_once(WZP_PLUGIN_PATH.'/gender/lg/config.php');
                    $LGD_TID      = $bk['bk_tno'];
                    $LGD_MERTKEY  = $wzpconfig['pn_pg_site_key'];
                    $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

                    $card_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
                }
            ?>
            <a href="javascript:;" onclick="<?php echo $card_receipt_script; ?>">영수증 출력</a>
            <?php
            }

            if($bk['bk_payment'] == 'KAKAOPAY')
            {
                $card_receipt_script = 'window.open(\'https://mms.cnspay.co.kr/trans/retrieveIssueLoader.do?TID='.$bk['bk_tno'].'&type=0\', \'popupIssue\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=420,height=540\');';
            ?>
            <a href="javascript:;" onclick="<?php echo $card_receipt_script; ?>">영수증 출력</a>
            <?php
            }
            ?>
        </td>
    </tr>
    <?php } ?>
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
        <th scope="col">예약상태</th>
        <td>
            <?php echo $bk['bk_status'];?>
            <?php
            if ($bk['bk_status'] == '대기') { 
                echo '&nbsp;<input type="button" class="btn_action" value="예약취소" onclick="getCancel();" />';
            }
            ?>
        </td>
    </tr> 
    <tr>
        <th scope="col">예약번호</th>
        <td><?php echo $bk['od_id'];?></td>
    </tr>
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


<script type="text/javascript">
<!--
    <?php if ($bk['bk_status'] == '대기') { 
    ?>
    function getCancel() {
        if (confirm("예약내역을 취소 하시겠습니까?")) {
            $.ajax({
                type: 'POST',
                url: '<?php echo $action_url?>',
                dataType: 'json',
                data: {'uid': '<?php echo $uid?>', 'od_id': '<?php echo $od_id?>', 'mode': 'cancel'},
                cache: false,
                async: false,
                success: function(json) {
                    if (json.rescd == '00') {
                        alert("취소되었습니다.");
                        location.reload();
                    }
                    else {
                        alert(json.restx);
                    }
                }
            });
        }
    }
    <?php } ?>
//-->
</script>
