<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (isset($_GET['sch_year']) && $_GET['sch_year'])
    $sch_year = (int)$_GET['sch_year'];
else
    $sch_year = substr(G5_TIME_YMD,0,4);
$qstring .= '&sch_year='. urlencode($sch_year);

if (isset($_GET['sch_month']) && $_GET['sch_month']) 
    $sch_month = (int)$_GET['sch_month'];
else
    $sch_month = (int)substr(G5_TIME_YMD,5,2);
$qstring .= '&sch_month='. urlencode($sch_month);

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

$today          = G5_TIME_YMD;
$sch_month_02d  = sprintf("%02d", $sch_month);
$sch_date_month = $sch_year.'-'.$sch_month_02d;
if (isset($_GET['sch_day']) && $_GET['sch_day']) {
    $_GET['sch_day'] = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $_GET['sch_day']) ? $_GET['sch_day'] : "";
    $sch_day = $sch_date_month .'-'. substr($_GET['sch_day'], 8);
}
else
    $sch_day = $today;

// 객실상태정보
unset($arr_status);
$arr_status = array();
$query = "select rm_ix, rms_date, rms_status from {$g5['wzp_room_status_table']} where rms_year = '$sch_year' and rms_month = '$sch_month_02d' ";
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
    <span class="title_red"><?php echo $sch_year?>년 <span><?php echo $sch_month?>월</span>&nbsp;
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

    // 선택한 월의 총 일수를 구함.
    $total_day      = wz_max_day($sch_month, $sch_year);

    // 선택한 월의 1일의 요일을 구함. 일요일은 0.
    $first_day      = date('w', mktime(0, 0, 0, $sch_month, 1, $sch_year));

    // count는 <tr>태그를 넘기기위한 변수. 변수값이 7이되면 <tr>태그를 삽입한다.
    $count          = 0;

    //첫번째 주에서 빈칸을 1일전까지 빈칸을 삽입
    for ($i = 0; $i < $first_day; $i++) {
        echo '<td class="prev"></td>'.PHP_EOL;
        $count++;
    }

    for ($day = 1; $day <= $total_day; $day++) {
        
        $count++;

        $vDate = $sch_year ."-". $sch_month_02d ."-". sprintf("%02d", $day); // 표시 날짜.

        if ($vDate == $today) { // 오늘 표시
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

        echo '<td class="'. $bg_class .'">'.PHP_EOL;
        echo '  <p class="titday">'.(wz_holiday_check($vDate) ? '<span class="hlday">'.wz_holiday_check($vDate).'</span>' : $day).'</p>'.PHP_EOL;
        if ($cnt_room > 0) { 
            echo '  <ul class="rmlist">'.PHP_EOL;
            for ($j=0;$j<$cnt_room;$j++) { 
                $nw_status = '';
                if (isset($arr_status[$vDate]))
                    $nw_status = $arr_status[$vDate][$arr_room[$j]['rm_ix']]['rms_status'];

                $atag1 = $atag2 = $liclass = $txheader = '';
                switch ($nw_status) {
                    case '예약완료':
                        $liclass    = 'done';
                        $txheader   = '<span class="txheader done">완</span>';
                        break;
                    case '예약대기':
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
                
                // 당일 및 이전날짜 예약불가. 
                // 2016-03-30 : 최대예약가능일 추가.
                if ($vDate <= G5_TIME_YMD || $vDate > $day_expire) { 
                    $rm_subject = '';
                } 
                else {
                    $rm_subject = $atag1.$txheader.'<span class="tit">'.$arr_room[$j]['rm_subject'].'</span>'.$atag2;
                }

                echo '  <li class="'.$liclass.'">'.$rm_subject.'</li>'.PHP_EOL;
            } 
            echo '  </ul>'.PHP_EOL;
        } 
        echo '</td>'.PHP_EOL;

        if($count==7) { // 토요일이 되면 줄바꾸기 위한 <tr>태그 삽입을 위한 식
            echo '</tr>'.PHP_EOL;
            if($day != $total_day) {
                echo '<tr height="30" class="date">'.PHP_EOL;
                $count = 0;
            }
        }
    }

    // 선택한 월의 마지막날 이후의 빈테이블 삽입
    for ($day++; $total_day < $day && $count < 7;) {
        $count++;
        echo '<td class="next"></td>'.PHP_EOL;
        if ($count == 7) 
            echo '</tr>'.PHP_EOL;
    }
    ?>
</tbody>
</table>

<script type="text/javascript">
<!--
    function getCalender(sch_year, sch_month, sch_day) { 
        location.href = "<?php echo WZP_STATUS_URL?>&sch_year="+sch_year+"&sch_month="+sch_month+"&sch_day="+sch_day;
    }
//-->
</script>