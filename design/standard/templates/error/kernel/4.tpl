<div class="warning">
<h2>{"Object moved"|i18n("design/standard/error/kernel")}</h2>
<p>{"The object is no longer available at this URL."|i18n("design/standard/error/kernel")}</p>
<p>{"You should automatically be redirected to the new location. If not click %url."|i18n("design/standard/error/kernel",,
                                                                                          hash('%url',concat('<a href=',$parameters.new_location|ezurl(),'>',
                                                                                                             'here'|i18n('design/standard/error/kernel'),
                                                                                                              '</a>')))}</p>
</div>

