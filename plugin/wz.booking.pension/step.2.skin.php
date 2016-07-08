<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (isset($_POST['sch_day']) && $_POST['sch_day']) {
    $sch_day = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_POST['sch_day']) ? $_POST['sch_day'] : "";
}

if (!$sch_day) { 
    alert("잘못된 접근입니다.", WZP_STATUS_URL);
}

// 선택객실정보.
unset($arr_room);
$arr_room   = wz_calculate_room($_POST);
$bk_subject = $arr_room[0]['rm_subject'];
$cnt_room   = count($arr_room);

$bk_hp1 = $bk_hp2 = $bk_hp3 = '';
if ($is_member) { 
    if ($member['mb_hp']) { 
        $bk_hp1 = substr(str_replace('-', '', $member['mb_hp']), 0, 3);
        $bk_hp2 = substr(str_replace('-', '', $member['mb_hp']), 3, 4);
        $bk_hp3 = substr(str_replace('-', '', $member['mb_hp']), 7);
    } 
} 

// 주문번호 생성.
$od_id      = $wzpconfig['pn_ix'].substr(date('YmdHis',mktime()),2).rand(100,999);
set_session('ss_order_id', $od_id);
$action_url = https_url(G5_PLUGIN_DIR.'/wz.booking.pension/step.2.update.php', true);
$goods      = $bk_subject . ($cnt_room>1 ? ' 외'.($cnt_room-1).'건' : '');

// 모바일 주문인지
$is_mobile_pay = is_mobile();

// PG 결제를 위한 코드
if ($wzpconfig['pn_pg_service']) { 
    @include_once(WZP_PLUGIN_PATH.'/gender/'.$wzpconfig['pn_pg_service'].'/config.php');
    @include_once(WZP_PLUGIN_PATH.'/gender/'.$wzpconfig['pn_pg_service'].'/pg_form1.php');
}
?>

