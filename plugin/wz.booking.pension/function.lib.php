<?php

$weekstr = array('일', '월', '화', '수', '목', '금', '토');

class wz_calendar {
    
    public $sch_year = '';
    public $sch_month = '';
    public $total_day = '';
    public $year_prev = '';
    public $month_prev = '';
    public $year_next = '';
    public $month_next = '';
    public $today = '';
    public $sch_day = '';
    public $sch_month_mm = '';
    public $first_day = '';

    function __construct($sch_year='', $sch_month='', $sch_day='') {
        
        $sch_year   = preg_match("/^([0-9]{4})$/", $sch_year) ? (int)$sch_year : (int)substr(G5_TIME_YMD,0,4);
        $sch_month  = preg_match("/^([0-9]{1,2})$/", $sch_month) ? (int)$sch_month : (int)substr(G5_TIME_YMD,5,2);
        $sch_day    = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sch_day) ? $sch_day : '';

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

        $this->sch_year     = $sch_year;
        $this->sch_month    = $sch_month;
        $this->year_prev    = $year_prev;
        $this->year_next    = $year_next;
        $this->month_prev   = $month_prev;
        $this->month_next   = $month_next;
        $this->today        = G5_TIME_YMD;
        $this->sch_month_mm = sprintf("%02d", $sch_month);
        $this->first_day    = date('w', mktime(0, 0, 0, $this->sch_month, 1, $this->sch_year));

        $this->max_day($sch_year, $sch_month);
        $this->set_sch_day($sch_day);
    }

    function max_day($i_year, $i_month) { 
        
        $day = 1;
        
        while(checkdate($i_month, $day, $i_year))
            $day++;

        $day--;

        $this->total_day = $day;
    } 

    function set_sch_day($sch_day='') { 
        
        if (isset($sch_day) && $sch_day) 
            ;
        else
            $sch_day = $this->today;

        $this->sch_day = $sch_day;
    } 

    function day_class($sch_day, $count) { 
        
        if ($sch_day == $this->today) { // 오늘 표시
            $bg_class = 'dday';
        }
        else { // 오늘이 아니면...
            if ($count == 0) // 일요일
                $bg_class = 'sun';
            elseif ($count == 6) // 토요일
                $bg_class = 'sat';
            else // 평일
                $bg_class = '';
        }

        return $bg_class;
    } 

    function day_class_sch($sch_day, $count) { 
        
        if ($sch_day == $this->sch_day) { // 오늘 표시
            $bg_class = 'dday';
        }
        else { // 오늘이 아니면...
            if ($count == 0) // 일요일
                $bg_class = 'sun';
            elseif ($count == 6) // 토요일
                $bg_class = 'sat';
            else // 평일
                $bg_class = '';
        }

        return $bg_class;
    } 

    function holiday_list($date) { // 특정일정보
        
        global $g5;

        $ho = array();

        $solar = array(
                    "01-01" => "신정",
                    "03-01" => "삼일절",
                    "05-05" => "어린이날",
                    "06-06" => "현충일",
                    "08-15" => "광복절",
                    "10-03" => "개천절",
                    "12-25" => "성탄절",
        );
        
        $lunar = array(
                    "2016-02-07" => "설날",
                    "2016-02-08" => "설날",
                    "2016-02-09" => "설날",
                    "2016-05-14" => "석가탄신일",
                    "2016-09-14" => "추석",
                    "2016-09-15" => "추석",
                    "2016-09-16" => "추석",
                    "2017-01-27" => "설날",
                    "2017-01-28" => "설날",
                    "2017-01-29" => "설날",
                    "2017-05-03" => "석가탄신일",
                    "2017-10-03" => "추석",
                    "2017-10-04" => "추석",
                    "2017-10-05" => "추석",
        );

        $date_mn = substr($date, 5);

        if ($solar[$date_mn]) $str = $solar[$date_mn];
        if ($lunar[$date]) $str = $lunar[$date];
        return $str;

    } 
}

