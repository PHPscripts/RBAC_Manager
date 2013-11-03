// JavaScript for PHPMaker 9
// (C)2002-2012 e.World Technology Ltd.
// Global variables

var ewAddOptDialog;
var ewEmailDialog;
var ewTooltipDiv;
var $rowindex$ = null;

// Global constants
var EW_TABLE_CLASSNAME = "ewTable";
var EW_GRID_CLASSNAME = "ewGrid";
var EW_TABLE_ROW_CLASSNAME = "ewTableRow";
var EW_TABLE_ALT_ROW_CLASSNAME = "ewTableAltRow";
var EW_ITEM_TEMPLATE_CLASSNAME = "ewTemplate";
var EW_ITEM_TABLE_CLASSNAME = "ewItemTable";
var EW_TABLE_LAST_ROW_CLASSNAME = "ewTableLastRow";
var EW_TABLE_LAST_COL_CLASSNAME = "ewTableLastCol";
var EW_TABLE_PREVIEW_ROW_CLASSNAME = "ewTablePreviewRow";
var EW_TABLE_EDIT_ROW_CLASSNAME = "ewTableEditRow";
var EW_TABLE_SELECT_ROW_CLASSNAME = "ewTableSelectRow";
var EW_TABLE_HIGHLIGHT_ROW_CLASSNAME = "ewTableHighlightRow";
var EW_REPORT_CONTAINER_ID = "ewContainer";
var EW_ROWTYPE_ADD = 2;
var EW_ROWTYPE_EDIT = 3;
var EW_UNFORMAT_YEAR = 50;

// Shortcuts
var ewEnv = YAHOO.env;
var ewWidget = YAHOO.widget;
var ewLang = YAHOO.lang;
var ewUtil = YAHOO.util;
var ewJson = ewLang.JSON;
var ewDom = ewUtil.Dom;
var ewEvent = ewUtil.Event;
var ewGet = ewUtil.Get;
var ewConnect = ewUtil.Connect;
var ewDate = ewUtil.Date; // Read http://developer.yahoo.com/yui/docs/ewUtil.Date.html
var ewNumber = ewUtil.Number; // Read http://developer.yahoo.com/yui/docs/YAHOO.util.Number.html
var ewSelect = jQuery.find;

// Custom events
var ewAddOptionEvent = new ewUtil.CustomEvent("AddOption"); // args[0].data is the new option to be validated
var ewNewOptionEvent = new ewUtil.CustomEvent("NewOption"); // args[0].data is the new option to be added
var ewAutoFillEvent = new ewUtil.CustomEvent("AutoFill"); // args[0].data is the value to fill
var ewRenderTemplateEvent = new ewUtil.CustomEvent("RenderTemplate"); // args[0].data is optional data for the template
var ewCreateEditorEvent = new ewUtil.CustomEvent("CreateEditor"); // args[0].id is the id of textarea to be replaced

// Subscribe custom event 
// Read: http://developer.yahoo.com/yui/event/#customevent
// Example:
//ewNewOptionEvent.subscribe(function(type, args) {
//	//alert(ewJson.stringify(args)); // View all arguments
//	var data = args[0].data; // Data
//	// your code to customize args[0].data, e.g.
//	if (args[0].id == "x_MyDateField") data[1] = ewDate.format(ew_StrToDate(data[1]), {format: "%d/%m/%Y"}); // Format the display value #1 as yyyy-mm-dd
//});

ewAddOptionEvent.subscribe(function(type, args) { // Default lookup filter event
	var row = args[0].data; // New row to be validated
	var arp = args[0].parents; // Parent field values
	for (var i = 0, cnt = arp.length; i < cnt; i++) { // Iterate parent values
		var p = arp[i];
		if (!p.length) // Empty parent

			//continue; // Allow
			return args[0].valid = false; // Disallow
		var val = row[5+i]; // Filter fields start from the 6th field
		if (!ewLang.isUndefined(val) && ew_InArray(val, p) < 0) // Filter field value not in parent field values
			return args[0].valid = false; // Returns false if invalid
	}
});

// Attach event by element id or name
function ew_On(el, sType, fn, obj, overrideContext) {
	if (ewLang.isString(el)) { // String
		if (!/^\w+(\[\])?$/.test(el)) { // Not element name => selector
			el = ew_Select(el);
		} else {
			el = ew_Select("[name='" + el + "'],[id='" + el + "']");
		}
	} 
	ewEvent.on(el, sType, fn, obj, overrideContext);
}

// Forms object
var ewForms = {};

// Select elements by selector
// Pass in a selector and an optional context (if no context is provided the root "document" is used). Runs the specified selector and returns an array of matched DOMElements.
function ew_Select(selector, context, fn) {
	var L = ewLang, root = L.isString(context) ? ewDom.get(context) : context;
	var els = ewSelect(selector, root);
	if (L.isFunction(fn)) {
		els = ewDom.batch(els, fn);
	} else if (L.isString(fn)) {
		els = ewDom.batch(els, new Function(fn));
	}
	return els;
}

// Filter elements by selector
// Takes in a set of DOMElements, filters them against the specified selector, and returns the results. The selector can be a full selector (e.g. "div > span.foo") and not just a fragment.
function ew_Matches(selector, set, fn) {
	var L = ewLang, els = ewSelect.matches(selector, set);
	if (L.isFunction(fn)) {
		els = ewDom.batch(els, fn);
	} else if (L.isString(fn)) {
		els = ewDom.batch(els, new Function(fn));
	}
	return els;
}

// Page class
function ew_Page(name) {
	this.Name = name;
	this.PageID = "";
}

// Form class
function ew_Form(formId) {	
	this.ID = formId; // Same ID as the form
	this.Form = document.getElementById(formId);

	// Search panel
	this.ShowHighlightText = ewLanguage.Phrase("ShowHighlight");
	this.HideHighlightText = ewLanguage.Phrase("HideHighlight");
	this.InitSearchPanel = false; // Expanded by default
	this.SearchPanel = formId + "_SearchPanel";
	this.SearchButton = formId + "_SearchImage";

	// Toggle search panel
	this.ToggleSearchPanel = function(expand) {
		var form = this.GetForm();
		var img = ew_GetElement(this.SearchButton, form);
		var p = ew_GetElement(this.SearchPanel, form);
		if (!p || !img)
			return;
		if (expand === true || expand !== false && p.style.display == "none") {
			p.style.display = "";
			if (ew_SameText(img.tagName, "IMG"))
				img.src = EW_IMAGE_FOLDER + "collapse.gif";
		} else {
			p.style.display = "none";
			if (ew_SameText(img.tagName, "IMG"))
				img.src = EW_IMAGE_FOLDER + "expand.gif";
		}
	}

	// Toggle highlight
	this.ToggleHighlight = function(lnk, name) {
		ew_Select("span[name=" + name + "]", document, function(el) {
			el.className = (el.className == "") ? "ewHighlightSearch" : "";
		});
		if (ewDom.hasClass(lnk, "ewHideHighlight")) {
			ewDom.replaceClass(lnk, "ewHideHighlight", "ewShowHighlight");
			if (lnk.value) {
				lnk.value = this.ShowHighlightText;
			} else if (lnk.innerHTML) {
				lnk.innerHTML = this.ShowHighlightText;
			}
		} else {
			ewDom.replaceClass(lnk, "ewShowHighlight", "ewHideHighlight");
			if (lnk.value) {
				lnk.value = this.HideHighlightText;
			} else if (lnk.innerHTML) {
				lnk.innerHTML = this.HideHighlightText;
			} 
		}
	}

	// Change search operator
	this.SrchOprChanged = function(el) {
		var form = this.GetForm();
		var elem = ewLang.isString(el) ? form.elements[el] : el;
		if (!elem)
			return;
		if (/^z_/.test(elem.id)) {
			form.elements["x_" + elem.id.substr(2)].disabled = ew_InArray(elem.options[elem.selectedIndex].value, ["IS NULL", "IS NOT NULL"]) > -1;
		} else if (/^w_/.test(elem.id)) {
			form.elements["y_" + elem.id.substr(2)].disabled = ew_InArray(elem.options[elem.selectedIndex].value, ["IS NULL", "IS NOT NULL"]) > -1;
		}
		var isBetween = (elem.options[elem.selectedIndex].value == "BETWEEN");
		ew_Select("span.btw0_" + elem.id.substr(2), form, function(node) {
			node.style.display = (isBetween) ? "none" : "";
		});
		ew_Select("span.btw1_" + elem.id.substr(2), form, function(node) {
			node.style.display = (isBetween) ? "" : "none";
			ew_Select(":input", node, function(obj) {
				obj.disabled = !isBetween;
			});
		});
	}

	// Validate
	this.ValidateRequired = true;
	this.Validate = null;

	// Disable form
	this.DisableForm = function() {
		if (!EW_DISABLE_BUTTON_ON_SUBMIT)
			return;
		ew_Matches(":submit", this.Form.elements, function(el) {
			el.disabled = true;
		});	
	}

	// Enable form
	this.EnableForm = function() {
		if (!EW_DISABLE_BUTTON_ON_SUBMIT)
			return;
		ew_Matches(":submit", this.Form.elements, function(el) {
			el.disabled = false;
		});
	}

	// Submit
	this.Submit = function(action) {
		var form = this.GetForm();
		this.DisableForm();
		if (typeof ew_UpdateTextArea == "function")
			ew_UpdateTextArea();
		if (!this.Validate || this.Validate(form)) {			
			if (action)
				form.action = action;
			ew_Matches("input[name^=s_],input[name^=sv_],input[name^=q_]", form.elements, function(el) {
				el.disabled = true; // Do not submit these values
			});
			form.submit();			
		}
		this.EnableForm();
		return false;
	}	

	// Check empty row
	this.EmptyRow = null;

	// Multi-page
	this.MultiPage = null;

	// Dynamic selection lists
	this.Lists = {};

	// AutoSuggests
	this.AutoSuggests = {};

	// DHTML editors
	this.Editors = [];

	// Get the HTML form object
	this.GetForm = function() {
		if (!this.Form) {			
			var el = document.getElementById(this.ID);
			if (el) {
				if (ew_SameText(el.tagName, "FORM")) { // HTML form
					this.Form = el;
				} else if (ew_SameText(el.tagName, "DIV")) { // DIV => Grid page
					this.Form = ewDom.getAncestorByTagName(el, "FORM");	
				}
			}
		}
		return this.Form;
	}

	// Get Auto-Suggest unmatched item (for form submission by pressing Return)
	this.PostAutoSuggest = function() {
		for (var i in this.AutoSuggests) {		
			var o = this.AutoSuggests[i];
			if (o && o.ac && o.ac.isFocused && o.ac.isFocused()) {
				o.input.blur();
				break;
			}
		}
	}

	// Update dynamic selection lists
	this.UpdateOpts = function(rowindex) {
		if (rowindex === $rowindex$) // null => return, undefined => update all
			return;		
		var L = ewLang, lists = [];
		var form = this.GetForm();
		for (var id in this.Lists) {					
			var parents = this.Lists[id].ParentFields.slice(0); // Clone
			var ajax = this.Lists[id].Ajax;
			if (L.isValue(rowindex)) {
				id = id.replace(/^x_/, "x" + rowindex + "_");
				for (var i = 0, len = parents.length; i < len; i++)						
					parents[i] = parents[i].replace(/^x_/, "x" + rowindex + "_");
			}				
			if (ajax) { // Ajax 
				var pvalues = [];
				for (var i = 0, len = parents.length; i < len; i++)						
					pvalues[pvalues.length] = ew_GetOptValues(parents[i], form); // Save the initial values of the parent lists	
				lists[lists.length] = [id, pvalues, true, false];
			} else { // Non-Ajax
				ew_UpdateOpt.call(this, id, parents, null, false);	
			}   	
		}

		// Update the Ajax lists
		for (var i = 0, cnt = lists.length; i < cnt; i++)
			ew_UpdateOpt.apply(this, lists[i]);
	}

	// Create editor(s)
	this.CreateEditor = function(name) {
		for (var i = 0, len = this.Editors.length; i < len; i++) {
			var ed = this.Editors[i];
			var create = !ed.active && ed.name.indexOf("$rowindex$") == -1 &&
				(!name || ed.name == ew_ConcatId(this.ID, name));
			if (!create)
				continue;	
			if (ewLang.isFunction(ed.create))
				ed.create();
			if (name)
				break;
		}
	}

	// Destroy editor(s)
	this.DestroyEditor = function(name) {
		for (var i = 0, len = this.Editors.length; i < len; i++) {
			var ed = this.Editors[i];
			var kill = (!name || ed.name == ew_ConcatId(this.ID, name));
			if (!kill)
				continue;	
			if (ewLang.isFunction(ed.destroy))
				ed.destroy();
			if (name)
				break;
		}
	}

	// Init form
	this.Init = function() {
		var form = this.GetForm();
		if (!form)
			return;

		// Check if Search panel
		var isSearch = /s(ea)?rch$/.test(this.ID);

		// Search panel
		if (isSearch && this.InitSearchPanel && !ew_HasFormData(form))
			this.ToggleSearchPanel();

		// Multi-page
		if (this.MultiPage) {
			this.MultiPage.Render(this.ID);
		} else { // DHTML editors
			ewLang.later(10, this, "CreateEditor"); // Create a little later (make sure HTML is ready)
		}

		// Dynamic selection lists
		this.UpdateOpts();

		// Search operators
		if (isSearch) { // Search form
			ew_Matches("select[id^=z_]", form.elements, function(el) {
				if (el.onchange)
					el.onchange();
				if (this.options[this.selectedIndex].value != "BETWEEN") {
					ew_Matches("#w_" + this.id.substr(2), form.elements, function(el2) {
						if (el2.onchange)
							el2.onchange();
					});
				}
			});
		}
	}

	// Add to the global forms object
	ewForms[this.ID] = this;
}