<div class="st2-form">
    
    <form method="post" name="wzfrm" id="wzfrm">
    <input type="hidden" name="mode" id="mode" value="step3" />
    <input type="hidden" name="sch_day" id="sch_day" value="<?php echo $sch_day;?>" />
    <input type="hidden" name="bo_table" id="bo_table" value="<?php echo $bo_table;?>" />
    <input type="hidden" name="od_id" id="od_id" value="<?php echo $od_id;?>" />

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

            $last_day = wz_get_addday($sch_day, $arr_room[$z]['bk_day']);
            ?>
            <input type="hidden" name="chk[]" value="<?php echo $z;?>" id="chk_<?php echo $z;?>" />
            <input type="hidden" name="rm_ix[<?php echo $z;?>]"     value="<?php echo $arr_room[$z]['rm_ix'];?>" />
            <input type="hidden" name="person_min[<?php echo $z;?>]" id="person_min_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_person_min'];?>" />
            <input type="hidden" name="person_max[<?php echo $z;?>]" id="person_max_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_person_max'];?>" />
            <input type="hidden" name="bk_day[<?php echo $z;?>]"    value="<?php echo $arr_room[$z]['bk_day'];?>" />
            <input type="hidden" name="bk_cnt_adult[<?php echo $z;?>]"    value="<?php echo $arr_room[$z]['bk_cnt_adult'];?>" />

            <tr>
                <td><?php echo $arr_room[$z]['rm_subject'];?></td>
                <td><?php echo wz_get_hangul_date_md($sch_day).'('.get_yoil($sch_day).') ~ '.wz_get_hangul_date_md($last_day).'('.get_yoil($last_day).')';?></td>
                <td><?php echo $arr_room[$z]['bk_day'];?>박 <?php echo $arr_room[$z]['bk_day']+1;?>일</td>
                <td><?php echo $arr_room[$z]['bk_cnt_adult'];?>명</td>
                <td><?php echo number_format($arr_room[$z]['price_room']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['price_person']);?> 원</td>
                <td><?php echo number_format($arr_room[$z]['price_room'] + $arr_room[$z]['price_person']);?> 원</td>
            </tr>
            <?php 
            $total_room     += $arr_room[$z]['price_room'];
            $total_person   += $arr_room[$z]['price_person'];
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
        <?php
        $total_price += $total_room + $total_person;
        $reserv_price = round(($total_price / 100) * ($wzpconfig['pn_reserv_price_avg'] ? $wzpconfig['pn_reserv_price_avg'] : 100));
        ?>
    </table>

    <h3>- 최종결제금액</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="130px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">총 이용요금</th>
            <td>
                <strong><?php echo number_format($total_price);?> 원</strong>
            </td>
        </tr>
        <tr>
            <th scope="col">예약금</th>
            <td>
                <input type="hidden" name="bk_price" id="bk_price" value="<?php echo $reserv_price;?>" />
                <input type="hidden" name="org_bk_price" id="org_bk_price" value="<?php echo $reserv_price;?>" />
                <strong id="od_tot_price"><?php echo number_format($reserv_price);?> 원</strong> (결제/입금이 완료되어야 최종 예약이 완료됩니다.)
            </td>
        </tr>
        <tr>
            <th scope="col">결제방법</th>
            <td>
                <?php
                if ($wzpconfig['pn_bank_use']) { 
                    echo '<label><input type="radio" name="bk_payment" id="bk_payment_bank" class="payment_type" value="무통장" /> 무통장입금</label>&nbsp;';    
                } 
                if ($wzpconfig['pn_pg_card_use']) { 
                    echo '<label><input type="radio" name="bk_payment" id="bk_payment_card" class="payment_type" value="신용카드" /> 신용카드</label>&nbsp;';    
                } 
                if ($wzpconfig['pn_pg_vbank_use']) { 
                    echo '<label><input type="radio" name="bk_payment" id="bk_payment_vbank" class="payment_type" value="가상계좌" /> 가상계좌</label>&nbsp;';    
                } 
                if ($wzpconfig['pn_pg_dbank_use']) { 
                    echo '<label><input type="radio" name="bk_payment" id="bk_payment_dbank" class="payment_type" value="계좌이체" /> 계좌이체</label>&nbsp;';    
                } 
                if ($wzpconfig['pn_pg_hp_use']) { 
                    echo '<label><input type="radio" name="bk_payment" id="bk_payment_hp" class="payment_type" value="휴대폰" /> 휴대폰</label>&nbsp;';    
                } 
                ?>
            </td>
        </tr>
        <tr id="bank_info_box" style="display:none;">
            <th scope="col">무통장입금</th>
            <td>
                <div class="option-desc">당일 입금 확인을 위해 무통장 결제는 평일 오전9시~오후4시, 토요일 오전9시~오후12시(공휴일 제외)까지만 가능합니다.</div>
                <div style="margin:3px 0 3px">
                    입금자명 : <input type="text" name="bk_deposit_name" id="bk_deposit_name" style="width:100px;" maxlength="20" />
                </div>
                <div style="margin:5px 0 3px">
                    <?php
                    $str = explode("\n", trim($wzpconfig['pn_bank_info']));
                    if (count($str) <= 1) {
                        $bank_account = '<input type="hidden" name="bk_bank_account" value="'.$str[0].'">입금계좌 : <strong>'.$str[0].'</strong>'.PHP_EOL;
                    }
                    else {
                        $bank_account = '입금계좌 : <select name="bk_bank_account" id="bk_bank_account">'.PHP_EOL;
                        $bank_account .= '<option value="">선택하십시오.</option>';
                        for ($i=0; $i<count($str); $i++) {
                            $str[$i] = trim($str[$i]);
                            $bank_account .= '<option value="'.$str[$i].'">'.$str[$i].'</option>'.PHP_EOL;
                        }
                        $bank_account .= '</select>'.PHP_EOL;
                    }
                    echo $bank_account;
                    ?>
                </div>
            </td>
        </tr>
    </table>

    <h3>- 예약자정보입력</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="130px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약자명</th>
            <td>
                <input type="text" name="bk_name" id="bk_name" value="<?php echo $member['mb_name'];?>" style="width:100px;" maxlength="20" /> (실명으로 입력해주세요)
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
                <input type="text" name="bk_hp2" id="bk_hp2" value="<?php echo $bk_hp2;?>" style="width:50px;" maxlength="4" /> - 
                <input type="text" name="bk_hp3" id="bk_hp3" value="<?php echo $bk_hp3;?>" style="width:50px;" maxlength="4" />
                <script type="text/javascript"> document.getElementById("bk_hp1").value = '<?php echo $bk_hp1?>' </script>
            </td>
        </tr>
        <tr>
            <th scope="col">이메일</th>
            <td>
                <input type="text" name="bk_email" id="bk_email" value="<?php echo $member['mb_email'];?>" style="width:80%;" maxlength="100" />
            </td>
        </tr>
        <tr>
            <th scope="col">요청사항</th>
            <td>
                <textarea name="bk_memo" id="bk_memo" style="width:98%;height:100px;"></textarea>
            </td>
        </tr>
    </table>  
    
    <h3>- 기본예약안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_info'];?></div></div>

    <h3>- 입/퇴실 안내</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_checkinout'];?></div></div>

    <h3>- 환불규정</h3>
    <div class="box_type"><div class="noti"><?php echo $wzpconfig['pn_con_refund'];?></div></div>

    <div class="agree">
        <input type="checkbox" name="agree1" id="agree1" value="1" /><label for="agree1"> 상기의 내용을 숙지하고 예약 및 환불규정에 동의 합니다.</label>
    </div>

    <h3>- 개인정보 활용 동의</h3>
    <div class="box_type">
        <div class="noti privacy">
            귀하의 소중한 개인정보는 개인정보보호법의 관련 규정에 의하여 예약 및 조회 등 아래의 목적으로 수집 및 이용됩니다.
            <ul class="purpose">
                <li>1. 개인정보의 수집·이용 목적 - 프로그램/숙박/대관 예약, 조회를 위한 본인 확인 절차</li>
                <li>2. 개인정보 수집 항목 - 예약자명, 핸드폰, 이메일</li>
                <li>3. 개인정보의 보유 및 이용기간 - 이용자의 개인정보는 원칙적으로 개인정보의 처리목적이 달성되면 지체 없이 파기합니다.</li>
            </ul>
            
            예약을 위하여 수집된 개인정보는 ‘전자상거래 등에서의 소비자보호에 관한 법률’ 제6조에의거 정해진 기간동안 보유됩니다.<br />
            ※ 상기 내용은 고객님께 예약서비스를 제공하는데 필요한 최소한의 정보입니다.<br />
            ※ 상기 내용에 대하여 본인이 동의하지 않을 수 있으나, 그러할 경우 예약 서비스 제공에 차질이 발생할 수 있습니다.
        </div>
    </div>

    <div class="agree">
        <input type="checkbox" name="agree2" id="agree2" value="1" /><label for="agree2"> 개인정보 활용에 동의합니다.</label>
    </div>

    <div class="action" id="display_pay_button">
        <a href="<?php echo WZP_STATUS_URL;?>&mode=step1&sch_day=<?php echo $sch_day;?>" class="btn_submit before">&lt; 이전단계</a>&nbsp;
        <a href="javascript:getNext();" class="btn_submit next">예약하기 &gt;</a>
    </div>
    <div id="display_pay_process" style="display:none;">
        결제가 진행중입니다...
    </div>

    <?php
    if ($wzpconfig['pn_pg_service']) { 
        @include_once(WZP_PLUGIN_PATH.'/gender/'.$wzpconfig['pn_pg_service'].'/pg_form2.php');    
    }     
    ?>

    </form>

