/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if (MooTools.version>="1.2.4") {  // Instruction browser of new type of fields
	Object.append(Browser.Features, {
		inputemail: (function() {
			var i = document.createElement("input");
			i.setAttribute("type", "email");
			return i.type !== "text";
		})()
	});
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

var flexi_j16ge = 1;
var tab_focused;
var max_cat_assign_fc = 0;
var existing_cats_fc  = [];
var max_cat_overlimit_msg_fc = 'Too many categories selected. You are allowed a maximum number of ';
var fcflabels = null;

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: Chris Campbell <www.particletree.com>
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 * @since		1.5
 */
var JFormValidator = new Class({
	initialize: function()
	{
		// Initialize variables
		this.handlers	= Object();
		this.custom		= Object();

		// Default handlers
		this.setHandler('username',
			function (value) {
				regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
				return !regex.test(value);
			}
		);

		this.setHandler('password',
			function (value) {
				regex=/^\S[\S ]{2,98}\S$/;
				return regex.test(value);
			}
		);

		this.setHandler('numeric',
			function (value) {
				regex=/^(\d|-)?(\d|,)*\.?\d*$/;
				return regex.test(value);
			}
		);

		this.setHandler('email',
			function (value) {
				regex=/^[a-zA-Z0-9._-]+(\+[a-zA-Z0-9._-]+)*@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
				return regex.test(value);
			}
		);

		this.setHandler('fieldname',
			function (value) {
				regex=/^[a-zA-Z0-9_-]+$/;
				return regex.test(value);
			}
		);

		this.setHandler('radio',
			function (par) {
				var nl, i;
				if (par.parentNode == null) {
					return true;
				} else {
					var options = par.parentNode.getElementsByTagName('input');
			
					for (i=0, nl = options; i<nl.length; i++) {
						if (nl[i].checked) return true;
					}
			
					return false;
				}
			}
		);

		this.setHandler('checkbox',
			function (par) {
				var nl, i;
				if (par.parentNode == null) {
					return true;
				} else {
					var options = par.parentNode.getElementsByTagName('input');
			
					for (i=0, nl = options; i<nl.length; i++) {
						if (nl[i].checked) return true;
					}
			
					return false;
				}
			}
		);
		
		this.setHandler('checkbox2',
			function (par) {
				var nl, i;
				if (par.parentNode == null) {
					return true;
				} else {
					var options = par.parentNode.getElementsByTagName('input');
			
					var count = 0;
					for (i=0, nl = options; i<nl.length; i++) {
						if (nl[i].checked) count++;
					}
			
					//exactly 2 options
					if(count == 2) return true;
					return false;
				}
			}
		);
		
		this.setHandler('checkbox3',
			function (par) {
				var nl, i;
				if (par.parentNode == null) {
					return true;
				} else {
					var options = par.parentNode.getElementsByTagName('input');
			
					var count = 0;
					for (i=0, nl = options; i<nl.length; i++) {
						if (nl[i].checked) count++;
					}
			
					//exactly 3 options
					if(count == 3) return true;
					return false;
				}
			}
		);

		this.setHandler('catid',
			function (el) {
				var value = el.get('value');
				
				// Check for value for primary category was not set
				if (!value) {
					
					// Retrieve selected values for secondary categories
					var element_id = flexi_j16ge ? 'jform_cid' : 'cid';
					var field_name = flexi_j16ge ? 'jform[cid][]' : 'cid[]';
					
					if(MooTools.version>="1.2.4") {
						//var values = $(element_id).getSelected();  // does not work in old template form overrides with no id parameter
						var values = $$(document.getElementsByName(field_name))[0].getSelected();
						values = values.map( function(g) { return g.get('value'); } );
					} else {
						//values = $(element_id).getValue();  // does not work in old template form overrides with no id parameter
						var values = $$(document.getElementsByName(field_name))[0].getValue();
						//  ** Alternative code **
						//var values = $(element_id).getChildren().filter( function(g) { return g.selected; } );
						//values = values.map( function(g) { return g.getProperty('value'); } );
					}
					
					// If exactly one secondary category was selected then set it as primary
					if (values.length == 1) {
						el.set('value', values[0]);
						return true;
					}
					return false;
				}
				return true;
			}
		);
		
		this.setHandler('fccats',
			function (el) {
				//var value = el.get('value');
				
				// Retrieve selected values for secondary categories
				var element_id = flexi_j16ge ? 'jform_cid' : 'cid';
				var field_name = flexi_j16ge ? 'jform[cid][]' : 'cid[]';
				var field_name_catid = flexi_j16ge ? 'jform[catid]' : 'catid';
					
				if(MooTools.version>="1.2.4") {
					//var values = $(element_id).getSelected();  // does not work in old template form overrides with no id parameter
					var values = $$(document.getElementsByName(field_name))[0].getSelected();
					values = values.map( function(g) { return g.get('value'); } );
					
					var value_catid = $$(document.getElementsByName(field_name_catid))[0].getSelected();
					value_catid = value_catid.map( function(g) { return g.get('value'); } );
					value_catid = value_catid[0];
				} else {
					//values = $(element_id).getValue();  // does not work in old template form overrides with no id parameter
					var values = $$(document.getElementsByName(field_name))[0].getValue();
					//  ** Alternative code **
					//var values = $(element_id).getChildren().filter( function(g) { return g.selected; } );
					//values = values.map( function(g) { return g.getProperty('value'); } );
					
					var value_catid = $$(document.getElementsByName(field_name_catid))[0].getValue();
				}
				
				//console.log(values);
				//console.log(existing_cats_fc);
				
				var add_val = ( value_catid && ( jQuery.inArray(value_catid, values) >= 0) )  ?  0 : 1;
				
				// Check if the number of categories is over the allowed limit for current user
				if (max_cat_assign_fc && (values.length+add_val) > max_cat_assign_fc) {
					var existing_only = 1;
					for (var i = 0; i < values.length; i++) {
						existing_only = existing_only && ( jQuery.inArray(values[i], existing_cats_fc) >= 0 );
					}
					existing_only = existing_only && ( jQuery.inArray(value_catid, existing_cats_fc) >= 0 );
					if (!existing_only) {
						alert(max_cat_overlimit_msg_fc+max_cat_assign_fc);
						return false;
					}
				}
				return true;
			}
		);

		this.setHandler('selmul',
			function (par) {
				var nl, i;
				if (par.parentNode == null) {
					return true;
				} else {
					var options = par.parentNode.getElementsByTagName('option');
			
					var count = 0;
					for (i=0, nl = options; i<nl.length; i++) {
						if (nl[i].selected) count++;
					}
			
					// Check maximum number of selected options
					if(count <= max_sel) return true;
					return false;
				}
			}
		);

		// Attach to forms with class 'form-validate'
		var forms = $$('form.form-validate');
		forms.each(function(form){ this.attachToForm(form); }, this);
	},

	setHandler: function(name, fn, en)
	{
		en = (en == '') ? true : en;
		this.handlers[name] = { enabled: en, exec: fn };
	},

	attachToForm: function(form)
	{
		// Iterate through the form object and attach the validate method to all input fields.
		form.getElements('input,textarea,select,button').each(function(el){
			el = (MooTools.version>="1.2.4")  ?  document.id(el)  :  $(el);
			if (el.hasClass('required')) {
				el.set('required', 'required');
				if (flexi_j16ge) el.set('aria-required', 'true');
			}
			var validate_flag = (MooTools.version>="1.2.4")  ?  
				(el.get('tag') == 'input' || el.get('tag') == 'button') && el.get('type') == 'submit'  :
				(el.getTag() == 'input' || el.getTag() == 'button') && el.getProperty('type') == 'submit';
			if (validate_flag) {
				if (el.hasClass('validate')) {
					el.onclick = function(){return document.formvalidator.isValid(this.form);};
				}
			} else {
				el.addEvent('blur', function(){return document.formvalidator.validate(this);});
				if ( MooTools.version>="1.2.4" && el.hasClass('validate-email') && Browser.Features.inputemail ) {
					el.type = 'email';
				}
			}
		});
	},

	validate: function(el)
	{
		if(MooTools.version>="1.2.4") {
		  el = document.id(el);
			el_value = el.get('value');
		} else {
			document.id = $;
		  el = document.id(el);
			el.get = el.getProperty;
			el.set = el.setProperty;
			el_value = el.getValue();
		}
		el_name = el.get('name');

		// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
		if(el.get('disabled')) {
			this.handleResponse(true, el);
			return true;
		}

		// If the field is required make sure it has a value
		if (el.hasClass('required')) {
			if(el.get('type') == 'radio' || el.get('type') == 'checkbox') {
				// Checked specially bellow
			}
			else if (!el_value || el_value == false) {
				this.handleResponse(false, el);
				return false;
			}
		}

		// Only validate the field if the validate class is set
		var handler = (el.className && el.className.search(/validate-([a-zA-Z0-9\_\-]+)/) != -1) ? el.className.match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
		if (handler == '') {
			this.handleResponse(true, el);
			return true;
		}
		
		// We try to fill-in automatically the Primary Category Select Field, when it is empty
		var auto_filled = new Object();
		if (flexi_j16ge)
			auto_filled['jform[catid]'] = 1;
		else
			auto_filled['catid'] = 1;
		
		// Check the additional validation types
	  // Individual radio & checkbox can have blank value, providing one element in group is set
	  if(!(el.getProperty('type') == "radio" || el.getProperty('type') == "checkbox")){
	  	if ( typeof auto_filled[el_name] != 'undefined' ) {
				// Execute the validation handler and return result
				if (this.handlers[handler].exec(el) != true) {
					this.handleResponse(false, el);
					return false;
				}
			} else if ((handler) && (handler != 'none') && (this.handlers[handler]) && el_value) {
				// Execute the validation handler and return result
				if (this.handlers[handler].exec(el_value) != true) {
					this.handleResponse(false, el);
					return false;
				}
			}
	  } else {
	     if ((handler) && (handler != 'none') && (this.handlers[handler])) {
	        if(el.getProperty('type') == "radio" || el.getProperty('type') == "checkbox"){
	          if ($(el).hasClass('required')) {
							// Execute the validation handler and return result
							if (this.handlers[handler].exec(el.parentNode) != true) {
								this.handleResponse(false, el);
								return false;
							}
	           }
	        }
	     }
	  }
	
		// Return validation state
		this.handleResponse(true, el);
		return true;
	},

	isValid: function(form)
	{
		var valid = true;
		tab_focused = false; // global variable defined above, we use this to focus the first tab that contains required field

		// Validate form fields
		if (flexi_j16ge)
			var elements = form.getElements('fieldset').concat(Array.from(form.elements));
		else
			var elements = form.elements;
		
		for (var i=0;i < elements.length; i++) {
			if (this.validate(elements[i]) == false) {
				tab_focused = true;
				valid = false;
			}
		}

		// Run custom form validators if present
		new Hash(this.custom).each(function(validator){
			if (validator.exec() != true) {
				valid = false;
			}
		});

		return valid;
	},

	handleResponse: function(state, el)
	{
		// Extra code for auto-focusing the tab that contains the first field to fail the validation
		if (typeof jQuery != 'undefined' && state === false && tab_focused === false) {
			var tab = jQuery(el).closest("div.tabbertab");
			var tabset = jQuery(el).closest("div.tabberlive");
			
			if (tabset.length!=0 && tab.length!=0) {
				var tabsetid = tabset.attr('id');
				var tabid = tab.attr('id');
				var tabno = (tabid.search(/grpmarker_tabset_([0-9]+)_tab_([0-9]+)/) != -1) ? tabid.match(/grpmarker_tabset_([0-9]+)_tab_([0-9]+)/)[2] : "";
				fctabber[tabset.attr('id')].tabShow(tabno);
			}
		}
		
		// Executed only once to retrieve and hash all label via their for property
		if ( !fcflabels )
		{
			fcflabels = new Object;
			labels = $$('label');
			labels.each( function(g) {
				label_for = (MooTools.version>="1.2.4") ? g.get('for') : g.getProperty('for');
				if ( label_for )  fcflabels[ label_for ] = g;
			} );
			//var fcflabels_size = Object.size(fcflabels);  alert(fcflabels_size);
		}
		
		// Find the label object for the given field if it exists
		var el_id = (MooTools.version>="1.2.4") ? el.get('id') : el.getProperty('id');
		if ( !(el.labelref) && el_id )
		{
			var lblfor = el_id;
			if ( !el.labelref )  el.labelref = fcflabels[   lblfor   ];
			if ( !el.labelref )  el.labelref = fcflabels[   lblfor = lblfor.replace(/_[0-9]+$/, '')   ];
			if (flexi_j16ge) {
				if ( !el.labelref )   el.labelref = fcflabels[   lblfor = lblfor.replace(/custom_/, '')   ];
				if ( !el.labelref )   el.labelref = fcflabels[   lblfor = 'custom_' + lblfor   ];
			}
		}

		// Set the element and its label (if exists) invalid state
		if (state == false) {
			el.addClass('invalid');
			if (flexi_j16ge) el.set('aria-invalid', 'true');
			if (el.labelref) {
				document.id(el.labelref).addClass('invalid');
				if (flexi_j16ge) document.id(el.labelref).set('aria-invalid', 'true');
			}
		} else {
			el.removeClass('invalid');
			if (flexi_j16ge) el.set('aria-invalid', 'false');
			if (el.labelref) {
				document.id(el.labelref).removeClass('invalid');
				if (flexi_j16ge) document.id(el.labelref).set('aria-invalid', 'false');
			}
		}
	}
});

document.formvalidator = null;
if(MooTools.version>="1.2.4") {
	window.addEvent('domready', function(){
		document.formvalidator = new JFormValidator();
	});
} else {
	Window.onDomReady(function(){
		document.formvalidator = new JFormValidator();
	});
}