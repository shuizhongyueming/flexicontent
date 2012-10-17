<?php
/**
 * HTML5 Template
 * @version 1.5 stable $Id: category_items_html5.php 0001 2012-09-23 14:00:28Z Rehne $
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
// first define the template name
$tmpl = $this->tmpl;
?>
<script type="text/javascript">
	function tableOrdering( order, dir, task )
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		
		var form = document.getElementById("adminForm");
		
		adminFormPrepare(form);
		form.submit( task );
	}
	
	function adminFormPrepare(form) {
		var extra_action = '';
		var var_sep = form.action.match(/\?/) ? '&' : '?';
		
		for(i=0; i<form.elements.length; i++) {
			
			var element = form.elements[i];
			
			// No need to add the default values for ordering, to the URL
			if (element.name=='filter_order' && element.value=='i.title') continue;
			if (element.name=='filter_order_Dir' && element.value=='ASC') continue;
			
			var matches = element.name.match(/(filter[.]*|letter|clayout)/);
			if (matches && element.value != '') {
			  extra_action += var_sep + element.name + '=' + element.value;
			  var_sep = '&';
			}
		}
		form.action += extra_action;   //alert(extra_action);
	}
	
	function adminFormClearFilters (form) {
		for(i=0; i<form.elements.length; i++) {
			var element = form.elements[i];
			
			if (element.name=='filter_order') {	element.value=='i.title'; continue; }
			if (element.name=='filter_order_Dir') { element.value=='ASC'; continue; }
			
			var matches = element.name.match(/(filter[.]*|letter)/);
			if (matches) {
				element.value = '';
			}
		}
	}
</script>

<?php if ((($this->params->get('use_filters', 0)) && $this->filters) || ($this->params->get('use_search')) || ($this->params->get('show_alpha', 1))) : ?>
<aside class="group">
	<form action="<?php echo htmlentities($this->action); ?>" method="POST" id="adminForm" onsubmit="" class="group well">

	<?php if ( JRequest::getVar('clayout') == $this->params->get('clayout', 'blog') ) :?>
	<input type="hidden" name="clayout" value="<?php echo JRequest::getVar('clayout'); ?>" />
	<?php endif; ?>

	<?php if ((($this->params->get('use_filters', 0)) && $this->filters) || ($this->params->get('use_search'))) : ?>
	<div id="fc_filter" class="floattext control-group group">
		<?php if ($this->params->get('use_search')) : ?>
		<div class="fc_fleft">
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="text_area input-medium search-query" />
			<?php if ( $this->params->get('show_filter_labels', 0) && $this->params->get('use_filters', 0) && $this->filters ) : ?><br /><?php endif; ?>
			<button class="fc_button btn" onclick="var form=document.getElementById('adminForm');                               adminFormPrepare(form);"><i class="icon-search"></i><?php echo JText::_( 'FLEXI_GO' ); ?></button>
			<button class="fc_button btn" onclick="var form=document.getElementById('adminForm'); adminFormClearFilters(form);  adminFormPrepare(form);"><i class="icon-refresh"></i><?php echo JText::_( 'FLEXI_RESET' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('use_filters', 0) && $this->filters) : ?>
            <!--div class="fc_fright"-->
            <?php
            foreach ($this->filters as $filt) :
                if (empty($filt->html)) continue;
                // Add form preparation
                if ( preg_match('/onchange[ ]*=[ ]*([\'"])/i', $filt->html, $matches) ) {
                    $filt->html = preg_replace('/onchange[ ]*=[ ]*([\'"])/i', 'onchange=${1}adminFormPrepare(document.getElementById(\'adminForm\'));', $filt->html);
                } else {
                    $filt->html = preg_replace('/<(select|input)/i', '<${1} onchange="adminFormPrepare(document.getElementById(\'adminForm\'));"', $filt->html);
                }
                ?>
                <span class="filter" style="white-space: nowrap;">
                    <?php if ( $this->params->get('show_filter_labels', 0) ) : ?>
                    <span class="filter_label">
                    <?php echo $filt->label; ?>
                    </span>
                    <?php endif; ?>
                    <span class="filter_field">
                    <?php echo $filt->html; ?>
                    </span>
                </span>
            <?php endforeach; ?>
        
            <?php if (!$this->params->get('use_search')) : ?>
            <button class="btn" onclick="var form=document.getElementById('adminForm'); adminFormClearFilters(form);  adminFormPrepare(form);"><i class="icon-refresh"></i><?php echo JText::_( 'FLEXI_RESET' ); ?></button>
            <?php endif; ?>
            <!--/div-->
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php
    if ($this->params->get('show_alpha', 1)) :
        echo $this->loadTemplate('alpha_html5');
    endif;
    ?>
    <input type="hidden" name="option" value="com_flexicontent" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="" />
    <input type="hidden" name="view" value="category" />
    <input type="hidden" name="letter" value="<?php echo JRequest::getVar('letter');?>" id="alpha_index" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
    <input type="hidden" name="cid" value="<?php echo $this->category->id; ?>" />
</form>
<?php endif; ?>

	<?php
    if ($this->items) :
        $currcatid = $this->category->id;
        $cat_items[$currcatid] = array();
        $sub_cats[$currcatid] = & $this->category;
        foreach ($this->categories as $subindex => $sub) :
            $cat_items[$sub->id] = array();
            $sub_cats[$sub->id] = & $this->categories[$subindex];
        endforeach;
        
        $items = & $this->items;
        for ($i=0; $i<count($items); $i++) :
            foreach ($items[$i]->cats as $cat) :
                if (isset($cat_items[$cat->id])) :
                    $cat_items[$cat->id][] = & $items[$i];
                endif;
            endforeach;
        endfor;
    
        // routine to determine all used columns for this table
        $layout = $this->params->get('clayout', 'default');
        $fbypos		= flexicontent_tmpl::getFieldsByPositions($layout, 'category');
        $columns['aftertitle'] = array();
        foreach ($this->items as $item) :
            if (isset($item->positions['aftertitle'])) :
                foreach ($fbypos['aftertitle']->fields as $f) :
                    if (!in_array($f, $columns['aftertitle'])) :
                        $columns['aftertitle'][$f] = @$item->fields[$f]->label;
                    endif;
                endforeach;
            endif;
        endforeach;
    
    $classnum = '';
	$classspan = ''; // bootstrap span
    if ($this->params->get('tmpl_cols', 2) == 1) :
       $classnum = 'one';
	   $classspan = 'span12';
    elseif ($this->params->get('tmpl_cols', 2) == 2) :
       $classnum = 'two';
	   $classspan = 'span6';
    elseif ($this->params->get('tmpl_cols', 2) == 3) :
       $classnum = 'three';
	   $classspan = 'span4';
    elseif ($this->params->get('tmpl_cols', 2) == 4) :
       $classnum = 'four';
	   $classspan = 'span3';
    endif;
    ?>


    <!-- BOF items total-->
    <?php if ($this->params->get('show_item_total', 1)) : ?>
    <div id="item_total" class="item_total group">
        <?php	//echo $this->pageNav->getResultsCounter(); // Alternative way of displaying total (via joomla pagination class) ?>
        <?php echo $this->resultsCounter; // custom Results Counter ?>
    </div>
    <?php endif; ?>
    <!-- BOF items total-->
</aside>

<ul class="faqblock <?php echo $classnum; ?> row group">	

<?php
global $globalcats;
$count_cat = -1;
foreach ($cat_items as $catid => $items) :
	$sub = & $sub_cats[$catid];
	if (count($items)==0) continue;
	if ($catid!=$currcatid) $count_cat++;
?>

		<li class="<?php echo $catid==$currcatid ? 'full' : ($count_cat%2 ? 'even' : 'odd'); ?> <?php echo $classspan; ?>">
		<section class="group">	
		<!-- BOF subcategory title -->
		<header class="flexi-cat group">
			<a href="<?php echo JRoute::_( FlexicontentHelperRoute::getCategoryRoute($sub->slug) ); ?>">
				<?php if (!empty($sub->image) && $this->params->get('show_description_image', 1)) : ?>
				<figure class="catimg">
					<img src="images/stories/<?php echo $sub->image ?>" alt="<?php echo $this->escape($sub->title) ?>" height="24" />
				</figure>
				<?php endif; ?>				
				<?php
					echo '<h1>'.$sub->title.'</h1>';
					if ($catid!=$currcatid) {
						$subsubcount = count($sub->subcats);
						if ($this->params->get('show_itemcount', 1)) echo ' (' . ($sub->assigneditems != null ? $sub->assigneditems.'/'.$subsubcount : '0/'.$subsubcount) . ')';
					}
				?>
			</a>
		</header>
		<!-- EOF subcategory title -->

		<!-- BOF subcategory description  -->
		<?php if ( ($this->params->get('show_description', 1) && $sub->description != '') ) : ?>
		<div class="catdescription group">
			<?php echo flexicontent_html::striptagsandcut( $sub->description, $this->params->get('description_cut_text', 120) ); ?>
		</div>
		<?php endif; ?>
		<!-- EOF subcategory description -->


		<!-- BOF subcategory items -->
			
<?php
	if (!$this->params->get('show_title', 1) && $this->params->get('limit', 0) && !count($columns['aftertitle'])) :
		echo '<span style="font-weight:bold; color:red;">'.JText::_('FLEXI_TPL_NO_COLUMNS_SELECT_FORCING_DISPLAY_ITEM_TITLE').'</span>';
		$this->params->set('show_title', 1);
	endif;
?>

		<?php if ( $this->params->get('show_title', 1) || count($columns['aftertitle']) ) : ?>
			<div class="group">
			<ul class="flexi-itemlist">
			<?php foreach ($items as $item) : ?>
				<li class="flexi-item">
					
				  <!-- BOF beforeDisplayContent -->
				  <?php if ($item->event->beforeDisplayContent) : ?>
						<span class="fc_beforeDisplayContent group">
							<?php echo $item->event->beforeDisplayContent; ?>
						</span>
					<?php endif; ?>
				  <!-- EOF beforeDisplayContent -->

				<?php if ($this->params->get('show_editbutton', 0)) : ?>
					<?php $editbutton = flexicontent_html::editbutton( $item, $this->params ); ?>
					<?php if ($editbutton) : ?>
						<div style="float:left;"><?php echo $editbutton;?></div>
					<?php endif; ?>
				<?php endif; ?>
					
				<!-- BOF item title -->
				<ul class="flexi-fieldlist">
				<?php if ($this->params->get('show_title', 1)) : ?>
		   		<li class="flexi-field flexi-title"><i class="icon-arrow-right"></i>
		   			<?php if ($this->params->get('link_titles', 0)) : ?>
		   			<a class="fc_item_title" href="<?php echo JRoute::_(FlexicontentHelperRoute::getItemRoute($item->slug, $item->categoryslug)); ?>"><?php echo $item->title; ?></a>
		   			<?php
		   			else :
		   			echo $item->title;
		   			endif;
		    		?>
                    <!-- BOF afterDisplayTitle -->
                    <?php if ($item->event->afterDisplayTitle) : ?>
                        <div class="fc_afterDisplayTitle group">
                            <?php echo $item->event->afterDisplayTitle; ?>
                        </div>
                    <?php endif; ?>
                    <!-- EOF afterDisplayTitle -->
				</li>
				<?php endif; ?>
				<!-- BOF item title -->
						  
				<!-- BOF item fields block aftertitle -->
				<?php
				foreach ($columns['aftertitle'] as $name => $label) :
					$label_str = '';
					if ($item->fields[$name]->parameters->get('display_label', 0)) :
						$label_str = $label.': ';
					endif; ?>
					<li class="flexi-field">
					<?php echo $label_str.( isset($item->positions['aftertitle']->{$name}->display) ? $item->positions['aftertitle']->{$name}->display : ''); ?>
					</li>
				<?php endforeach; ?>
				</ul>
				<!-- EOF item fields block aftertitle -->
			    
		    	<!-- BOF afterDisplayContent -->
		    	<?php if ($item->event->afterDisplayContent) : ?>
					<div class="afterDisplayContent group">
						<?php echo $item->event->afterDisplayContent; ?>
					</div>
				<?php endif; ?>
		    	<!-- EOF afterDisplayContent -->
				
				</li>
			<?php endforeach; ?>
			</ul>
            </div>
			
		<?php endif; ?>
		</section>	
		</li>
		<!-- EOF subcategory items -->
		
<?php endforeach; ?>

</ul>

<?php elseif ($this->getModel()->getState('limit')) : // Check case of creating a category view without items ?>
	<div class="noitems"><?php echo JText::_( 'FLEXI_NO_ITEMS_CAT' ); ?></div>
<?php endif; ?>