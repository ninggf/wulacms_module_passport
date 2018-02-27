<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.type}</td>
        <td>{$row.passport_id}</td>
        <td>{$row.open_id}</td>
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
        <td class="text-right">
            <div class="btn-group">
                {if $row.login_time && $row.expiration > $ctime}
                    <a href="{'passport/oauth/logout'|app}?token={$row.token}" data-ajax data-confirm="真的要强退退出吗?"
                       class="btn btn-xs btn-danger" title="强制退出">
                        <i class="fa fa-sign-out"></i>
                    </a>
                {/if}
                <a href="{'passport/log'|app}?oauth={$row.id}" class="btn btn-xs btn-default"
                   title="登录日志[{$row.passport_id}:{$row.type}]" data-tab="&#xe64a;">
                    <i class="fa fa-list-ol"></i>
                </a>
            </div>
        </td>
    </tr>
{/foreach}
</tbody>