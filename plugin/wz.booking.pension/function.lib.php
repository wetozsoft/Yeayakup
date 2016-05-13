<?php

$weekstr = array('일', '월', '화', '수', '목', '금', '토');

// 설정된 시간이 지나면 예약대기건은 자동으로 취소처리.
$query = "select bk_ix from {$g5['wzp_booking_table']} where bk_status = '대기' and date_add(bk_time, interval ".($wzpconfig['pn_wating_time'] ? $wzpconfig['pn_wating_time'] : 6)." hour) < now() ";
$res = sql_query($query);
while($row = sql_fetch_array($res)) { 
    
    // 객실예약정보 변경
    $sql = " update {$g5['wzp_booking_table']} set bk_status = '취소' where bk_ix = '".$row['bk_ix']."' ";
    sql_query($sql);

    // 객실상태정보 변경
    $query = "update {$g5['wzp_room_status_table']} set rms_status = '취소' where bk_ix = '".$row['bk_ix']."' ";
    sql_query($query);

    // 예약자에게 자동취소처리 내역 전송 (mail, sms)
}

// 한달의 총 날짜 계산 함수
function wz_max_day($i_month, $i_year) {
    $day = 1;
    while(checkdate($i_month, $day, $i_year))
    {
        $day++;
    }
    $day--;
    return $day;
}

// 휴일 여부확인
function wz_holiday_check($date) {

    $str = '';
    $date = preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date) ? $date : "";
    if (!$date) {
        return '';
    }

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

    global $g5, $weekstr;
    $result = array();
    
    $weekday = $weekstr[date('w',strtotime($sch_day))];

    // 요금적용의 순서를 정합니다.
    // s 가 성수기 이므로 성수기요금이 먼저 적용될수 있도록 1로 적용하여 sort asc 처리 합니다.
    $query = "select se_type from {$g5['wzp_season_table']} where '".substr($sch_day, 5)."' between se_frdate and se_todate
                order by (
                    case se_type
                    when 'H' then 3
                    when 'F' then 2
                    when 'S' then 1
                    else 4
                    end
                ) asc limit 1 ";
    $row = sql_fetch($query);
    $result['type'] = isset($row['se_type']) ? $row['se_type'] : '7';
    $result['week'] = $weekday;

    // 특정일(공휴일) 적용 (다음날이 공휴일인지 확인)
    if (wz_holiday_check(wz_get_addday($sch_day, 1))) {
        $result['type'] = 'H';
    }

    return $result;

} 

// 요금정보
function wz_calculate($rm_ix, $dayinfo) { 
    
    global $g5;
    // 요금정보
    $query = "select * from {$g5['wzp_room_table']} where rm_ix = '$rm_ix'";
    $rm = sql_fetch($query);

    switch (strtoupper($dayinfo['type'])) {
        case 'S': // 준성수기
            $price = $dayinfo['week'] == '금' || $dayinfo['week'] == '토' ? $rm['rm_price_sf'] : $rm['rm_price_sw'];
        break;
        case 'F': // 성수기
            $price = $dayinfo['week'] == '금' || $dayinfo['week'] == '토' ? $rm['rm_price_ff'] : $rm['rm_price_fw'];
        break;
        case 'H': // 특정일
            $price = $rm['rm_price_hs'];
        break;
        default: // 비성수기
            $price = $dayinfo['week'] == '금' || $dayinfo['week'] == '토' ? $rm['rm_price_rf'] : $rm['rm_price_rw'];
        break;
    }

    return $price;
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
function wz_price_type_str($dayinfo) { 

    switch (strtoupper($dayinfo['type'])) {
        case 'H': // 특정일
            $str = wz_season_type_str('H');
        break;
        default:
            $str = wz_season_type_str($dayinfo['type']) . ($dayinfo['week'] == '금' || $dayinfo['week'] == '토' ? '주말' : '주중');
        break;
    }

    return $str;
} 

function wz_get_email_address($emails) {
    preg_match("/[0-9a-z._-]+@[a-z0-9._-]{4,}/i", $emails, $matches);
    return $matches[0];
}

function wz_get_hangul_date($date) {
    $date = str_replace('-', '', $date);
    return preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년\\2월\\3일", $date);
}

?>
