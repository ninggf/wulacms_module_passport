<div class="vbox wulaui" id="app-list">
    <section>
        <table id="table" data-auto data-table="{'passport/apps/data'|app}" data-sort="status,d">
            <thead>
            <tr>
                <th width="100">ID</th>
                <th width="100" data-sort="name,a">名称</th>
                <th>说明</th>
                <th width="60" data-sort="status,d">状态</th>
                <th width="60" data-sort="ios,d">苹果</th>
                <th width="60" data-sort="ipad,d">iPad</th>
                <th width="60" data-sort="android,d">安卓</th>
                <th width="60" data-sort="pad,d">平板</th>
                <th width="60" data-sort="web,d">WEB</th>
                <th width="60" data-sort="pc,d">PC</th>
                <th width="60" data-sort="h5,d">H5</th>
                <th width="60"></th>
            </tr>
            </thead>
        </table>
    </section>
</div>
<script>
	layui.use(['jquery', 'layer', 'wulaui'], ($, layer, $$) => {
		$('#app-list').on('before.dialog', '.cfg-app', function (e) {
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#edit-app-form').submit();
				return false;
			};
		}).removeClass('layui-hide');
		$('body').on('ajax.success', '#edit-app-form', function () {
			layer.closeAll();
		});
	});
</script>