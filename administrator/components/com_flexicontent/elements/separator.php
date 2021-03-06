<?php
/**
 * @version 1.5 stable $Id$
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Renders a fields element
 *
 * @package 	Joomla
 * @subpackage	FLEXIcontent
 * @since		1.5
 */
if (FLEXI_J16GE) {
	jimport('joomla.form.helper');
	JFormHelper::loadFieldClass('spacer');
}


class JElementSeparator extends JElement
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'separator';
		
	function add_css_js() {
		$css="
		div table td.paramlist_value {
			padding-left:8px;
		}
		div .paramlist_value label {
			min-width:10px!important; padding: 0px 10px 0px 0px!important; margin: 4px 0px 0px 1px!important;
		}
		div .paramlist_value input, div .paramlist_value textarea, div .paramlist_value img, div .paramlist_value button { margin:5px 0px 2px 0px; }
		div .paramlist_value select { margin:0px; }
		fieldset.radio  { margin: 0; padding: 0; }
		
		.tool-tip { }
		.tip-title { }
		";
		
		$document = JFactory::getDocument();
		$document->addStyleDeclaration($css);
		$document->addStyleSheet(JURI::root().'components/com_flexicontent/assets/css/flexi_form.css');
		
		// WORKAROUNDs of for 2 issues in com_config: slow chosen JS and PHP 5.3.9+ 'max_input_vars' limit
		if (FLEXI_J30GE) $jinput = JFactory::getApplication()->input;
		$option = FLEXI_J30GE ? $jinput->get('option', '', 'string') : JRequest::getVar('option');
		$view   = FLEXI_J30GE ? $jinput->get('view', '', 'string') : JRequest::getVar('view');
		$controller = FLEXI_J30GE ? $jinput->get('controller', '', 'string') : JRequest::getVar('controller');
		$component  = FLEXI_J30GE ? $jinput->get('component', '', 'string')  : JRequest::getVar('component');
		
		//if ($option=='com_config' || $option=='com_menus' || $option=='com_modules') {
		$document->addStyleSheet(JURI::root().'components/com_flexicontent/assets/css/flexi_shared.css');
		//}
		
		$js = '';
		if ($option=='com_config' && ($view == 'component' || $controller='component') && $component == 'com_flexicontent') {
			$document->addStyleSheet(JURI::root().'components/com_flexicontent/assets/css/tabber.css');
			$document->addScript(JURI::root().'components/com_flexicontent/assets/js/tabber-minimized.js');
			$document->addScriptDeclaration(' document.write(\'<style type="text/css">.fctabber{display:none;}<\/style>\'); ');
			
			if (FLEXI_J30GE) {
				// Make sure chosen JS file is loaded before our code
				JHtml::_('formbehavior.chosen', '#_some_iiidddd_');
				// replace chosen function
				$js .= "
					jQuery.fn.chosen = function(){};
				";
			}
			
			if (FLEXI_J16GE) {
				/*$js .= "
					function fc_prepare_config_form(){
						jQuery('#jform_fcdata_serialized').val( '' );
						jQuery('#jform_fcdata_serialized').val( JSON.stringify(jQuery('#component-form').serializeArray()) );
						jQuery('#component-form select').attr('disabled', true);
						jQuery('#component-form textarea').attr('disabled', true);
						jQuery('#component-form input[type=text], #component-form input[type=checkbox], #component-form input[type=radio]').attr('disabled', true);
					}
					jQuery(document).ready(function() {
						jQuery('#component-form').attr('onsubmit', \"fc_prepare_config_form();\");
					})
				";*/
			}
		}
		if ($js) $document->addScriptDeclaration($js);
		
		if (FLEXI_J16GE) {
			require_once (JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'classes'.DS.'flexicontent.helper.php');
			FLEXI_J30GE ? JHtml::_('behavior.framework', true) : JHTML::_('behavior.mootools');
			flexicontent_html::loadJQuery();
			$document->addScript(JURI::root().'components/com_flexicontent/assets/js/admin.js');
			$document->addScript(JURI::root().'components/com_flexicontent/assets/js/validate.js');
			//if (!FLEXI_J30GE)  $document->addStyleSheet(JURI::base().'components/com_flexicontent/assets/css/j25.css');
			if (FLEXI_J30GE)  $document->addStyleSheet(JURI::base().'components/com_flexicontent/assets/css/j3x.css');
		}
	}
	
	
	function getLabel() {
		return "";
	}
	
	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		static $count = 1;
		$count++;
		
		$output = '<label ';
		if ($description) {
			$output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
		} else {
			$output .= '>';
		}
		$output .= JText::_( $label ).'</label>';
		
		return $output;
	}
	
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		static $js_css_added = null;
		if ($js_css_added===null) {
			$this->add_css_js();
			$js_css_added = true;
		}
		
		if (FLEXI_J16GE) {
			$node = & $this->element;
			$attributes = get_object_vars($node->attributes());
			$attributes = $attributes['@attributes'];
		} else {
			$attributes = & $node->_attributes;
		}
		$level = $attributes['level'];
		$description = @$attributes['description'];
		$initial_tbl_hidden = @$attributes['initial_tbl_hidden'];
		$value = FLEXI_J16GE ? $this->element['default'] : $value;
		
		if (FLEXI_J16GE && in_array($level, array('tblbreak','tabs_start','tab_open','tab_close','tabs_end')) ) return 'do no use type "'.$level.'" in J1.6+';
		
		static $tab_js_css_added = false;
		
		if ($level == 'tblbreak') {
			$style = '';
/*
		} else if ($level == 'level1') {
			$style = '';
		} else if ($level == 'level2') {
			$pos_left   = FLEXI_J16GE ? 'left:4%;' : 'left:2%;';
			$width_vals = FLEXI_J16GE ? 'width:86%;' : 'width:91%;';
			$style = ''.$pos_left.$width_vals;
		} else if ($level == 'level3') {
			$pos_left = FLEXI_J16GE ? 'left:144px;' : 'left:4%;';
			$style = ''.$pos_left;
*/
		} else {
			$style = '';
		}
		
		$class = 'fcsep_'.$level; $title = "";
		if ($description) {
			$class .= FLEXI_J30GE ? " hasTooltip" : " hasTip";
			$title = JText::_($value)."::".JText::_($description);
		}
		
		if ($level == 'tabs_start') {
			$html = '';
			if (!empty($initial_tbl_hidden)) {
				$initial_tbl_hidden = true;
				$html = '<style> table.paramlist.admintable {display:none;} table.paramlist.admintable.flexi {display:table;} div.tabberlive {margin-top:0px;}</style>';
			}
			return $html.'
			</td></tr>
			</table>
			<div class="fctabber">
				<div class="tabbertab">
					<h3 class="tabberheading">'.str_replace('&', ' - ', JText::_($value)).'</h3>
					<table width="100%" cellspacing="1" class="paramlist admintable flexi">
					<tr><td>
			';
		} else if ($level == 'tab_close_open') {
			return '
					</td></tr>
					</table>
				</div>
				<div class="tabbertab">
					<h3 class="tabberheading">'.str_replace('&', ' - ', JText::_($value)).'</h3>
					<table width="100%" cellspacing="1" class="paramlist admintable flexi">
					<tr><td>
				';
		} else if ($level == 'tabs_end') {
			return'
					</td></tr>
					</table>
				</div>
			</div>
				<table width="100%" cellspacing="1" class="paramlist admintable flexi">
				<tr><td>
			';
		} else if ($level == 'tblbreak') {
			return '
			</td></tr>
			</table>
			'
			.'<span style="'.$style.'" class="'.$class.'" title="'.$title.'" >'.JText::_($value).'</span>'.
			'
			<table width="100%" cellspacing="1" class="paramlist admintable flexi">
			<tr><td>
			';
		}
		$pad = '';
		if ($level=='level0') $pad .= ' ';
		else if ($level=='level1') $pad .= ' &nbsp; ';
		else if ($level=='level2') $pad .= ' &nbsp; &nbsp; ';
		else if ($level=='level3') $pad .= '';
		return '<div class="fcclear clear"> </div> <div style="'.$style.'" class="'.$class.'" title="'.$title.'" >'.$pad.JText::_($value).'</div>';
	}
}