// Check search form data
function ew_HasFormData(form) {
	var els = ew_Matches("[name^=x_][value!=''],[name^=y_][value!=''],[name='psearch'][value!='']", form.elements);
	for (var i = 0, len = els.length; i < len; i++) {
		var el = els[i];
		if (el.type == "checkbox" || el.type == "radio") {
			if (el.checked)
				return true;
		} else if (el.type == "select-one" || el.type == "select-multiple") {
			for (var j = 0, cnt = el.options.length; j < cnt; j++) {
				if (el.options[j].selected && el.options[j].value != "")
					return true;
			}
		} else if (el.type == "text" || el.type == "hidden" || el.type == "textarea") {
				return true;
		}
	}
	return false;
}

// Queue
function ew_Queue() {
	var L = ewLang;
	this.list = []; // Array of functions
	this.args = []; // Array of arguments

	// Add a function
	this.add = function(fn, args) {
		this.list[this.list.length] = fn;
		this.args[this.args.length] = args;
	}

	// Start
	this.start = function() {
		if (L.isFunction(this.onstart))
			this.onstart();
		this.next();
	}

	// Next
	this.next = function() {
		if (this.list.length == 0) {
			if (L.isFunction(this.onend))
				this.onend();
			return;
		}	
		var fn = this.list.shift();
		var args = this.args.shift();
		if (L.isFunction(fn))
			fn(args);
	}
}

// Update a dynamic selection list
// obj {HTMLElement|array[HTMLElement]|string|array[string]} target HTML element(s) or the id of the element(s) 
// parentId {array[string]|array[array]} parent field element names or data
// async {boolean|null} async(true) or sync(false) or non-Ajax(null)
// change {boolean} trigger onchange event
function ew_UpdateOpt(obj, parentId, async, change) {
	var L = ewLang, self = this, args = [];
	var exit = function() {
		if (self._queue)
			self._queue.next();
	};	
	if (!obj || obj.length == 0)
		return exit();
	var f = (this.Form) ? this.Form : (this.form) ? this.form : null;
	if (this.form && /^x\d+_/.test(this.id)) // Has row index => grid
		f = ewDom.getAncestorByClassName(this, "ewForm"); // Detail grid or HTML form
	if (!f)
		return exit();
	var frm = (this.Form) ? this : ewForms[f.id];
	if (!frm)
		return exit();	
	for (var i = 0, len = arguments.length; i < len; i++) // Copy the arguments
		args[i] = arguments[i];
	if (this.form && L.isArray(obj) && L.isString(obj[0])) { // Array of id (onchange/onclick event)
		var queue = this._queue = new ew_Queue();		
		for (var i = 0, len = obj.length; i < len; i++) {
			args[0] = obj[i];
			queue.add(function(a){ew_UpdateOpt.apply(self, a);}, args.slice(0));
		}
		var list = frm.Lists[this.id.replace(/^[xy]\d*_/, "x_")];
		if (list && list.AutoFill) // AutoFill
			queue.add(function(){ew_AutoFill(self);});
		return queue.start();
	}
	if (L.isString(obj))
		obj = ew_GetElements(obj, f);
	var ar = ew_GetOptValues(obj);
	var oid = ew_GetId(obj, false);
	if (!oid)
		return exit();
	var nid = oid.replace(/^([xy])(\d*)_/, "x_");
	var prefix = RegExp.$1;	
	var rowindex = RegExp.$2;
	var arp = [];
	if (L.isUndefined(parentId)) { // Parent IDs not specified, use default
		parentId = frm.Lists[nid].ParentFields.slice(0); // Clone
		if (rowindex != "") {
			for (var i = 0, len = parentId.length; i < len; i++)
				parentId[i] = parentId[i].replace(/^x_/, "x" + rowindex + "_");
		} else if (prefix == "y") {

//			for (var i = 0, len = parentId.length; i < len; i++) {
//				var yid = parentId[i].replace(/^x_/, "y_");
//				var yobj = ew_GetElements(yid, f);
//				if (yobj.type || yobj.length > 0) // Has y_* parent
//					parentId[i] = yid; // Changes with y_* parent
//			}

		}
	}
	if (L.isArray(parentId) && parentId.length > 0) {
		if (L.isArray(parentId[0])) { // Array of array => data
			arp = parentId;
		} else if (L.isString(parentId[0])) { // Array of string => Parent IDs
			for (var i = 0, len = parentId.length; i < len; i++)
				arp[arp.length] = ew_GetOptValues(parentId[i], f);				
		}
	}
	if (!ew_IsAutoSuggest(obj)) // Do not clear Auto-Suggest
		ew_ClearOpt(obj);
	var addOpt = function(aResults) {
		for (var i = 0, cnt = aResults.length; i < cnt; i++) {
			var args = {data: aResults[i], parents: arp, valid: true, id: ew_GetId(obj), form: f};
			ewAddOptionEvent.fire(args);
			if (args.valid)
				ew_NewOpt(obj, aResults[i], f);
		}
		if (!obj.options && obj.length) { // Radio/Checkbox list
			ew_RenderOpt(obj, f);
			obj = ew_GetElements(oid, f); // Update the list
		}
		ew_SelectOpt(obj, ar);
		if (change !== false) {
			if (L.isFunction(obj.onchange)) {
				obj.onchange();					
			} else if (obj.length && obj.length > 0) { // Radio/Checkbox list
				var el = obj[0];
				if (L.isFunction(el.onclick))
					el.onclick();
			}
		}			
	}
	if (L.isUndefined(async)) // Async not specified, use default
		async = frm.Lists[nid].Ajax;
	if (!L.isBoolean(async)) { // Non-Ajax
		var ds = frm.Lists[nid].Options;
		addOpt(ds);
		if (/s(ea)?rch$/.test(f.id) && prefix == "x") { // Search form
			args[0] = oid.replace(/^x_/, "y_");
			ew_UpdateOpt.apply(this, args); // Update the y_* element
		}
		return exit();
	} else { // Ajax		
		var s = ew_Select("#s_" + ew_GetId(obj), f)[0];
		if (!s || s.value == "")
			return exit();
		var cb = {			
			success: function(oResponse) {
				var aResults = ew_ParseResponse(oResponse.responseText);
				addOpt(aResults || []);
				if (this._queue)
					this._queue.next();			
			},
			failure: function(oResponse) {
				if (this._queue)
					this._queue.next();
			},		
			scope: this, argument: null
		};
		var data = s.value;
		if (ew_IsAutoSuggest(obj) && this.Form) // Auto-Suggest (init form or auto-fill)
			data += "&v0=" + encodeURIComponent(ar[0]); // Filter by the current value
		for (var i = 0, cnt = arp.length; i < cnt; i++) // Filter by parent fields
			data += "&v" + (i+1) + "=" + encodeURIComponent(arp[i].join(","));
		ewConnect.asyncRequest("post", EW_LOOKUP_FILE_NAME, cb, data);
		if (/s(ea)?rch$/.test(f.id) && prefix == "x") { // Search form
			args[0] = oid.replace(/^x_/, "y_");
			ew_UpdateOpt.apply(this, args); // Update the y_* element
		}		
	}	
}

// Parse responseText
function ew_ParseResponse(txt, one) {
	var aResults;
	txt = txt.replace(/^\s*|\s*$/g, ""); // Trim
	if (txt.length > 0) {          
		var newLength = txt.length - EW_RECORD_DELIMITER.length;
		if (txt.substr(newLength) == EW_RECORD_DELIMITER)
			txt = txt.substr(0, newLength);
		aResults = [];					
		var aRecords = txt.split(EW_RECORD_DELIMITER);					
		for (var n = aRecords.length - 1; n >= 0; n--) {
			var record = aRecords[n];
			var newLength = record.length - EW_FIELD_DELIMITER.length;
			if (record.substr(newLength) == EW_FIELD_DELIMITER)
				record = record.substr(0, newLength);
			aResults[n] = record.split(EW_FIELD_DELIMITER);
		}

		// Check if single row or single value
		if (one && aResults.length == 1) { // Single row
			aResults = aResults[0];
			if (ewLang.isArray(aResults) && aResults.length == 1) { // Single column
				return aResults[0]; // Return a value
			} else {
				return aResults; // Return a row
			}	
		}
	}
	return aResults;
}

// ew_Language class
function ew_Language(obj) {
	this.obj = obj;
	this.Phrase = function(id) {
		return this.obj[id.toLowerCase()];
	};
}

// Include another client script
function ew_ClientScriptInclude(path, opts) {
	ewGet.script(path, opts);
}

// Apply client side template to a DIV
function ew_ApplyTemplate(divId, tmplId) {
	var tmpl = document.getElementById(tmplId);
	if (!window.jQuery || !jQuery.views || !tmpl)
		return;
	if (!tmpl.type) // Not script
		tmpl.type = "text/html";
	var args = {data: {}, id: divId, template: tmplId, enabled: true};
	ewRenderTemplateEvent.fire(args);
	if (args.enabled)
		jQuery("#" + divId).html(jQuery(tmpl).render(args.data));
}

// Render client side template and return the rendered HTML
function ew_RenderTemplate(tmplId) {
	var tmpl = document.getElementById(tmplId);
	if (!window.jQuery || !jQuery.views || !tmpl)
		return "";
	if (!tmpl.type) // Not script
		tmpl.type = "text/html";
	var args = {data: {}, template: tmplId};
	ewRenderTemplateEvent.fire(args);
	return jQuery(tmpl).render(args.data);
}

