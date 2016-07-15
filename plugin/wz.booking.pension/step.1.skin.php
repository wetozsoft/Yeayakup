<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 디폴트 예약날짜
define('WZP_DEFAULT_TODAY', wz_get_addday(G5_TIME_YMD, 1));

if (isset($_GET['sch_year']) && $_GET['sch_year'])
    $sch_year = (int)$_GET['sch_year'];

if (isset($_GET['sch_month']) && $_GET['sch_month']) 
    $sch_month = (int)$_GET['sch_month'];

$sch_day = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_GET['sch_day']) ? $_GET['sch_day'] : WZP_DEFAULT_TODAY;

if (isset($sch_year) && $sch_year && isset($sch_month) && $sch_month) {

}
else { // 실시간예약 처음화면에서 넘어왔을경우.
    $sch_year   = $sch_day ? substr($sch_day, 0, 4) : $sch_year;
    $sch_month  = $sch_day ? substr($sch_day, 5, 2) : $sch_month;
}

$wz_cal = new wz_calendar($sch_year, $sch_month, $sch_day);
$total_day      = $wz_cal->total_day;
$year_prev      = $wz_cal->year_prev;
$month_prev     = $wz_cal->month_prev;
$year_next      = $wz_cal->year_next;
$month_next     = $wz_cal->month_next;
$today          = $wz_cal->today;
$sch_day        = $wz_cal->sch_day;
$sch_month_02d  = $wz_cal->sch_month_mm;
$first_day      = $wz_cal->first_day;
$sch_year       = $wz_cal->sch_year;
$sch_month      = $wz_cal->sch_month;

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
    $row['price'] = wz_calculate_season($row, $today_type);    
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
        <thead>
        <tr height="30">
            <th class="sunday">일</th>
            <th>월</th>
            <th>화</th>
            <th>수</th>
            <th>목</th>
            <th>금</th>
            <th class="saturday">토</th>
        </tr>
        </thead>
        <tbody>
        <tr height="30" class="date">
            <?php
            $count = 0;

            for ($i = 0; $i < $first_day; $i++) {
                echo '<td class="mini prev"></td>'.PHP_EOL;
                $count++;
            }

            for ($day = 1; $day <= $total_day; $day++) {
                
                $v02Dd = sprintf("%02d", $day);
                $vMmDd = $sch_month_02d ."-". $v02Dd;
                $vDate = $sch_year ."-". $vMmDd; // 표시 날짜.
                $bClss = $wz_cal->day_class_sch($vDate, $count);

                // 당일 및 이전날짜 예약불가. 
                // 2016-03-30 : 최대예약가능일 추가.
                $is_block = false;
                if ($vDate <= G5_TIME_YMD || $vDate > $day_expire) { 
                    $is_block = true;
                }

                echo '<td class="mini '. $bClss .'">'.PHP_EOL;

                if ($is_block) { 
                    echo '  <span class="closeday">'.$day.'</span>'.PHP_EOL; 
                } 
                else {
                    echo '  <a class="titday" href="#none" onclick="getCalender(\''.$sch_year.'\',\''.$sch_month.'\',\''.$vDate.'\')">'.($wz_cal->holiday_list($vDate) ? '<span class="hlday">'.$wz_cal->holiday_list($vDate).'</span>' : $day).'</a>'.PHP_EOL; 
                }
                echo '</td>'.PHP_EOL;

                if ($count==6) { // 토요일이 되면 줄바꾸기 위한 <tr>태그 삽입을 위한 식
                    echo '</tr>'.PHP_EOL;
                    if ($day != $total_day) {
                        echo '<tr height="30" class="date">'.PHP_EOL;
                        $count = 0;
                    }
                }
                else {
                    $count++;
                }
            }

            // 선택한 월의 마지막날 이후의 빈테이블 삽입
            for ($i=$day; $total_day <= $day && $count <= 6;$i++) {
                echo '<td class="next '.($count == 6 ? 'sat' : '').'"></td>'.PHP_EOL;
                if ($count == 6) 
                    echo '</tr>'.PHP_EOL;
                $count++;
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
        <thead>
        <tr>
            <th scope="col">선택</th>
            <th scope="col">객실명</th>
            <th scope="col">크기</th>
            <th scope="col">기준인원</th>
            <th scope="col">기간</th>
            <th scope="col">인원선택</th>
            <th scope="col">요금</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        if ($cnt_room > 0) { 
            for ($z = 0; $z < $cnt_room; $z++) { 

            // 예약이 가능한 날짜계산, 방막기 처리된 날짜가 있는지 확인.
            $max_day = wz_check_date_room($arr_room[$z]['rm_ix'], $sch_day);
            if (!$max_day)
                continue;
            ?>
            <tr>
                <td>
                    <input type="checkbox" name="chk[]" id="chk_<?php echo $z;?>" value="<?php echo $z;?>" <?php echo $arr_room[$z]['rm_ix'] == $rm_ix ? 'checked' : '';?> />
                    <input type="hidden" name="rm_ix[<?php echo $z;?>]" id="rm_ix_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_ix'];?>" />
                    <input type="hidden" name="person_min[<?php echo $z;?>]" id="person_min_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_person_min'];?>" class="cal_person_min" />
                    <input type="hidden" name="person_max[<?php echo $z;?>]" id="person_max_<?php echo $z;?>" value="<?php echo $arr_room[$z]['rm_person_max'];?>" class="cal_person_max" />
                </td>
                <td>
                    <?php 
                    echo $arr_room[$z]['rm_subject'];
                    if ($arr_room[$z]['rm_link_url']) { 
                        echo ' <a href="http://'.$arr_room[$z]['rm_link_url'].'" target="_blank" title="'.$arr_room[$z]['rm_subject'].' 바로보기"><img src="'.WZP_PLUGIN_URL.'/img/szz_photo.gif" border=0></a>';
                    } 
                    ?>
                </td>
                <td><?php echo $arr_room[$z]['rm_size'];?></td>
                <td><?php echo $arr_room[$z]['rm_person_min'].'/'.$arr_room[$z]['rm_person_max'];?></td>
                <td>
                    <select name="bk_day[<?php echo $z;?>]" id="bk_day_<?php echo $z;?>">
                        <?php 
                        for ($i=1;$i<=$max_day;$i++) { 
                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'박 '.($i+1).'일</option>';
                        } 
                        ?>
                    </select>
                </td>
                <td>
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

