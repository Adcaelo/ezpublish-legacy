{let matrix=$attribute.content}

<table>
<tr>
<th>
{section name=ColumnNames loop=$matrix.columns.sequential}
{$ColumnNames:item.name}

{delimiter}
</th>
<th>
{/delimiter}
{/section}
</th>
</tr>
<tr>
{section name=Rows loop=$matrix.rows.sequential}
<td>
{section name=Columns loop=$Rows:item.columns}
<input type="text" name="ContentObjectAttribute_ezmatrix_cell_{$attribute.id}[]" value="{$Rows:Columns:item|wash(xhtml)}" />
{delimiter}
</td>
<td>
{/delimiter}
{/section}
</td>
<td>
<input type="checkbox" name="ContentObjectAttribute_data_matrix_remove_{$attribute.id}[]" value="{$Rows:index}" /><br />
</td>
{delimiter}
</tr>
<tr>
{/delimiter}
{/section}
</tr>
</table>

<div class="buttonblock">
<input class="button" type="submit" name="CustomActionButton[{$attribute.id}_new_row]" value="{'New row'|i18n('design/standard/content/datatype')}" />
<input class="button" type="submit" name="CustomActionButton[{$attribute.id}_remove_selected]" value="{'Remove Selected'|i18n('design/standard/content/datatype')}" />
</div>

{/let}