// Show template
function ew_ShowTemplates(classname) {
	ew_Select("script" + ((classname) ? "." + classname : "") + "[type='text/html']", document, function(scr) {
		if (/^\s*(<td[\s\S]*>[\s\S]*<\/td>)\s*$/i.test(scr.innerHTML)) { // Table cells
			var tbl = jQuery("<table><tr>" + RegExp.$1 + "</tr></table>")[0];
			var s = ewDom.getNextSibling(scr);
			if (tbl && s)
				ew_Select("tr:first > td", tbl, function(c) {ewDom.insertBefore(c, s);});
		} else {
			var sp = document.createElement("SPAN");
			sp.className = scr.className;
			sp.innerHTML = scr.innerHTML;
			ewDom.insertAfter(sp, scr);
		}
		var tbl = ewDom.getAncestorByClassName(scr, /ewTable|ewCssTableRow/);
		if (tbl && tbl.style.display == "none")
			tbl.style.display = "";
		var tbl = ewDom.getAncestorByClassName(scr, "ewGrid");
		if (tbl && tbl.style.display == "none")
			tbl.style.display = "";
	});
}

// Check if boolean value is true
function ew_ConvertToBool(value) {
	return ew_InArray(value.toLowerCase(), ["1", "y", "t"]) > -1;
}

// Check if element value changed
function ew_ValueChanged(fobj, infix, fld, bool) {
	var nelm = ew_GetElements("x" + infix + "_" + fld, fobj);
	var oelm = fobj.elements["o" + infix + "_" + fld]; // Hidden element
	var foelm = fobj.elements["fo" + infix + "_" + fld]; // Hidden element
	if (!oelm && (!nelm || ewLang.isArray(nelm) && nelm.length == 0))
		return false;
	var getvalue = function(obj) {
		return ew_GetOptValues(obj).join(",");	
	}		
	if (oelm && nelm) {
		if (bool) {
			if (ew_ConvertToBool(getvalue(oelm)) === ew_ConvertToBool(getvalue(nelm)))
				return false;
		} else {
			if (foelm) {
				if (getvalue(foelm) == getvalue(nelm))
					return false;
			} else {
				if (getvalue(oelm) == getvalue(nelm))
					return false;
			}
		}
	}
	return true;
}

// DHTML editor
function ew_Editor(name, createfn, destroyfn) {
	this.name = name;
	this.create = createfn; // Function to create editor and set this.active = true
	this.destroy = destroyfn; // Function to destroy editor
	this.active = false;
}

// Long form element name (for use with DHTML editor)
function ew_ConcatId(formid, id) {
	return formid + "$" + id + "$";
}

// Read Only Text Area
function ew_ReadOnlyTextArea(ta, w, h) {
	if (!ta || !ta.parentNode)
		return;
	ta.readOnly = true;
	ta.style.display = "none";
	var div = document.createElement("DIV");
	div.className = "ewReadOnlyTextArea";
	ta.parentNode.appendChild(div);
	var divdata = document.createElement("DIV");
	divdata.className = "ewReadOnlyTextAreaData";
	divdata.innerHTML = ta.value;
	div.appendChild(divdata);
	return new ewUtil.Resize(div, {width: w, height: h});
}

// Submit language form
function ew_SubmitLanguageForm(f) {
	if (!f || !f.language || !f.language.value)
		return;
	var url = window.location.href;
	if (window.location.search) {
		var query = window.location.search;
		var param = {};			
		query.replace(/(?:\?|&)([^&=]*)=?([^&]*)/g, function ($0, $1, $2) {
			if ($1)
				param[$1] = $2;
		});
		param["language"] = encodeURIComponent(f.language.value);
		var q = "?";
		for (var i in param)
			q += i + "=" + param[i] + "&";
		q = q.substr(0, q.length-1);
		var p = url.lastIndexOf(window.location.search);
		url = url.substr(0, p) + q;			
	} else {
		url += "?language=" + encodeURIComponent(f.language.value);
	}
	window.location = url;
}

// Submit selected records for update/delete
function ew_SubmitSelected(f, a, msg) {
	if (!f)
		return;
	if (!ew_KeySelected(f)) {
		alert(ewLanguage.Phrase("NoRecordSelected"));
	} else {
		if ((msg) ? ew_Confirm(msg) : true) {
			f.action = a;
			f.encoding = "application/x-www-form-urlencoded";
			f.enctype = "application/x-www-form-urlencoded";
			f.submit();
		}
	}
}

// Submit selected records for export
function ew_SubmitSelectedExport(f, a, val) {
	if (!f)
		return;
	if (!ew_KeySelected(f)) {
		alert(ewLanguage.Phrase("NoRecordSelected"));
	} else {
		if (f.exporttype && val != "")
			f.exporttype.value = val;
		f.action = a;
		f.encoding = "application/x-www-form-urlencoded";
		f.enctype = "application/x-www-form-urlencoded";
		f.submit();
	}
}

// Remove spaces
function ew_RemoveSpaces(value) {
	var str = value.replace(/\s/g, "").toLowerCase();
	if (ew_InArray(str, ["", "<p/>", "<p>", "<br/>", "<br>", "&nbsp;", "<p>&nbsp;</p>"]) > -1)
		return ""
	else
		return value;
}

// Check if hidden text area (DHTML editor)
function ew_IsHiddenTextArea(el) {
	return (el && el.type && el.type == "textarea" &&
		el.style && el.style.display == "none");
}

// Check if hidden textbox (Auto-Suggest)
function ew_IsAutoSuggest(el) {
	return (el && el.type && el.type == "hidden" &&
		el.form && el.id && el.id in ewForms[el.form.id].AutoSuggests);
}

// Get AutoSuggest instance
function ew_GetAutoSuggest(el) {
	return ewForms[el.form.id].AutoSuggests[el.id];
}

// Set focus
function ew_SetFocus(obj) {
	if (!obj)
		return;
	if (ew_IsHiddenTextArea(obj) && typeof ew_FocusEditor == "function") { // DHTML editor
		ew_FocusEditor(ew_ConcatId(obj.form.id, obj.id));
		return;
	} else if (!obj.options && obj.length) { // Radio/Checkbox list 	
		obj = ew_Matches("[value!='{value}']", obj)[0];
	} else if (ew_IsAutoSuggest(obj)) { // Auto-Suggest
		obj = ew_GetAutoSuggest(obj).input; 
		}	
	if (obj.focus)
		obj.focus();
	if (obj.select)
		obj.select();
}

// Show error message
function ew_OnError(frm, el, msg) {
	alert(msg); 
	if (frm && frm.MultiPage) { // Check if multi-page
		frm.MultiPage.GotoPageByElement(el);
		ewLang.later(200, this, "ew_SetFocus", el); // Focus a litter later to make sure DHTML editors are created
	} else {
		ew_SetFocus(el);
	}
	return false;
}

// Check if object has value
function ew_HasValue(obj) {
	return ew_GetOptValues(obj).join("") != "";
}

// Get Ctrl key for multiple column sort
function ew_Sort(e, url, type) {
	var newUrl = url
	if (type == 2 && e.ctrlKey)
		newUrl +=	"&ctrl=1";
	location = newUrl;
	return true;
}

// Confirm message
function ew_Confirm(msg) {
	return confirm(msg);
}

// Confirm Delete Message
function ew_ConfirmDelete(msg, el) {
	var del = confirm(msg);
	if (!del)
		ew_ClearDelete(el); // Clear delete status
	return del;
}

// Check if any key selected // PHP
function ew_KeySelected(f) {
	return ew_Select(":checkbox[name='key_m[]']:checked", f).length > 0;
}

