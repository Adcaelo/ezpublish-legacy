<p class="box">
{section name=Author loop=$attribute.content.author_list sequence=array(bglight,bgdark) }
 {$Author:item.name|wash(xhtml)} - ( <a href="mailto:{$Author:item.email|wash(xhtml)}">{$Author:item.email|wash(xhtml)}</a> )

{delimiter}
,
{/delimiter}
{/section}
</p>