</div>

<script type="text/javascript">
<!--
    function getCalender(sch_year, sch_month, sch_day) { 
        location.href = "<?php echo WZP_STATUS_URL?>&mode=step1&sch_year="+sch_year+"&sch_month="+sch_month+"&sch_day="+sch_day;
    }
    function getNext() { 
        var f = document.forms.wzfrm;

        var rm_cnt = $("input[name='chk[]']").length;
        if (rm_cnt < 1) {
            alert("예약할 객실이 존재하지 않습니다.");
            return;
        }
        if (!f.bk_name.value) {
            alert("예약자명을 입력해주세요.");
            f.bk_name.focus();
            return;
        }
        if (f.bk_hp1.selectedIndex == 0) {
            alert("핸드폰번호를 선택해주세요.");
            f.bk_hp1.focus();
            return;
        }
        if (!f.bk_hp2.value) {
            alert("핸드폰번호를 입력해주세요.");
            f.bk_hp2.focus();
            return;
        }
        if (!f.bk_hp3.value) {
            alert("핸드폰번호를 입력해주세요.");
            f.bk_hp3.focus();
            return;
        }
        var _bk_payment = f.bk_payment.value;
        if (_bk_payment == '무통장') {
            if (!f.bk_deposit_name.value) {
                alert("입금자명을 입력해주세요.");
                f.bk_deposit_name.focus();
                return;
            }
            var bk_bank_account = document.getElementById("bk_bank_account");
            if (bk_bank_account) {
                if (f.bk_bank_account.selectedIndex == 0) {
                    alert("계좌번호를 선택해주세요.");
                    f.bk_bank_account.focus();
                    return;
                }
            }
        }

        if (f.agree1.checked == false) {
            alert("예약 및 환불규정에 동의 후 예약이 가능합니다.");
            f.agree1.focus();
            return;
        }
        if (f.agree2.checked == false) {
            alert("개인정보 활용에 동의 후 예약이 가능합니다.");
            f.agree2.focus();
            return;
        }

        var payment = $(":input:radio[name=bk_payment]:checked").val();

        if (!payment) {
            alert("결제방식을 선택해주세요.");
            return;
        }

        if (payment == '무통장') {
            if (confirm("예약하시겠습니까?")) {
                f.action = "<?php echo $action_url;?>";
                f.target = "_self";
                f.submit();
            }
        }
        else {
            pg_pay(f);
        }        
        
    }
//-->
</script>

<script type="text/javascript">
<!--
$(function() {
    $(".payment_type").on("click", function() {
        var payment = $(":input:radio[name=bk_payment]:checked").val();
        if (payment == '무통장') {
            $("#bk_deposit_name").val( $("#bk_name").val() );
            $("#bank_info_box").show();
        }
        else {
            $("#bank_info_box").hide();
        }
    });
});
//-->
</script>