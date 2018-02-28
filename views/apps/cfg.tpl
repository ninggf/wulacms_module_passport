<div class="container-fluid wulaui m-t-sm">
    <form id="edit-app-form" name="AppEditForm" data-validate="{$rules|escape}" action="{'passport/apps/save'|app}"
          data-ajax method="post" data-loading>
        {$form|render}
        {if $aform}
            <div class="line line-dashed line-lg pull-in"></div>
            <p class="text-muted m-t-n-md">第三方配置</p>
            {$aform|render}
        {/if}
    </form>
</div>