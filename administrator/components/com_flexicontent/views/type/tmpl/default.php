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

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td valign="top" style="width:50%;">
				<table  class="admintable">
					<tr>
						<td class="key">
							<label for="name">
								<?php echo JText::_( 'FLEXI_TYPE_NAME' ).':'; ?>
							</label>
						</td>
						<td>
							<input id="name" name="name" class="required" value="<?php echo $this->row->name; ?>" size="50" maxlength="100" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="published">
								<?php echo JText::_( 'FLEXI_PUBLISHED' ).':'; ?>
							</label>
						</td>
						<td>
							<?php
							$html = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->row->published );
							echo $html;
							?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="name">
								<?php echo JText::_( 'FLEXI_ALIAS' ).':'; ?>
							</label>
						</td>
						<td>
							<input id="name" name="alias" value="<?php echo $this->row->alias; ?>" size="50" maxlength="100" />
						</td>
					</tr>
					
					<?php if (FLEXI_ACCESS || FLEXI_J16GE) : ?>
					<tr>
						<td class="key">
							<label for="access" class="hasTip" title="<?php echo JText::_('FLEXI_PERMIT_SUBMISSION').'::'.JText::_('FLEXI_PERMIT_SUBMISSION_DESC');?>">
								<?php echo JText::_( 'FLEXI_PERMIT_SUBMISSION' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->lists['itemscreatable']; ?>
						</td>
					</tr>
					<?php endif; ?>
					
					<?php if (!FLEXI_ACCESS || FLEXI_J16GE) : ?>
					<tr>
						<td class="key">
							<label for="access" class="hasTip" title="<?php echo JText::_('FLEXI_ACCESS_LEVEL').'::'.JText::_('FLEXI_FIELD_ACCESSLEVEL_DESC');?>">
								<?php echo JText::_( 'FLEXI_ACCESS_LEVEL' ); ?>
							</label>
						</td>
						<td>
							<?php echo $this->lists['access']; ?>
						</td>
					</tr>
					<?php endif; ?>
				</table>
				
			<?php
			if (FLEXI_ACCESS) :
				$this->document->addScriptDeclaration("
					window.addEvent('domready', function() {
						var slideaccess = new Fx.Slide('tabacces');
						var slidenoaccess = new Fx.Slide('notabacces');
						slideaccess.hide();
						$$('fieldset.flexiaccess legend').addEvent('click', function(ev) {
							slideaccess.toggle();
							slidenoaccess.toggle();
						});
					});
				");
			?>
			<fieldset class="flexiaccess">
				<legend><?php echo JText::_( 'FLEXI_RIGHTS_MANAGEMENT' ); ?></legend>
				<table id="tabacces" class="admintable" width="100%">
					<tr>
						<td>
							<div id="access"><?php echo $this->lists['access']; ?></div>
						</td>
					</tr>
				</table>
				<div id="notabacces">
					<?php echo JText::_( 'FLEXI_RIGHTS_MANAGEMENT_DESC' ); ?>
				</div>
			</fieldset>
		<?php endif; ?>
			
			</td>
			<td valign="top" style="width:50%; padding: 7px 0 0 5px" align="left">
				<?php
				echo JText::_('FLEXI_ITEM_PARAM_OVERRIDE_ORDER_DETAILS');
				$title = JText::_( 'FLEXI_PARAMETERS' );
				echo $this->pane->startPane( 'det-pane' );
				echo $this->pane->startPanel( $title, "params-page" );
				echo $this->form->render('params');
				echo $this->pane->endPanel();
				
				echo '<span class="fc-note fc-mssg-inline" style="margin: 8px 0px!important;">' . JText::_( 'FLEXI_PARAMETERS_LAYOUT_EXPLANATION' ) . '</span>';
				
				echo $this->form->render('params', 'themes');
				
				foreach ($this->tmpls as $tmpl) {
					$title = JText::_( 'FLEXI_PARAMETERS_THEMES_SPECIFIC' ) . ' : ' . $tmpl->name;
					echo $this->pane->startPanel( $title, "params-".$tmpl->name );
					echo
						str_replace('id="layouts', 'id="layouts_'.$tmpl->name.'_', 
							str_replace('for="layouts', 'for="layouts_'.$tmpl->name.'_', 
								str_replace('name="layouts[', 'name="layouts['.$tmpl->name.'][',
									$tmpl->params->render('layouts')
								)
							)
						);
					echo $this->pane->endPanel();
				}

				echo $this->pane->endPane();
				?>
			</td>
		</tr>
	</table>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_flexicontent" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="controller" value="types" />
<input type="hidden" name="view" value="type" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>