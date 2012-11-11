<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage FLEXIcontent
 * @subpackage plugin.text
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');

class plgFlexicontent_fieldsText extends JPlugin
{
	// ***********
	// CONSTRUCTOR
	// ***********
	
	function plgFlexicontent_fieldsText( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		JPlugin::loadLanguage('plg_flexicontent_fields_text', JPATH_ADMINISTRATOR);
	}
	
	
	
	// *******************************************
	// DISPLAY methods, item form & frontend views
	// *******************************************
	
	// Method to create field's HTML display for item form
	function onDisplayField(&$field, &$item)
	{
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'text') return;
		
		$field->label = JText::_($field->label);
		
		// some parameter shortcuts
		$size				= $field->parameters->get( 'size', 30 ) ;
		
		$default_value_use = $field->parameters->get( 'default_value_use', 0 ) ;
		$default_value     = ($item->version == 0 || $default_value_use > 0) ? $field->parameters->get( 'default_value', '' ) : '';
		
		$multiple			= $field->parameters->get( 'allow_multiple', 1 ) ;
		$maxval				= $field->parameters->get( 'max_values', 0 ) ;
		$remove_space = $field->parameters->get( 'remove_space', 0 ) ;
		
		$required		= $field->parameters->get( 'required', 0 ) ;
		$required		= $required ? ' required' : '';
		
		// initialise property
		if (!$field->value) {
			$field->value = array();
			$field->value[0] = JText::_($default_value);
		} else {
			for ($n=0; $n<count($field->value); $n++) {
				$field->value[$n] = htmlspecialchars( $field->value[$n], ENT_QUOTES, 'UTF-8' );
			}
		}
		
		$document	= & JFactory::getDocument();
		
		if ($multiple) // handle multiple records
		{
			//add the drag and drop sorting feature
			$js = "
			window.addEvent('domready', function(){
				new Sortables($('sortables_".$field->id."'), {
					'constrain': true,
					'clone': true,
					'handle': '.fcfield-drag'
					});			
				});
			";
			if (!FLEXI_J16GE) $document->addScript( JURI::root().'administrator/components/com_flexicontent/assets/js/sortables.js' );
			$document->addScriptDeclaration($js);

			$js = "
			var uniqueRowNum".$field->id."	= ".count($field->value).";  // Unique row number incremented only
			var rowCount".$field->id."	= ".count($field->value).";      // Counts existing rows to be able to limit a max number of values
			var maxVal".$field->id."		= ".$maxval.";

			function addField".$field->id."(el) {
				if((rowCount".$field->id." < maxVal".$field->id.") || (maxVal".$field->id." == 0)) {

					var thisField 	 = $(el).getPrevious().getLast();
					var thisNewField = thisField.clone();
					if (MooTools.version>='1.2.4') {
						var fx = new Fx.Morph(thisNewField, {duration: 0, transition: Fx.Transitions.linear});
					} else {
						var fx = thisNewField.effects({duration: 0, transition: Fx.Transitions.linear});
					}
					
					thisNewField.getFirst().setProperty('value','');  /* First element is the text input field, second is e.g remove button */

					thisNewField.injectAfter(thisField);
		
					new Sortables($('sortables_".$field->id."'), {
						'constrain': true,
						'clone': true,
						'handle': '.fcfield-drag'
					});			

					fx.start({ 'opacity': 1 }).chain(function(){
						this.setOptions({duration: 600});
						this.start({ 'opacity': 0 });
						})
						.chain(function(){
							this.setOptions({duration: 300});
							this.start({ 'opacity': 1 });
						});

					rowCount".$field->id."++;       // incremented / decremented
					uniqueRowNum".$field->id."++;   // incremented only
				}
			}

			function deleteField".$field->id."(el)
			{
				if(rowCount".$field->id." > 1)
				{
					var field	= $(el);
					var row		= field.getParent();
					if (MooTools.version>='1.2.4') {
						var fx = new Fx.Morph(row, {duration: 300, transition: Fx.Transitions.linear});
					} else {
						var fx = row.effects({duration: 300, transition: Fx.Transitions.linear});
					}
					
					fx.start({
						'height': 0,
						'opacity': 0
						}).chain(function(){
							(MooTools.version>='1.2.4')  ?  row.destroy()  :  row.remove();
						});
					rowCount".$field->id."--;
				}
			}
			";
			$document->addScriptDeclaration($js);
			
			$css = '
			#sortables_'.$field->id.' { float:left; margin: 0px; padding: 0px; list-style: none; white-space: nowrap; }
			#sortables_'.$field->id.' li {
				clear: both;
				display: block;
				list-style: none;
				height: 20px;
				position: relative;
			}
			#sortables_'.$field->id.' li input { cursor: text;}
			#add'.$field->name.' { margin-top: 5px; clear: both; display:block; }
			#sortables_'.$field->id.' li .admintable { text-align: left; }
			#sortables_'.$field->id.' li:only-child span.fcfield-drag, #sortables_'.$field->id.' li:only-child input.fcfield-button { display:none; }
			';
			
