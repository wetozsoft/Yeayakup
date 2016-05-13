<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 디폴트 예약날짜
define('WZP_DEFAULT_TODAY', wz_get_addday(G5_TIME_YMD, 1));

if (isset($_GET['sch_year']) && $_GET['sch_year'])
    $sch_year = (int)$_GET['sch_year'];
else
    $sch_year = substr(WZP_DEFAULT_TODAY,0,4);

if (isset($_GET['sch_month']) && $_GET['sch_month']) 
    $sch_month = (int)$_GET['sch_month'];
else
    $sch_month = (int)substr(WZP_DEFAULT_TODAY,5,2);

$sch_day = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_GET['sch_day']) ? $_GET['sch_day'] : WZP_DEFAULT_TODAY;

if (isset($_GET['sch_year']) && $_GET['sch_year'] && isset($_GET['sch_month']) && $_GET['sch_month']) {
    
} 
else { // 실시간예약 처음화면에서 넘어왔을경우.
    $sch_year   = $sch_day ? substr($sch_day, 0, 4) : $sch_year;
    $sch_month  = $sch_day ? substr($sch_day, 5, 2) : $sch_month;
}

// 지난달과 다음달을 보는 루틴
$year_p = $sch_year - 1;
$year_n = $sch_year + 1;

if($sch_month == 1) {
    $year_prev	= $year_p;
    $year_next	= $sch_year;
    $month_prev	= 12;
    $month_next	= $sch_month + 1;
}
else if($sch_month == 12) {
    $year_prev	= $sch_year;
    $year_next	= $year_n;
    $month_prev	= $sch_month - 1;
    $month_next	= 1;
}
else if($sch_month != 1 && $sch_month != 12) {
    $year_prev	= $sch_year;
    $year_next	= $sch_year;
    $month_prev	= $sch_month - 1;
    $month_next	= $sch_month + 1;
}

$sch_month_02d  = sprintf("%02d", $sch_month);

// 객실별 예약상태정보
unset($arr_status);
$arr_status = array();
$query = "select 
            rms.rm_ix, min(rms_date) as rms_date, 
            rm.rm_price_adult
          from {$g5['wzp_room_status_table']} as rms inner join {$g5['wzp_room_table']} as rm on rms.rm_ix = rm.rm_ix 
          where rms_date >= '$sch_day' and (rms.rms_status = '완료' or rms.rms_status = '대기')
          group by rms.rm_ix";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    // 예약이 가능한 날짜계산.
    $max_day = wz_date_between($sch_day, $row['rms_date']);
    if ($max_day > $wzpconfig['pn_max_booking_day']) // 관리자에서 정해진 최대 예약일수보다 클경우.
        $max_day = (int)$wzpconfig['pn_max_booking_day'];

    $arr_status[$row['rm_ix']]['max_day'] = $max_day;
}
$cnt_status = count($arr_status);
sql_free_result($res);

// 시즌정보
$today_type = wz_get_type($sch_day);

// 객실정보
unset($arr_room);
$arr_room = array();
$query = "select * from {$g5['wzp_room_table']} order by rm_sort asc ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $row['price'] = wz_calculate($row['rm_ix'], $today_type);    
    $arr_room[] = $row;
}
$cnt_room = count($arr_room);
sql_free_result($res);

if ($sch_day <= G5_TIME_YMD) { // 오늘 이전날짜 예약불가.
    $cnt_room = 0;
}

// 최대예약가능일.
$day_expire = wz_get_addday(G5_TIME_YMD, $wzpconfig['pn_max_booking_expire']);
?>

<div class="st1-header">
    
    <div class="st1-left">

        <div class="cal_navi">
            <a href="javascript:getCalender('<?php echo $year_prev?>','<?php echo $month_prev?>','<?php echo $sch_day?>');"><span class="btn_reserve_prev">&lt;</span></a>&nbsp;
            <span class="title_red"><?php echo $sch_year?>년 <span><?php echo $sch_month_02d?>월</span>&nbsp;
            <a href="javascript:getCalender('<?php echo $year_next?>','<?php echo $month_next?>','<?php echo $sch_day?>');"><span class="btn_reserve_next">&gt;</span></a>       
        </div>

        <table border="0" cellpadding="0" cellspacing="0" class="caltable">
        <tbody>
        <tr height="30">
            <th class="sunday">일</th>
            <th>월</th>
            <th>화</th>
            <th>수</th>
            <th>목</th>
            <th>금</th>
            <th class="saturday">토</th>
        </tr>
        <tr height="30" class="date">
            <?php
            $total_day      = wz_max_day($sch_month, $sch_year);
            $first_day      = date('w', mktime(0, 0, 0, $sch_month, 1, $sch_year));
            $count          = 0;

            for ($i = 0; $i < $first_day; $i++) {
                echo '<td class="mini prev"></td>'.PHP_EOL;
                $count++;
            }

            for ($day = 1; $day <= $total_day; $day++) {
                
                $count++;
                $vDate = $sch_year ."-". $sch_month_02d ."-". sprintf("%02d", $day); // 표시 날짜.

                if ($vDate == $sch_day) { // 오늘 표시
                    $bg_class = 'dday';
                }
                else { // 오늘이 아니면...

                    if ($count == 1) // 일요일
                        $bg_class = 'sun';
                    elseif ($count == 7) // 토요일
                        $bg_class = 'sat';
                    else // 평일
                        $bg_class = '';

                }

                echo '<td class="mini '. $bg_class .'">'.PHP_EOL;

                // 당일 및 이전날짜 예약불가.
                // 2016-03-30 : 최대예약가능일 추가.
                if ($vDate <= G5_TIME_YMD || $vDate > $day_expire) { 
                    echo '  <span class="closeday">'.$day.'</span>'.PHP_EOL; 
                } 
                else {
                    echo '  <a class="titday" href="#none" onclick="getCalender(\''.$sch_year.'\',\''.$sch_month.'\',\''.$vDate.'\')">'.(wz_holiday_check($vDate) ? '<span class="hlday">'.wz_holiday_check($vDate).'</span>' : $day).'</a>'.PHP_EOL; 
                }
                echo '</td>'.PHP_EOL;

                if ($count==7) { // 토요일이 되면 줄바꾸기 위한 <tr>태그 삽입을 위한 식
                    echo '</tr>'.PHP_EOL;
                    if ($day != $total_day) {
                        echo '<tr height="30" class="date">'.PHP_EOL;
                        $count = 0;
                    }
                }
            }

            // 선택한 월의 마지막날 이후의 빈테이블 삽입
            for ($day++; $total_day < $day && $count < 7;) {
                $count++;
                echo '<td class="mini next"></td>'.PHP_EOL;
                if ($count == 7) 
                    echo '</tr>'.PHP_EOL;
            }
            ?>
        </tbody>
        </table>

    </div>
    <div class="st1-right">
        <div class="bx">
            <h3>예약일 : <?php echo date('Y년 m월 d일',strtotime(str_replace('-', '', $sch_day)));?></h3>
            <ul class="desc">                
                <li>달력에서 원하시는 예약일을 선택하시면 이용가능한 객실의 정보가 출력됩니다.</li>
                <li>예약 전 반드시 주의사항 / 환불규정을 숙지하시기 바랍니다.</li>
            </ul>
            <div class="notice">
                <?php echo $wzpconfig['pn_con_notice'];?>
            </div>
        </div>
    </div>