// Select all key
function ew_SelectAllKey(cb) {
	ew_SelectAll(cb);
	var tbl = ewDom.getAncestorByClassName(cb, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;
	ewDom.batch(tbl.tBodies, function(tb) {
		ewDom.batch(tb.rows, function(r) {
			r.selected = cb.checked;
			r.checked = cb.checked;
			ew_SetColor(r);
		});
	});
}

// Select all related checkboxes
function ew_SelectAll(cb)	{
	ew_Matches(":checkbox[name^=" + cb.name + "_], :checkbox[name=" + cb.name + "]", (cb.form) ? cb.form.elements : [], function(c) {	
		if (c != cb)
			c.checked = cb.checked;
	});
}

// Update selected checkbox
function ew_UpdateSelected(f) {
	return ew_Select(":checkbox[name^=u_]:checked", f).length > 0;
}

// Add class to table row
function ew_AddClass(row, classname) {
	if (!row._bgcolor)
		row._bgcolor = ewDom.getStyle(row, "backgroundColor");
	if (!row._color)
		row._color = ewDom.getStyle(row, "color");
	ewDom.setStyle(row, "backgroundColor", "");
	ewDom.setStyle(row, "color", "");
	ewDom.addClass(row, classname);
}

// Remove class from table row
function ew_RemoveClass(row, classname) {
	ewDom.removeClass(row, classname);
	if (row._bgcolor)
		ewDom.setStyle(row, "backgroundColor", row._bgcolor);
	if (row._color)
		ewDom.setStyle(row, "color", row._color);
}

// Appy function to sibling rows
function ew_UpdateRow(row, fn) {
	if (!row || !ewLang.isFunction(fn))
		return;
	var index;
	if (index = row.getAttribute("data-rowindex")) {
		return ew_Matches("[data-rowindex='" + index + "']", row.parentNode.rows, fn);
	} else {
		return fn(row);
	}
}

// Set mouse over color
function ew_MouseOver(e) {
	if (!this.selected) {
		var tbl = ewDom.getAncestorByClassName(this, EW_TABLE_CLASSNAME);
		if (!tbl)
			return;
		ew_UpdateRow(this, function(r) {
			ew_AddClass(r, tbl.getAttribute("data-rowhighlightclass") || EW_TABLE_HIGHLIGHT_ROW_CLASSNAME);
		});
	}
}

// Set mouse out color
function ew_MouseOut(e) {
	if (!this.selected)
		ew_UpdateRow(this, ew_SetColor);
}

// Set selected row color
function ew_Click(e) {
	var tbl = ewDom.getAncestorByClassName(this, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;	
	if (!this.checked) {
		var selected = this.selected;
		ew_ClearSelected(tbl); // Clear all other selected rows		
		ew_UpdateRow(this, function(r) {
			r.selected = !selected; // Toggle
			ew_SetColor(r);
		});
	}
}

// Set row color
function ew_SetColor(row) {
	var tbl = ewDom.getAncestorByClassName(row, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;
	if (row.selected) {
		ew_RemoveClass(row, tbl.getAttribute("data-rowhighlightclass") || EW_TABLE_HIGHLIGHT_ROW_CLASSNAME);
		ew_RemoveClass(row, tbl.getAttribute("data-roweditclass") || EW_TABLE_EDIT_ROW_CLASSNAME);
		ew_AddClass(row, tbl.getAttribute("data-rowselectclass") || EW_TABLE_SELECT_ROW_CLASSNAME);
	} else if (row.edit) {
		ew_RemoveClass(row, tbl.getAttribute("data-rowselectclass") || EW_TABLE_SELECT_ROW_CLASSNAME);
		ew_RemoveClass(row, tbl.getAttribute("data-rowhighlightclass") || EW_TABLE_HIGHLIGHT_ROW_CLASSNAME);
		ew_AddClass(row, tbl.getAttribute("data-roweditclass") || EW_TABLE_EDIT_ROW_CLASSNAME);
	} else {
		ew_RemoveClass(row, tbl.getAttribute("data-rowselectclass") || EW_TABLE_SELECT_ROW_CLASSNAME);
		ew_RemoveClass(row, tbl.getAttribute("data-roweditclass") || EW_TABLE_EDIT_ROW_CLASSNAME);
		ew_RemoveClass(row, tbl.getAttribute("data-rowhighlightclass") || EW_TABLE_HIGHLIGHT_ROW_CLASSNAME);
	}
}

// Clear selected rows color
function ew_ClearSelected(tbl) {
	if (!tbl)
		return;
	ewDom.batch(tbl.rows, function(r) {	
		if (!r.checked && r.selected) {
			r.selected = false;
			ew_SetColor(r);
		}
	});	
}

// Clear all row delete status
function ew_ClearDelete(el) {
	var tbl = ewDom.getAncestorByClassName(el, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;
	var row = ewDom.getAncestorBy(el, function(tr) {return ew_SameText(tr.tagName, "TR") && tr.parentNode.parentNode == tbl;});
	ew_UpdateRow(row, function(r) {r.selected = r.checked;});
}

// Click single delete link
function ew_ClickDelete(el) {
	var tbl = ewDom.getAncestorByClassName(el, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;		
	ew_ClearSelected(tbl);
	var row = ewDom.getAncestorBy(el, function(tr) {return ew_SameText(tr.tagName, "TR") && tr.parentNode.parentNode == tbl;});
	ew_UpdateRow(row, function(r) {
		r.selected = true;
		ew_SetColor(r);
	});
}

// Click multiple checkbox
function ew_ClickMultiCheckbox(e, cb) {
	var tbl = ewDom.getAncestorByClassName(cb, EW_TABLE_CLASSNAME);
	if (!tbl)
		return;
	ew_ClearSelected(tbl);
	var row = ewDom.getAncestorBy(cb, function(tr) {return ew_SameText(tr.tagName, "TR") && tr.parentNode.parentNode == tbl;});
	ew_UpdateRow(row, function(r) {
		ew_Select(":checkbox[name='key_m[]']", r, function(el) {if (el != cb) el.checked = cb.checked;});
		r.checked = cb.checked;
		r.selected = cb.checked;
		ew_SetColor(r);
	});
	ewEvent.stopPropagation(e);
}

// Setup table
function ew_SetupTable(tbl, force) {
	if (!tbl || !tbl.rows || !force && tbl.isset || tbl.tBodies.length == 0)
		return;
	if (ewEnv.ua.ie < 8)
		tbl.cellSpacing = "0";
	var n = ew_Matches("[data-rowindex=1]", tbl.rows).length || ew_Matches("[data-rowindex=0]", tbl.rows).length || 1; // Alternate color every n rows
	var removeClass = ewDom.removeClass, addClass = ewDom.addClass;
	var rows = ew_Matches(":not(." + EW_ITEM_TEMPLATE_CLASSNAME + ")", tbl.rows, function(r) {
		var cells = r.cells;
		if (cells.length) {
			addClass(cells[cells.length-1], EW_TABLE_LAST_COL_CLASSNAME); // Cell of last column
			removeClass(cells, EW_TABLE_LAST_ROW_CLASSNAME); // Cell of last row
		}
		return r;
	});
	if (rows.length) {
		for (var i = 1; i <= n; i++) {
			var r = rows[rows.length - i];
			for (var j = 0, len = r.cells.length; j < len; j++) {
				var c = r.cells[j];
				if (c.rowSpan == i) // Cell of last row
					addClass(c, EW_TABLE_LAST_ROW_CLASSNAME);
			}
		}
	}
	var form = ewDom.getAncestorByTagName(tbl, "FORM");
	var attach = form && ew_Matches("input#a_list:not([value^=grid])", form.elements).length > 0; 
	rows = ew_Matches(":not(." + EW_ITEM_TEMPLATE_CLASSNAME + "):not(." + EW_TABLE_PREVIEW_ROW_CLASSNAME + ")",
		tbl.tBodies[tbl.tBodies.length - 1].rows, function(r) {
			if (attach && !r.isset) {
				if (ew_InArray(r.getAttribute("data-rowtype"), [EW_ROWTYPE_ADD, EW_ROWTYPE_EDIT]) > -1) {
					ewEvent.on(r, "mouseover", function(e) {this.edit = true;});
					addClass(r, EW_TABLE_EDIT_ROW_CLASSNAME);
				}
				ewEvent.on(r, "mouseover", ew_MouseOver);
				ewEvent.on(r, "mouseout", ew_MouseOut);
				ewEvent.on(r, "click", ew_Click);
				r.isset = true;
			}
			return r;
		}); // Use last TBODY (avoid Opera bug)
	for (var i = 0, len = rows.length; i < len; i++) { // Loop the rows (avoid selector bug)
		var r = rows[i];
		if (i % (2 * n) < n) {
			addClass(r, EW_TABLE_ROW_CLASSNAME); // Row color
			removeClass(r, EW_TABLE_ALT_ROW_CLASSNAME);
		} else {
			addClass(r, EW_TABLE_ALT_ROW_CLASSNAME); // Alt row color
			removeClass(r, EW_TABLE_ROW_CLASSNAME);
		}
	}
	ew_SetupGrid(ewDom.getAncestorByClassName(tbl, EW_GRID_CLASSNAME), force);
	tbl.isset = true;
}

// Setup grid
function ew_SetupGrid(grid, force) {
	if (!grid || !force && grid.isset)
		return;
	var rowcnt = ew_Select("table." + EW_TABLE_CLASSNAME + " > tbody:first > tr:not(." + EW_TABLE_PREVIEW_ROW_CLASSNAME + ", ." + EW_ITEM_TEMPLATE_CLASSNAME + ")", grid).length;
	var divupper = ew_Select("div.ewGridUpperPanel", grid)[0];
	var divmiddle = ew_Select("div.ewGridMiddlePanel", grid)[0];
	var divlower = ew_Select("div.ewGridLowerPanel", grid)[0]; 
	if (divupper && divlower) {
		if (rowcnt == 0) {
			ewDom.addClass(divlower, "ewDisplayNone");
			ewDom.addClass(divupper, "ewNoBorderBottom");
		} else {
			ewDom.removeClass(divlower, "ewDisplayNone");
			ewDom.removeClass(divupper, "ewNoBorderBottom");
		}
	} else if (divupper && !divlower) {
		if (rowcnt == 0) {
			ewDom.addClass(divupper, "ewNoBorderBottom");
		} else {
			ewDom.removeClass(divupper, "ewNoBorderBottom");
		}
	} else if (divlower && !divupper) {
		if (rowcnt == 0) {
			ewDom.addClass(divlower, "ewNoBorderTop");
		} else {
			ewDom.removeClass(divlower, "ewNoBorderTop");
		}
	}
	grid.isset = true;
}

// Add a row to grid
function ew_AddGridRow(el) {
	var grid = ewDom.getAncestorByClassName(el, EW_GRID_CLASSNAME);
	if (!el || !grid)
		return;
	var tbl = ew_Select("table." + EW_TABLE_CLASSNAME, grid)[0];
	if (!tbl)
		return;
	var tpl = ew_Select("tr." + EW_ITEM_TEMPLATE_CLASSNAME, tbl)[0];
	if (!tpl)
		return;
	var lastrow = tbl.rows[tbl.rows.length-1];
	ewDom.removeClass(lastrow, EW_TABLE_LAST_ROW_CLASSNAME);
	var row = tpl.cloneNode(true);
	ewDom.removeClass(row, EW_ITEM_TEMPLATE_CLASSNAME);
	var form = ew_Select("form.ewForm[id^=f][id$=list]", grid)[0] || ew_Select("div.ewForm[id^=f][id$=grid]", grid)[0];
	var elkeycnt = ew_GetElement("key_count", form);
	var keycnt = parseInt(elkeycnt.value) + 1;
	row.id = "r" + keycnt + row.id.substring(2);
	row.setAttribute("data-rowindex", keycnt);
	var els = ewDom.getElementsBy(function(node) { // Get scripts with rowindex
		return (node.text.indexOf("$rowindex$") > -1)	
		}, "SCRIPT", tbl); // Script tags are under the table node
	ewDom.insertAfter(row, lastrow); // Insert first (for IE <=7)
	ewDom.batch(row.cells, function(cell) {
		var html = cell.innerHTML;
		html = html.replace(/\$rowindex\$/g, keycnt); // Replace row index
		cell.innerHTML = html;
	});
	ewDom.getElementsBy(function(node) { // Process the scripts in the row (not in cell)
		if (node.text.indexOf("$rowindex$") > -1)
			node.text = node.text.replace(/\$rowindex\$/g, keycnt); // Replace row index
		}, "SCRIPT", row);
	elkeycnt.value = keycnt;
	var keyact = document.createElement("INPUT");
	keyact.type = "hidden";
	keyact.id = "k" + keycnt + "_action";
	keyact.name = keyact.id;
	keyact.value = "insert";
	ewDom.insertAfter(keyact, elkeycnt);
	ew_Select("select[name*='$rowindex$']", tpl, function(node) { // Copy selected options for the selection lists (browsers do not clone selected options)
		var sel = node.form.elements[node.name.replace(/\$rowindex\$/g, keycnt)]; // Replace row index
		ew_SelectOpt(sel, ew_GetOptValues(node));
	});
	ewDom.batch(els, function(node) {
		var scr = ew_AddScript(node.text.replace(/\$rowindex\$/g, keycnt));
		ewForms[form.id].CreateEditor();
	});	
	ew_SetupTable(tbl, true);
}

// Delete a row from grid
function ew_DeleteGridRow(el, infix) {
	var row = ewDom.getAncestorByTagName(el, "TR");
	var tbl = ewDom.getAncestorByClassName(row, EW_TABLE_CLASSNAME);
	if (!el || !row || !tbl)
		return;
	var rowidx = parseInt(row.getAttribute("data-rowindex"));
	var c = true;
	var f = ewDom.getAncestorByTagName(el, "FORM");
	if (!f)
		return;
	var frm = ewForms[f.id];
	if (!frm)
		return;
	if (ewLang.isFunction(frm.EmptyRow))		
		c = !frm.EmptyRow(infix);
	if (c && !confirm(ewLanguage.Phrase('DeleteConfirmMsg')))
			return;
	tbl.deleteRow(row.rowIndex);
	ew_SetupTable(tbl, true);
	if (rowidx > 0) {
		var keyact = ew_GetElement("k" + rowidx + "_action", f);
		if (keyact) {
			if (keyact.value == "insert")
				keyact.value = "insertdelete";
			else
				keyact.value = "delete";
		} else {
			var elkeycnt = ew_GetElement("key_count", f);
			var keyact = document.createElement("INPUT");
			keyact.type = "hidden";
			keyact.id = "k" + rowidx + "_action";
			keyact.name = keyact.id;
			keyact.value = "delete";
			ewDom.insertAfter(keyact, elkeycnt);
		}
		return true;
	}
	return false;
}

// HTML encode text
function ew_HtmlEncode(text) {
	var str = String(text);
	str = str.replace(/&/g, '&amp');
	str = str.replace(/\"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;'); 
	return str;
}

// Clear search form
function ew_ClearForm(form){
	ew_Matches("[id^=x_],[id^=y_]", form.elements, function(el) {
		if (el.type == "checkbox" || el.type == "radio") {
			el.checked = false;
		} else if (el.type == "select-one") {
			el.selectedIndex = 0;
		} else if (el.type == "select-multiple") {
			for (var i = 0, len = el.options.length; i < len; i++)
				el.options[i].selected = false;
		} else if (el.type == "text" || el.type == "textarea") {
			el.value = "";
		}
	});
}

// Multi-Page
function ew_MultiPage(formid) {
	this.FormID = formid;
	this.PageIndex = 1;
	this.MaxPageIndex = 0;
	this.MinPageIndex = 0;
	this.Elements = [];
	this.TabView = null;
	this.SubmitButton = null;
	this.LastPageSubmit = false;
	this.HideDisabledButton = true;

	// Init
	this.Init = function() {
		for (var i = 0, len = this.Elements.length; i < len; i++) {
			var el = this.Elements[i]; 		
			if (el[1] > this.MaxPageIndex)
				this.MaxPageIndex = el[1]; 
		}	
		this.MinPageIndex = this.MaxPageIndex;
		for (var i = 0, len = this.Elements.length; i < len; i++) {
			var el = this.Elements[i];	
			if (el[1] < this.MinPageIndex)
				this.MinPageIndex = el[1];  
		}
	}

	// Show page	
	this.ShowPage = function() {
		for (var i = 0, len = this.Elements.length; i < len; i++) {
			var el = this.Elements[i];
			if (el[1] == this.PageIndex)
				ewForms[this.FormID].CreateEditor(el[0]);
		}
		this.EnableButtons();
	}

	// Enable buttons
	this.EnableButtons = function() {
		if (this.SubmitButton) {
			this.SubmitButton.disabled = (this.LastPageSubmit) ? (this.PageIndex != this.MaxPageIndex) : false;
			if (this.SubmitButton.disabled) {
				this.SubmitButton.style.display = (this.HideDisabledButton) ? "none" : "";
			} else {
				this.SubmitButton.style.display = "";	
			}
		}
	}

	// Get page index by element ID
	this.GetPageIndexByElementId = function(elemid) {
		for (var i = 0, len = this.Elements.length; i < len; i++) {
			if (this.Elements[i][0] == elemid)
				return this.Elements[i][1];
		}
		return -1;
	}

	// Goto page by index
	this.GotoPageByIndex = function(pageIndex) {
		if (pageIndex < this.MinPageIndex || pageIndex > this.MaxPageIndex)
			return; 
		this.PageIndex = pageIndex;
		this.ShowPage();
		if (this.TabView)
			this.TabView.set("activeIndex", pageIndex - 1);
	}

	// Goto page by element
	this.GotoPageByElement = function(elem) {
		if (!elem)
			return;
		var id = (!elem.type && elem[0]) ? elem[0].id : elem.id;
		if (id == "")
			return;	
			var pageIndex = this.GetPageIndexByElementId(id);
		if (pageIndex > 0) {
			this.GotoPageByIndex(pageIndex);
		}		
	}

	// Render 
	this.Render = function(id) {
		var tv = this.TabView = new ewWidget.TabView(id);
		var mp = this;
		tv.subscribe("activeTabChange", function(e) {
			var i = tv.getTabIndex(e.newValue) + 1;
			mp.GotoPageByIndex(i);
		});
		tv.subscribe("contentReady", function(e) {
			mp.Init(); // Multi-page initialization
			var div = document.getElementById(id);
			mp.SubmitButton = ew_GetElement("btnAction", div);
			var i = tv.get("activeIndex") + 1;
			mp.GotoPageByIndex(i);
		});
	}
}

// Get element as element or radio/checkbox list as array
function ew_GetElements(name, root) {
	var ar = ewDom.getElementsBy(function(node){
		return ew_SameStr(node.id || node.name, name); // Exclude template element
	}, null, root);
	if (ar.length == 1) {
		var el = ar[0];
		if (el.type && el.type != "checkbox" && el.type != "radio") 
			return el;
	}	
	return ar;
}

// Get first element only
function ew_GetElement(name, root) {
	return ewDom.getElementBy(function(node){
		return ew_SameStr(node.id || node.name, name);
	}, null, root);
}

// Check if same text
function ew_SameText(o1, o2) {
	return (String(o1).toLowerCase() == String(o2).toLowerCase());
}

// Check if same string
function ew_SameStr(o1, o2) {
	return (String(o1) == String(o2));
}

// Check if starts with some string
//function ew_StartsWith(str, prefix) {
//	return (str.substr(0, prefix.length) == prefix);
//}
// Check if ends with some string
//function ew_EndsWith(str, suffix) {
//	return (str.substr(str.length - suffix.length) == suffix);
//}
// Check if an element is in array
function ew_InArray(el, ar) {
	if (!ar)
		return -1;	
	for (var i = 0, len = ar.length; i < len; i++) {
		if (ew_SameStr(ar[i], el))
			return i;
	}		
	return -1;
}

// Render repeat column table (rowcnt is zero based row count)
function ew_RepeatColumnTable(totcnt, rowcnt, repeatcnt, rendertype) {
	var sWrk = "";
	if (rendertype == 1) { // Render start
		if (rowcnt == 0)
			sWrk += "<table class=\"" + EW_ITEM_TABLE_CLASSNAME + "\">";
		if (rowcnt % repeatcnt == 0)
			sWrk += "<tr>";
		sWrk += "<td>";
	} else if (rendertype == 2) { // Render end
		sWrk += "</td>";
		if (rowcnt % repeatcnt == repeatcnt - 1) {
			sWrk += "</tr>";
		} else if (rowcnt == totcnt - 1) {
			for (i = (rowcnt % repeatcnt) + 1; i < repeatcnt; i++)
				sWrk += "<td>&nbsp;</td>";
			sWrk += "</tr>";
		}
		if (rowcnt == totcnt - 1) sWrk += "</table>";
	}
	return sWrk;
}

// Get existing selected values as an array
function ew_GetOptValues(el, form) {
	var obj = (ewLang.isString(el)) ? ew_GetElements(el, form) : el;
	if (obj.options) { // Selection list
		return ew_Matches(":selected[value!='']", obj.options, function(opt) {
			return opt.value;
		});
	} else if (ewLang.isNumber(obj.length)) { // Radio/Checkbox list, or element not found
		return ew_Matches(":checked[value!='{value}']", obj, function(el) {
			return el.value;
		});
	} else { // text/hidden
		return [obj.value];	
	}	
}

// Clear existing options
function ew_ClearOpt(obj) {
	if (obj.options) { // Selection list
		var lo = (obj.type == "select-multiple") ? 0 : 1;
		for (var i = obj.length - 1; i >= lo; i--)
			obj.options[i] = null;
	} else if (obj.length) { // Radio/Checkbox list
		if (!obj[0])
			return;
		var id = ew_GetId(obj); 
		var p = ew_GetElement("dsl_" + id, obj[0].form);
		if (!p)
			return;
		var tbl = ew_Select("table." + EW_ITEM_TABLE_CLASSNAME, p)[0];
		if (tbl)
			p.removeChild(tbl);
		p._options = [];
	} else if (ew_IsAutoSuggest(obj)) {
		var o = ew_GetAutoSuggest(obj);
		o._options = [];
		o.input.value = "";
		obj.value = "";
	}
}

// Get the id or name of an element
// remove {boolean} remove square brackets, default: true
function ew_GetId(el, remove) {
	var id = "";
	if (ewLang.isString(el)) {
		id = el;
	} else {
		if (!el.options && el.length)
			el = el[0];
		id = (el) ? ((el.id || el.name) ? (el.id || el.name) : "") : "";
	}
	if (remove !== false && id.substr(id.length-2, 2) == "[]")
		id = id.substr(0, id.length-2); 	
	return id;
}

// Get display value separator
function ew_ValueSeparator(index, obj) {
	return ", ";
}

// Create combobox option 
function ew_NewOpt(obj, ar, f) {
	var args = {data: ar, id: ew_GetId(obj), form: f};
	ewNewOptionEvent.fire(args);
	ar = args.data;
	var value = ar[0];
	var text = ar[1];	
	for (var i = 2; i <= 4; i++) {
		if (ar[i] && ar[i] != "") {
			if (text != "")
				text += ew_ValueSeparator(i-1, obj);
			text += ar[i];
		}
	}
	if (obj.options) { // Selection list
		obj.options[obj.length] = new Option(text, value, false, false);
	} else if (obj.length) { // Radio/Checkbox list
		var p = ew_GetElement("dsl_" + ew_GetId(obj), f); // Parent element		
		if (p && p._options)
			p._options[p._options.length] = {val:value, lbl:text};
	} else if (ew_IsAutoSuggest(obj)) { // Auto-Suggest
		var o = ew_GetAutoSuggest(obj);
		o._options[o._options.length] = {val:value, lbl:text};
	}
	return text;
}

// Render the options
function ew_RenderOpt(obj, f) {
	var id = ew_GetId(obj); 
	var p = ew_GetElement("dsl_" + id, f); // Parent element	
	if (!p || !p._options)
		return;
	var t = ew_GetElement("tp_" + id, f); 	
	if (!t)
		return;
	var cols = parseInt(p.getAttribute("data-repeatcolumn"));
	if (isNaN(cols) || cols < 1)
		cols = 5;
	var tpl = t.innerHTML;		 
	var html = "";
	var ihtml;
	for (var i = 0, cnt = p._options.length; i < cnt; i++) {
		html += ew_RepeatColumnTable(cnt, i, cols, 1);
		ihtml = tpl;
		ihtml = ihtml.replace(/\"?{value}\"?/g, "\"" + ew_HtmlEncode(p._options[i].val) + "\""); // Replace value		
		html += "<label>" + ihtml + p._options[i].lbl + "</label>";		
		html += ew_RepeatColumnTable(cnt, i, cols, 2);		
	} 
	p.innerHTML += html;
	p._options = [];		
}

// Select combobox option
function ew_SelectOpt(obj, value_array) {
	if (!obj || !value_array)
		return;
	if (obj.options) { // Selection List
		ew_Matches("*", obj.options, function(opt) { // ewDom.batch(obj.options, fn) does not work with IE
			opt.selected = (ew_InArray(opt.value, value_array) > -1);
		});
	} else if (obj.length) { // Radio/Checkbox list
		if (obj.length == 1 && obj[0].type == "checkbox" && obj[0].value != "{value}") { // Assume boolean field // P802
			obj[0].checked = (ew_ConvertToBool(obj[0].value) === ew_ConvertToBool(value_array[0]));
		} else {
			ewDom.batch(obj, function(el) {
				el.checked = (ew_InArray(el.value, value_array) > -1);
			});
		}

//	} else if (obj.type == "hidden") {
//		var asEl = ew_GetElement("sv_" + obj.id, obj.form);
//		if (asEl && asEl.type == "text") {
//			obj.value = value_array.join(",");
//			asEl.value = value_array.join(",");
//		}

	} else if (ew_IsAutoSuggest(obj) && value_array.length == 1) {
		var o = ew_GetAutoSuggest(obj);
		for (var i = 0, len = o._options.length; i < len; i++) {
			if (o._options[i].val == value_array[0]) {
				obj.value = o._options[i].val;
				o.input.value = o._options[i].lbl;
				break;
			}
		}
	} else if (obj.type) {
		obj.value = value_array.join(",");
	}

	// Auto-select if only one option
	function isAutoSelect(el) {
		if (el.getAttribute("data-autoselect")) // Disabled
			return false;
		var form = ewDom.getAncestorByClassName(el, "ewForm");
		if (form) {
			if (/s(ea)?rch$/.test(form.id)) // Search forms
				return false;
			var nid = el.id.replace(/^([xy])(\d*)_/, "x_");
			if (nid in ewForms[form.id].Lists && ewForms[form.id].Lists[nid].ParentFields.length == 0) // No parent fields
				return false;
			return true;
		}
		return false;
	} 
	if (obj.options) { // Selection List
		if (obj.type == "select-one" && obj.options.length == 2 && !obj.options[1].selected && isAutoSelect(obj)) {
			obj.options[1].selected = true;
		} else if (obj.type == "select-multiple" && obj.options.length == 1 && !obj.options[0].selected && isAutoSelect(obj)) {
			obj.options[0].selected = true;
		}
	} else if (obj.length) { // Radio/Checkbox list
		if (obj.length == 2 && isAutoSelect(obj[1]))
			obj[1].checked = true;
	} else if (ew_IsAutoSuggest(obj)) {
		var o = ew_GetAutoSuggest(obj);
		if (o._options.length == 1 && isAutoSelect(obj)) {
			obj.value = o._options[0].val;
			o.input.value = o._options[0].lbl;
		}
	}
}

// Auto-Suggest
function ew_AutoSuggest(elValue, frm, forceSelection, maxEntries) {
	var nid = elValue.replace(/^[xy](\d*|\$rowindex\$)_/, "x_");
	var rowindex = RegExp.$1;
	var oEmpty = {ac:{},ds:{}}; // Empty Auto-Suggest object
	if (rowindex == "$rowindex$")
		return oEmpty;
	var form = frm.GetForm(); 
	var elInput = ew_GetElement("sv_" + elValue, form);
	if (!elInput)
		return oEmpty;
	var elContainer = ew_GetElement("sc_" + elValue, form);
	var elSQL = ew_GetElement("q_" + elValue, form);
	var elMessage = ew_GetElement("em_" + elValue, form);	
	var elParent = frm.Lists[nid].ParentFields.slice(0); // Clone
	for (var i = 0, len = elParent.length; i < len; i++)
		elParent[i] = elParent[i].replace(/^x_/, "x" + rowindex + "_");
	this.input = elInput;
	this.element = ew_GetElement(elValue, form);
	this._options = [];

	// Create DataSource
	this.ds = new ewUtil.XHRDataSource(EW_LOOKUP_FILE_NAME);
	this.ds.responseType = ewUtil.XHRDataSource.TYPE_TEXT;
	this.ds.responseSchema = {recordDelim: EW_RECORD_DELIMITER, fieldDelim: EW_FIELD_DELIMITER};
	this.ds.maxCacheEntries = 0; // DO NOT CHANGE!		
	this.ds.connMethodPost = true; 

	// Create AutoComplete
	this.ac = new ewWidget.AutoComplete(elInput, elContainer, this.ds);
	this.ac._as = this;
	this.ac.useShadow = false;
	this.ac.animVert = false;
	this.ac.minQueryLength = 1;
	this.ac.maxResultsDisplayed = maxEntries;
	this.ac.typeAhead = false; // 902
	this.ac.forceSelection = forceSelection;
	this.ac.useIFrame = (ewEnv.ua.ie > 0 && ewEnv.ua.ie < 8);

	// Override _focus method
	this.ac._focus = function() {
		var oSelf = this;
		setTimeout(function() {
			try {
				oSelf._elTextbox.focus();
			}	catch(e) {}
		}, 500); // Increase the delay time
	};	

	// Do before expand container
	this.ac.doBeforeExpandContainer = function(oTextbox, oContainer, sQuery, aResults) {
		var pos = ewDom.getXY(oTextbox);
		pos[1] += ewDom.get(oTextbox).offsetHeight + 1;
		ewDom.setXY(oContainer, pos);
		oContainer.style.width = ewDom.get(oTextbox).offsetWidth + "px"; // Set container width
		return true;
	};

	// Format display value (Note: Override this function if link field <> display field)
	this.formatResult = function(ar) {
		return ar[0];
	};

	// Set the key to the actual value field
	this.setValue = function(v) {
		var el = this.element;
		el.value = v;		
		if (el.onchange)					
			el.onchange();
	};

	// Format result
	this.ac.formatResult = function(oResultItem, sQuery) {	

		//var key = oResultItem[0];
		var lbl = this._as.formatResult(oResultItem);

		//oResultItem[0] = lbl;		
		//oResultItem.push(key); // Save the key to last

		return lbl;		
	};

	// Generate request
	this.ac.generateRequest = function(sQuery) {
		var data = elSQL.value;
		if (elParent.length > 0) {
			for (var i = 0, len = elParent.length; i < len; i++) {
				var arp = ew_GetOptValues(elParent[i], form);
				data += "&v" + (i+1) + "=" + encodeURIComponent(arp.join(","));
			}
		}
		return "q=" + sQuery + "&" + data; 
	};

	// Item selected
	this.itemSelect = function(ar) {
		this.setValue(ar[0]);
		this.input.value = this.formatResult(ar);
	}

	// Update the key to the actual value field
	this.ac.itemSelectEvent.subscribe(function(type, e) {
		this._as.itemSelect(e[2]);
	}); 

	// Remove styles for unmatched item
	this.ac.textboxFocusEvent.subscribe(function(type, e) {
		ewDom.removeClass(elInput, "ewUnmatched");
		ewDom.setStyle(elMessage, "display", "none");
	});

	// Clear the actual value field
	if (forceSelection) {
		this.ac.selectionEnforceEvent.subscribe(function(type, e) {
			this._as.setValue("");
			if (e[1] == "")
				return;
			ewDom.addClass(elInput, "ewUnmatched");
			ewDom.setStyle(elMessage, "display", "");
		});	
	} else {
		this.ac.unmatchedItemSelectEvent.subscribe(function(type, e) {
			this._as.setValue(this._elTextbox.value);	
		});
	}
}

// Start event handler for add option dialog
function ew_AddOptStart(type, args) {
	var btns = ewAddOptDialog.getButtons();
	for (var i = 0; i < btns.length; i++)
		btns[i].set("disabled", true);
	var els = ew_Matches(":file[value!='']", ewAddOptDialog.form.elements);
	if (els.length == 0) // Not file upload
		return;
	var div = ew_Select("div.ewMessageDialog", ewAddOptDialog.body)[0];
	if (!div)
		return;
	var p = document.createElement("P");
	p.id = "ewUploading";
	p.innerHTML = "<img src=\"" + EW_IMAGE_FOLDER + "loading.gif" + "\" alt=\"\" style=\"border: 0;\">&nbsp;" + ewLanguage.Phrase("Uploading");
	ewDom.insertBefore(p, div);
}

// Init add option dialog
function ew_InitAddOptDialog() {
	if (!document.getElementById("ewAddOptDialog"))
		return;
	ewAddOptDialog = new ewWidget.Dialog("ewAddOptDialog", { visible: false, constraintoviewport: true, hideaftersubmit: false, zIndex: 9000 }); 
	ewAddOptDialog.callback = {success: ew_AddOptSuccess, failure: ew_AddOptFailure, upload: ew_AddOptSuccess,
		customevents: {"onStart": ew_AddOptStart}};
	ewAddOptDialog.render();
}

// Init email dialog
function ew_InitEmailDialog() {
	if (!document.getElementById("ewEmailDialog"))
		return;
	ewEmailDialog = new ewWidget.Dialog("ewEmailDialog", { visible: false, constraintoviewport: true, hideaftersubmit: false, zIndex: 10000 });
	if (ewEmailDialog.body) {
		ewEmailDialog._body = ewEmailDialog.body.innerHTML;
		ewEmailDialog.setBody("");
	}
	ewEmailDialog.validate = function() {
		var elm;
		var fobj = this.form;
		elm = fobj.elements["sender"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterSenderEmail"));
		if (elm && !ew_CheckEmailList(elm.value, 1))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterProperSenderEmail"));
		elm = fobj.elements["recipient"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterRecipientEmail"));
		if (elm && !ew_CheckEmailList(elm.value, EW_MAX_EMAIL_RECIPIENT))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterProperRecipientEmail"));
		elm = fobj.elements["cc"];
		if (elm && !ew_CheckEmailList(elm.value, EW_MAX_EMAIL_RECIPIENT))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterProperCcEmail"));
		elm = fobj.elements["bcc"];
		if (elm && !ew_CheckEmailList(elm.value, EW_MAX_EMAIL_RECIPIENT))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterProperBccEmail"));
		elm = fobj.elements["subject"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(null, elm, ewLanguage.Phrase("EnterSubject"));
		return true;
	};
	ewEmailDialog.render();
}

// Default submit handler
function ew_DefaultHandleSubmit() {
	this.submit();
	ew_RemoveScript(this.callback.argument.el);
}

// Default cancel handler
function ew_DefaultHandleCancel() {
	this.cancel();
	this.setBody("");
	ew_RemoveScript(this.callback.argument.el);	
}

// Execute JavaScript in HTML loaded by Ajax
function ew_ExecScript(html, id) {
	var ar, i = 0, re = /<script([^>]*)>([\s\S]*?)<\/script\s*>/ig;
	while ((ar = re.exec(html)) != null) {
		var text = RegExp.$2;
		if (/(\s+type\s*=\s*['"]*(text|application)\/(java|ecma)script['"]*)|^((?!\s+type\s*=).)*$/i.test(RegExp.$1))
			ew_AddScript(text, "scr_" + id + i++);
	}
}

// Strip JavaScript in HTML loaded by Ajax
function ew_StripScript(html) {
	var ar, re = /<script([^>]*)>([\s\S]*?)<\/script\s*>/ig;
	var str = html;
	while ((ar = re.exec(html)) != null) {
		var text = RegExp.lastMatch;
		if (/(\s+type\s*=\s*['"]*(text|application)\/(java|ecma)script['"]*)|^((?!\s+type\s*=).)*$/i.test(RegExp.$1))
			str = str.replace(text, "");
	}
	return str;
}

// Add SCRIPT tag
function ew_AddScript(text, id) {
	var scr = document.createElement("SCRIPT");
	if (id)
		scr.id = id;
	scr.type = "text/javascript";
	scr.text = text;
	return document.body.appendChild(scr);
}

// Remove JavaScript added by Ajax
function ew_RemoveScript(id) {
	if (!id)
		return;
	var els = ew_Select("script[id^=scr_" + id + "_]");
	for (var i = els.length - 1; i >= 0; i--)	
		els[i].parentNode.removeChild(els[i]);
}

// Get form elements as object
function ew_ElementsToRow(fobj, infix) {
	var row = {"index": infix};
	ew_Matches("[name^=x" + infix + "_]", fobj.elements, function(el) {
		var elname = "x_" + el.name.substr(infix.length + 2);
		if (ewLang.isObject(row[elname])) { // Already exists
			if (ewLang.isArray(row[elname])) {
				row[elname][row[elname].length] = el; // Add to array
			} else {
				row[elname] = [row[elname], el]; // Convert to array
			}
		} else {
			row[elname] = el;
		}
	});
	fobj.row = row;
}

//  Add Option failure handler
function ew_AddOptFailure(o) {
	ewAddOptDialog.hide();
	ewAddOptDialog.setBody("");
	alert("Server Error " + o.status + ": " + o.statusText);
}

//  Add Option success handler
function ew_AddOptSuccess(o) {
	var results;
	if (o.responseText) {
		try { 	
			results = ewJson.parse(o.responseText);
		} catch(e) {}
	}		
	if (results && results.length > 0) {
		ewAddOptDialog.hide();
		ewAddOptDialog.setBody("");
		var result = results[0];
		var el = o.argument.el; // HTML element
		var frm = o.argument.frm; // ew_Form object
		var form = frm.Form; // HTML form object				
		var obj = ew_GetElements(el, form);
		if (obj) {
			var lf = frm.Lists[el].LinkField;
			var dfs = frm.Lists[el].DisplayFields;
			var ffs = frm.Lists[el].FilterFields;
			var pfs = frm.Lists[el].ParentFields;			
			var lfv = (lf != "") ? result[lf] : "";
			var row = [lfv];
			for (var i = 0, len = dfs.length; i < len; i++)				
				row[row.length] = (dfs[i] in result) ? result[dfs[i]] : "";
			for (var i = 0, len = ffs.length; i < len; i++)				
				row[row.length] = (ffs[i] in result) ? result[ffs[i]] : "";
			if (lfv && dfs.length > 0 && row[1]) {
				var id = ew_GetId(el, false);
				if (frm.Lists[id].Ajax === null) { // Non-Ajax	 
					var ar = frm.Lists[id].Options; 					
					ar[ar.length] = row;
				}

				// Get the parent field values
				var arp = [];
				for (var i = 0, len = pfs.length; i < len; i++)
					arp[arp.length] = ew_GetOptValues(pfs[i], form);
				var args = {data: row, parents: arp, valid: true, id: ew_GetId(obj), form: form};
				ewAddOptionEvent.fire(args);
				if (args.valid) { // Add the new option
					var vals = [];
					if (!obj.options && obj.length) { // Radio/Checkbox list
						var p = ew_GetElement("dsl_" + ew_GetId(obj), form); // Container element
						if (!p)
							return;
						vals = ew_Matches(":checked", obj, function(el){return el.value;});						
						ew_ClearOpt(obj);
						p._options = ew_Matches("[value!='{value}']", obj, function(el){
							return {val: el.value, lbl: (el.nextSibling) ? el.nextSibling.nodeValue : ""};
						});				
					}
					var txt = ew_NewOpt(obj, row, form);
					if (obj.options) {
						obj.options[obj.options.length-1].selected = true;
						if (obj.onchange)
							obj.onchange();
						obj.focus();				
					} else if (obj.length) { // Radio/Checkbox list					
						ew_RenderOpt(obj, form);
						obj = ew_GetElements(id, form); // Update the list
						if (vals.length > 0)
							ew_SelectOpt(obj, vals);	
						if (obj.length > 0) {
							var el = obj[obj.length-1];
							el.checked = true;
							if ((el.type == "checkbox" || el.type == "radio") && el.onclick)
								el.onclick();
							el.focus();
						}
					} else if (ew_IsAutoSuggest(obj)) {
						var o = ew_GetAutoSuggest(obj);
						obj.value = lfv;
						o.ac._bItemSelected = true;
						if (obj.onchange)
							obj.onchange();
						o.input.value = txt;
						o.input.focus();
					}
				}
			}
		}
	} else {
		var btns = ewAddOptDialog.getButtons();
		for (var i = 0; i < btns.length; i++)
			btns[i].set("disabled", false);
		var div = ew_Select("div.ewMessageDialog", ewAddOptDialog.body)[0];
		div.innerHTML = "";
		var p = ew_Select("#ewUploading", ewAddOptDialog.body)[0];
		if (p)
			p.parentNode.removeChild(p);
		var div3 = document.createElement("DIV");
		div3.className = "ewDisplayNone";
		div3.innerHTML = o.responseText;
		var msg, div2 = ew_Select("div.ewMessageDialog", div3)[0];
		if (div2) {
			msg = div2.innerHTML;
		} else {
			msg = o.responseText;
			if (!msg || msg.replace(/^\s*|\s*$/g, "") == "")
				msg = ewLanguage.Phrase("InsertFailed");
			msg = "<p class=\"ewErrorMessage\"><img src=\"" + EW_IMAGE_FOLDER + "error.gif" + "\" alt=\"\" style=\"border: 0;\">&nbsp;" + msg + "</p>";
		}
		div.innerHTML = msg;
		if (div.style.display == "none")
			ew_ShowMessage(msg);
	}
}

// Submit Add Option dialog
function ew_AddOptSubmit() {
	if (typeof ew_UpdateTextArea == "function")
		ew_UpdateTextArea();
	var form = this.form;
	var frm = ewForms[form.id];
	if (frm.Validate()) {
		frm.DestroyEditor();
		this.submit();		
	}	
}

// Cancel Add Option dialog
function ew_AddOptCancel() {
	var form = this.form; // Get the form before cancel
	this.cancel();
	this.setBody("");
	ew_RemoveScript(this.callback.argument.el);
	ewForms[form.id].DestroyEditor();	
}

// Show Add Option dialog
// argument object properties:
// frm {object} ew_Form object
// lnk {HTMLElement} add option anchor element
// el {string} form element id
// url {string} URL of the Add form 
function ew_AddOptDialogShow(oArg) {
	if (ewAddOptDialog.cfg.getProperty("visible"))
		ewAddOptDialog.hide();
	var cb = {
		success: function(o) {
			if (ewAddOptDialog) {
				var args = o.argument;				
				var pf = args.frm.Lists[args.el].ParentFields;
				var ff = args.frm.Lists[args.el].FilterFields;
				var form = args.frm.Form;
				var ar = [];
				for (var i = 0, len = pf.length; i < len; i++) {			
					var obj = ew_GetElements(pf[i], form); // Get the parent field value
					ar[ar.length] = ew_GetOptValues(obj);
				}
				var cfg = {context: [args.lnk, "tl", "bl"], width: null,
					buttons: [{text:EW_ADDOPT_BUTTON_SUBMIT_TEXT, handler:ew_AddOptSubmit, isDefault:true},
						{text:EW_BUTTON_CANCEL_TEXT, handler:ew_AddOptCancel}]};
				if (ewEnv.ua.ie && ewEnv.ua.ie >= 8)
					cfg["underlay"] = "none";
				ewAddOptDialog.cfg.applyConfig(cfg);
				ewAddOptDialog.callback.argument = args;
				ewAddOptDialog.setBody(ew_StripScript(o.responseText));
				ewAddOptDialog.setHeader(args.lnk.innerHTML || args.lnk.value);
				ewAddOptDialog.render();
				ewAddOptDialog.registerForm(); // Make sure the form is registered (otherwise, the form is not registered in the first time)				
				if (ewAddOptDialog.form) { // Set the filter field value
					for (var i = 0, len = ar.length; i < len; i++) {
						if (ff[i] && ewAddOptDialog.form.elements[ff[i]])
							ew_SelectOpt(ewAddOptDialog.form.elements[ff[i]], ar[i]);
					}
				}
				ewAddOptDialog.show();
				ewAddOptDialog.focusDefaultButton(); // Do not focus first element (if it is AutoSuggest, not work in FF) 
				ew_ExecScript(o.responseText, args.el);
			}
		},
		failure: function(oResponse) {},
		scope: this,
		argument: oArg
	}
	ewConnect.asyncRequest("get", oArg.url, cb, null);
}

// Auto-fill
function ew_AutoFill(el) {
	var f = el.form;
	if (!f)
		return;
	var ar = ew_GetOptValues(el);
	var id = ew_GetId(el);	
	var sf = ew_GetElement("sf_" + id, f);
	if (!sf || sf.value == "")
		return;
	var cb = {};
	cb.success = function(oResponse) { 
		var dn = ew_GetElement("ln_" + id, f);
		var destNames = (dn) ? dn.value : "";
		var dest_array = destNames.split(",");
		var results = ew_ParseResponse(oResponse.responseText);
		var result = (results) ? results[0] : [];
		for (var j = 0; j < dest_array.length; j++) {
			var destEl = ew_GetElements(dest_array[j], f);
			if (destEl) {
				var val = (ewLang.isValue(result[j])) ? result[j] : "";
				var args = {result: result, data: val, id: dest_array[j], form: f, sender: id, cancel: false, trigger: true};
				ewAutoFillEvent.fire(args); // Fire event
				if (args.cancel)
					continue;
				val = args.data; // Process the value
				if (destEl.options || destEl.length && destEl[0].type == "radio") { // Selection/Radio list
					ew_SelectOpt(destEl, val.split(","));
				} else if (destEl.length && destEl[0].type == "checkbox") { // Checkbox list
					ew_SelectOpt(destEl, val.split(","));
				} else if (ew_IsAutoSuggest(destEl)) { // Auto-Suggest
					destEl.value = val;
					ew_GetAutoSuggest(destEl).input.value = val;
					ew_UpdateOpt.call(ewForms[f.id], destEl);
				} else if (ew_IsHiddenTextArea(destEl)) { // DHTML editor
					destEl.value = val;
					if (typeof ew_UpdateEditor == "function")
						ew_UpdateEditor(ew_ConcatId(f.id, destEl.id));
				} else {
					destEl.value = val;
				}
				if (args.trigger) {
					if (ewLang.isFunction(destEl.onchange)) {
						destEl.onchange();
					} else if (destEl.length && destEl.length > 0) { // Radio/Checkbox list
						var el = destEl[0];
						if (ewLang.isFunction(destEl.onclick))
							destEl.onclick();
					}
				}
			}
		}
	};
	if (ar.length > 0 && ar[0] != "") {		
		var data = "q=" + encodeURIComponent(ar[0]) + "&s=" + sf.value;
		ewConnect.asyncRequest("post", EW_LOOKUP_FILE_NAME, cb, data);
	} else {
		cb.success({responseText: ""});
	}
}

// Init tooltip div
function ew_InitTooltipDiv() {
	if (!document.getElementById("ewTooltipDiv"))
		return;
	ewTooltipDiv = new ewWidget.Panel("ewTooltipDiv", { context:null, visible:false, zIndex:11000, draggable:false, close:false, iframe:true });
	ewTooltipDiv.render();
}

// Show tooltip div
function ew_ShowTooltip(obj, el, wd) { // wd = width (px)
	el = ew_GetElement(el, obj.parentNode.parentNode);
	if (!ewTooltipDiv || !el || !el.innerHTML || ew_RemoveSpaces(el.innerHTML) == "")
		return;
	var cfg = {context:[obj,"tl","tr"], visible:false, constraintoviewport:true, preventcontextoverlap:true};
	wd = parseInt(wd);
	cfg["width"] = (ewLang.isNumber(wd) && (wd > 0)) ? wd + "px" : "";
	ewTooltipDiv.cfg.applyConfig(cfg, true);
	ewTooltipDiv.setBody("<div>" + el.innerHTML + "</div>");
	ewTooltipDiv.render();
	ewTooltipDiv.show();
}

// Hide tooltip div
function ew_HideTooltip() {
	if (ewTooltipDiv)
		ewTooltipDiv.hide();
}

// Show title 
function ew_ShowTitle(obj, html, wd) { // wd = width (px)
	if (!ewTooltipDiv || ew_RemoveSpaces(html) == "")
		return;
	var cfg = {context:[obj,"tl","tr"], visible:false, constraintoviewport:true, preventcontextoverlap:true};
	wd = parseInt(wd);
	cfg["width"] = (ewLang.isNumber(wd) && (wd > 0)) ? wd + "px" : "";
	ewTooltipDiv.cfg.applyConfig(cfg, true);
	ewTooltipDiv.setBody("<div>" + html + "</div>");
	ewTooltipDiv.render();
	ewTooltipDiv.show();
}

// Show dialog for email sending
// argument object members:
// lnk {string} email link id
// hdr {string} dialog header
// url {string} URL of the email script
// f {HTMLElement} form
// key {object} key as object
// sel {boolean} exported selected
function ew_EmailDialogShow(oArg) {
	if (!ewEmailDialog)
		return;
	if (oArg.sel && !ew_KeySelected(oArg.f)) {
		alert(ewLanguage.Phrase("NoRecordSelected"));
		return;
	}
	if (ewEmailDialog.cfg.getProperty("visible"))
		ewEmailDialog.hide();
	var cfg = {context: [oArg.lnk, "tl", "bl"], postmethod: "form", width: null,
		buttons: [ { text:EW_EMAIL_EXPORT_BUTTON_SUBMIT_TEXT, handler:ew_DefaultHandleSubmit, isDefault:true },
			{text:EW_BUTTON_CANCEL_TEXT, handler:ew_DefaultHandleCancel}]};
	if (ewEnv.ua.ie && ewEnv.ua.ie >= 8)
		cfg["underlay"] = "none";
	ewEmailDialog.cfg.applyConfig(cfg);
	ewEmailDialog.callback.argument = oArg;
	ewEmailDialog.setHeader(oArg.hdr);
	ewEmailDialog.setBody(ewEmailDialog._body);
	ewEmailDialog.render();
	ewEmailDialog.registerForm(); // Make sure the form is registered (otherwise, the form is not registered in the first time)    
	var form = oArg.f;
	if (form && oArg.sel) { // If export selected
		ew_Matches(":checkbox[name='key_m[]']:checked", form.elements, function(chk) {
			var el = document.createElement("INPUT");
			el.setAttribute("name","key_m[]");
			el.type = "hidden";
			el.value = chk.value;
			ewEmailDialog.form.appendChild(el);
		});
	}
	var key = oArg.key;
	if (key) {
		for (n in key) {
			el = document.createElement("INPUT");
			el.setAttribute("name", n);
			el.type = "hidden";
			el.value = key[n];
			ewEmailDialog.form.appendChild(el);
		}
	}
	ewEmailDialog.show();
}

// Ajax query
// Usage: First create query by server side function ew_CreateQuery($id, $sql)
// where $sql should contain "{query_value}" which will be replaced by the argument "value",
// then call this client side function ew_Query() to execute the SQL.
// el {HTMLElement|string} DIV element or id of the DIV element created by ew_CreateQuery()
// value {string} client side runtime value to replace "{query_value}" in SQL
// callback {function} callback function for async request, empty for sync request 
// fn {string} server side function name "fn" for more complex string replacement on server side
// Note: All results are STRING.
function ew_Query(el, value, callback, fn) {
	if (ewLang.isString(el))
		el = document.getElementById(el);
	if (!el || el.innerHTML == "")
		return;
	var data = "s=" + el.innerHTML + "&q=" + encodeURIComponent(value) + "&fn=" + encodeURIComponent(fn);		
	if (ewLang.isFunction(callback)) { // Async
		var cb = {success: function(oResponse) {callback(ew_ParseResponse(oResponse.responseText, true));}};
		ewConnect.asyncRequest("post", EW_LOOKUP_FILE_NAME, cb, data);
	} else { // Sync
		var o = ewConnect.getConnectionObject();	
		o.conn.open("post", EW_LOOKUP_FILE_NAME, false);
		o.conn.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		o.conn.send(data);	
		return ew_ParseResponse(o.conn.responseText, true);
	}
}

// Toggle search operator
function ew_ToggleSrchOpr(id, value) {
	var el = this.form.elements[id];
	if (!el)
		return;
	el.value = (el.value != value) ? value : "=";
}

// Validators
// Check US Date format (mm/dd/yyyy)
function ew_CheckUSDate(object_value) {
	return ew_CheckDateEx(object_value, "us", EW_DATE_SEPARATOR);
}

// Check US Date format (mm/dd/yy)
function ew_CheckShortUSDate(object_value) {
	return ew_CheckDateEx(object_value, "usshort", EW_DATE_SEPARATOR);
}

// Check Date format (yyyy/mm/dd)
function ew_CheckDate(object_value) {
	return ew_CheckDateEx(object_value, "std", EW_DATE_SEPARATOR);
}

// Check Date format (yy/mm/dd)
function ew_CheckShortDate(object_value) {
	return ew_CheckDateEx(object_value, "stdshort", EW_DATE_SEPARATOR);
}

// Check Euro Date format (dd/mm/yyyy)
function ew_CheckEuroDate(object_value) {
	return ew_CheckDateEx(object_value, "euro", EW_DATE_SEPARATOR);
}

// Check Euro Date format (dd/mm/yy)
function ew_CheckShortEuroDate(object_value) {
	return ew_CheckDateEx(object_value, "euroshort", EW_DATE_SEPARATOR);
}

// Check date format
// format: std/stdshort/us/usshort/euro/euroshort
function ew_CheckDateEx(value, format, sep) {
	if (!value || value.length == "")
		return true;
	while (value.indexOf("  ") > -1)
		value = value.replace(/  /g, " ");
	value = value.replace(/^\s*|\s*$/g, "");
	var arDT = value.split(" ");
	if (arDT.length > 0) {
		var re, sYear, sMonth, sDay;
		re = /^(\d{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2]\d|[3][0|1])$/;
		if (ar = re.exec(arDT[0])) {
			sYear = ar[1];
			sMonth = ar[2];
			sDay = ar[3];
		} else {
			var wrksep = "\\" + sep;
			switch (format) {
				case "std":
					re = new RegExp("^(\\d{4})" + wrksep + "([0]?[1-9]|[1][0-2])" + wrksep + "([0]?[1-9]|[1|2]\\d|[3][0|1])$");
					break;
				case "stdshort":
					re = new RegExp("^(\\d{2})" + wrksep + "([0]?[1-9]|[1][0-2])" + wrksep + "([0]?[1-9]|[1|2]\\d|[3][0|1])$");
					break;
				case "us":
					re = new RegExp("^([0]?[1-9]|[1][0-2])" + wrksep + "([0]?[1-9]|[1|2]\\d|[3][0|1])" + wrksep + "(\\d{4})$");
					break;
				case "usshort":
					re = new RegExp("^([0]?[1-9]|[1][0-2])" + wrksep + "([0]?[1-9]|[1|2]\\d|[3][0|1])" + wrksep + "(\\d{2})$");
					break;
				case "euro":
					re = new RegExp("^([0]?[1-9]|[1|2]\\d|[3][0|1])" + wrksep + "([0]?[1-9]|[1][0-2])" + wrksep + "(\\d{4})$");
					break;
				case "euroshort":
					re = new RegExp("^([0]?[1-9]|[1|2]\\d|[3][0|1])" + wrksep + "([0]?[1-9]|[1][0-2])" + wrksep + "(\\d{2})$");
					break;
			}
			if (!re.test(arDT[0]))
				return false;
			var arD = arDT[0].split(sep);
			switch (format) {
				case "std":
				case "stdshort":
					sYear = ew_UnformatYear(arD[0]);
					sMonth = arD[1];
					sDay = arD[2];
					break;
				case "us":
				case "usshort":
					sYear = ew_UnformatYear(arD[2]);
					sMonth = arD[0];
					sDay = arD[1];
					break;
				case "euro":
				case "euroshort":
					sYear = ew_UnformatYear(arD[2]);
					sMonth = arD[1];
					sDay = arD[0];
					break;
			}
		}
		if (!ew_CheckDay(sYear, sMonth, sDay))
			return false;
	}
	if (arDT.length > 1 && !ew_CheckTime(arDT[1]))
		return false;
	return true;
}

// Unformat 2 digit year to 4 digit year
function ew_UnformatYear(yr) {
	if (yr.length == 2)
		return (yr > EW_UNFORMAT_YEAR) ? "19" + yr : "20" + yr;
	return yr;
}

// Check day
function ew_CheckDay(checkYear, checkMonth, checkDay) {
	var maxDay = 31;
	if (ew_InArray(checkMonth, [4, 6, 9, 11]) > -1) {
		maxDay = 30;
	} else if (checkMonth == 2)	{
		if (checkYear % 4 > 0)
			maxDay = 28;
		else if (checkYear % 100 == 0 && checkYear % 400 > 0)
			maxDay = 28;
		else
			maxDay = 29;
	}
	return ew_CheckRange(checkDay, 1, maxDay);
}

// Check integer
function ew_CheckInteger(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	if (object_value.indexOf(EW_DECIMAL_POINT) > -1)
		return false;
	return ew_CheckNumber(object_value);
}

// Check number
function ew_CheckNumber(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = new RegExp("^[+-]?(\\d{1,3}(" + ((EW_THOUSANDS_SEP) ? "\\" + EW_THOUSANDS_SEP + "?" : "") + "\\d{3})*(\\" +
		EW_DECIMAL_POINT + "\\d+)?|\\" + EW_DECIMAL_POINT + "\\d+)$");
	return re.test(object_value);
}

// Convert to float
function ew_StrToFloat(object_value) {
	if (EW_THOUSANDS_SEP != "") {
		var re = new RegExp("\\" + EW_THOUSANDS_SEP, "g");
		object_value = object_value.replace(re, "");
	}
	if (EW_DECIMAL_POINT != "")
		object_value = object_value.replace(EW_DECIMAL_POINT, ".");
	return parseFloat(object_value);
}

// Convert string (yyyy-mm-dd hh:mm:ss) to date object
function ew_StrToDate(object_value) {
	var re = /^(\d{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2]\d|[3][0|1]) (?:(0\d|1\d|2[0-3]):([0-5]\d):([0-5]\d))?$/;
	var ar = object_value.replace(re, "$1 $2 $3 $4 $5 $6").split(" ");
	return new Date(ar[0], ar[1]-1, ar[2], ar[3], ar[4], ar[5]);
}

// Check range
function ew_CheckRange(object_value, min_value, max_value) {
	if (!object_value || object_value.length == 0)
		return true;
	var L = ewLang;
	if (L.isNumber(min_value) || L.isNumber(max_value)) { // Number
		if (ew_CheckNumber(object_value))
			object_value = ew_StrToFloat(object_value);
	}
	if (!L.isNull(min_value) && object_value < min_value)
		return false;
	if (!L.isNull(max_value) && object_value > max_value)
		return false;
	return true;
}

// Check time
function ew_CheckTime(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^(0\d|1\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/;
	return re.test(object_value);
}

// Check phone
function ew_CheckPhone(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^\(\d{3}\) ?\d{3}( |-)?\d{4}|^\d{3}( |-)?\d{3}( |-)?\d{4}$/;
	return re.test(object_value);
}

// Check zip
function ew_CheckZip(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^\d{5}$|^\d{5}-\d{4}$/;
	return re.test(object_value);
}

// Check credit card
function ew_CheckCreditCard(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	var creditcard_string = object_value.replace(/\D/g, "");	
	if (creditcard_string.length == 0)
		return false;
	var doubledigit = creditcard_string.length % 2 == 1 ? false : true;
	var tempdigit, checkdigit = 0;
	for (var i = 0, len = creditcard_string.length; i < len; i++) {
		tempdigit = parseInt(creditcard_string.charAt(i));		
		if (doubledigit) {
			tempdigit *= 2;
			checkdigit += (tempdigit % 10);			
			if (tempdigit / 10 >= 1.0)
				checkdigit++;			
			doubledigit = false;
		}	else {
			checkdigit += tempdigit;
			doubledigit = true;
		}
	}		
	return (checkdigit % 10 == 0);
}

// Check social security number
function ew_CheckSSC(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d\3(?!0000)\d{4}$/;
	return re.test(object_value);
}

// Check emails
function ew_CheckEmailList(object_value, email_cnt) {
	if (!object_value || object_value.length == 0)
		return true;
	var arEmails = object_value.replace(/,/g, ";").split(";");
	for (var i = 0, len = arEmails.length; i < len; i++) {
		if (email_cnt > 0 && len > email_cnt)
			return false;
		if (!ew_CheckEmail(arEmails[i]))
			return false;
	}
	return true;
}

// Check email
function ew_CheckEmail(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^[\w.%+-]+@[\w.-]+\.[A-Z]{2,6}$/i;
	return re.test(object_value);
}

// Check GUID {xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx}
function ew_CheckGUID(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	object_value = object_value.replace(/^\s*|\s*$/g, "");
	var re = /^\{\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\}$/;
	var re2 = /^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/;
	return re.test(object_value) || re2.test(object_value);
}

// Check file extension
function ew_CheckFileType(object_value) {
	if (!object_value || object_value.length == 0)
		return true;
	if (!EW_UPLOAD_ALLOWED_FILE_EXT)
		return true;
	if (EW_UPLOAD_ALLOWED_FILE_EXT.replace(/^\s*|\s*$/g, "") == "")
		return true;	
	var exts = EW_UPLOAD_ALLOWED_FILE_EXT.toLowerCase().split(",");
	var ext = object_value.substr(object_value.lastIndexOf(".") + 1).toLowerCase();
	return (ew_InArray(ext, exts) > -1); 
}

// Check by regular expression
function ew_CheckByRegEx(object_value, pattern) {
	if (!object_value || object_value.length == 0)
		return true;
	return (object_value.match(pattern)) ? true : false;
}

// Show message dialog
function ew_ShowMessage(msg, cfg) {
	var div = ew_Select("div.ewMessageDialog", document)[0];
	var html = msg || ((div) ? div.innerHTML : "");
	if (html.replace(/^\s*|\s*$/g, "") == "")
		return;
	var o = {width: "500px", fixedcenter: true, visible: false, draggable: false,
			modal: true, close: false, constraintoviewport: true, zIndex: 10000,
			text: html,
			buttons: [{text: "&nbsp;&nbsp;&nbsp;" + ewLanguage.Phrase("MessageOK") + "&nbsp;&nbsp;&nbsp;",
				handler: function(){this.hide();this.destroy();}}]};
	var dlg = new ewWidget.SimpleDialog("ewMessageDialog", ewLang.isObject(cfg) ? ewLang.merge(o, cfg) : o);
	dlg.render(document.body);
	dlg.show();
}