			$remove_button = '<input class="fcfield-button" type="button" value="'.JText::_( 'FLEXI_REMOVE_VALUE' ).'" onclick="deleteField'.$field->id.'(this);" />';
			$move2 	= '<span class="fcfield-drag">'.JHTML::image ( JURI::root().'administrator/components/com_flexicontent/assets/images/move3.png', JText::_( 'FLEXI_CLICK_TO_DRAG' ) ) .'</span>';
		} else {
			$remove_button = '';
			$move2 = '';
			$css = '';
		}
		
		$document->addStyleDeclaration($css);
		
		$fieldname = FLEXI_J16GE ? 'custom['.$field->name.'][]' : $field->name.'[]';
		$elementid = FLEXI_J16GE ? 'custom_'.$field->name : $field->name;
		
		$field->html = array();
		$n = 0;
		foreach ($field->value as $value) {
			$field->html[] = '
				<input id="'.$elementid.'_'.$n.'" name="'.$fieldname.'" class="inputbox'.$required.'" type="text" size="'.$size.'" value="'.$value.'"'.$required.' />
				'.$remove_button.'
				'.$move2.'
				';
			
			$n++;
			if (!$multiple) break;  // multiple values disabled, break out of the loop, not adding further values even if the exist
		}
		
		if ($multiple) { // handle multiple records
			$field->html = '<li>'. implode('</li><li>', $field->html) .'</li>';
			$field->html = '<ul class="fcfield-sortables" id="sortables_'.$field->id.'">' .$field->html. '</ul>';
			$field->html .= '<input type="button" class="fcfield-addvalue" onclick="addField'.$field->id.'(this);" value="'.JText::_( 'FLEXI_ADD_VALUE' ).'" />';
		} else {  // handle single values
			$field->html = '<div>'.$field->html[0].'</div>';
		}
	}
	
	
	// Method to create field's HTML display for frontend views
	function onDisplayFieldValue(&$field, $item, $values=null, $prop='display')
	{
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'text') return;
		
		$field->label = JText::_($field->label);
		
		// Get field values
		$values = $values ? $values : $field->value;
		$values = !is_array($values) ? array($values) : $values;     // make sure values is an array
		$isempty = !count($values) || !strlen($values[0]);           // detect empty value
		
		// Handle default value loading, instead of empty value
		$default_value_use= $field->parameters->get( 'default_value_use', 0 ) ;
		$default_value		= ($default_value_use == 2) ? $field->parameters->get( 'default_value', '' ) : '';
		if ( empty($values) && !strlen($default_value) ) {
			$field->{$prop} = '';
			return;
		} else if ( empty($values) && strlen($default_value) ) {
			$values = array($default_value);
		}
		
		// some parameter shortcuts
		$pretext			= $field->parameters->get( 'pretext', '' ) ;
		$posttext			= $field->parameters->get( 'posttext', '' ) ;
		$separatorf		= $field->parameters->get( 'separatorf', 1 ) ;
		$opentag			= $field->parameters->get( 'opentag', '' ) ;
		$closetag			= $field->parameters->get( 'closetag', '' ) ;
		$remove_space		= $field->parameters->get( 'remove_space', 0 ) ;
		
		if($pretext) { $pretext = $remove_space ? $pretext : $pretext . ' '; }
		if($posttext) {	$posttext = $remove_space ? $posttext : ' ' . $posttext; }
		
		// Some variables
		$document	= & JFactory::getDocument();
		$view 	= JRequest::setVar('view', JRequest::getVar('view', FLEXI_ITEMVIEW));
		
		// Get ogp configuration
		$useogp     = $field->parameters->get('useogp', 0);
		$ogpinview  = $field->parameters->get('ogpinview', array());
		$ogpinview  = FLEXIUtilities::paramToArray($ogpinview);
		$ogpmaxlen  = $field->parameters->get('ogpmaxlen', 300);
		$ogpusage   = $field->parameters->get('ogpusage', 0);
		
		switch($separatorf)
		{
			case 0:
			$separatorf = '&nbsp;';
			break;

			case 1:
			$separatorf = '<br />';
			break;

			case 2:
			$separatorf = '&nbsp;|&nbsp;';
			break;

			case 3:
			$separatorf = ',&nbsp;';
			break;

			case 4:
			$separatorf = $closetag . $opentag;
			break;

			default:
			$separatorf = '&nbsp;';
			break;
		}
		
		// initialise property
		$field->{$prop} = array();
		$n = 0;
		foreach ($values as $value)
		{
			if ( !strlen($value) ) continue;
			
			$field->{$prop}[]	= strlen($values[$n]) ? $pretext.$values[$n].$posttext : '';
			$n++;
		}
		
		// Apply seperator and open/close tags
		if(count($field->{$prop})) {
			$field->{$prop}  = implode($separatorf, $field->{$prop});
			$field->{$prop}  = $opentag . $field->{$prop} . $closetag;
		} else {
			$field->{$prop} = '';
		}
		
		if ($useogp && $field->{$prop}) {
			if ( in_array($view, $ogpinview) ) {
				switch ($ogpusage)
				{
					case 1: $usagetype = 'title'; break;
					case 2: $usagetype = 'description'; break;
					default: $usagetype = ''; break;
				}
				if ($usagetype) {
					$content_val = flexicontent_html::striptagsandcut($field->{$prop}, $ogpmaxlen);
					$document->addCustomTag('<meta property="og:'.$usagetype.'" content="'.$content_val.'" />');
				}
			}
		}
	}
	
	
	
	// **************************************************************
	// METHODS HANDLING before & after saving / deleting field events
	// **************************************************************
	
	// Method to handle field's values before they are saved into the DB
	function onBeforeSaveField( &$field, &$post, &$file, &$item )
	{
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'text') return;
		if(!is_array($post) && !strlen($post)) return;
		
		// Make sure posted data is an array 
		$post = !is_array($post) ? array($post) : $post;
		
		// Reformat the posted data
		$newpost = array();
		$new = 0;
		foreach ($post as $n=>$v)
		{
			if ($post[$n] !== '')
			{
				$newpost[$new] = $post[$n];
			}
			$new++;
		}
		$post = $newpost;
	}
	
	
	// Method to take any actions/cleanups needed after field's values are saved into the DB
	function onAfterSaveField( &$field, &$post, &$file, &$item ) {
	}
	
	
	// Method called just before the item is deleted to remove custom item data related to the field
	function onBeforeDeleteField(&$field, &$item) {
	}
	
	
	
	// *********************************
	// CATEGORY/SEARCH FILTERING METHODS
	// *********************************
	
	// Method to display a search filter for the advanced search view
	function onAdvSearchDisplayField(&$field, &$item)
	{
		if($field->field_type != 'text') return;
		
		plgFlexicontent_fieldsText::onDisplayField($field, $item);
	}
	
	
	// Method to display a category filter for the category view
	function onDisplayFilter(&$filter, $value='')
	{
		// execute the code only if the field type match the plugin type
		if($filter->field_type != 'text') return;

		// ** some parameter shortcuts
		$label_filter 		= $filter->parameters->get( 'display_label_filter', 0 ) ;
		if ($label_filter == 2) $text_select = $filter->label; else $text_select = JText::_('FLEXI_ALL');
		$field->html = '';
		
		
		// *** Retrieve values
		// *** Limit values, show only allowed values according to category configuration parameter 'limit_filter_values'
		$results = flexicontent_cats::getFilterValues($filter);
		
		
		// *** Create the select form field used for filtering
		$options = array();
		$options[] = JHTML::_('select.option', '', '-'.$text_select.'-');
		
		foreach($results as $result) {
			if (!trim($result->value)) continue;
			$options[] = JHTML::_('select.option', $result->value, JText::_($result->text));
		}
		if ($label_filter == 1) $filter->html  .= $filter->label.': ';
		$filter->html	.= JHTML::_('select.genericlist', $options, 'filter_'.$filter->id, ' class="fc_field_filter" onchange="document.getElementById(\'adminForm\').submit();"', 'value', 'text', $value);
		
	}
	
	
	
	// *************************
	// SEARCH / INDEXING METHODS
	// *************************
	
	// Method to create (insert) advanced search index DB records for the field values
	function onIndexAdvSearch(&$field, &$post, &$item) {
		if ($field->field_type != 'text') return;
		if ( !$field->isadvsearch ) return;
		
		FlexicontentFields::onIndexAdvSearch($field, $post, $item, $required_properties=array(), $search_properties=array(), $properties_spacer=' ', $filter_func=null);
		return true;
	}
	
	
	// Method to create basic search index (added as the property field->search)
	function onIndexSearch(&$field, &$post, &$item)
	{
		if ($field->field_type != 'text') return;
		if ( !$field->issearch ) return;
		
		FlexicontentFields::onIndexSearch($field, $post, $item, $required_properties=array(), $search_properties=array(), $properties_spacer=' ', $filter_func=null);
		return true;
	}
	
		
	// Method to get ALL items that have matching search values for the current field id
	function onFLEXIAdvSearch(&$field)
	{
		if ($field->field_type!='selectmultiple') return;
		
		FlexicontentFields::onFLEXIAdvSearch($field);
	}
	
}
