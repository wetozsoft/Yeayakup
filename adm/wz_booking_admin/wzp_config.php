<?php
$sub_menu = '780100';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '환경설정';

$db_reload = false;

if(!sql_query(" DESCRIBE {$g5['wzp_pension_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_pension_table']}` (
                    `pn_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `pn_bank_info` text NOT NULL,
                    `pn_main_calendar_use` tinyint(4) NOT NULL DEFAULT '1',
                    `pn_con_notice` text NOT NULL,
                    `pn_con_info` text NOT NULL,
                    `pn_con_checkinout` text NOT NULL,
                    `pn_con_refund` text NOT NULL,
                    `pn_max_booking_day` smallint(6) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`pn_ix`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);

    sql_query(" INSERT INTO {$g5['wzp_pension_table']} 
                    SET `pn_ix`         = 1, 
                        `pn_bank_info`   = '신한은행1 333-333-33333 홍길동\r\n신한은행2 333-333-33333 홍길동', 
                        `pn_con_notice`   = '공지글을 테스트합니다. - 관리자화면에서 에디터로 수정가능', 
                        `pn_con_info`   = '기본예약안내&nbsp;- 관리자화면에서 에디터로 수정가능', 
                        `pn_con_checkinout`   = '입퇴실안내&nbsp;- 관리자화면에서 에디터로 수정가능',
                        `pn_con_refund`   = '환불규정&nbsp;- 관리자화면에서 에디터로 수정가능',
                        `pn_max_booking_day`   = 7
            ", true);
    $db_reload = true;
}

if(!sql_query(" DESCRIBE {$g5['wzp_booking_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_booking_table']}` (
                    `bk_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `od_id` bigint(20) NOT NULL,
                    `mb_id` varchar(255) NOT NULL,
                    `bk_name` varchar(20) NOT NULL,
                    `bk_subject` varchar(255) NOT NULL,
                    `bk_cnt_room` tinyint(4) NOT NULL DEFAULT '0',
                    `bk_hp` varchar(20) NOT NULL,
                    `bk_email` varchar(100) NOT NULL,
                    `bk_memo` text NOT NULL,
                    `bk_payment` varchar(255) NOT NULL,
                    `bk_deposit_name` varchar(20) NOT NULL,
                    `bk_bank_account` varchar(255) NOT NULL,
                    `bk_price` int(11) NOT NULL DEFAULT '0',
                    `bk_receipt_price` int(11) NOT NULL DEFAULT '0',
                    `bk_misu` int(11) NOT NULL DEFAULT '0',
                    `bk_receipt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `bk_mobile` tinyint(4) NOT NULL DEFAULT '0',
                    `bk_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `bk_ip` varchar(20) NOT NULL,
                    `bk_status` varchar(20) NOT NULL DEFAULT '대기',
                    `bk_log` varchar(255) NOT NULL,
                    PRIMARY KEY (`bk_ix`),
                    KEY `od_id` (`od_id`),
                    KEY `mb_id` (`mb_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

if(!sql_query(" DESCRIBE {$g5['wzp_booking_room_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_booking_room_table']}` (
                    `bkr_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `bk_ix` int(11) NOT NULL,
                    `rm_ix` int(11) NOT NULL,
                    `bkr_subject` varchar(255) NOT NULL,
                    `bkr_price` int(11) NOT NULL DEFAULT '0',
                    `bkr_cnt_adult` tinyint(4) NOT NULL DEFAULT '0',
                    `bkr_price_adult` int(11) NOT NULL DEFAULT '0',
                    `bkr_frdate` char(10) NOT NULL,
                    `bkr_todate` char(10) NOT NULL,
                    `bkr_day` tinyint(4) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`bkr_ix`),
                    KEY `rm_ix` (`rm_ix`),
                    KEY `bk_ix` (`bk_ix`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

if(!sql_query(" DESCRIBE {$g5['wzp_room_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_room_table']}` (
                    `rm_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `rm_subject` varchar(255) NOT NULL,
                    `rm_size` varchar(20) NOT NULL,
                    `rm_person_min` tinyint(4) NOT NULL DEFAULT '0',
                    `rm_person_max` tinyint(4) NOT NULL DEFAULT '0',
                    `rm_price_rw` int(11) NOT NULL DEFAULT '0',
                    `rm_price_rf` int(11) NOT NULL DEFAULT '0',
                    `rm_price_sw` int(11) NOT NULL DEFAULT '0',
                    `rm_price_sf` int(11) NOT NULL DEFAULT '0',
                    `rm_price_fw` int(11) NOT NULL DEFAULT '0',
                    `rm_price_ff` int(11) NOT NULL DEFAULT '0',
                    `rm_price_hs` int(11) NOT NULL DEFAULT '0',
                    `rm_price_adult` int(11) NOT NULL DEFAULT '0',
                    `rm_link_url` varchar(255) NOT NULL,
                    `rm_sort` smallint(6) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`rm_ix`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

if(!sql_query(" DESCRIBE {$g5['wzp_room_status_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_room_status_table']}` (
                    `rms_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `rm_ix` int(11) NOT NULL,
                    `rms_year` char(4) NOT NULL,
                    `rms_month` char(2) NOT NULL,
                    `rms_date` char(10) NOT NULL DEFAULT '0000-00-00',
                    `rms_status` varchar(10) NOT NULL,
                    `bk_ix` int(11) NOT NULL,
                    PRIMARY KEY (`rms_ix`),
                    KEY `rm_ix` (`rm_ix`),
                    KEY `rms_date` (`rms_date`),
                    KEY `rms_ym` (`rms_year`,`rms_month`),
                    KEY `bk_ix` (`bk_ix`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);

}

if(!sql_query(" DESCRIBE {$g5['wzp_season_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_season_table']}` (
                    `se_ix` int(11) NOT NULL AUTO_INCREMENT,
                    `se_type` varchar(10) NOT NULL,
                    `se_frdate` char(5) NOT NULL,
                    `se_todate` char(5) NOT NULL,
                    PRIMARY KEY (`se_ix`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

if ($db_reload) { 
    alert("DB를 갱신합니다.", G5_ADMIN_URL.'/wz_booking_admin/wzp_config.php'); 
} 

include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_EDITOR_LIB);
?>

<form name="frm" id="frm" action="./wzp_config_update.php" method="post" enctype="multipart/form-data" onsubmit="return getAction(document.forms.frm);">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>환경설정</caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">예약가능최대일</th>
        <td>
            객실 최대 <input type="text" name="pn_max_booking_day" value="<?php echo $wzpconfig['pn_max_booking_day']; ?>" id="pn_max_booking_day" required class="frm_input required" size="5">
            일 까지 예약가능.
        </td>
    </tr>
    <tr>
        <th scope="row">초기화면설정</th>
        <td>
            <input type="checkbox" name="pn_main_calendar_use" value="1" id="pn_main_calendar_use" <?php echo $wzpconfig['pn_main_calendar_use']?'checked':''; ?>><label for="pn_main_calendar_use"> 큰달력 사용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">입금계좌정보</th>
        <td>
            <div style="margin:5px 0">엔터로 구분 등록해주세요.</div>
            <textarea name="pn_bank_info" id="pn_bank_info"><?php echo $wzpconfig['pn_bank_info']; ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">공지</th>
        <td>
            <?php echo editor_html('pn_con_notice', get_text($wzpconfig['pn_con_notice'], 0)); ?>
        </td>
    </tr>
    <tr>
        <th scope="row">기본예약안내</th>
        <td>
            <?php echo editor_html('pn_con_info', get_text($wzpconfig['pn_con_info'], 0)); ?>
        </td>
    </tr>
    <tr>
        <th scope="row">입/퇴실안내</th>
        <td>
            <?php echo editor_html('pn_con_checkinout', get_text($wzpconfig['pn_con_checkinout'], 0)); ?>
        </td>
    </tr>
    <tr>
        <th scope="row">환불규정</th>
        <td>
            <?php echo editor_html('pn_con_refund', get_text($wzpconfig['pn_con_refund'], 0)); ?>
        </td>
    </tr>
    </tbody>
    </table>

</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="수정" class="btn_submit" accesskey="s">
</div>

</form>

<script type="text/javascript">
<!--
    function getAction(f) {

        <?php echo get_editor_js('pn_con_notice'); ?>
        <?php echo get_editor_js('pn_con_info'); ?>
        <?php echo get_editor_js('pn_con_checkinout'); ?>
        <?php echo get_editor_js('pn_con_refund'); ?>

        return true;
    }
//-->
</script>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
2016-01-04