</div>

<div class="st1-list">
    
    <form method="post" name="wzfrm" id="wzfrm">
    <input type="hidden" name="mode" id="mode" value="step2" />
    <input type="hidden" name="sch_day" id="sch_day" value="<?php echo $sch_day;?>" />
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type">
        <caption></caption>
        <colgroup>
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">선택</th>
            <th scope="col">객실명</th>
            <th scope="col">크기</th>
            <th scope="col">기준인원</th>
            <th scope="col">기간</th>
            <th scope="col">인원선택</th>
            <th scope="col">요금</th>
        </tr>
        <?php 
        if ($cnt_room > 0) { 
            for ($z = 0; $z < $cnt_room; $z++) { 

            $max_day = $wzpconfig['pn_max_booking_day']; // 관리자에서 정해진 최대 예약일수.
            if (isset($arr_status[$arr_room[$z]['rm_ix']]))
                $max_day = $arr_status[$arr_room[$z]['rm_ix']]['max_day'];
            ?>
            <tr>
                <td>
                    <?php if ($max_day) { ?>
                    <input type="hidden" name="rm_ix[<?php echo $z;?>]" id="rm_ix_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_ix'];?>" />
                    <input type="checkbox" name="chk[]" id="chk_<?php echo $z;?>" value="<?php echo $z;?>" <?php echo $arr_room[$z]['rm_ix'] == $rm_ix ? 'checked' : '';?> />
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
                <td><?php echo $arr_room[$z]['rm_subject'];?></td>
                <td><?php echo $arr_room[$z]['rm_size'];?></td>
                <td><?php echo $arr_room[$z]['rm_person_min'].'/'.$arr_room[$z]['rm_person_max'];?></td>
                <td>
                    <?php if ($max_day) { ?>
                    <select name="bk_day[<?php echo $z;?>]" id="bk_day_<?php echo $z;?>">
                        <?php 
                        for ($i=1;$i<=$max_day;$i++) { 
                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'박 '.($i+1).'일</option>';
                        } 
                        ?>
                    </select>
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
                <td>
                    <?php if ($max_day) { ?>
                    <select name="bk_cnt_adult[<?php echo $z;?>]" id="bk_cnt_adult_<?php echo $z;?>" data-price="<?php echo $arr_room[$z]['rm_price_adult'];?>" data-min="<?php echo $arr_room[$z]['rm_person_min'];?>">
                        <?php 
                        for ($i=1;$i<=$arr_room[$z]['rm_person_max'];$i++) { 
                            $selected = '';
                            if ($i == $arr_room[$z]['rm_person_min'])
                                $selected = 'selected';

                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'명</option>';
                        } 
                        ?>
                    </select>
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
                <td><?php echo number_format($arr_room[$z]['price']);?> 원</td>
            </tr>
            <?php 
            }
        } 
        else {
            ?>
            <tr>
                <td colspan="7">예약할수 있는 객실이 존재하지 않습니다.</td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>
    </form>
    
    <div class="action">
        <a href="<?php echo WZP_STATUS_URL;?>" class="btn_submit before">&lt; 이전단계</a>&nbsp;
        <a href="javascript:getNext();" class="btn_submit next">다음단계 &gt;</a>
    </div>

</div>

<script type="text/javascript">
<!--
    function getCalender(sch_year, sch_month, sch_day) { 
        location.href = "<?php echo WZP_STATUS_URL?>&mode=step1&sch_year="+sch_year+"&sch_month="+sch_month+"&sch_day="+sch_day;
    }
    function getNext() { 
        var f = document.forms.wzfrm;
        if ($("input[name='chk[]']:checkbox:checked").length < 1) {
            alert("예약할 객실을 한개이상 체크해주세요.");
            return;
        }
        f.action = "<?php echo WZP_STATUS_URL;?>";
        f.target = "_self";
        f.submit();
    }
//-->
</script>

