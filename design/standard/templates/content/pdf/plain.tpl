{pdf(text, $object.name|wash)}

{let version_attributes=$object.contentobject_attributes}
{section name=ContentObjectAttribute loop=$version_attributes}

{pdf(text,concat($ContentObjectAttribute:item.contentclass_attribute.name, " :")|wash)}

{attribute_pdf_gui view='plain' attribute=$ContentObjectAttribute:item}

{/section}
{/let}