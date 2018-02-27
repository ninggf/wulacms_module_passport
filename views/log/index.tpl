<section class="hbox stretch wulaui" id="oauth-page">
    <section>
        <div class="vbox">
            <header class="bg-light header b-b clearfix">
                <div class="row m-t-sm">
                    <div class="col-xs-12 m-b-xs text-right">
                        <form id="search-form" class="form-inline" data-table-form="#table">
                            <input type="hidden" name="type" id="type" value=""/>
                            <input type="hidden" name="oid" value="{$oauth}"/>
                            <input type="text" data-expend="200" class="input-sm form-control" name="token"
                                   placeholder="TOKEN"/>
                            <div class="input-group input-group-sm">
                                <input type="text" data-expend="300" name="q" class="input-sm form-control"
                                       placeholder="{'Search'|t}" id="sqkey" value="{$pid}"/>
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <section class="w-f">
                <div class="table-responsive">
                    <table id="table" data-auto data-table="{'passport/log/data'|app}" data-sort="OS.create_time,d"
                           style="min-width: 900px">
                        <thead>
                        <tr>
                            <th width="20">
                                <input type="checkbox" class="grp"/>
                            </th>
                            <th width="70" data-sort="type,a">类型</th>
                            <th width="100" data-sort="passport_id,d">通行证ID</th>
                            <th>OPENID</th>
                            <th width="100" data-sort="OS.create_time,d">登录时间</th>
                            <th width="120">登录IP</th>
                            <th width="80" data-sort="OS.device,a">设备</th>
                            <th width="100" data-sort="expiration,d">过期时间</th>
                            <th>TOKEN</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <footer class="footer b-t">
                <div data-table-pager="#table" data-limit="30"></div>
            </footer>
        </div>
    </section>
    <aside class="aside aside-sm b-l hidden-xs">
        <div class="vbox">
            <header class="bg-light dk header b-b">
                <p>第三方应用</p>
            </header>
            <section class="hidden-xs scrollable m-t-xs">
                <ul class="nav nav-pills nav-stacked no-radius" id="app-list">
                    <li class="active">
                        <a href="javascript:;"> 全部 </a>
                    </li>
                    {foreach $groups as $gp=>$name}
                        <li>
                            <a href="javascript:;" rel="{$gp}" title="{$name}"> {$name}</a>
                        </li>
                    {/foreach}
                </ul>
            </section>
        </div>
    </aside>
</section>
<script>
	layui.use(['jquery', 'bootstrap', 'wulaui'], function ($, b, wui) {
		var group = $('#app-list');
		group.find('a').click(function () {
			var me = $(this), mp = me.closest('li');
			if (mp.hasClass('active')) {
				return;
			}
			group.find('li').not(mp).removeClass('active');
			mp.addClass('active');
			$('#type').val(me.attr('rel'));
			$('#search-form').submit();
			return false;
		});
	});
</script>