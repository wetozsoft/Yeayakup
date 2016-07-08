<?php
$sub_menu = '780300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '예약정보 상세보기';

$bk_ix = (int)$_GET['bk_ix'];

$sql = " select * from {$g5['wzp_booking_table']} where bk_ix = '$bk_ix' ";
$bk = sql_fetch($sql);
if (!$bk['bk_ix']) alert('등록된 자료가 없습니다.', 'wzp_booking_list.php');

$bk_hp1 = $bk_hp2 = $bk_hp3 = '';
if ($bk['bk_hp']) { 
    $bk_hp1 = substr(str_replace('-', '', $bk['bk_hp']), 0, 3);
    $bk_hp2 = substr(str_replace('-', '', $bk['bk_hp']), 3, 4);
    $bk_hp3 = substr(str_replace('-', '', $bk['bk_hp']), 7);
} 

$is_done = true;
if ($bk['bk_status'] != '완료') {
    $is_done = false;
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
sql_free_result($res);

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<style>
.frm_input.number {text-align:right;padding-right:3px;}
</style>

<section id="anc_spp_pay" class="cbox">

     <h2 class="h2_frm">객실예약정보</h2>
    <div class="tbl_head01 tbl_wrap">
        <table>
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
            <th scope="col">삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $total_price = 0;
        if ($cnt_room > 0) { 
            for ($z = 0; $z < $cnt_room; $z++) { 
            ?>
            <input type="hidden" name="bkr_ix[]" value="<?php echo $row['bkr_ix'] ?>">
            <tr>
                <td class="td_alignc"><?php echo $arr_room[$z]['bkr_subject'];?></td>
                <td class="td_alignc"><?php echo wz_get_hangul_date_md($arr_room[$z]['bkr_frdate']).'('.get_yoil($arr_room[$z]['bkr_frdate']).') ~ '.wz_get_hangul_date_md($arr_room[$z]['bkr_todate']).'('.get_yoil($arr_room[$z]['bkr_todate']).')';?></td>
                <td class="td_alignc"><?php echo $arr_room[$z]['bkr_day'].'박'.($arr_room[$z]['bkr_day']+1).'일';?></td>
                <td class="td_alignc"><?php echo $arr_room[$z]['bkr_cnt_adult'];?> 명</td>
                <td class="td_alignc"><?php echo number_format($arr_room[$z]['bkr_price']);?> 원</td>
                <td class="td_alignc"><?php echo number_format($arr_room[$z]['bkr_price_adult']);?> 원</td>
                <td class="td_alignc"><?php echo number_format($arr_room[$z]['bkr_price'] + $arr_room[$z]['bkr_price_adult']);?> 원</td>
                <td class="td_alignc">
                    <?php if (!$is_done) {?>
                    <a href="./wzp_booking_form_update.php?mode=kd&amp;bkr_ix=<?php echo $arr_room[$z]['bkr_ix']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();">삭제</a>
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
            </tr>
            <?php 
            }
        } 
        else {
            ?>
            <tr>
                <td class="td_alignc" colspan="8">예약된 객실내역이 없습니다.</td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>

    <h2 class="h2_frm">결제정보</h2>

    <div class="tbl_head01 tbl_wrap">
    <table>
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
            <td class="td_alignc">금액</td>
            <td class="td_alignc"><?php echo number_format($bk['bk_reserv_price']);?> 원</td>
            <td class="td_alignc"><?php echo number_format($bk['bk_price'] - $bk['bk_reserv_price']);?> 원</td>
            <td class="td_alignc"><strong><?php echo number_format($bk['bk_price']);?> 원</strong></td>
        </tr>
        <tr>
            <td class="td_alignc">상태</td>
            <td class="td_alignc"><?php echo ($bk['bk_reserv_price'] <= ($bk['bk_price'] - $bk['bk_misu']) ? '결제완료' : '미결제');?></td>
            <td class="td_alignc"><?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?></td>
            <td class="td_alignc"><strong><?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?></strong></td>
        </tr>
        </tbody>
    </table>
    </div>

    <form method="post" name="frmpay" id="frmpay" action="./wzp_booking_form_update.php?<?php echo $qstr;?>">
    <input type="hidden" name="mode" value="pay">
    <input type="hidden" name="bk_ix" value="<?php echo $bk_ix ?>">
    <input type="hidden" name="bk_price" id="bk_price" value="<?php echo $bk['bk_price'];?>" />

    <div class="tbl_frm01 tbl_wrap">
        
        <table>
        <caption>접속자집계 목록</caption>
        <colgroup>
            <col width="15%">
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
                <p><?php echo $app_no; ?></p>
            </td>
        </tr>
        <?php } ?>

        <?php if($disp_bank) {?>
        <tr>
            <th>입금정보</th>
            <td>
                <p>
                    <?php
                    echo ' 입금자명 : '.get_text($bk['bk_deposit_name']).' 입금계좌 : '.get_text($bk['bk_bank_account']);
                    ?>
                </p>
            </td>
        </tr>
        <?php } ?>

        <?php if($disp_receipt) {?>
        <tr>
            <th>영수증</th>
            <td>
                <p>
                <?php
                if($bk['bk_payment'] == '휴대폰')
                {
                    if($bk['bk_pg'] == 'kcp') {
                        include_once(WZP_PLUGIN_PATH.'/gender/kcp/config.php');
                        $hp_receipt_script = 'window.open(\''.$g_receipt_url_bill.'mcash_bill&tno='.$bk['bk_tno'].'&order_no='.$bk['od_id'].'&trade_mony='.$bk['bk_receipt_price'].'\', \'winreceipt\', \'width=500,height=690,scrollbars=yes,resizable=yes\');';
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
                </p>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th>결제처리</th>
            <td>
                <?php if ($bk['bk_payment'] == '가상계좌' && $bk['bk_status'] == '취소' && $bk['bk_pg_price']) {?>
                <div style="text-align:left;color:red;padding:5px 0 2px 8px">가상계좌는 자동환불처리가 되지 않으므로 반드시 예약자님께 직접 송금 처리 바랍니다. (환불금액 : <?php echo number_format($bk['bk_pg_price']);?> 원)</div>
                <?php } ?>
                <p>
                    예약상태 : 
                    <select name="bk_status" id="bk_status">
                        <option value="대기" <?php echo ($bk['bk_status'] == '대기' ? 'selected=selected' : '');?>>대기</option>
                        <option value="완료" <?php echo ($bk['bk_status'] == '완료' ? 'selected=selected' : '');?>>완료</option>
                        <option value="취소" <?php echo ($bk['bk_status'] == '취소' ? 'selected=selected' : '');?>>취소</option>
                    </select>
                    <span class="vbar">&#124;</span>
                    
                    <?php if ($bk['bk_payment'] == '신용카드' || $bk['bk_payment'] == '계좌이체' || $bk['bk_payment'] == '휴대폰') {?>
                        <?php if (!$bk['bk_pg_cancel']) {?>
                        <label><input type="checkbox" name="bk_cancel" id="bk_cancel" value="1" /> 결제승인취소</label>
                        <?php } else { ?>
                        <label>PG결제 취소 완료</label>
                        <?php } ?>
                    <span class="vbar">&#124;</span>
                    <?php } ?>
                    
                    입금액 : <input type="text" name="bk_receipt_price" id="bk_receipt_price" value="<?php echo $bk['bk_receipt_price'];?>" required class="required frm_input number" style="width:80px;" maxlength="15" onkeyup="_jsCalculate('receipt');" onblur="_jsCalculate('receipt');" /> 원
                    <span class="vbar">&#124;</span>
                    미수금 : <input type="text" name="bk_misu" id="bk_misu" value="<?php echo $bk['bk_misu'];?>" required class="required frm_input number" style="width:80px;" maxlength="15" onkeyup="_jsCalculate('misu');" onblur="_jsCalculate('misu');" /> 원
                </p>
            </td>
        </tr> 
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="결제처리변경" class="btn_submit" accesskey="s">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>

    </form>
    
    <h2 class="h2_frm">예약자정보</h2>

    <form method="post" name="frminfo" id="frminfo" action="./wzp_booking_form_update.php?<?php echo $qstr;?>" onsubmit="return getAction(this);">
    <input type="hidden" name="mode" value="info">
    <input type="hidden" name="bk_ix" value="<?php echo $bk_ix ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>예약자정보</caption>
        <colgroup>
            <col width="15%">
            <col width="35%">
            <col width="15%">
            <col width="35%">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약번호</th>
            <td colspan="3"><?php echo $bk['od_id'];?></td>
        </tr>
        <tr>
            <th scope="col">예약자명</th>
            <td>
                <input type="text" name="bk_name" id="bk_name" value="<?php echo $bk['bk_name'];?>" required class="required frm_input" maxlength="100" size="20" />
            </td>
            <th scope="col">회원아이디</th>
            <td>
                <input type="text" name="mb_id" id="mb_id" value="<?php echo $bk['mb_id'];?>" class="frm_input" maxlength="100" size="15" />
            </td>
        </tr>
        <tr>
            <th scope="col">핸드폰</th>
            <td>
                <select name="bk_hp1" id="bk_hp1">
                    <option value="">선택</option>
                    <option value="010">010</option>
                    <option value="011">011</option>
                    <option value="016">016</option>
                    <option value="017">017</option>
                    <option value="018">018</option>
                    <option value="019">019</option>
                </select> - 
                <input type="text" name="bk_hp2" id="bk_hp2" value="<?php echo $bk_hp2;?>" style="width:50px;" required class="required frm_input" maxlength="4" /> - 
                <input type="text" name="bk_hp3" id="bk_hp3" value="<?php echo $bk_hp3;?>" style="width:50px;" required class="required frm_input" maxlength="4" />
                <script type="text/javascript"> document.getElementById("bk_hp1").value = '<?php echo $bk_hp1?>' </script>
            </td>
            <th scope="col">이메일</th>
            <td>
                <input type="text" name="bk_email" id="bk_email" value="<?php echo $bk['bk_email'];?>" style="width:300px;" maxlength="100" class="frm_input" />
            </td>
        </tr>
        <tr>
            <th scope="col">요청사항</th>
            <td colspan="3">
                <textarea name="bk_memo" id="bk_memo" style="width:98%;height:100px;"><?php echo $bk['bk_memo'];?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="col">예약등록일</th>
            <td colspan="3"><?php echo $bk['bk_time'];?></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="예약자정보수정" class="btn_submit" accesskey="s">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>

    </form>

</section>

</form>

<script type="text/javascript">
<!--
    function _jsCalculate(mode) {
        var f = document.frmpay;
        var bk_price = parseInt(f.bk_price.value);

        if (mode == 'receipt') {
            f.bk_misu.value = bk_price - parseInt(f.bk_receipt_price.value);
        }
        else {
            f.bk_receipt_price.value = bk_price - parseInt(f.bk_misu.value);
        }
    }
    function getAction(f) {

    }
//-->
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>