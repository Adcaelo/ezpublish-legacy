{if $hide_right_menu|not}{* Only fetch policy if right menu is visible *}
{if fetch( 'user', 'has_access_to', hash( 'module', 'setup', 'function', 'managecache' ) )}

<div id="clearcache-tool">
{if ezpreference( 'admin_clearcache_menu' )}

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

    {if and( ne( $ui_context, 'edit' ), ne( $ui_context, 'browse' ) )}
        <h4><a class="show-hide-control" href={'/user/preferences/set/admin_clearcache_menu/0'|ezurl} title="{'Hide clear cache menu.'|i18n( 'design/admin/pagelayout' )}">-</a> {'Clear cache'|i18n( 'design/admin/pagelayout' )}</h4>
    {else}
	    {if eq( $ui_context, 'edit' )}
	       <h4><span class="disabled show-hide-control">-</span> <span class="disabled">{'Clear cache'|i18n( 'design/admin/pagelayout' )}</span></h4>
	    {else}
	       <h4><a class="show-hide-control" href={'/user/preferences/set/admin_clearcache_menu/0'|ezurl} title="{'Hide clear cache menu.'|i18n( 'design/admin/pagelayout' )}">-</a> {'Clear cache'|i18n( 'design/admin/pagelayout' )}</h4>
	    {/if}
    {/if}
    
</div></div></div></div></div></div>


<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

    {include uri='design:setup/clear_cache.tpl'}

</div></div></div></div></div></div>

{else}

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

    {if and( ne( $ui_context,'edit' ), ne( $ui_context, 'browse' ) )}
        <h4><a class="show-hide-control" href={'/user/preferences/set/admin_clearcache_menu/1'|ezurl} title="{'Show clear cache menu.'|i18n( 'design/admin/pagelayout' )}">+</a> {'Clear cache'|i18n( 'design/admin/pagelayout' )}</h4>
    {else}
	    {if eq( $ui_context, 'edit' )}
	        <h4><span class="disabled show-hide-control">+</span> <span class="disabled">{'Clear cache'|i18n( 'design/admin/pagelayout' )}</span></h4>
	    {else}
	        <h4><a class="show-hide-control" href={'/user/preferences/set/admin_clearcache_menu/1'|ezurl} title="{'Show clear cache menu.'|i18n( 'design/admin/pagelayout' )}">+</a> {'Clear cache'|i18n( 'design/admin/pagelayout' )}</h4>
	    {/if}
    {/if}
    
</div></div></div></div></div></div>

{/if}
</div>

{/if}
{/if}