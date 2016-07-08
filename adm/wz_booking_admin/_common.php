<?php
define('G5_IS_ADMIN', true);
include_once ('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/wz_booking_admin/style.css">', 10);
?>