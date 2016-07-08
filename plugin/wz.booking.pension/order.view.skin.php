<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div class="st3-form">

    <?php include_once(WZP_PLUGIN_PATH.'/order.info.skin.php')?>  

    <?php if ($is_member) {?>
    <div class="action">
        <a href="<?php echo WZP_STATUS_URL;?>&mode=orderlist" class="btn_submit next">목록으로</a>
    </div>
    <?php } ?>
    
</div>
