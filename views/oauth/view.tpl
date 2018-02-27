<section class="hbox stretch wulaui">
    <section>
        <div class="vbox">
            <section>
                <div class="table-responsive">
                    <table data-table>
                        <thead>
                        <tr>
                            <th width="20"></th>
                            <th width="70">类型</th>
                            <th width="100">创建时间</th>
                            <th width="100">最近登录</th>
                            <th width="120">登录IP</th>
                            <th width="80">设备</th>
                            <th width="100">过期时间</th>
                            <th>TOKEN</th>
                            <th width="60"></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $rows as $row}
                            <tr rel="{$row.id}">
                                <td></td>
                                <td>{$row.type}</td>
                                <td>{$row.create_time|date_format:'Y-m-d H:i:s'}</td>
                                <td>
                                    {if $row.login_time}
                                        {$row.login_time|date_format:'Y-m-d H:i:s'}
                                    {/if}
                                </td>
                                <td>
                                    {$row.ip}
                                </td>
                                <td>{$row.device}</td>
                                <td>
                                    {if $row.expiration}
                                        {if $row.expiration <$ctime}
                                            已过期
                                        {else}
                                            {$row.expiration|date_format:'Y-m-d H:i:s'}
                                        {/if}
                                    {/if}
                                </td>
                                <td>
                                    {$row.token}
                                </td>
                                <td class="text-center">
                                    {if $row.login_time && $row.expiration > $ctime}
                                        <a href="{'passport/oauth/logout'|app}?token={$row.token}" data-ajax
                                           data-confirm="真的要强退退出吗?" class="btn btn-xs btn-danger" title="强制退出">
                                            <i class="fa fa-sign-out"></i>
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                            <tr class="hidden">
                                <td></td>
                                <td>OPENID:</td>
                                <td colspan="3">{$row.open_id}</td>
                                <td>UNIONID:</td>
                                <td colspan="3">{$row.union_id}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</section>
<script>
	layui.use(['jquery', 'wulaui']);
</script>