// 한달의 총 날짜 계산 함수
function wz_max_day($i_month, $i_year) {
    $day = 1;
    while(checkdate($i_month, $day, $i_year)) {
        $day++;
    }
    $day--;
    return $day;
}

// 날짜구하기
function wz_get_addday($day, $add) {
    $day    = preg_replace('/[^0-9]/', '', $day);
    $y      = substr( $day, 0, 4 );
    $m      = substr( $day, 4, 2 );
    $d      = substr( $day, 6, 2 );
    return date("Y-m-d", mktime(0,0,0, $m, ($d+$add), $y));
}

 //날짜 사이의 일수를 구한다.
function wz_date_between($date1, $date2) {
    $retval = intval((strtotime($date2) - strtotime($date1)) / 86400);
    return $retval;
}

// 시즌정보
function wz_get_type($sch_day) { 

    global $g5;
    $result = array();
    
    $weekday = date('w',strtotime($sch_day));

    // 요금적용의 순서를 정합니다.
    // F 가 성수기 이므로 성수기요금이 먼저 적용될수 있도록 1로 적용하여 sort asc 처리 합니다.
    // H 가 특정일 이므로 특정일요금이 제일 먼저 적용될수 있도록 1로 적용하여 sort asc 처리 합니다.
    $query = "select se_type from {$g5['wzp_season_table']} where '".substr($sch_day, 5)."' between se_frdate and se_todate
                order by (
                    case se_type
                    when 'H' then 1
                    when 'F' then 2
                    when 'S' then 3
                    else 4
                    end
                ) asc limit 1 ";
    $row = sql_fetch($query);
    $result['type'] = isset($row['se_type']) ? $row['se_type'] : '';
    $result['week'] = $weekday;
    $result['date'] = $sch_day;

    return $result;

} 

// 요금정보
function wz_calculate_season($rm, $dt) { 
    
    global $g5;

    // 객실 최우선적용정보
    $rmp_month  = substr($dt['date'],5,2);
    $rmp_day    = substr($dt['date'],8);
    $query = "select * from {$g5['wzp_room_extend_price_table']} where rm_ix = '{$rm['rm_ix']}' and rmp_date = '{$dt['date']}' ";
    $rmp = sql_fetch($query);
    if ($rmp['rmp_ix']) {
        $price = $rmp['rmp_price'];
    } 
    else {
        switch (strtoupper($dt['type'])) {
            case 'F': // 성수기
                $price = $dt['week'] == '5' || $dt['week'] == '6' ? $rm['rm_price_ff'] : $rm['rm_price_fw'];
            break;
            case 'S': // 준성수기
                $price = $dt['week'] == '5' || $dt['week'] == '6' ? $rm['rm_price_sf'] : $rm['rm_price_sw'];
            break;
            default: // 비성수기
                $price = $dt['week'] == '5' || $dt['week'] == '6' ? $rm['rm_price_rf'] : $rm['rm_price_rw'];
            break;
        }
    }

    return $price;
} 

// 예약가능한 날짜 계산
function wz_check_date_room($rm_ix, $fr_date) { 
    
    global $g5, $wzpconfig;

    $max_day = $wzpconfig['pn_max_booking_day'];

    for ($i=0; $i<$max_day; $i++) { 
        $sch_date   = wz_get_addday($fr_date, $i);
        $rms_month  = substr($sch_date,5,2);
        $rms_day    = substr($sch_date,8);
        $query      = "select rms_ix from {$g5['wzp_room_status_table']} where rm_ix = '{$rm_ix}' and rms_date = '$sch_date' and rms_status <> '취소' ";
        $rms = sql_fetch($query);
        if ($rms['rms_ix']) { // 예약이 불가능한 날짜라면.
            $max_day = $i;
            break;
        }
    } 

    return $max_day;
} 

