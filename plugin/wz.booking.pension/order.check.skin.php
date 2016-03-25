<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$action_url = WZP_STATUS_HTTPS_URL;
?>

<div class="ord-form">
    
    <form method="post" name="wzfrm" id="wzfrm" onsubmit="return getNext();">
    <input type="hidden" name="mode" id="mode" value="orderlist" />

    <h3>- 예약자정보입력</h3>
    <table cellpadding="0" cellspacing="0" border="0" class="tbl_type frm">
        <caption></caption>
        <colgroup>
            <col width="150px">
            <col width="auto">
        </colgroup>
        <tbody>
        <tr>
            <th scope="col">예약자명</th>
            <td>
                <input type="text" name="user_nm" id="user_nm" value="<?php echo $member['mb_name'];?>" style="width:100px;" maxlength="20" /> (실명으로 입력해주세요)
            </td>
        </tr>
        <tr>
            <th scope="col">핸드폰</th>
            <td>
                <input type="text" name="user_hp" id="user_hp" style="width:150px;" maxlength="20" />
            </td>
        </tr>
    </table>

    <div class="action">
        <input type="submit" class="btn_submit next" value="예약정보확인" />
    </div>

    </form>

</div>

<script type="text/javascript">
<!--
    function getNext() { 
        var f = document.forms.wzfrm;

        if (!f.user_nm.value) {
            alert("예약자명을 입력해주세요.");
            f.user_nm.focus();
            return false;
        }
        if (!f.user_hp.value) {
            alert("핸드폰번호를 입력해주세요.");
            f.user_hp.focus();
            return false;
        }

        f.action = "<?php echo $action_url;?>";
        f.target = "_self";


    }
//-->
</script>


