<section class="hbox stretch wulaui layui-hide" id="passport-page">
    <section>
        <div class="vbox">
            <header class="bg-light header b-b clearfix">
                <div class="row m-t-sm">
                    <div class="col-sm-6 hidden-xs m-b-xs">
                        <a href="{'passport/edit'|app}" class="btn btn-sm btn-success edit-admin" data-ajax="dialog"
                           data-area="700px,auto" data-title="新的通行证">
                            <i class="fa fa-plus"></i> {'Add'|t}
                        </a>
                        <div class="btn-group">
                            <a href="{'passport/del'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                               data-confirm="你真的要删除这些用户吗？" data-warn="请选择要删除的用户" class="btn btn-danger btn-sm"><i
                                        class="fa fa-trash"></i> {'Delete'|t}</a>
                            <a href="{'passport/set-status/0'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                               data-confirm="你真的要禁用这些用户吗？" data-warn="请选择要禁用的用户" class="btn btn-sm btn-warning"><i
                                        class="fa fa-square-o"></i>
                                禁用</a>
                            <a href="{'passport/set-status/1'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                               data-confirm="你真的要激活这些用户吗？" data-warn="请选择要激活的用户" class="btn btn-sm btn-primary"><i
                                        class="fa fa-check-square-o"></i>
                                激活</a>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12 m-b-xs text-right">
                        <form id="simple-search" class="form-inline">
                            <div class="checkbox m-l-xs m-r-xs">
                                <label>
                                    <input type="checkbox" name="status" id="ustatus" value="0"/> 禁用
                                </label>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="text" data-expend="300" class="input-sm form-control"
                                       placeholder="{'Search'|t}" id="sqkey"/>
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                                    {if $forms}
                                        <a href="#advancedf" class="btn btn-sm btn-default" data-toggle="class:show">
                                            <i class="fa fa-ellipsis-h text"></i>
                                            <i class="fa fa-ellipsis-v text-active"></i>
                                        </a>
                                    {/if}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <section class="w-f">
                <div class="table-responsive">
                    <table id="table" data-auto data-table="{'passport/data'|app}" data-sort="PAS.login_time,d"
                           style="min-width: 1300px">
                        <thead>
                        <tr>
                            <th width="20">
                                <input type="checkbox" class="grp"/>
                            </th>
                            <th width="70" data-sort="PAS.id,d">ID</th>
                            <th style="min-width: 120px" data-sort="PAS.username,a">账户</th>
                            <th width="200" data-sort="PAS.nickname,a">姓名</th>
                            <th width="120" data-sort="PAS.create_time,a">注册时间</th>
                            <th width="120" data-sort="PAS.login_time,d">最后登录</th>
                            <th width="80" data-sort="PAS.device,a">设备</th>
                            {'passport.table'|tablehead}
                            <th width="100" class="text-right">{'passport.table'|tableset}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <footer class="footer b-t">
                <div data-table-pager="#table"></div>
            </footer>
        </div>
    </section>
    <aside class="aside aside-md b-l hide" id="advancedf">
        <form data-table-form="#table" class="p-xs" id="do-search">
            <input type="hidden" name="q" id="qkey" value=""/>
            <input type="hidden" name="status" id="qstatus" value="1"/>
            {if $forms}
                {foreach $forms as $f}
                    {$f->render()}
                {/foreach}
                <div class="form-group m-b-xs text-right">
                    <button class="btn btn-primary">{'Search'|t}</button>
                </div>
            {/if}
        </form>
    </aside>
</section>
<script>
	layui.use(['jquery', 'layer', 'wulaui'], ($, _, $$) => {
		$('body').on('change', '#ustatus', function () { //按状态查看用户
			$('#qstatus').val($(this).prop('checked') ? '0' : '1');
			$('#do-search').submit();
		}).on('submit', '#simple-search', function () {
			$('#do-search').submit();
			return false;
		}).on('change', '#sqkey', function () {
			$('#qkey').val($(this).val());
		}).on('before.dialog', '.edit-admin', function (e) {
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#edit-form').on('ajax.success', function () {
					layer.closeAll();
					$('#table').reload();
				}).submit();
				return false;
			};
		}).on('uploader.remove', '#user-avatar', function () {
			if (confirm('你真的要删除当前头像吗?')) {
				$.get("{'system/account/users/del-avatar'|app}/" + $('#id').val())
			} else {
				return false;
			}
		});
		$('#passport-page').removeClass('layui-hide');
	});
</script>