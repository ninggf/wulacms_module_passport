<tbody data-total="0">
{foreach $rows as $row}
    <tr>
        <td>{$row.id}</td>
        <td>
            <a href="{'passport/log'|app}?type={$row.id}" title="登录日志[{$row.id}]" data-tab="&#xe64a;">{$row.name}</a>
        </td>
        <td>{$row.desc}</td>
        <td class="{if $row.status}active{/if}">
            <i class="fa fa-check text-success text-active"></i>
            <i class="fa fa-times text-danger text"></i>
        </td>
        <td class="{if $row.ios}active{/if}">
            <i class="fa fa-check text-success text-active"></i>
            <i class="fa fa-times text-danger text"></i>
        </td>
        <td class="{if $row.android}active{/if}">
            <i class="fa fa-check text-success text-active"></i>
            <i class="fa fa-times text-danger text"></i>
        </td>
        <td class="{if $row.web}active{/if}">
            <i class="fa fa-check text-success text-active"></i>
            <i class="fa fa-times text-danger text"></i>
        </td>
        <td class="text-center">
            <a href="{'passport/apps/cfg'|app}/{$row.type}" class="cfg-app" data-ajax="dialog"
               data-area="600px,{if $row.hasForm}400px{else}auto{/if}" title="配置[{$row.name}]">
                <i class="fa fa-gear"></i>
            </a>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="8">无数据</td>
    </tr>
{/foreach}
</tbody>