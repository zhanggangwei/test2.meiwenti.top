<?php if (!defined('THINK_PATH')) exit();?><script>
    var company_trucker_s_url = '<?php echo U('CompanyTruck/searchCompanyTruck');?>';
</script>
<div class="pageContent">
    <form action="<?php echo U('Vip/CompanyTruck/addCompanyTruck');?>" id="j_custom_form_add_company_truck" data-toggle="validate" data-alertmsg="false" method="post">
        <div style="margin: 0 auto;width:500pt;padding: 30pt">
            <div class="bjui-row">
                <label class="row-label">内部编号：</label>
                <div class="row-input">
                    <input type="text" name="owner_order"><span>如果不输入则系统自动生成</span>
                </div>
                <label class="row-label">车型：</label>
                <div class="row-input required">
                    <input type="text" name="model" data-rule="required">
                </div>
                <label class="row-label">吨位：</label>
                <div class="row-input required">
                    <input type="text" name="maximum" value="40" data-rule="required">
                </div>
                <label class="row-label">车辆图片：</label>
                <div class="row-input required">
                    <input type="file" data-name="photo" data-toggle="webuploader" data-options="
                    {
                        pick: {label: '点击选择图片'},
                        server: '<?php echo U('Api/Common/uploadImg');?>',
                        fileNumLimit: 3,
                        formData: {dir:'custompic'},
                        required: true,
                        uploaded: '',
                        basePath: '',
                        accept: {
                            title: '图片',
                            extensions: 'jpg,png',
                            mimeTypes: '.jpg,.png'
                        }
                    }">
                </div>
                <label class="row-label">行驶证：</label>
                <div class="row-input required">
                    <input type="file" data-name="lic_pic" data-toggle="webuploader" data-options="
                    {
                        pick: {label: '点击选择图片'},
                        server: '<?php echo U('Api/Common/uploadImg');?>',
                        fileNumLimit: 3,
                        formData: {dir:'custompic'},
                        required: true,
                        uploaded: '',
                        basePath: '',
                        accept: {
                            title: '图片',
                            extensions: 'jpg,png',
                            mimeTypes: '.jpg,.png'
                        }
                    }">
                </div>
                <label class="row-label">车牌号：</label>
                <div class="row-input required">
                    <input type="text" name="lic_number" data-rule="required">
                </div>
                <label class="row-label">发证日期：</label>
                <div class="row-input required">
                    <input type="text" name="lic_date" value="" data-toggle="datepicker" data-rule="required">
                </div>
                <label class="row-label">保险日期：</label>
                <div class="row-input required">
                    <input type="text" name="ins_date" value="" data-toggle="datepicker" data-rule="required">
                </div>
                <label class="row-label">年检日期：</label>
                <div class="row-input required">
                    <input type="text" name="check_date" value="" data-toggle="datepicker" data-rule="required">
                </div>
                <label class="row-label">备注：</label>
                <div class="row-input">
                    <input type="text" name="comment">
                </div>
            </div>
            <hr style="margin:5px 0 15px;">
            <div class="text-center">
                <button type="submit" class="btn-default btn">添加</button>
            </div>
        </div>
    </form>
</div>
<script>
    var win_h = $(window).height();
    $('#j_custom_form_add_company_truck').css({'height':win_h - 140, 'overflow-y':'scroll'});
</script>