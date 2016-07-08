<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

define('WZP_STATUS_VER', '0.1.11');

$g5['wzp_pension_table']        = G5_TABLE_PREFIX.'wzp_pension'; // 펜션기본정보 테이블
$g5['wzp_booking_table']        = G5_TABLE_PREFIX.'wzp_booking'; // 예약정보 테이블
$g5['wzp_booking_room_table']   = G5_TABLE_PREFIX.'wzp_booking_room'; // 예약룸 테이블
$g5['wzp_booking_data_table']   = G5_TABLE_PREFIX.'wzp_booking_data'; // 예약정보 임시 테이블
$g5['wzp_room_table']           = G5_TABLE_PREFIX.'wzp_room'; // 객실정보 테이블
$g5['wzp_room_status_table']    = G5_TABLE_PREFIX.'wzp_room_status'; // 객실정보상태 테이블
$g5['wzp_room_extend_price_table']  = G5_TABLE_PREFIX.'wzp_room_extend_price'; // 객실개별요금정보 테이블 (이용요금 최우선순위적용)
$g5['wzp_season_table']         = G5_TABLE_PREFIX.'wzp_season'; // 시즌관리 테이블

define('WZP_STATUS_URL',    G5_BBS_URL.'/board.php?bo_table='.$bo_table); // 예약상태페이지 URL
define('WZP_STATUS_HTTPS_URL',    G5_HTTPS_BBS_URL.'/board.php?bo_table='.$bo_table); // 예약상태페이지 URL (보안서버)
define('WZP_PLUGIN_URL',    G5_PLUGIN_URL.'/wz.booking.pension');
define('WZP_PLUGIN_PATH',   G5_PLUGIN_PATH.'/wz.booking.pension');

$wzpconfig = sql_fetch(" select * from {$g5['wzp_pension_table']} ");
?>
