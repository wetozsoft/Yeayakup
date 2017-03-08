<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$wz_cal = new wz_calendar($_GET['sch_year'], $_GET['sch_month'], $sch_day);
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

// 객실상태정보
unset($arr_status);
$arr_status = array();
$query = "select rm_ix, rms_date, rms_status from {$g5['wzp_room_status_table']} where rms_year = '$sch_year' and rms_month = '$sch_month_02d' and rms_status <> '취소' ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_status[$row['rms_date']][$row['rm_ix']]['rms_status'] = $row['rms_status'];
}
$cnt_status = count($arr_status);
sql_free_result($res);

// 객실정보
unset($arr_room);
$arr_room = array();
$query = "select rm_ix, rm_subject from {$g5['wzp_room_table']} order by rm_sort asc ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    $arr_room[] = $row;
}
$cnt_room = count($arr_room);
sql_free_result($res);

// 최대예약가능일.
$day_expire = wz_get_addday(G5_TIME_YMD, $wzpconfig['pn_max_booking_expire']);
?>

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
    // count는 <tr>태그를 넘기기위한 변수. 변수값이 7이되면 <tr>태그를 삽입한다.
    $weekno  = 0;

    //첫번째 주에서 빈칸을 1일전까지 빈칸을 삽입
    for ($i = 0; $i < $first_day; $i++) {
        echo '<td class="prev"></td>'.PHP_EOL;
        $weekno++;
    }

    for ($day = 1; $day <= $total_day; $day++) {
        
        $v02Dd = sprintf("%02d", $day);
        $vMmDd = $sch_month_02d ."-". $v02Dd;
        $vDate = $sch_year ."-". $vMmDd; // 표시 날짜.
        $bClss = $wz_cal->day_class($vDate, $weekno);

        // 당일 및 이전날짜 예약불가. 
        // 2016-03-30 : 최대예약가능일 추가.
        // 2016-07-25 : 관리자에서 설정한 당일예약 여부 
        $is_block = false;
        if ($wzpconfig['pn_booking_today_use']) { 
            if ($vDate < G5_TIME_YMD)
                $is_block = true;
        } 
        else {
            if ($vDate <= G5_TIME_YMD)
                $is_block = true;
        }

        if ($vDate > $day_expire) { 
            $is_block = true;
        }

        $rm_html = '';
        $rm_cnts = 0;                
        if ($cnt_room > 0 && !$is_block) { 
            $rm_html .= '  <ul class="rmlist">'.PHP_EOL;
            for ($j=0;$j<$cnt_room;$j++) { 
                $nw_status = '';
                if (isset($arr_status[$vDate][$arr_room[$j]['rm_ix']]))
                    $nw_status = $arr_status[$vDate][$arr_room[$j]['rm_ix']]['rms_status'];

                $atag1 = $atag2 = $liclass = $txheader = '';
                switch ($nw_status) {
                    case '예약완료':
                    case '완료':
                        $liclass    = 'done';
                        $txheader   = '<span class="txheader done">완</span>';
                        break;
                    case '예약대기':
                    case '대기':
                        $liclass    = 'stay';
                        $txheader   = '<span class="txheader stay">대</span>';
                        break;
                    default:
                        $liclass    = 'live';
                        $txheader   = '<span class="txheader live">예</span>';
                        $atag1      = '<a href="'.$_SERVER['SCRIPT_NAME'].'?bo_table='.$bo_table.'&mode=step1&rm_ix='.$arr_room[$j]['rm_ix'].'&sch_day='.$vDate.'">';
                        $atag2      = '</a>';    
                        break;
                }
                
                $rm_subject = $atag1.$txheader.'<span class="tit">'.$arr_room[$j]['rm_subject'].'</span>'.$atag2;
                $rm_html .= '  <li class="'.$liclass.'">'.$rm_subject.'</li>'.PHP_EOL;

            } 
            $rm_html .= '  </ul>'.PHP_EOL;
        }
        
        echo '<td class="'. $bClss .'">'.PHP_EOL;
        echo '  <p class="titday">'.($wz_cal->holiday_list($vDate) ? '<span class="hlday">'.$wz_cal->holiday_list($vDate).'</span>' : $day).'</p>'.PHP_EOL;
        echo $rm_html;
        echo '</td>'.PHP_EOL;

        if ($weekno==6) { // 토요일이 되면 줄바꾸기 위한 <tr>태그 삽입을 위한 식
            echo '</tr>'.PHP_EOL;
            if($day != $total_day) {
                echo '<tr height="30" class="date">'.PHP_EOL;
            }
            $weekno = 0;
        }
        else {
            $weekno++;
        }
    }

    unset($arr_status);
    unset($arr_room);

    // 선택한 월의 마지막날 이후의 빈테이블 삽입
    if ($weekno != 0) { 
        for ($i=$day; $total_day <= $day && $weekno <= 6;$i++) {
            echo '<td class="next '.($weekno == 6 ? 'sat' : '').'"></td>'.PHP_EOL;
            if ($weekno == 6) 
                echo '</tr>'.PHP_EOL;
            $weekno++;
        }
    }
    ?>
</tbody>
</table>

<div style="margin:4px 0;">
    <span class="txheader live">예</span> : 예약가능
    <span class="txheader done">완</span> : 예약완료
    <span class="txheader stay">대</span> : 예약대기
</div>

<script type="text/javascript">
<!--
    function getCalender(sch_year, sch_month, sch_day) { 
        location.href = "<?php echo WZP_STATUS_URL?>&sch_year="+sch_year+"&sch_month="+sch_month+"&sch_day="+sch_day;
    }
//-->
</script>