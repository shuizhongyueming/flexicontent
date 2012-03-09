<?php
/**
 * @version 1.5 stable $Id: fields.php 171 2010-03-20 00:44:02Z emmanuel.danan $
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

jimport('joomla.application.component.controller');

/**
 * FLEXIcontent Component Fields Controller
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class FlexicontentControllerFields extends FlexicontentController{
	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	function __construct() {
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'add'  ,		 		'edit' );
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'saveandnew',	'save' );
		$this->registerTask( 'copy', 				'copy' );
	}

	/**
	 * Logic to save a field
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function save() {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$task		= JRequest::getVar('task');
		$post = JRequest::get( 'post' );
		$model = $this->getModel('field');
		$user	=& JFactory::getUser();


		// calculate access
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$field_id		= (int)$cid[0];
		$asset = 'com_flexicontent.field.' . $field_id;
		if (!$field_id) {
			$is_authorised = $user->authorise('flexicontent.createfield', 'com_flexicontent');
		} else {
			$is_authorised = $user->authorise('flexicontent.editfield', $asset);
		}
		
		// check access
		if ( !$is_authorised ) {
			JError::raiseWarning( 403, JText::_( 'FLEXI_ALERTNOTAUTH' ) );
			$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', '');
			return;
		}
		
		if ( $model->store($post) ) {
			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_flexicontent&view=field&cid[]='.(int) $model->get('id');
					break;

				case 'saveandnew' :
					$link = 'index.php?option=com_flexicontent&view=field';
					break;

				default :
					$link = 'index.php?option=com_flexicontent&view=fields';
					break;
			}
			$msg = JText::_( 'FLEXI_FIELD_SAVED' );

			$cache = &JFactory::getCache('com_flexicontent');
			$cache->clean();
			$itemcache 	=& JFactory::getCache('com_flexicontent_items');
			$itemcache->clean();

		} else {

			$msg = JText::_( 'FLEXI_ERROR_SAVING_FIELD' );
			$link = 'index.php?option=com_flexicontent&view=field';
			JError::raiseWarning( 500, $model->getError() );
		}

		$model->checkin();

		$this->setRedirect($link, $msg);
	}

	/**
	 * Logic to publish fields
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'default', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_SELECT_ITEM_PUBLISH' ) );
		} else {
			$model = $this->getModel('fields');

			if(!$model->publish($cid, 1)) {
				$msg = JText::_( 'FLEXI_OPERATION_FAILED' );
				JError::raiseWarning( 500, $model->getError() );
			} else {
				$total = count( $cid );
				$msg 	= $total.' '.JText::_( 'FLEXI_FIELD_PUBLISHED' );
				$cache = &JFactory::getCache('com_flexicontent');
				$cache->clean();
				$itemcache 	=& JFactory::getCache('com_flexicontent_items');
				$itemcache->clean();
			}
		}
		
		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg );
	}

	/**
	 * Logic to unpublish fields
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function unpublish()
	{
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$model 		= $this->getModel('fields');

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_SELECT_ITEM_UNPUBLISH' ) );
		} else if (!$model->canunpublish($cid)) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_YOU_CANNOT_UNPUBLISH_THESE_FIELDS' ));
		} else {
			if(!$model->publish($cid, 0)) {
				$msg = JText::_( 'FLEXI_OPERATION_FAILED' );
				JError::raiseWarning( 500, $model->getError() );
			} else {
				$total = count( $cid );
				$msg 	= $total.' '.JText::_( 'FLEXI_FIELD_UNPUBLISHED' );
				$cache = &JFactory::getCache('com_flexicontent');
				$cache->clean();
				$itemcache 	=& JFactory::getCache('com_flexicontent_items');
				$itemcache->clean();
			}
		}
		
		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg );
	}


	/**
	 * Logic to delete fields
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function remove()
	{
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$model 		= $this->getModel('fields');
		$user	=& JFactory::getUser();

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_SELECT_ITEM_DELETE' ) );
		} else if (!$model->candelete($cid)) {
			$msg = '';
			JError::raiseWarning(500, JText::_( 'FLEXI_YOU_CANNOT_REMOVE_CORE_FIELDS' ));
		} else {

			$field_id		= (int)$cid[0];
			$asset = 'com_flexicontent.field.' . $field_id;
			$has_delete = $user->authorise('flexicontent.deletefield', $asset);
			
			if ( !$has_delete ) {
				$msg = '';
				JError::raiseWarning( 403, JText::_( 'FLEXI_ALERTNOTAUTH' ) );
			} else if (!$model->delete($cid)) {
				$msg = JText::_( 'FLEXI_OPERATION_FAILED' );
				JError::raiseWarning( 500, $model->getError() );
			} else {
				$msg = count($cid).' '.JText::_( 'FLEXI_FIELDS_DELETED' );
				$cache = &JFactory::getCache('com_flexicontent');
				$cache->clean();
				$itemcache 	=& JFactory::getCache('com_flexicontent_items');
				$itemcache->clean();
			}
		}
		
		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg );
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$model 	= $this->getModel('field');
		$user	=& JFactory::getUser();
		
		// calculate access
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$field_id		= (int)$cid[0];
		$asset = 'com_flexicontent.field.' . $field_id;
		$is_authorised = $user->authorise('flexicontent.editfield', $asset);
		
		// check access
		if ( !$is_authorised ) {
			JError::raiseWarning( 403, JText::_( 'FLEXI_ALERTNOTAUTH' ) );
			$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', '');
			return;
		}
		
		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::_( 'FLEXI_EDITED_BY_ANOTHER_ADMIN' );
			$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg);
			return;
		}

		$field = & JTable::getInstance('flexicontent_fields', '');
		$field->bind(JRequest::get('post'));
		$field->checkin();

		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields' );
	}

	/**
	 * Logic to create the view for the edit field screen
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'field' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('field');
		$user	=& JFactory::getUser();

		// calculate access
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$field_id		= (int)$cid[0];
		$asset = 'com_flexicontent.field.' . $field_id;
		if (!$field_id) {
			$is_authorised = $user->authorise('flexicontent.createfield', 'com_flexicontent');
		} else {
			$is_authorised = $user->authorise('flexicontent.editfield', $asset);
		}
		
		// check access
		if ( !$is_authorised ) {
			JError::raiseWarning( 403, JText::_( 'FLEXI_ALERTNOTAUTH' ) );
			$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', '');
			return;
		}
		
		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::_( 'FLEXI_EDITED_BY_ANOTHER_ADMIN' );
			$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg);
			return;
		}

		$model->checkout( $user->get('id') );

		parent::display();
	}


	/**
	 * Logic to order up/down a field
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function reorder($dir=null)
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$model 	= $this->getModel('fields');
		$user	=& JFactory::getUser();

		// calculate access
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$field_id		= (int)$cid[0];
		$is_authorised = $user->authorise('flexicontent.orderfields', 'com_flexicontent');

		// check access
		if ( !$is_authorised ) {
			JError::raiseWarning( 403, JText::_( 'FLEXI_ALERTNOTAUTH' ) );
		} else if ( $model->move($dir) ){
			// success
		} else {
			$msg = JText::_( 'FLEXI_ERROR_REORDERING_FIELD' );
			JError::raiseWarning( 500, $model->getError() );
		}

		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields');
	}



	/**
	 * Logic to orderup a field
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function orderup()
	{
		$this->reorder($dir=-1);
	}

	/**
	 * Logic to orderdown a field
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function orderdown()
	{
		$model->reorder($dir=1);
	}

	/**
	 * Logic to mass ordering fields
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$order 	= JRequest::getVar( 'order', array(0), 'post', 'array' );
		
		$model = $this->getModel('fields');
		if(!$model->saveorder($cid, $order)) {
			$msg = JText::_( 'FLEXI_OPERATION_FAILED' );
			JError::raiseWarning( 500, $model->getError() );
		} else {
			$msg = JText::_( 'FLEXI_NEW_ORDERING_SAVED' );
		}

		$this->setRedirect( 'index.php?option=com_flexicontent&view=fields', $msg );
	}

	/**
	 * Logic to set the access level of the Fields
	 *
	 * @access public
	 * @return void
	 * @since 1.5
	 */
	function access( )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cid		= JRequest::getVar( 'cid', array(0), 'default', 'array' );
		$id		= (int)$cid[0];
		$accesses	= JRequest::getVar( 'access', array(0), 'post', 'array' );
		$access = $accesses[$id];

		$model = $this->getModel('fields');
		
		if(!$model->saveaccess( $id, $access )) {
			$msg = JText::_( 'FLEXI_OPERATION_FAILED' );
			JError::raiseWarning( 500, $model->getError() );
		} else {
			$msg = '';
			$cache = &JFactory::getCache('com_flexicontent');
			$cache->clean();
		}
		
		$this->setRedirect('index.php?option=com_flexicontent&view=fields', $msg);
	}

	/**
	 * Logic to copy the fields
	 *
	 * @access public
	 * @return void
	 * @since 1.5
	 */
	function copy( )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		$cid = JRequest::getVar( 'cid', array(0), 'default', 'array' );
		
		$cf = 0;
		$ncf = 0;
		foreach ($cid as $id) {
			if ($id < 15) {
				$cf++;
			} else {
				$ncf++;
			}
		}

		$model = $this->getModel('fields');
		
		if(!$model->copy( $cid )) {
			$msg = JText::_( 'FLEXI_FIELDS_COPY_FAILED' );
			JError::raiseWarning( 500, $model->getError() );
		} else {
			$msg = '';
			if ($ncf) {
				$msg .= JText::sprintf('FLEXI_FIELDS_COPY_SUCCESS', $ncf) . ' ';
			}
			if ($cf) {
				$msg .= JText::sprintf('FLEXI_FIELDS_CORE_FIELDS_NOT_COPIED', $cf);
			}
			$cache = &JFactory::getCache('com_flexicontent');
			$cache->clean();
		}
		
		$this->setRedirect('index.php?option=com_flexicontent&view=fields', $msg );
	}
}
