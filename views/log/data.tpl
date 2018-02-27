<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.type}</td>
        <td>{$row.passport_id}</td>
        <td>{$row.open_id}</td>
        <td>
            {$row.create_time|date_format:'Y-m-d H:i:s'}
        </td>
        <td>
            {$row.ip}
        </td>
        <td>{$row.device}</td>
        <td>
            {if $row.expiration}
                {$row.expiration|date_format:'Y-m-d H:i:s'}
            {/if}
        </td>
        <td>
            {$row.token}
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="9" class="text-center">无数据</td>
    </tr>
{/foreach}
</tbody>