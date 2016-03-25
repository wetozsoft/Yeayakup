<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.WZP_PLUGIN_URL.'/style.css">', 0);

if (isset($_REQUEST['mode'])) {
    $mode = preg_replace('/[^a-z0-9_]/i', '', trim($_REQUEST['mode']));
    $mode = substr($mode, 0, 20);
} else {
    $mode = '';
}
?>
<div class="wzpmnwrap">
    <ul>
        <li><a href="<?php echo WZP_STATUS_URL;?>&mode=info" class="<?php echo ($mode == 'info' ? 'on' : '');?>">예약안내</a></li>
        <li><a href="<?php echo WZP_STATUS_URL;?>" class="<?php echo (substr($mode,0,4) == 'step' || $mode == '' ? 'on' : '');?>">실시간예약</a></li>
        <li><a href="<?php echo WZP_STATUS_URL;?>&mode=orderlist" class="<?php echo (substr($mode,0,5) == 'order' ? 'on' : '');?>">예약확인/취소</a></li>
    </ul>
</div>
<?php
if (!isset($wzpconfig['pn_main_calendar_use'])) {
    sql_query(" ALTER TABLE `{$g5['wzp_pension_table']}` ADD `pn_main_calendar_use` tinyint(4) NOT NULL DEFAULT '1' ", false);
    $wzpconfig['pn_main_calendar_use'] = 1;
}

if (!$wzpconfig['pn_main_calendar_use'] && $mode == '') { // 초기화면 설정
    $mode = 'step1';
} 

if ($mode == '') { // 달력    
    include_once(WZP_PLUGIN_PATH.'/calendar.skin.php');
} 
else if ($mode == 'step1') { // 객실선택정보 확인 및 인원선택, 옵션선택
    include_once(WZP_PLUGIN_PATH.'/step.1.skin.php');
} 
else if ($mode == 'step2') { // 예약자 정보 입력 및 동의
    include_once(WZP_PLUGIN_PATH.'/step.2.skin.php');
} 
else if ($mode == 'step3') { // 예약결과 
    include_once(WZP_PLUGIN_PATH.'/step.3.skin.php');
} 
else if ($mode == 'ordercheck') { // 비회원예약검증 
    include_once(WZP_PLUGIN_PATH.'/order.check.skin.php');
} 
else if ($mode == 'orderlist') { // 예약확인 
    include_once(WZP_PLUGIN_PATH.'/order.list.skin.php');
} 
else if ($mode == 'orderdetail') { // 예약상세확인 
    include_once(WZP_PLUGIN_PATH.'/order.view.skin.php');
} 
else if ($mode == 'info') { // 예약안내 
    include_once(WZP_PLUGIN_PATH.'/info.skin.php');
} 
?>

<div style="height:30px;"></div>