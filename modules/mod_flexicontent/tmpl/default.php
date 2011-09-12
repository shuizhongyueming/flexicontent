<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($add_tooltips)
	JHTML::_('behavior.tooltip');
?>

<?php
$ord_titles = array(
	'popular'=>JText::_( 'FLEXI_MOST_POPULAR'),
	'commented'=>JText::_( 'FLEXI_MOST_COMMENTED'),
	'rated'=>JText::_( 'FLEXI_BEST_RATED' ),
	'added'=>	JText::_( 'FLEXI_RECENTLY_ADDED'),
	'updated'=>JText::_( 'FLEXI_RECENTLY_UPDATED'),
	'alpha'=>	JText::_( 'FLEXI_ALPHABETICAL'),
	'alpharev'=>JText::_( 'FLEXI_ALPHABETICAL_REVERSE'),
	'catorder'=>JText::_( 'FLEXI_CAT_ORDER'),
	'random'=>JText::_( 'FLEXI_RANDOM' ) );

$separator = "";
	
foreach ($ordering as $ord) :
	echo $separator;
  if (isset($list[$ord]['featured']) || isset($list[$ord]['standard'])) {
	  $separator = "<hr>";
  } else {
	  $separator = "";
	}
	
	if ($ordering_addtitle && $ord) echo "<div class='mod_flexicontent_order_group_title'> ".$ord_titles[$ord]." </div>";
		
	if (isset($list[$ord]['featured'])) :
?>
<ul class="mod_flexicontent<?php echo $params->get('moduleclass_sfx'); ?> mod_flexicontent_featured">
<?php foreach ($list[$ord]['featured'] as $item) : ?>
	<li>
		<?php if ($add_tooltips) : ?>
		<a href="<?php echo $item->link; ?>" class="hasTip" title="<?php echo htmlspecialchars($item->fulltitle, ENT_COMPAT, "UTF-8").'::'.htmlspecialchars($item->text, ENT_COMPAT, "UTF-8"); ?>">
			<?php echo $item->title; ?>
		</a>
		<?php else : ?>
		<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php
	endif;
	if (isset($list[$ord]['standard'])) :
?>
<ul class="mod_flexicontent<?php echo $params->get('moduleclass_sfx'); ?> mod_flexicontent_standard">
<?php foreach ($list[$ord]['standard'] as $item) : ?>
	<li>
		<?php if ($add_tooltips) : ?>
		<a href="<?php echo $item->link; ?>" class="hasTip" title="<?php echo htmlspecialchars($item->fulltitle, ENT_COMPAT, "UTF-8").'::'.htmlspecialchars($item->text, ENT_COMPAT, "UTF-8"); ?>">
			<?php echo $item->title; ?>
		</a>
		<?php else : ?>
		<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php
	endif;
endforeach;
?>