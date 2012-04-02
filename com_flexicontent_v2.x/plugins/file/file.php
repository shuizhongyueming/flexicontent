<?php
/**
 * @version 1.0 $Id: file.php 1222 2012-03-27 20:27:49Z ggppdk $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @subpackage plugin.file
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

class plgFlexicontent_fieldsFile extends JPlugin
{
	function plgFlexicontent_fieldsFile( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		JPlugin::loadLanguage('plg_flexicontent_fields_file', JPATH_ADMINISTRATOR);
	}

	function onAdvSearchDisplayField(&$field, &$item) {
		plgFlexicontent_fieldsFile::onDisplayField($field, $item);
	}
	function onDisplayField(&$field, &$item)
	{
		$field->label = JText::_($field->label);
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'file') return;

		// some parameter shortcuts
		$size		= $field->parameters->get( 'size', 30 ) ;
						
		$document	=& JFactory::getDocument();
		$app		=& JFactory::getApplication();
		$prefix		= $app->isSite() ? 'administrator/' : '';
		$required 			= $field->parameters->get( 'required', 0 ) ;
		$required 	= $required ? ' required' : '';
		$dummy_required_form_field = "<input type=\"text\" class=\"{$required}\" name=\"{$field->name}[]\" value=\"\" style=\"display:none;\"/>";

		$js = "
		
		var req_container_innerHTML='".$dummy_required_form_field."';
		var value_counter=".count($field->value).";
		
		function qfSelectFile".$field->id."(id, file) {
		  value_counter++;
		  var req_container = $('req_container_{$field->id}');
		  req_container.innerHTML = '';
		  
			var name 	= 'a_name'+id;
			var ixid 	= 'a_id'+id;			
			var li 		= document.createElement('li');
			var txt		= document.createElement('input');
			var hid		= document.createElement('input');
			var span	= document.createElement('span');
			var img		= document.createElement('img');
			
			var filelist = document.getElementById('sortables_".$field->id."');
			
			$(li).addClass('sortabledisabled');
			$(span).addClass('drag".$field->id."');
			
			var button = document.createElement('input');
			button.type = 'button';
			button.name = 'removebutton_'+id;
			button.id = 'removebutton_'+id;
			$(button).addClass('fcbutton');
			$(button).addEvent('click', function() { deleteField".$field->id."(this) });
			button.value = '".JText::_( 'FLEXI_REMOVE_FILE' )."';
			
			txt.type = 'text';
			txt.size = '".$size."';
			txt.disabled = 'disabled';
			txt.id	= name;
			txt.value	= file;
			
			hid.type = 'hidden';
			hid.name = 'custom[".$field->name."][]';
			hid.value = id;
			hid.id = ixid;
			
			img.src = '".$prefix."components/com_flexicontent/assets/images/move3.png';
			img.alt = '".JText::_( 'FLEXI_CLICK_TO_DRAG' )."';
			
			filelist.appendChild(li);
			li.appendChild(txt);
			li.appendChild(button);
			li.appendChild(hid);
			li.appendChild(span);
			span.appendChild(img);
			
			new Sortables($('sortables_".$field->id."'), {
				'constrain': true,
				'clone': true,
				'handle': '.drag".$field->id."'
			});			
		
		}
		function deleteField".$field->id."(el) {
		  var req_container = $('req_container_{$field->id}');
		  value_counter--;
		  if (value_counter<=0)
		    req_container.innerHTML = req_container_innerHTML;
		  
			var field	= $(el);
			var row		= field.getParent();
			var fx = new Fx.Morph(row, {duration: 300, transition: Fx.Transitions.linear});
			
			fx.start({
				'height': 0,
				'opacity': 0			
			}).chain(function(){
				row.destroy();
			});
		}
		";
		$document->addScriptDeclaration($js);

			// Add the drag and drop sorting feature
			$js = "
			window.addEvent('domready', function(){
				new Sortables($('sortables_".$field->id."'), {
					'constrain': true,
					'clone': true,
					'handle': '.drag".$field->id."'
					});			
				});
			";
			if (!FLEXI_J16GE) $document->addScript( JURI::root().'administrator/components/com_flexicontent/assets/js/sortables.js' );
			$document->addScriptDeclaration($js);

			$css = '
			#sortables_'.$field->id.' { margin: 0px; padding: 0px; list-style: none; white-space: nowrap; }
			#sortables_'.$field->id.' li {
				clear:both;
				list-style: none;
				height: 20px;
				}
			#sortables_'.$field->id.' li input { cursor: text;}
			#sortables_'.$field->id.' li input.fcbutton, .fcbutton { cursor: pointer; margin-left: 3px; }
			span.drag'.$field->id.' img {
				margin: -4px 8px;
				cursor: move;
				float: none;
				display: inline;
			}
			';
			$document->addStyleDeclaration($css);

			$move 	= JHTML::image ( JURI::root().'administrator/components/com_flexicontent/assets/images/move3.png', JText::_( 'FLEXI_CLICK_TO_DRAG' ) );
				
		JHTML::_('behavior.modal', 'a.modal_'.$field->id);
		
		$field->html = "<span id='req_container_{$field->id}'>".(($field->value) ? "":$dummy_required_form_field)."</span>";
		$i = 0;
		$field->html .= '<ul id="sortables_'.$field->id.'">';
		if($field->value) {
			foreach($field->value as $file) {
				$field->html .= '<li>';
				$filename = $this->getFileName( $file );
				$field->html .= "<input size=\"".$size."\" class=\"{$required}\" style=\"background: #ffffff;\" type=\"text\" id=\"a_name".$i."\" value=\"".$filename->filename."\" disabled=\"disabled\" />";
				$field->html .= "<input type=\"hidden\" id=\"a_id".$i."\" name=\"custom[".$field->name."][]\" value=\"".$file."\" />";
				$field->html .= "<input class=\"inputbox fcbutton\" type=\"button\" onclick=\"deleteField".$field->id."(this);\" value=\"".JText::_( 'FLEXI_REMOVE_FILE' )."\" />";
				$field->html .= "<span class=\"drag".$field->id."\">".$move."</span>";
				$field->html .= '</li>';
				$i++;
			}
		}
		$files = implode(":", $field->value);
		$user = & JFactory::getUser();
		$linkfsel = JURI::base().'index.php?option=com_flexicontent&amp;view=fileselement&amp;tmpl=component&amp;index='.$i.'&amp;field='.$field->id.'&amp;itemid='.$item->getValue('id').'&amp;items=0&amp;filter_uploader='.$user->get('id').'&amp;'.JUtility::getToken().'=1';
		$field->html .= "
		</ul>
		<div class=\"button-add\">
			<div class=\"blank\">
				<a class=\"modal_".$field->id."\" title=\"".JText::_( 'FLEXI_ADD_FILE' )."\" href=\"".$linkfsel."\" rel=\"{handler: 'iframe', size: {x:window.getSize().x-100, y: window.getSize().y-100}}\">".JText::_( 'FLEXI_ADD_FILE' )."</a>
			</div>
		</div>
		";
	}

	function onDisplayFieldValue(&$field, $item, $values=null, $prop='display')
	{
		$field->label = JText::_($field->label);
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'file') return;

		$values = $values ? $values : $field->value ;
		
		$mainframe =& JFactory::getApplication();

		// some parameter shortcuts
		$separatorf	= $field->parameters->get( 'separatorf', 3 ) ;
		$opentag		= $field->parameters->get( 'opentag', '' ) ;
		$closetag		= $field->parameters->get( 'closetag', '' ) ;
		$useicon		= $field->parameters->get( 'useicon', 1 ) ;
		$usebutton	= $field->parameters->get( 'usebutton', 0 ) ;
		$display_filename	= $field->parameters->get( 'display_filename', 0 ) ;
		$display_descr		= $field->parameters->get( 'display_descr', 0 ) ;
		
		// Description as tooltip
		if ($display_filename==2) JHTML::_('behavior.tooltip');

		switch($separatorf)
		{
			case 0:
			$separatorf = ' ';
			break;

			case 1:
			$separatorf = '<br />';
			break;

			case 2:
			$separatorf = ' | ';
			break;

			case 3:
			$separatorf = ', ';
			break;

			case 4:
			$separatorf = $closetag . $opentag;
			break;

			default:
			$separatorf = ' ';
			break;
		}
		
		// initialise property
		$field->{$prop} = array();

		$n = 0;
		foreach ($values as $value) {
			$icon = '';
			$filename = $this->getFileName( $value );
			if ($filename) {	
				
				// --. Create icon according to filetype
				if ($useicon) {
					$filename	= $this->addIcon( $filename );
					$icon		= JHTML::image($filename->icon, $filename->ext, 'class="icon-mime"') .'&nbsp;';
				}
				
				// --. Decide whether to show filename (if we do not use button, then displaying of filename is forced)
				$name_str   = ($display_filename || !$usebutton) ? $filename->altname : '';
				$name_html  = !empty($name_str) ? '&nbsp;<span class="fcfile_name">'. $name_str . '</span>' : '';
				
				// --. Description as tooltip or inline text ... prepare related variables
				$title_str = $class_str = $text_html  = '';
				if (!empty($filename->description)) {
					if ($display_descr==2) {   // As tooltip
						$title_str  = $name_str . '::' . $filename->description;
						$class_str  = ' hasTip';
						$text_html  = '';
					} else if ($display_descr==1) {  // As inline text
						$title_str  = '';
						$class_str  = '';
						$text_html  = ' <span class="fcfile_descr">'. $filename->description . '</span>';
					}
				}
				
				// --. Create the download link
				$dl_link = JRoute::_( 'index.php?option=com_flexicontent&id='. $value .'&cid='.$field->item_id.'&fid='.$field->id.'&task=download' );
				
				// --. Finally create displayed html ... a download button (*) OR a download link
				// (*) with file manager 's description of file as tooltip or as inline text
				if ($usebutton) {
					$class_str .= ' button';   // Add an extra css class
					$str = '<form id="form-download-'.$field->id.'-'.($n+1).'" method="post" action="'.$dl_link.'">';
						$str .= $icon.'<input type="submit" name="download-'.$field->id.'[]" class="'.$class_str.'" value="'.JText::_('FLEXI_DOWNLOAD').'"/>'. $name_html . $text_html;
					$str .= '</form>';
					$field->{$prop}[] = $str;
				} else {
					$name_str = $filename->altname;   // no download button, force display of filename
					$field->{$prop}[]	= $icon . '<a href="' . $dl_link . '" class="'.$class_str.'" >' . $name_str . '</a>' . $text_shown;
				}
			}
			$n++;
		}
		$field->{$prop} = implode($separatorf, $field->{$prop});
	}
	

	function onBeforeSaveField($field, &$post, &$file)
	{
		// execute the code only if the field type match the plugin type
		if($field->field_type != 'file') return;
		if(!$post) return;

		$mainframe =& JFactory::getApplication();
		
		$newpost = array();
		
		for ($n=0, $c=count($post); $n<$c; $n++)
		{
			if ($post[$n] != '') $newpost[] = $post[$n];
		}
		
		$post = array_unique($newpost);
	}


	function getFileName( $value )
	{
		$db =& JFactory::getDBO();
		$session = & JFactory::getSession();
		jimport('joomla.database.table');
		
		$sessiontable = JTable::getInstance('session');
		$sessiontable->load($session->getId());
		$and = '';
		if(!$sessiontable->client_id) 
			$and = ' AND published = 1';
		$query = 'SELECT filename, altname, description, ext, id'
				. ' FROM #__flexicontent_files'
				. ' WHERE id = '. (int) $value . $and
				;
		$db->setQuery($query);
		$filename = $db->loadObject();

		return $filename;
	}
	

	function addIcon( &$file )
	{
		switch ($file->ext)
		{
			// Image
			case 'jpg':
			case 'png':
			case 'gif':
			case 'xcf':
			case 'odg':
			case 'bmp':
			case 'jpeg':
				$file->icon = 'components/com_flexicontent/assets/images/mime-icon-16/image.png';
			break;

			// Non-image document
			default:
				$icon = JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'assets'.DS.'images'.DS.'mime-icon-16'.DS.$file->ext.'.png';
				if (file_exists($icon)) {
					$file->icon = 'components/com_flexicontent/assets/images/mime-icon-16/'.$file->ext.'.png';
				} else {
					$file->icon = 'components/com_flexicontent/assets/images/mime-icon-16/unknown.png';
				}
			break;
		}
		return $file;
	}
}