// 선택한 객실요금계산
function wz_calculate_room($parm) { 
    
    global $g5;
    $arr_room = array();

    $cnt_chk = 0;
    if (is_array($parm['chk'])) {
        $cnt_chk = count($parm['chk']);
        for ($z = 0; $z < $cnt_chk; $z++) {
            $rmix   = (int)$parm['rm_ix'][$parm['chk'][$z]]; // 객실키
            
            if ($rmix) { 

                $query = "select * from {$g5['wzp_room_table']} where rm_ix = '$rmix' ";   
                $rm = sql_fetch($query);
                
                $bkday          = (int)$parm['bk_day'][$parm['chk'][$z]]; // 예약일수
                $price_room     = 0;
                for ($j=0;$j<$bkday;$j++) { 
                    $rms_date   = wz_get_addday($parm['sch_day'], $j);
                    $today_type = wz_get_type($rms_date);
                    $price_room  += wz_calculate_season($rm, $today_type);
                }     
                            
                $price_person   = 0;

                $person_min     = (int)$parm['person_min'][$parm['chk'][$z]]; // 최소인원
                $person_max     = (int)$parm['person_max'][$parm['chk'][$z]]; // 최대인원
                $cnt_adult      = (int)$parm['bk_cnt_adult'][$parm['chk'][$z]]; // 선택인원 - 성인
                $rm['bk_cnt_adult'] = $cnt_adult; 

                $price_adult    = $rm['rm_price_adult']; // 추가요금 - 성인

                $cnt_extra = ($cnt_adult) - $person_min;
                
                $cnt_adult = $cnt_adult > $cnt_extra ? $cnt_extra : $cnt_adult;
                if ($cnt_adult > 0 && $cnt_extra > 0) { // 성인요금적용.
                    $price_person += $price_adult * $cnt_adult;
                    $cnt_extra = $cnt_extra - $cnt_adult;
                }

                $price_person       = $price_person * $bkday;
     
                $rm['price_room']   = $price_room;
                $rm['price_person'] = $price_person;
                $rm['bk_day']       = $bkday;    

                $arr_room[]         = $rm;
            } 
        }
    }

    return $arr_room;
} 

// 시즌유형정보
function wz_season_type_str($season) { 

    switch (strtoupper($season)) {
        case 'S':
            $str = '준성수기';
        break;
        case 'F':
            $str = '성수기';
        break;
        case 'H':
            $str = '특정일';
        break;
        default:
            $str = '비수기';
        break;
    }

    return $str;
} 

// 요금유형정보
function wz_price_type_str($dt) { 

    $str = wz_season_type_str($dt['type']) . ($dt['week'] == '5' || $dt['week'] == '6' ? '주말' : '주중');

    return $str;
} 

// 이메일주소 유효성검사
function wz_get_email_address($emails) {
    preg_match("/[0-9a-z._-]+@[a-z0-9._-]{4,}/i", $emails, $matches);
    return $matches[0];
}

// 한글날짜로 리턴
function wz_get_hangul_date($date) {
    $date = str_replace('-', '', $date);
    return preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년\\2월\\3일", $date);
}

// 한글날짜로 리턴
function wz_get_hangul_date_md($date) {
    $date = str_replace('-', '', $date);
    return preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\2/\\3", $date);
}

// 설정된 시간이 지나면 예약대기건은 자동으로 취소처리.
function wz_ready_order_cancel() { 

    global $g5, $wzpconfig;

    $query = "select bk_ix from {$g5['wzp_booking_table']} where bk_status = '대기' and date_add(bk_time, interval ".($wzpconfig['pn_wating_time'] ? $wzpconfig['pn_wating_time'] : 6)." hour) < now() ";
    $res = sql_query($query);
    while($row = sql_fetch_array($res)) { 
        
        // 객실예약정보 변경
        $query = " update {$g5['wzp_booking_table']} set bk_status = '취소' where bk_ix = '".$row['bk_ix']."' ";
        sql_query($query);

        // 객실상태정보 변경
        $query = "update {$g5['wzp_room_status_table']} set rms_status = '취소' where bk_ix = '".$row['bk_ix']."' ";
        sql_query($query);

        // 예약자에게 자동취소처리 내역 전송 (mail, sms)
    }
}
?>
