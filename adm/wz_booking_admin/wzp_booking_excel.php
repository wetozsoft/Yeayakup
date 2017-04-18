<?php
$sub_menu = '780400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$sql_common = " from {$g5['wzp_booking_table']} ";

$sql_search = " where (1) ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($sfs) { 
    switch ($sfs) {
        case "완료":
            $sql_search .= " and (bk_status = '예약완료' or bk_status = '완료') ";
            break;
        case "취소":
            $sql_search .= " and (bk_status = '예약취소' or bk_status = '취소') ";
            break;
        default :
            $sql_search .= " and (bk_status = '예약대기' or bk_status = '대기') ";
            break;
    }
}

if ($sch_frdate1 && $sch_todate1) { 
    $sql_search .= " and bk_ix in (select bk_ix from {$g5['wzp_booking_room_table']} where bkr_frdate <= '{$sch_todate1}' AND bkr_todate > '{$sch_frdate1}') ";
} 

if (!$sst) {
    $sst = "bk_ix";
    $sod = "desc";
}

$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
$result = sql_query($sql);

if(!@sql_num_rows($result))
    alert('출력할 내역이 없습니다.');

/*================================================================================
php_writeexcel http://www.bettina-attack.de/jonny/view.php/projects/php_writeexcel/
=================================================================================*/

include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_workbook.inc.php');
include_once(G5_LIB_PATH.'/Excel/php_writeexcel/class.writeexcel_worksheet.inc.php');

$fname = tempnam(G5_DATA_PATH, "tmp-bookinglist.xls");
$workbook = new writeexcel_workbook($fname);
$worksheet = $workbook->addworksheet();

# Create a format for the stock volume
$f_volume =& $workbook->addformat();
$f_volume->set_align('right');
$f_volume->set_num_format('#,##0');

// Put Excel data
$data = array('예약번호', '예약객실', '예약자명', '예약금', '잔금', '총이용금액', '회원아이디', '핸드폰', '이메일', '요청사항', '등록일', '상태');
$data = array_map('iconv_euckr', $data);

$col = 0;
foreach($data as $cell) {
    $worksheet->write(0, $col++, $cell);
}

for($i=1; $row=sql_fetch_array($result); $i++) {
    $row = array_map('iconv_euckr', $row);

    $worksheet->write($i, 0, ' '.$row['od_id']);
    $worksheet->write($i, 1, ' '.$row['bk_subject']);
    $worksheet->write($i, 2, ' '.$row['bk_name']);

    $worksheet->write($i, 3, $row['bk_reserv_price'], $f_volume);
    $worksheet->write($i, 4, ($row['bk_price'] - $row['bk_reserv_price']), $f_volume);
    $worksheet->write($i, 5, $row['bk_price'], $f_volume);

    $worksheet->write($i, 6, ' '.$row['mb_id']);
    $worksheet->write($i, 7, ' '.$row['bk_hp']);
    $worksheet->write($i, 8, ' '.$row['bk_email']);
    $worksheet->write($i, 9, ' '.$row['bk_memo']);
    $worksheet->write($i, 10, ' '.$row['bk_time']);
    $worksheet->write($i, 11, ' '.$row['bk_status']);
}

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"bookinglist-".date("ymd", time()).".xls\"");
header("Content-Disposition: inline; filename=\"bookinglist-".date("ymd", time()).".xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);
?>