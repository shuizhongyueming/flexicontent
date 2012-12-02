<?php
/**
 * @version 1.5 stable $Id: view.html.php 1193 2012-03-14 09:20:15Z emmanuel.danan@gmail.com $
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the FLEXIcontent categories screen
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class FlexicontentViewImport extends JViewLegacy {

	function display($tpl = null)
	{
		global $globalcats;
		$mainframe = JFactory::getApplication();
		
		//initialise variables
		$user 		= & JFactory::getUser();
		$db  		= & JFactory::getDBO();
		$document	= & JFactory::getDocument();
		$option		= JRequest::getCmd( 'option' );
		$context	= 'com_flexicontent';
		$task		= JRequest::getVar('task', '');
		$cid		= JRequest::getVar('cid', array());
		$extlimit	= JRequest::getInt('extlimit', 100);
		
		$this->setLayout('import');

		//initialise variables
		$user 		= & JFactory::getUser();
		$document	= & JFactory::getDocument();
		$context	= 'com_flexicontent';
		
		JHTML::_('behavior.tooltip');

		//add css to document
		$document->addStyleSheet('components/com_flexicontent/assets/css/flexicontentbackend.css');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $context.'.items.filter_order', 		'filter_order', 	'', 	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'.items.filter_order_Dir',	'filter_order_Dir',	'', 		'word' );

		FLEXISubmenu('notvariable');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'FLEXI_COPYMOVE_ITEM' ), 'import' );
		$ctrl_task = FLEXI_J16GE ? 'items.importcsv' : 'importcsv';
		JToolBarHelper::customX( $ctrl_task, 'import.png', 'import.png', 'FLEXI_IMPORT', $list_check = false );  // list_check will check that at least one row is checked in listing-like views
		JToolBarHelper::cancel();
		
		$db = &JFactory::getDBO();
		$query = 'SELECT id, name'
			. ' FROM #__flexicontent_types'
			. ' WHERE published = 1'
			. ' ORDER BY name ASC'
			;
		$db->setQuery($query);
		$types = $db->loadObjectList();
		
		$lists['type_id'] = flexicontent_html::buildtypesselect($types, 'type_id', '', true, 'class="inputbox" size="1"');

		$categories = $globalcats;
		
		if (FLEXI_J16GE) {
			// build the main category select list
			$actions_allowed = array('core.create');
			$lists['maincat'] = flexicontent_cats::buildcatselect($categories, 'maincat', '', 0, 'class="inputbox" size="10"', false, true, $actions_allowed);
			// build the secondary categories select list
			$lists['seccats'] = flexicontent_cats::buildcatselect($categories, 'seccats[]', '', 0, 'class="inputbox" multiple="multiple" size="10"', false, true, $actions_allowed);
		} else {
			// build the main category select list
			$lists['maincat'] = flexicontent_cats::buildcatselect($categories, 'maincat', '', 0, 'class="inputbox" size="10"', false, false);
			// build the secondary categories select list
			$lists['seccats'] = flexicontent_cats::buildcatselect($categories, 'seccats[]', '', 0, 'class="inputbox" multiple="multiple" size="10"', false, false);
		}
		
		//build languages list
		// Retrieve author configuration
		$db->setQuery('SELECT author_basicparams FROM #__flexicontent_authors_ext WHERE user_id = ' . $user->id);
		if ( $authorparams = $db->loadResult() )
			$authorparams = new JParameter($authorparams);
		
		$allowed_langs = !$authorparams ? null : $authorparams->get('langs_allowed',null);
		$allowed_langs = !$allowed_langs ? null : FLEXIUtilities::paramToArray($allowed_langs);
		
		
		// We will not use the default getInput() function of J1.6+ since we want to create a radio selection field with flags
		// we could also create a new class and override getInput() method but maybe this is an overkill, we may do it in the future
		if (FLEXI_FISH || FLEXI_J16GE) {
			$lists['languages'] = flexicontent_html::buildlanguageslist('language', '', flexicontent_html::getSiteDefaultLang(), 6, $allowed_langs);
		} else {
			$default_lang = flexicontent_html::getSiteDefaultLang();
			$languages[] = JHTML::_('select.option', $default_lang, JText::_( 'FLEXI_DEFAULT' ).' ('.flexicontent_html::getSiteDefaultLang().')' );
			$lists['languages'] = JHTML::_('select.genericlist', $languages, 'language', $class='', 'value', 'text', $default_lang );
		}
		
		$lists['states'] = flexicontent_html::buildstateslist('state', '', '', 2);
		
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('cid'      	, $cid);
		$this->assignRef('user'			, $user);

		parent::display($tpl);
	}
}
?>
