<div class="articleline">

<h2><a href={$node.url_alias|ezurl}>{$node.name}</a></h2>

<div class="byline">
  <p>
  ({$node.object.published|add(21600)|l10n( datetime )})
  </p>
</div>

{* need to check if image exists

<div class="imageright">
    <a class="small" href={concat( "/content/view/full/", $node.node_id, "/")|ezurl}>
    {section show=$node.object.data_map.frontpage_image.data_int}
	{let image=fetch(content,object,hash(object_id,$node.object.data_map.frontpage_image.data_int))}
        {attribute_view_gui attribute=$image.data_map.image image_class=small}
	{/let}
     {/section}
     </a>
</div>

*}

<div class="object_brief">
  {attribute_view_gui attribute=$node.object.data_map.intro}
</div>

<div class="readmore">
    <p>
       <a href={$node.url_alias|ezurl}>Read more</a>
    </p>
</div>

</div>