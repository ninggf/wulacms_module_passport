<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.id}</td>
        <td>{$row.username}</td>
        <td>{$row.nickname}</td>
        <td>{$row.create_time|date_format:'Y-m-d H:i:s'}</td>
        {'passport.table'|tablerow:$row}
        <td class="text-right">
            <div class="btn-group">
                <a href="{'passport/edit'|app}/{$row.id}" data-ajax="dialog" data-area="700px,auto"
                   data-title="编辑『{$row.username|escape}』" class="btn btn-xs btn-primary edit-admin">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
                <a href="{'passport/del'|app}/{$row.id}" data-ajax class="btn btn-xs btn-danger"
                   data-confirm="你真的要删除该用户?">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="{'passport.table'|tablespan:6}" class="text-center">无数据</td>
    </tr>
{/foreach}
</tbody>