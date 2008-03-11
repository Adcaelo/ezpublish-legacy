/**
 * $Id: editor_template_src.js 677 2008-03-07 13:52:41Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, extend = tinymce.extend, each = tinymce.each, Cookie = tinymce.util.Cookie, lastExtID;

	tinymce.create('tinymce.themes.eZTheme', {
		// Control name lookup, format: title, command
		controls : {
			bold : [eZOeMCE['i18n']['bold'], 'Bold'],
			italic : [eZOeMCE['i18n']['italic'], 'Italic'],
			underline : [eZOeMCE['i18n']['underline'], 'Underline'],
			strikethrough : ['striketrough_desc', 'Strikethrough'],
			justifyleft : ['justifyleft_desc', 'JustifyLeft'],
			justifycenter : ['justifycenter_desc', 'JustifyCenter'],
			justifyright : ['justifyright_desc', 'JustifyRight'],
			justifyfull : ['justifyfull_desc', 'JustifyFull'],
			bullist : [eZOeMCE['i18n']['bullet_list'], 'InsertUnorderedList'],
			numlist : [eZOeMCE['i18n']['numbedred_list'], 'InsertOrderedList'],
			outdent : [eZOeMCE['i18n']['outdent'], 'Outdent'],
			indent : [eZOeMCE['i18n']['indent'], 'Indent'],
			cut : ['cut_desc', 'Cut'],
			copy : ['copy_desc', 'Copy'],
			paste : ['paste_desc', 'Paste'],
			undo : [eZOeMCE['i18n']['undo'], 'Undo'],
			redo : [eZOeMCE['i18n']['redo'], 'Redo'],
			link : [eZOeMCE['i18n']['make_link'], 'mceLink'],
			unlink : [eZOeMCE['i18n']['remove_link'], 'unlink'],
			image : [eZOeMCE['i18n']['insert_image'], 'mceImage'],
			object : [eZOeMCE['i18n']['insert_object'], 'mceObject'],
			custom : [eZOeMCE['i18n']['insert_custom'], 'mceCustom'],
			literal : [eZOeMCE['i18n']['insert_literal'], 'mceLiteral'],
			cleanup : ['cleanup_desc', 'mceCleanup'],
			help : ['help_desc', 'mceHelp'],
			code : ['code_desc', 'mceCodeEditor'],
			hr : ['hr_desc', 'InsertHorizontalRule'],
			removeformat : ['removeformat_desc', 'RemoveFormat'],
			sub : ['sub_desc', 'subscript'],
			sup : ['sup_desc', 'superscript'],
			forecolor : ['forecolor_desc', 'ForeColor'],
			forecolorpicker : ['forecolor_desc', 'mceForeColor'],
			backcolor : ['backcolor_desc', 'HiliteColor'],
			backcolorpicker : ['backcolor_desc', 'mceBackColor'],
			charmap : [eZOeMCE['i18n']['insert_special'], 'mceCharMap'],
			visualaid : ['visualaid_desc', 'mceToggleVisualAid'],
			anchor : [eZOeMCE['i18n']['insert_anchor'], 'mceInsertAnchor'],
			newdocument : ['newdocument_desc', 'mceNewDocument'],
			blockquote : ['blockquote_desc', 'mceBlockQuote'],
			pagebreak : [eZOeMCE['i18n']['insert_pagebreak'], 'mcePageBreak']
		},

		stateControls : ['bold', 'italic', 'underline', 'strikethrough', 'bullist', 'numlist', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'sub', 'sup', 'blockquote'],

		init : function(ed, url) {
			var t = this, s;

			t.editor = ed;
			t.url = url;
			t.onResolveName = new tinymce.util.Dispatcher(this);

			// Default settings
			t.settings = s = extend({
				theme_advanced_path : true,
				theme_advanced_toolbar_location : 'bottom',
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect",
				theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
				theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap",
				theme_advanced_blockformats : "p,pre,h1,h2,h3,h4,h5,h6",
				theme_advanced_toolbar_align : "center",
				theme_advanced_fonts : "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
				theme_advanced_font_sizes : "1,2,3,4,5,6,7",
				theme_advanced_more_colors : 1,
				theme_advanced_row_height : 23,
				theme_advanced_resize_horizontal : 1,
				theme_advanced_resizing_use_cookie : 1
			}, ed.settings);

            if (s.theme_advanced_path_location)
                s.theme_advanced_statusbar_location = s.theme_advanced_path_location;

            if (s.theme_advanced_statusbar_location === 'none')
                s.theme_advanced_statusbar_location = 0;

            // Init editor
            ed.onInit.add(function() {
                ed.onNodeChange.add(t._nodeChanged, t);
                if ( s.theme_advanced_content_css )
                {
                	var css_arr = s.theme_advanced_content_css.split(',');
                	for ( var ind = 0, len = css_arr.length; ind < len; ind++ )
                	   ed.dom.loadCSS( css_arr[ind] );
                }
                else
				    ed.dom.loadCSS(ed.baseURI.toAbsolute("themes/ez/skins/" + ed.settings.skin + "/content.css"));
			});

			ed.onSetProgressState.add(function(ed, b, ti) {
				var co, id = ed.id, tb;

				if (b) {
					t.progressTimer = setTimeout(function() {
						co = ed.getContainer();
						co = co.insertBefore(DOM.create('DIV', {style : 'position:relative'}), co.firstChild);
						tb = DOM.get(ed.id + '_tbl');

						DOM.add(co, 'div', {id : id + '_blocker', 'class' : 'mceBlocker', style : {width : tb.clientWidth + 2, height : tb.clientHeight + 2}});
						DOM.add(co, 'div', {id : id + '_progress', 'class' : 'mceProgress', style : {left : tb.clientWidth / 2, top : tb.clientHeight / 2}});
					}, ti || 0);
				} else {
					DOM.remove(id + '_blocker');
					DOM.remove(id + '_progress');
					clearTimeout(t.progressTimer);
				}
			});

            if ( s.theme_advanced_editor_css )
            {
                var ui_css_arr = s.theme_advanced_editor_css.split(',');
                for ( var ind2 = 0, len2 = ui_css_arr.length; ind2 < len2; ind2++ )
                    DOM.loadCSS( ui_css_arr[ind2] );
            }
            else
            {
				DOM.loadCSS( ed.baseURI.toAbsolute( s.editor_css || "themes/ez/skins/" + ed.settings.skin + "/ui.css"));
				
	            if (s.skin_variant && !s.editor_css )
	                DOM.loadCSS(ed.baseURI.toAbsolute( "themes/ez/skins/" + ed.settings.skin + "/ui_" + s.skin_variant + ".css"));
            }
		},

		createControl : function(n, cf) {
			var cd, c;

			if (c = cf.createControl(n))
				return c;

			switch (n) {
				case "styleselect":
					return this._createStyleSelect();

				case "formatselect":
					return this._createBlockFormats();

				case "fontselect":
					return this._createFontSelect();

				case "fontsizeselect":
					return this._createFontSizeSelect();

				case "forecolor":
					return this._createForeColorMenu();

				case "backcolor":
					return this._createBackColorMenu();
			}

			if ((cd = this.controls[n]))
				return cf.createButton(n, {title : cd[0], cmd : cd[1], ui : cd[2], value : cd[3]});
		},

		execCommand : function(cmd, ui, val) {
			var f = this['_' + cmd];

			if (f) {
				f.call(this, ui, val);
				return true;
			}

			return false;
		},

		_importClasses : function() {
			var ed = this.editor, c = ed.controlManager.get('styleselect');

			if (c.getLength() == 0) {
				each(ed.dom.getClasses(), function(o) {
					c.add(o['class'], o['class']);
				});
			}
		},

		_createStyleSelect : function(n) {
			var t = this, ed = t.editor, cf = ed.controlManager, c = cf.createListBox('styleselect', {
				title : 'ez.style_select',
				onselect : function(v) {
					if (c.selectedValue === v) {
						ed.execCommand('mceSetStyleInfo', 0, {command : 'removeformat'});
						c.select();
						return false;
					} else
						ed.execCommand('mceSetCSSClass', 0, v);
				}
			});

            each(ed.getParam('theme_advanced_styles', '', 'hash'), function(v, k) {
                if (v)
                    c.add(t.editor.translate(k), v);
			});

			c.onPostRender.add(function(ed, n) {
				Event.add(n, 'focus', t._importClasses, t);
				Event.add(n, 'mousedown', t._importClasses, t);
			});

			return c;
		},

		_createFontSelect : function() {
            var c, t = this, ed = t.editor;

            c = ed.controlManager.createListBox('fontselect', {title : 'advanced.fontdefault', cmd : 'FontName'});

            each(ed.getParam('theme_advanced_fonts', t.settings.theme_advanced_fonts, 'hash'), function(v, k) {
                c.add(ed.translate(k), v, {style : v.indexOf('dings') == -1 ? 'font-family:' + v : ''});
			});

			return c;
		},

		_createFontSizeSelect : function() {
			var c, t = this, lo = [
				"1 (8 pt)",
				"2 (10 pt)",
				"3 (12 pt)",
				"4 (14 pt)",
				"5 (18 pt)",
				"6 (24 pt)",
				"7 (36 pt)"
			], fz = [8, 10, 12, 14, 18, 24, 36];

			c = t.editor.controlManager.createListBox('fontsizeselect', {title : 'ez.font_size', cmd : 'FontSize'});

			each(t.settings.theme_advanced_font_sizes.split(','), function(v) {
                c.add(lo[parseInt(v) - 1], v, {'style' : 'font-size:' + fz[v - 1] + 'pt', 'class' : 'mceFontSize' + v});
			});

			return c;
		},

		_createBlockFormats : function() {
			var c, fmts = {
				p : eZOeMCE['i18n']['normal'],
				pre : eZOeMCE['i18n']['literal'],
				h1 : eZOeMCE['i18n']['h1'],
				h2 : eZOeMCE['i18n']['h2'],
				h3 : eZOeMCE['i18n']['h3'],
				h4 : eZOeMCE['i18n']['h4'],
				h5 : eZOeMCE['i18n']['h5'],
				h6 : eZOeMCE['i18n']['h6']
			}, t = this;

			c = t.editor.controlManager.createListBox('formatselect', {title : eZOeMCE['i18n']['type'], cmd : 'FormatBlock'});

			each(t.settings.theme_advanced_blockformats.split(','), function(v) {
				c.add(t.editor.translate(fmts[v]), v, {'class' : 'mce_formatPreview mce_' + v});
			});

			return c;
		},

		_createForeColorMenu : function() {
			var c, t = this, s = t.settings, o = {}, v;

			if (s.theme_advanced_more_colors) {
				o.more_colors_func = function() {
					t._mceColorPicker(0, {
						color : c.value,
						func : function(co) {
							c.setColor(co);
						}
					});
				};
			}

			if (v = s.theme_advanced_text_colors)
				o.colors = v;

			o.title = 'ez.forecolor_desc';
			o.cmd = 'ForeColor';
			o.scope = this;

			c = t.editor.controlManager.createColorSplitButton('forecolor', o);

			return c;
		},

		_createBackColorMenu : function() {
			var c, t = this, s = t.settings, o = {}, v;

			if (s.theme_advanced_more_colors) {
				o.more_colors_func = function() {
					t._mceColorPicker(0, {
						color : c.value,
						func : function(co) {
							c.setColor(co);
						}
					});
				};
			}

			if (v = s.theme_advanced_background_colors)
				o.colors = v;

			o.title = 'ez.backcolor_desc';
			o.cmd = 'HiliteColor';
			o.scope = this;

			c = t.editor.controlManager.createColorSplitButton('backcolor', o);

			return c;
		},

		renderUI : function(o) {
			var n, ic, tb, t = this, ed = t.editor, s = t.settings, sc, p, nl;

            n = p = DOM.create('span', {id : ed.id + '_parent', 'class' : 'mceEditor ' + ed.settings.skin + 'Skin' + (s.skin_variant ? ' ' + ed.settings.skin + 'Skin' + t._ufirst(s.skin_variant) : '')});

			if (!DOM.boxModel)
				n = DOM.add(n, 'div', {'class' : 'mceOldBoxModel'});

			n = sc = DOM.add(n, 'table', {id : ed.id + '_tbl', 'class' : 'mceLayout', cellSpacing : 0, cellPadding : 0});
			n = tb = DOM.add(n, 'tbody');

			switch ((s.theme_advanced_layout_manager || '').toLowerCase()) {
				case "rowlayout":
					ic = t._rowLayout(s, tb, o);
					break;

				case "customlayout":
					ic = ed.execCallback("theme_advanced_custom_layout", s, tb, o, p);
					break;

				default:
					ic = t._simpleLayout(s, tb, o, p);
			}

			n = o.targetNode;

			// Add classes to first and last TRs
			nl = sc.rows;
			DOM.addClass(nl[0], 'mceFirst');
			DOM.addClass(nl[nl.length - 1], 'mceLast');

			// Add classes to first and last TDs
			each(DOM.select('tr', tb), function(n) {
				DOM.addClass(n.firstChild, 'mceFirst');
				DOM.addClass(n.childNodes[n.childNodes.length - 1], 'mceLast');
			});

			if ( s.theme_advanced_toolbar_container && DOM.get(s.theme_advanced_toolbar_container) )
				DOM.get(s.theme_advanced_toolbar_container).appendChild(p);
			else
				DOM.insertAfter(p, n);

			Event.add(ed.id + '_path_row', 'click', function(e) {
				e = e.target;

				if (e.nodeName == 'A') {
					t._sel(e.className.replace(/^.*mcePath_([0-9]+).*$/, '$1'));

					return Event.cancel(e);
				}
			});
/*
			if (DOM.get(ed.id + '_path_row')) {
				Event.add(ed.id + '_tbl', 'mouseover', function(e) {
					var re;
	
					e = e.target;

					if (e.nodeName == 'SPAN' && DOM.hasClass(e.parentNode, 'mceButton')) {
						re = DOM.get(ed.id + '_path_row');
						t.lastPath = re.innerHTML;
						DOM.setHTML(re, e.parentNode.title);
					}
				});

				Event.add(ed.id + '_tbl', 'mouseout', function(e) {
					if (t.lastPath) {
						DOM.setHTML(ed.id + '_path_row', t.lastPath);
						t.lastPath = 0;
					}
				});
			}
*/

            if (!ed.getParam('accessibility_focus') || ed.getParam('tab_focus'))
                Event.add(DOM.add(p, 'a', {href : '#'}, '<!-- IE -->'), 'focus', function() {tinyMCE.get(ed.id).focus();});

			if (s.theme_advanced_toolbar_location == 'external')
				o.deltaHeight = 0;

			t.deltaHeight = o.deltaHeight;
			o.targetNode = null;

			return {
				iframeContainer : ic,
				editorContainer : ed.id + '_parent',
				sizeContainer : sc,
				deltaHeight : o.deltaHeight
			};
		},

		getInfo : function() {
			return {
				longname : 'eZ theme based on TinyMCE Advance theme',
				author : 'Moxiecode Systems AB / eZ Systems AS',
				authorurl : 'http://tinymce.moxiecode.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			}
		},

        resizeBy : function(dw, dh) {
            var e = DOM.get(this.editor.id + '_tbl');

            this.resizeTo(e.clientWidth + dw, e.clientHeight + dh);
        },

        resizeTo : function(w, h) {
            var ed = this.editor, s = ed.settings, e = DOM.get(ed.id + '_tbl'), ifr = DOM.get(ed.id + '_ifr'), dh;

            // Boundery fix box
            w = Math.max(s.theme_advanced_resizing_min_width || 100, w);
            h = Math.max(s.theme_advanced_resizing_min_height || 100, h);
            w = Math.min(s.theme_advanced_resizing_max_width || 0xFFFF, w);
            h = Math.min(s.theme_advanced_resizing_max_height || 0xFFFF, h);

            // Calc difference between iframe and container
            dh = e.clientHeight - ifr.clientHeight;

            // Resize iframe and container
            DOM.setStyle(ifr, 'height', h - dh);
            DOM.setStyles(e, {width : w, height : h});
        },

        // Internal functions

		_simpleLayout : function(s, tb, o, p) {
			var t = this, ed = t.editor, lo = s.theme_advanced_toolbar_location, sl = s.theme_advanced_statusbar_location, n, ic, etb, c;

			// Create toolbar container at top
			if (lo === 'top')
				t._addToolbars(tb, o);

			// Create external toolbar
			if (lo === 'external') {
				n = c = DOM.create('div', {style : 'position:relative'});
				n = DOM.add(n, 'div', {id : ed.id + '_external', 'class' : 'mceExternalToolbar'});
				DOM.add(n, 'a', {id : ed.id + '_external_close', href : 'javascript:;', 'class' : 'mceExternalClose'});
				n = DOM.add(n, 'table', {id : ed.id + '_tblext', cellSpacing : 0, cellPadding : 0});
				etb = DOM.add(n, 'tbody');

				if (p.firstChild.className === 'mceOldBoxModel')
					p.firstChild.appendChild(c);
				else
					p.insertBefore(c, p.firstChild);

				t._addToolbars(etb, o);

				ed.onMouseUp.add(function() {
					var e = DOM.get(ed.id + '_external');
					DOM.show(e);

					DOM.hide(lastExtID);

					var f = Event.add(ed.id + '_external_close', 'click', function() {
						DOM.hide(ed.id + '_external');
						Event.remove(ed.id + '_external_close', 'click', f);
					});

					DOM.show(e);
					DOM.setStyle(e, 'top', 0 - DOM.getRect(ed.id + '_tblext').h - 1);

					// Fixes IE rendering bug
					DOM.hide(e);
					DOM.show(e);
					e.style.filter = '';

					lastExtID = ed.id + '_external';

					e = null;
				});
			}

			if (sl === 'top')
				t._addStatusBar(tb, o);

			// Create iframe container
			if (!s.theme_advanced_toolbar_container) {
				n = DOM.add(tb, 'tr');
				n = ic = DOM.add(n, 'td', {'class' : 'mceIframeContainer'});
			}

			// Create toolbar container at bottom
			if (lo === 'bottom')
				t._addToolbars(tb, o);

			if (sl === 'bottom')
				t._addStatusBar(tb, o);

			return ic;
		},

		_rowLayout : function(s, tb, o) {
			var t = this, ed = t.editor, dc, da, cf = ed.controlManager, n, ic, to, a;

			dc = s.theme_advanced_containers_default_class || '';
			da = s.theme_advanced_containers_default_align || 'center';

			each((s.theme_advanced_containers || '').split(','), function(c, i) {
                var v = s['theme_advanced_container_' + c] || '';

                switch (c.toLowerCase()) {
					case 'mceeditor':
						n = DOM.add(tb, 'tr');
						n = ic = DOM.add(n, 'td', {'class' : 'mceIframeContainer'});
						break;

					case 'mceelementpath':
						t._addStatusBar(tb, o);
						break;

					default:
                        a = s['theme_advanced_container_' + c + '_align'].toLowerCase();
                        a = 'mce' + t._ufirst(a);

                        n = DOM.add(DOM.add(tb, 'tr'), 'td', {
                            'class' : 'mceToolbar ' + (s['theme_advanced_container_' + c + '_class'] || dc) + ' ' + a || da
                        });

						to = cf.createToolbar("toolbar" + i);
						t._addControls(v, to);
						DOM.setHTML(n, to.renderHTML());
						o.deltaHeight -= s.theme_advanced_row_height;
				}
			});

			return ic;
		},

		_addControls : function(v, tb) {
			var t = this, s = t.settings, di, cf = t.editor.controlManager;

			if (s.theme_advanced_disable && !t._disabled) {
				di = {};

				each(s.theme_advanced_disable.split(','), function(v) {
					di[v] = 1;
				});

				t._disabled = di;
			} else
				di = t._disabled;

			each(v.split(','), function(n) {
				var c;

				if (di && di[n])
					return;

				// Compatiblity with 2.x
				if (n === 'tablecontrols') {
					each(["table","|","row_props","cell_props","|","row_before","row_after","delete_row","|","col_before","col_after","delete_col","|","split_cells","merge_cells"], function(n) {
						n = t.createControl(n, cf);

						if (n)
							tb.add(n);
					});

					return;
				}

				c = t.createControl(n, cf);

				if (c)
					tb.add(c);
			});
		},

		_addToolbars : function(c, o) {
            var t = this, i, tb, ed = t.editor, s = t.settings, v, cf = ed.controlManager, di, n, h = [], a;

            a = s.theme_advanced_toolbar_align.toLowerCase();
            a = 'mce' + t._ufirst(a);

            n = DOM.add(DOM.add(c, 'tr'), 'td', {'class' : 'mceToolbar ' + a});

			if (!ed.getParam('accessibility_focus') || ed.getParam('tab_focus'))
				h.push(DOM.createHTML('a', {href : '#', onfocus : 'tinyMCE.get(\'' + ed.id + '\').focus();'}, '<!-- IE -->'));

			h.push(DOM.createHTML('a', {href : '#', accesskey : 'q', title : ed.getLang("ez.toolbar_focus")}, '<!-- IE -->'));

			// Create toolbar and add the controls
			for (i=1; (v = s['theme_advanced_buttons' + i]); i++) {
				tb = cf.createToolbar("toolbar" + i, {'class' : 'mceToolbarRow' + i});

				if (s['theme_advanced_buttons' + i + '_add'])
					v += ',' + s['theme_advanced_buttons' + i + '_add'];

				if (s['theme_advanced_buttons' + i + '_add_before'])
					v = s['theme_advanced_buttons' + i + '_add_before'] + ',' + v;

				t._addControls(v, tb);

				//n.appendChild(n = tb.render());
				if ( s.theme_advanced_toolbar_floating )
				    h.push( t._toolbarRenderFlowHTML.call( tb ) );
                else
                    h.push( tb.renderHTML() );

				o.deltaHeight -= s.theme_advanced_row_height;
			}

			h.push(DOM.createHTML('a', {href : '#', accesskey : 'z', title : ed.getLang("ez.toolbar_focus"), onfocus : 'tinyMCE.getInstanceById(\'' + ed.id + '\').focus();'}, '<!-- IE -->'));
            DOM.setHTML(n, h.join(''));
		},
		
		// Custom toolbar renderer for ez theme
	    _toolbarRenderFlowHTML : function()
	    {
	        var t = this, h = '<div class="mceToolbarGroupingElement">', c = 'mceToolbarElement mceToolbarEnd', co, s = t.settings;
	        
	        h += DOM.createHTML('span', {'class' : 'mceToolbarElement mceToolbarStart'}, DOM.createHTML('span', null, '<!-- IE -->'));

	        each(t.controls, function(c)
	        {
	            // seperators create invalid html, so we create it here instead 
	            if ( !c.classPrefix )
	            {
	                h += '<span class="mceToolbarElement">' + DOM.createHTML('span', {'class' : 'mceSeparator'}, '<!-- IE -->') + '</span>';
	                h += '</div><div class="mceToolbarGroupingElement">';
	            }
	            else h += '<span class="mceToolbarElement">' + c.renderHTML() + '</span>';
	        });
	        
	        co = t.controls[t.controls.length - 1].constructor;

	        if (co === tinymce.ui.Button)
	            c += ' mceToolbarEndButton';
	        else if (co === tinymce.ui.SplitButton)
	            c += ' mceToolbarEndSplitButton';
	        else if (co === tinymce.ui.ListBox)
	            c += ' mceToolbarEndListBox';
	
	        h += DOM.createHTML('span', {'class' : c}, DOM.createHTML('span', null, '<!-- IE -->')) + '</div>';

	        return DOM.createHTML('div', {id : t.id, 'class' : 'mceToolbar mceToolBarFlow' + (s['class'] ? ' ' + s['class'] : ''), align : t.settings.align || ''},  h );
	    },

		_addStatusBar : function(tb, o) {
			var n, t = this, ed = t.editor, s = t.settings, r, mf, me, td;

			n = DOM.add(tb, 'tr');
			n = td = DOM.add(n, 'td', {'class' : 'mceStatusbar'});
			n = DOM.add(n, 'div', {id : ed.id + '_path_row'}, s.theme_advanced_path ? eZOeMCE['i18n']['path'] + ': ' : '&nbsp;');
			DOM.add(n, 'a', {href : '#', accesskey : 'x'});

			if (s.theme_advanced_resizing && !tinymce.isOldWebKit) {
				DOM.add(td, 'a', {id : ed.id + '_resize', href : 'javascript:;', onclick : "return false;", 'class' : 'mceResize'});

				if (s.theme_advanced_resizing_use_cookie) {
					ed.onPostRender.add(function() {
						var o = Cookie.getHash("TinyMCE_" + ed.id + "_size"), c = DOM.get(ed.id + '_tbl');

						if (!o)
							return;

						if (s.theme_advanced_resize_horizontal)
							c.style.width = o.cw + 'px';

						c.style.height = o.ch + 'px';
						DOM.get(ed.id + '_ifr').style.height = (parseInt(o.ch) + t.deltaHeight) + 'px';
					});
				}

				ed.onPostRender.add(function() {
					Event.add(ed.id + '_resize', 'mousedown', function(e) {
						var c, p, w, h, n, pa;

						// Measure container
						c = DOM.get(ed.id + '_tbl');
						w = c.clientWidth;
						h = c.clientHeight;

						miw = s.theme_advanced_resizing_min_width || 100;
						mih = s.theme_advanced_resizing_min_height || 100;
						maw = s.theme_advanced_resizing_max_width || 0xFFFF;
						mah = s.theme_advanced_resizing_max_height || 0xFFFF;

						// Setup placeholder
						p = DOM.add(DOM.get(ed.id + '_parent'), 'div', {'class' : 'mcePlaceHolder'});
						DOM.setStyles(p, {width : w, height : h});

						// Replace with placeholder
						DOM.hide(c);
						DOM.show(p);

						// Create internal resize obj
						r = {
							x : e.screenX,
							y : e.screenY,
							w : w,
							h : h,
							dx : null,
							dy : null
						};

						// Start listening
						mf = Event.add(document, 'mousemove', function(e) {
							var w, h;

							// Calc delta values
							r.dx = e.screenX - r.x;
							r.dy = e.screenY - r.y;

							// Boundery fix box
							w = Math.max(miw, r.w + r.dx);
							h = Math.max(mih, r.h + r.dy);
							w = Math.min(maw, w);
							h = Math.min(mah, h);

							// Resize placeholder
							if (s.theme_advanced_resize_horizontal)
								p.style.width = w + 'px';

							p.style.height = h + 'px';

							return Event.cancel(e);
						});

						me = Event.add(document, 'mouseup', function(e) {
							var ifr;

							// Stop listening
							Event.remove(document, 'mousemove', mf);
							Event.remove(document, 'mouseup', me);

							c.style.display = '';
							DOM.remove(p);

							if (r.dx === null)
								return;

							ifr = DOM.get(ed.id + '_ifr');

							if (s.theme_advanced_resize_horizontal)
								c.style.width = (r.w + r.dx) + 'px';

							c.style.height = (r.h + r.dy) + 'px';
							ifr.style.height = (ifr.clientHeight + r.dy) + 'px';

							if (s.theme_advanced_resizing_use_cookie) {
								Cookie.setHash("TinyMCE_" + ed.id + "_size", {
									cw : r.w + r.dx,
									ch : r.h + r.dy
								});
							}
						});

						return Event.cancel(e);
					});
				});
			}

			o.deltaHeight -= 21;
			n = tb = null;
		},

		_nodeChanged : function(ed, cm, n, co) {
			var t = this, p, de = 0, v, c, s = t.settings, div = false;

			tinymce.each(t.stateControls, function(c) {
				cm.setActive(c, ed.queryCommandState(t.controls[c][1]));
			});

			//cm.setActive('visualaid', ed.hasVisual);
			cm.setDisabled('undo', !ed.undoManager.hasUndo() && !ed.typing);
			cm.setDisabled('redo', !ed.undoManager.hasRedo());
			//cm.setDisabled('outdent', !ed.queryCommandState('Outdent'));
			
			p = DOM.getParent(n, 'DIV');
            if (c = cm.get('object'))
            {
                div = !!p && p.className.indexOf('mceItem') === -1 && p.className.indexOf('mceNonEditable') !== -1;
                c.setActive( div );
            }

            p = DOM.getParent(n, 'UL,OL');
            if (c = cm.get('outdent'))
            {
                c.setDisabled( !p && !ed.queryCommandState('Outdent') );
            }
            
            if (c = cm.get('indent'))
            {
                if ( p )
                {
					for (var i = 0, l = p.childNodes.length, count = 0; i < l; i++)
					{
					    if (p.childNodes[i].nodeType === 1 && p.childNodes[i].nodeName === 'LI') count++;
					    if ( count === 2 ) break;					    
					}
			    }
                c.setDisabled( !p || count < 2 );
            }

			p = DOM.getParent(n, 'A');
			if (c = cm.get('link')) {
				if (!p || !p.name) {
					c.setDisabled(!p && co);
					c.setActive(!!p);
				}
			}

			if (c = cm.get('unlink')) {
				c.setDisabled(!p && co || div);
				c.setActive(!!p && !p.name);
			}

			if (c = cm.get('anchor')) {
				c.setActive(!!p && p.name);

				if (tinymce.isWebKit) {
					p = DOM.getParent(n, 'IMG');
					c.setActive(!!p && DOM.getAttrib(p, 'mce_name') === 'a');
				}
			}

			p = DOM.getParent(n, 'IMG');
			if (c = cm.get('image'))
				c.setActive(!!p && p.className.indexOf('mceItem') === -1);
				
            p = DOM.getParent(n, 'PRE');
            if (c = cm.get('literal'))
                c.setActive(!!p );
                
            p = DOM.getParent(n, 'SPAN');
            if (c = cm.get('custom'))
                c.setActive(!!p );

            if (c = cm.get('pagebreak'))
                c.setDisabled(!!p && DOM.hasClass(p, 'pagebreak') );

			/*if (c = cm.get('styleselect')) {
				if (n.className) {
					t._importClasses();
					c.select(n.className);
				} else
					c.select();
			}*/

			if (c = cm.get('formatselect')) {
				p = DOM.getParent(n, DOM.isBlock);

				if (p)
					c.select(p.nodeName.toLowerCase());
			}

			/*if (c = cm.get('fontselect'))
				c.select(ed.queryCommandValue('FontName'));

			if (c = cm.get('fontsizeselect'))
				c.select(ed.queryCommandValue('FontSize'));*/

			if (s.theme_advanced_path && s.theme_advanced_statusbar_location) {
				p = DOM.get(ed.id + '_path') || DOM.add(ed.id + '_path_row', 'span', {id : ed.id + '_path'});
				DOM.setHTML(p, '');

				ed.dom.getParent(n, function(n) {
					var na = n.nodeName.toLowerCase(), u, pi, ti = '';

					// Ignore non element and hidden elements
					if (n.nodeType !== 1 || (DOM.hasClass(n, 'mceItemHidden') || DOM.hasClass(n, 'mceItemRemoved')))
						return;

                    if ( v = t.tagsToXml( n ) )
                        na = v;

					// Fake name
					if (v = DOM.getAttrib(n, 'mce_name'))
						na = v;

					// Handle prefix
					if (tinymce.isIE && n.scopeName !== 'HTML')
						na = n.scopeName + ':' + na;

					// Remove internal prefix
					na = na.replace(/mce\:/g, '');

					// Handle node name
					switch (na)
					{
                        case 'tbody':
                            return false;
						case 'embed':
						case 'embed-inline':
							if (v = DOM.getAttrib(n, 'src'))
								ti += 'src: ' + v + ' ';

							break;
						case 'anchor':
						case 'link':
							if (v = DOM.getAttrib(n, 'name')) {
								ti += 'name: ' + v + ' ';
								na += '#' + v;
							}

							if (v = DOM.getAttrib(n, 'href'))
								ti += 'href: ' + v + ' ';

							break;
						case 'custom':
							if (v = DOM.getAttrib(n, 'style'))
								ti += 'style: ' + v + ' ';
						    if ( n.nodeName === 'U' && n.className === '' )
						        n.className = 'underline';

							break;
                        case 'header':
                            if (v = n.nodeName)
                                na += ' ' + v[1];

                            break;
					}

					if (v = DOM.getAttrib(n, 'id'))
						ti = ti + 'id: ' + v + ' ';

					if (v = n.className) {
						v = v.replace(/(webkit-[\w\-]+|Apple-[\w\-]+|mceItem\w+|mceVisualAid|mceNonEditable)/g, '');

						if ( v = ez.string.trim( v ) )
						{
                            ti = ti + 'class: ' + v + ' ';
							//if (na === 'embed' || na === 'custom' || DOM.isBlock(n))
                            na = na + '.' + v.replace(' ', '.');
						}
					}

					na = na.replace(/(html:)/g, '');
					na = {name : na, node : n, title : ti};
					t.onResolveName.dispatch(t, na);
					ti = na.title;
					na = na.name;

					//u = "javascript:tinymce.EditorManager.get('" + ed.id + "').theme._sel('" + (de++) + "');";
					pi = DOM.create('a', {'href' : "Javascript:void(0);", onmousedown : "return false;", title : ti, 'class' : 'mcePath_' + (de++)}, na);
					Event.add(pi, 'click', ez.fn.bind(t.pickTagCommand, t, ed, n, na, v));

					if (p.hasChildNodes()) {
						p.insertBefore(document.createTextNode(' \u00bb '), p.firstChild);
						p.insertBefore(pi, p.firstChild);
					} else
						p.appendChild(pi);
				}, ed.getBody());
			}
		},
		
		simpleTagsToXmlHash:
		{
		    'P' : 'paragraph',
		    'I' : 'emphasize',
		    'EM': 'emphasize',
		    'B' : 'strong',
		    'PRE': 'literal',
		    'SPAN': 'custom',
		    'U': 'custom',
		    'H1': 'header',
		    'H2': 'header',
		    'H3': 'header',
		    'H4': 'header',
		    'H5': 'header',
		    'H6': 'header',
		    'TABLE': 'table',
		    'TH': 'th',
		    'TD': 'td',
		    'TR': 'tr',
		    'UL': 'ul',
		    'OL': 'ol',
		    'LI': 'li'
		},
		
		tagsToXml : function( n )
		{
            if ( this.simpleTagsToXmlHash[ n.nodeName ] )
                return this.simpleTagsToXmlHash[ n.nodeName ];
            switch( n.nodeName )
            {
                case 'A':
                    return DOM.getAttrib(n, 'href') ? 'link' : 'anchor';
                    break;
                case 'DIV':
                case 'IMG':
                    return 'embed' + (DOM.getAttrib(n, 'inline') === 'true' ? '-inline' : '') ;
                    break;
            }
            return false;
		},
		
		pickTagCommand : function( ed, n, na, v )
		{
		    //ed.selection.select( n );
		    switch( n.nodeName )
		    {
		        case 'IMG':
		            ed.execCommand('mceImage', true, v);
		            break;
                case 'DIV':
                    ed.execCommand('mceObject', true, v);
                    break;
                case 'PRE':
                    ed.execCommand('mceLiteral', true, v);
                    break;
                case 'U':
                    v = 'underline';
                case 'SPAN':
                    ed.execCommand('mceCustom', true, v);
                    break;
                case 'TABLE':
                    ed.execCommand('mceInsertTable', true, v);
                    break;
                case 'TR':
                    ed.execCommand('mceTableRowProps', true, v);
                    break;
                case 'TD':
                case 'TH':
                    ed.execCommand('mceTableCellProps', true, v);
                    break;
		        default:
		            var tagName = this.tagsToXml( n );
                    if ( tagName ) this.generalXmlTagPopup( tagName );
		    }	    
		},
		
        generalXmlTagPopup : function( eurl, view, width, height  )
        {
            var ed = this.editor;
            if ( !view ) view = '/tags/';

            ed.windowManager.open({
                url : eZOeMCE['extension_url'] + view  + eZOeMCE['contentobject_id'] + '/' + eZOeMCE['contentobject_version'] + '/' + eurl,
                width : width || 400,
                height : height || 280,
                inline : true
            }, {
                theme_url : this.url
            });
        },

		// These commands gets called by execCommand

		_sel : function(v) {
			this.editor.execCommand('mceSelectNodeDepth', false, v);
		},

		_mceCharMap : function() {
			var ed = this.editor;

			ed.windowManager.open({
				url : tinymce.baseURL + '/themes/ez/charmap.htm',
				width : 550,
				height : 250,
				inline : true
			}, {
				theme_url : this.url
			});
		},

		_mceHelp : function() {
			var ed = this.editor;

			ed.windowManager.open({
				url : tinymce.baseURL + '/themes/ez/about.htm',
				width : 480,
				height : 380,
				inline : true
			}, {
				theme_url : this.url
			});
		},

		_mceColorPicker : function(u, v) {
			var ed = this.editor;

			v = v || {};

			ed.windowManager.open({
				url : tinymce.baseURL + '/themes/ez/color_picker.htm',
				width : 375 + parseInt(ed.getLang('ez.colorpicker_delta_width', 0)),
				height : 250 + parseInt(ed.getLang('ez.colorpicker_delta_height', 0)),
				close_previous : false,
				inline : true
			}, {
				input_color : v.color,
				func : v.func,
				theme_url : this.url
			});
		},

		_mceCodeEditor : function(ui, val) {
			var ed = this.editor;

			ed.windowManager.open({
				url : tinymce.baseURL + '/themes/ez/source_editor.htm',
				width : parseInt(ed.getParam("theme_advanced_source_editor_width", 720)),
				height : parseInt(ed.getParam("theme_advanced_source_editor_height", 580)),
				inline : true,
				resizable : true,
				maximizable : true
			}, {
				theme_url : this.url
			});
		},

		_mceImage : function(ui, val)
		{
			var ed = this.editor, e = ed.selection.getNode(), eurl = 'image/', type = '/upload/';
			
			// Internal image object like a flash placeholder
            if (ed.dom.getAttrib(e, 'class').indexOf('mceItem') !== -1)
                return;

            if (e !== null && e.nodeName === 'IMG')
            {
                type = '/relations/';
                eurl += e.id + '/' + e.getAttribute('inline') + '/' + e.alt;
            }
            this.generalXmlTagPopup( eurl, type, 480, 450 )
		},

        _mceObject : function(ui, val)
        {
            var ed = this.editor, e = ed.selection.getNode(), eurl = 'object/', type = '/upload/';

            while ( e !== null && e.nodeName !== undefined && e.nodeName !== 'BODY' )
            {
                if ( e.nodeName === 'DIV' && e.className.indexOf('mceNonEditable') !== -1 )
                {
                    type = '/relations/';
                    eurl += e.id + '/' + e.getAttribute('inline') + '/' + e.alt;
                    ed.selection.select( e );
                    break;
                }
                e = e.parentNode;
            }
            this.generalXmlTagPopup( eurl, type, 480, 450 );
        },
        
        _mcePageBreak : function( ui, val ) {
            var ed = this.editor;
            ed.execCommand('mceInsertContent', 0, '<span type="custom" class="pagebreak">&nbsp;</span>');
        },

        _mceInsertAnchor : function(ui, v)
        {
            this.generalXmlTagPopup( 'anchor' );
        },
        
        _mceCustom : function(ui, v)
        {
            this.generalXmlTagPopup( 'custom/' + v );
        },
        
        _mceLiteral : function(ui, v)
        {
            this.generalXmlTagPopup( 'literal' );
        },

		_mceLink : function(ui, v)
		{
			this.generalXmlTagPopup( 'link' );
		},

		_mceNewDocument : function() {
			var ed = this.editor;

			ed.windowManager.confirm('ez.newdocument', function(s) {
				if (s) ed.execCommand('mceSetContent', false, '');
			});
		},

		_mceForeColor : function() {
			var t = this;

			this._mceColorPicker(0, {
				func : function(co) {
					t.editor.execCommand('ForeColor', false, co);
				}
			});
		},

		_mceBackColor : function() {
			var t = this;

			this._mceColorPicker(0, {
				func : function(co) {
					t.editor.execCommand('HiliteColor', false, co);
				}
			});
		},

        _ufirst : function(s) {
            return s.substring(0, 1).toUpperCase() + s.substring(1);
        }
	});

	tinymce.ThemeManager.add('ez', tinymce.themes.eZTheme);
}());