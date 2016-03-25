<?php
$sub_menu = '780200';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/config.php');
include_once(G5_PLUGIN_PATH.'/wz.booking.pension/function.lib.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '시즌정보 관리';

$se_ix = (int)$_GET['se_ix'];

if ($w == 'u') {
    $html_title = '시즌정보 수정';

    $sql = " select * from {$g5['wzp_season_table']} where se_ix = '$se_ix' ";
    $se = sql_fetch($sql);
    if (!$se['se_ix']) alert('등록된 자료가 없습니다.', 'wzp_season_list.php');

    $se_frdate_m = substr($se['se_frdate'],0,2);
    $se_frdate_d = substr($se['se_frdate'],3);
    $se_todate_m = substr($se['se_todate'],0,2);
    $se_todate_d = substr($se['se_todate'],3);
}
else {
    $html_title = '시즌정보 입력';
}

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="frm" action="./wzp_season_form_update.php" method="post" onsubmit="return getAction(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="se_ix" value="<?php echo $se_ix; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

    <section id="anc_spp_pay" class="cbox">
        <h2><?php echo $html_title;?></h2>

        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $html_title;?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">시즌타입</th>
                <td>
                    <select name="se_type" id="se_type">
                        <option value="">선택</option>
                        <option value="S" <?php echo $se['se_type'] == 'S' ? 'selected' : '';?>><?php echo wz_season_type_str('S');?></option>
                        <option value="F" <?php echo $se['se_type'] == 'F' ? 'selected' : '';?>><?php echo wz_season_type_str('F');?></option>
                        <option value="H" <?php echo $se['se_type'] == 'H' ? 'selected' : '';?>><?php echo wz_season_type_str('H');?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">시작일</th>
                <td>
                    <select name="se_frdate_m" id="se_frdate_m">
                        <?php
                        for ($i=1;$i<=12;$i++) { 
                            $selected = '';
                            $num = sprintf("%02d", $i);
                            if ($se_frdate_m == $num) { 
                                $selected = 'selected';
                            } 
                            echo '<option value="'.$num.'" '.$selected.'>'.$i.'</option>';
                        } 
                        ?>
                    </select> 월
                    <select name="se_frdate_d" id="se_frdate_d">
                        <?php
                        for ($i=1;$i<=31;$i++) { 
                            $selected = '';
                            $num = sprintf("%02d", $i);
                            if ($se_frdate_d == $num) { 
                                $selected = 'selected';
                            } 
                            echo '<option value="'.$num.'" '.$selected.'>'.$i.'</option>';
                        } 
                        ?>
                    </select> 일
                </td>
            </tr>
            <tr>
                <th scope="row">종료일</th>
                <td>
                    <select name="se_todate_m" id="se_todate_m">
                        <?php
                        for ($i=1;$i<=12;$i++) { 
                            $selected = '';
                            $num = sprintf("%02d", $i);
                            if ($se_todate_m == $num) { 
                                $selected = 'selected';
                            } 
                            echo '<option value="'.$num.'" '.$selected.'>'.$i.'</option>';
                        } 
                        ?>
                    </select> 월
                    <select name="se_todate_d" id="se_todate_d">
                        <?php
                        for ($i=1;$i<=31;$i++) { 
                            $selected = '';
                            $num = sprintf("%02d", $i);
                            if ($se_todate_d == $num) { 
                                $selected = 'selected';
                            } 
                            echo '<option value="'.$num.'" '.$selected.'>'.$i.'</option>';
                        } 
                        ?>
                    </select> 일
                </td>
            </tr>
            </tbody>
            </table>
        </div>
    </section>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <a href="./wzp_season_list.php?<?php echo $qstr; ?>">목록</a>
    </div>

</form>

<script>
function getAction(f) {
    if (f.se_type.selectedIndex == 0) {
        alert("시즌타입을 선택해주세요.");
        f.se_type.focus();
        return false;
    }

    var _se_frdate = f.se_frdate_m.value +'-'+ f.se_frdate_d.value;
    var _se_todate = f.se_todate_m.value +'-'+ f.se_todate_d.value;
    if (_se_frdate > _se_todate) {
        alert("종료일이 시작일보다 빠를수 없습니다.");
        return false;
    }
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>