{let content_object_version=fetch(notification,event_content,hash(event_id,$collection_item.event_id))}

----------------------------
ffff

{content_view_gui view=plain content_object=$content_object_version.contentobject}

----------------------

{/let}
