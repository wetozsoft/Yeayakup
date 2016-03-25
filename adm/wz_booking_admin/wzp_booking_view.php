<?php
$sub_menu = '780300';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

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

<form name="frm" action="./wzp_booking_form_update.php" method="post" onsubmit="return getAction(this);">
<input type="hidden" name="w" value="u">
<input type="hidden" name="bk_ix" value="<?php echo $bk_ix; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_spp_pay" class="cbox">
    
    <h2 class="h2_frm">예약자정보</h2>
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
            <td><?php echo $bk['od_id'];?></td>
            <th scope="col">예약상태</th>
            <td>
                <?php if ($bk['bk_status'] == '취소') {?>
                    <input type="hidden" name="bk_status" id="bk_status" value="<?php echo $bk['bk_status'];?>" />
                    <?php echo $bk['bk_status'];?>
                <?php } else { ?>
                <select name="bk_status" id="bk_status">
                    <option value="대기">대기</option>
                    <option value="완료">완료</option>
                    <option value="취소">취소</option>
                </select>
                <script type="text/javascript"> document.getElementById("bk_status").value = "<?php echo $bk['bk_status']?>"; </script>
                <?php } ?>
            </td>
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
        <input type="submit" value="예약정보수정" class="btn_submit" accesskey="s">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>

    <h2 class="h2_frm">결제정보</h2>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>접속자집계 목록</caption>
        <colgroup>
            <col width="15%">
            <col width="85%">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">이용금액</th>
            <td>
                <strong><?php echo number_format($bk['bk_price']);?> 원</strong>
                (<?php echo ($bk['bk_misu'] ? '미결제' : '결제완료');?>)
            </td>
        </tr>
        <tr>
            <th scope="col">미수금</th>
            <td>
                <input type="text" name="bk_misu" id="bk_misu" value="<?php echo $bk['bk_misu'];?>" required class="required frm_input number"  maxlength="20" size="10" /> 원
            </td>
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
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="예약정보수정" class="btn_submit" accesskey="s">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>


    <h2 class="h2_frm">객실예약현황</h2>
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
                <td style="text-align:center;"><?php echo $arr_room[$z]['bkr_subject'];?></td>
                <td style="text-align:center;"><?php echo wz_get_hangul_date($arr_room[$z]['bkr_frdate']).'('.get_yoil($arr_room[$z]['bkr_frdate']).') ~ '.wz_get_hangul_date($arr_room[$z]['bkr_todate']).'('.get_yoil($arr_room[$z]['bkr_todate']).')';?></td>
                <td style="text-align:center;"><?php echo $arr_room[$z]['bkr_day'].'박'.($arr_room[$z]['bkr_day']+1).'일';?></td>
                <td style="text-align:center;"><?php echo $arr_room[$z]['bkr_cnt_adult'];?> 명</td>
                <td style="text-align:center;"><?php echo number_format($arr_room[$z]['bkr_price']);?> 원</td>
                <td style="text-align:center;"><?php echo number_format($arr_room[$z]['bkr_price_adult']);?> 원</td>
                <td style="text-align:center;"><?php echo number_format($arr_room[$z]['bkr_price'] + $arr_room[$z]['bkr_price_adult']);?> 원</td>
                <td style="text-align:center;">
                    <?php if (!$is_done) {?>
                    <a href="./wzp_booking_form_update.php?w=kd&amp;bkr_ix=<?php echo $arr_room[$z]['bkr_ix']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();">삭제</a>
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
                <td style="text-align:center;" colspan="8">예약된 객실내역이 없습니다.</td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="예약정보수정" class="btn_submit" accesskey="s">
        <a href="./wzp_booking_list.php?<?php echo $qstr; ?>">목록으로</a>
    </div>

</section>

</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>