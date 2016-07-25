<?php
$sub_menu = '780100';
include_once('./_common.php');

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
                    `bk_payment` varchar(10) NOT NULL,
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

if (!isset($wzpconfig['pn_max_booking_expire'])) {
    sql_query(" ALTER TABLE `{$g5['wzp_pension_table']}` ADD `pn_max_booking_expire` smallint(6) NOT NULL DEFAULT '90' ", true);
    $db_reload = true;
}

if (!isset($wzpconfig['pn_wating_time'])) { // 2016-05-14 : 예약대기상태 wating 시간.
    sql_query(" ALTER TABLE `{$g5['wzp_pension_table']}` ADD `pn_wating_time` smallint(6) NOT NULL DEFAULT '6'; ", true);
    sql_query(" ALTER TABLE `{$g5['wzp_room_status_table']}` ADD INDEX `rms_status` (`rms_status`); ", true);
    $db_reload = true;
}

// 2016-05-14 : PG결제설정.
if (!isset($wzpconfig['pn_bank_use'])) { 
    sql_query(" ALTER TABLE `{$g5['wzp_pension_table']}` 
                    ADD `pn_bank_use` tinyint(4) NOT NULL DEFAULT '1',
                    ADD `pn_reserv_price_avg` tinyint(4) NOT NULL DEFAULT '50',
                    ADD `pn_pg_service` varchar(20) NOT NULL,
                    ADD `pn_pg_card_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_dbank_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_vbank_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_hp_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_test` tinyint(4) NOT NULL DEFAULT '1',
                    ADD `pn_pg_mid` varchar(100) NOT NULL,
                    ADD `pn_pg_site_key` varchar(255) NOT NULL,
                    ADD `pn_pg_escrow_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_tax_flag_use` tinyint(4) NOT NULL DEFAULT '0',
                    ADD `pn_pg_tax_free` tinyint(4) NOT NULL DEFAULT '0'
                    ; ", true);
    $db_reload = true;
}

// 2016-05-19 : 결제결과값 필드 추가.
$query = "show columns from `{$g5['wzp_booking_table']}` like 'bk_tno' ";
$res = sql_fetch($query);
if (empty($res)) {
    sql_query(" ALTER TABLE `{$g5['wzp_booking_table']}` 
                    ADD `bk_reserv_price` int(11) NOT NULL DEFAULT '0' AFTER `bk_price`,
                    ADD `bk_pg_price` int(11) NOT NULL DEFAULT '0' AFTER `bk_receipt_price`,
                    ADD `bk_pg_cancel` int(11) NOT NULL DEFAULT '0' AFTER `bk_pg_price`,
                    ADD `bk_pg` varchar(20) NOT NULL AFTER `bk_log`,
                    ADD `bk_tno` varchar(255) NOT NULL,
                    ADD `bk_app_no` varchar(100) NOT NULL
                    ; ", true);
    $db_reload = true;
}

// 2016-07-07
if(!sql_query(" DESCRIBE {$g5['wzp_room_extend_price_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_room_extend_price_table']}` (
                    `rmp_ix` INT(11) NOT NULL AUTO_INCREMENT COMMENT '객실개별요금키',
                    `rm_ix` INT(11) NOT NULL COMMENT '객실키',
                    `rmp_year` CHAR(4) NOT NULL COMMENT '적용년도(yyyy)',
                    `rmp_month` CHAR(2) NOT NULL COMMENT '적용월(mm)',
                    `rmp_day` CHAR(2) NOT NULL COMMENT '적용일(dd)',
                    `rmp_date` DATE NOT NULL COMMENT '일자(yyyy-mm-dd)',
                    `rmp_price` INT(11) NOT NULL DEFAULT '0' COMMENT '이용요금',
                    PRIMARY KEY (`rmp_ix`),
                    INDEX `rm_ix` (`rm_ix`),
                    INDEX `rmp_rm` (`rmp_year`, `rmp_month`)
                )
                COMMENT='객실개별요금정보'
                ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

// 2016-07-07
if(!sql_query(" DESCRIBE {$g5['wzp_room_extend_price_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_room_extend_price_table']}` (
                    `rmp_ix` INT(11) NOT NULL AUTO_INCREMENT COMMENT '객실개별요금키',
                    `rm_ix` INT(11) NOT NULL COMMENT '객실키',
                    `rmp_year` CHAR(4) NOT NULL COMMENT '적용년도(yyyy)',
                    `rmp_month` CHAR(2) NOT NULL COMMENT '적용월(mm)',
                    `rmp_day` CHAR(2) NOT NULL COMMENT '적용일(dd)',
                    `rmp_date` DATE NOT NULL COMMENT '일자(yyyy-mm-dd)',
                    `rmp_price` INT(11) NOT NULL DEFAULT '0' COMMENT '이용요금',
                    PRIMARY KEY (`rmp_ix`),
                    INDEX `rm_ix` (`rm_ix`),
                    INDEX `rmp_rm` (`rmp_year`, `rmp_month`)
                )
                COMMENT='객실개별요금정보'
                ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

// 모바일결제시 사용될 임시결제정보
if(!sql_query(" DESCRIBE {$g5['wzp_booking_data_table']} ", false)) {
        sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['wzp_booking_data_table']}` (
                    `od_id` BIGINT(20) UNSIGNED NOT NULL,
                    `mb_id` VARCHAR(20) NOT NULL DEFAULT '',
                    `dt_pg` VARCHAR(255) NOT NULL DEFAULT '',
                    `dt_data` TEXT NOT NULL,
                    `dt_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                    INDEX `od_id` (`od_id`)
                )
                ENGINE=MyISAM  DEFAULT CHARSET=utf8;", true);
    $db_reload = true;
}

// 2016-07-07 : 예약상태 필드 추가.
$query = "show columns from `{$g5['wzp_room_status_table']}` like 'rms_day' ";
$res = sql_fetch($query);
if (empty($res)) {
    sql_query(" ALTER TABLE `{$g5['wzp_room_status_table']}` 
                    ADD `rms_day` char(2) NOT NULL AFTER `rms_month`,
                    CHANGE COLUMN `rms_date` `rms_date` date NOT NULL
                    ; ", true);
    $db_reload = true;
}

// 2016-07-25 : 당일예약 필드 추가.
$query = "show columns from `{$g5['wzp_pension_table']}` like 'pn_booking_today_use' ";
$res = sql_fetch($query);
if (empty($res)) {
    sql_query(" ALTER TABLE `{$g5['wzp_pension_table']}` 
                    ADD `pn_booking_today_use` tinyint(4) NOT NULL AFTER `pn_wating_time`
                    ; ", true);
    $db_reload = true;
}

if ($db_reload) { 
    alert("DB를 갱신합니다.", G5_ADMIN_URL.'/wz_booking_admin/wzp_config.php'); 
} 

include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_EDITOR_LIB);
?>

<form name="frm" id="frm" action="./wzp_config_update.php" method="post" enctype="multipart/form-data" onsubmit="return getAction(document.forms.frm);">

<h2 class="h2_frm">환경설정</h2>
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
            최대 <input type="text" name="pn_max_booking_expire" value="<?php echo $wzpconfig['pn_max_booking_expire']; ?>" id="pn_max_booking_expire" required class="frm_input required" size="5">
            일 까지 예약 가능.
        </td>
    </tr>
    <tr>
        <th scope="row">숙박가능최대일</th>
        <td>
            객실 최대 <input type="text" name="pn_max_booking_day" value="<?php echo $wzpconfig['pn_max_booking_day']; ?>" id="pn_max_booking_day" required class="frm_input required" size="5">
            박 까지 예약가능.
        </td>
    </tr>
    <tr>
        <th scope="row">예약대기시간설정</th>
        <td>
            <?php echo help('입력된 시간이 경과되면 자동으로 예약대기건은 취소처리 됩니다.') ?>
            예약대기건은 <input type="text" name="pn_wating_time" value="<?php echo $wzpconfig['pn_wating_time']; ?>" id="pn_wating_time" required class="frm_input required" size="3"> 시간이 지나면 자동으로 취소처리.
        </td>
    </tr>
    <tr>
        <th scope="row">당일예약설정</th>
        <td>
            <?php echo help('체크하시면 당일예약이 가능합니다.') ?>
            <input type="checkbox" name="pn_booking_today_use" value="1" id="pn_booking_today_use" <?php echo $wzpconfig['pn_booking_today_use']?'checked':''; ?>><label for="pn_booking_today_use"> 당일예약 사용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">초기화면설정</th>
        <td>
            <input type="checkbox" name="pn_main_calendar_use" value="1" id="pn_main_calendar_use" <?php echo $wzpconfig['pn_main_calendar_use']?'checked':''; ?>><label for="pn_main_calendar_use"> 큰달력 사용</label>
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

<h2 class="h2_frm">결제설정</h2>
<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>결제설정</caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">예약금설정</th>
        <td>
            결제금액의 <input type="text" name="pn_reserv_price_avg" value="<?php echo $wzpconfig['pn_reserv_price_avg']; ?>" id="pn_reserv_price_avg" required class="frm_input required" style="text-align:center;" size="5"> % 예약금.
        </td>
    </tr>
    <tr>
        <th scope="row">무통장입금사용</th>
        <td>
            <?php echo help("주문시 무통장으로 입금을 가능하게 할것인지를 설정합니다.\n사용할 경우 은행계좌번호를 반드시 입력하여 주십시오.", 50); ?>
            <select id="pn_bank_use" name="pn_bank_use">
                <option value="0" <?php echo get_selected($wzpconfig['pn_bank_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($wzpconfig['pn_bank_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">입금계좌정보</th>
        <td>
            <div style="margin:5px 0">엔터로 구분 등록해주세요.</div>
            <textarea name="pn_bank_info" id="pn_bank_info" style="height:60px;"><?php echo $wzpconfig['pn_bank_info']; ?></textarea>
        </td>
    </tr>

    <?php
    @include_once(WZP_PLUGIN_PATH.'/gender/pg.setting.1.php');
    ?>

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