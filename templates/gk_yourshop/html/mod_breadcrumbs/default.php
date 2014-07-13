<?php

// no direct access
defined('_JEXEC') or die;

?>

<div class="breadcrumbs<?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1))
	{
        echo "<span class=\"gkBreadcrumbStart\">";
		echo JText::_('TPL_GK_LANG_BREADCRUMBS_HERE');
        echo "</span>";
	}
?>
<?php for ($i = 0; $i < $count; $i ++) :

	// If not the last item in the breadcrumbs add the separator
	if ($i < $count -1) {
		if (!empty($list[$i]->link)) {
			echo '<a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a>';
		} else {
		    echo '<span>';
			echo $list[$i]->name;
			  echo '</span>';
		}
		if($i < $count -2){
			echo ' '.'<span class="pathwaySeprarator"/>'.$separator.'</span> ';
		}
	}  else if ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
		if($i > 0){
			echo ' '.'<span class="pathwaySeprarator"/>'.$separator.'</span> ';
		}
		 echo '<span class="last">';
		echo $list[$i]->name;
		  echo '</span>';
	}
endfor; ?>
</div>