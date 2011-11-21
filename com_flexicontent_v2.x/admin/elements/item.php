<?php
/**
 * @version 1.5 stable $Id: item.php 313 2010-06-19 08:32:09Z emmanuel.danan $
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
jimport('joomla.html.html');
jimport('joomla.form.formfield');
/**
 * Renders an Item element
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */

class JFormFieldItem extends JFormField
{
/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$type = 'Item';
	
	function getInput() {
		$doc 		=& JFactory::getDocument();
		$value		= $this->value;

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_flexicontent'.DS.'tables');

		$item =& JTable::getInstance('flexicontent_items', '');
		if ($value) {
			$item->load($value);
		} else {
			$item->title = JText::_( 'FLEXI_SELECT_ITEM' );
		}

		$js = "
		window.addEvent( 'domready', function()
		{
			$('remove').addEvent('click', function(){
				$('a_name').setProperty('value', '".JText::_( 'FLEXI_SELECT_ITEM' )."');
				$('a_id').setProperty('value', '0');
			});
		});

		function qfSelectItem(id, cid, title) {
			document.getElementById('a_id').value = id;
			document.getElementById('a_name').value = title;
			document.getElementById('jform_request_cid').value = cid;
			$('sbox-btn-close').fireEvent('click');
		}";

		$link = 'index.php?option=com_flexicontent&amp;view=itemelement&amp;tmpl=component';
		$doc->addScriptDeclaration($js);

		JHTML::_('behavior.modal', 'a.modal');

		$html = "\n<div style=\"float: left;\"><input style=\"background: #ffffff;\" type=\"text\" id=\"a_name\" value=\"{$item->title}\" disabled=\"disabled\" /></div>";
		$html .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_( 'FLEXI_SELECT' )."\"  href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_( 'FLEXI_SELECT' )."</a></div></div>\n";
		$html .= "\n<input type=\"hidden\" id=\"a_id\" name=\"jform[request][".$this->element["name"]."]\" value=\"{$value}\" />";
		$html .= "<div class=\"button2-left\"><div class=\"blank\"><a id=\"remove\" title=\"".JText::_( 'FLEXI_REMOVE_VALUE' )."\"  href=\"#\"\">".JText::_( 'FLEXI_REMOVE_VALUE' )."</a></div></div>\n";

		return $html;
	}
}
?>