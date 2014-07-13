<?php

/**
* GK Tab - content template
* @package Joomla!
* @Copyright (C) 2009-2011 Gavick.com
* @ All rights reserved
* @ Joomla! is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: GK4 1.0 $
**/

// access restriction
defined('_JEXEC') or die('Restricted access');

?>

<div class="gkTab" id="<?php echo $this->config['module_id'];?>">
	<div class="gkTabWrap">
	    <?php if($this->config['tabs_position'] == 'top') : ?>
	    <div class="gkTabsWrapper">
		    <ul class="gkTabs <?php echo $this->config['tabs_position']; ?>">
		    	<?php for($i = 0; $i < count($this->tabs_titles); $i++) : ?>
		    	<?php $active_class = ($active_tab == $i + 1) ? $active_class = ' active"' : '"'; ?>
		    	<li <?php echo 'class="gkTab-'.($i+1) . $active_class; ?>><span><?php echo $this->tabs_titles[$i]; ?></span></li>
		    	<?php endfor; ?>
		    </ul>
	    </div>
	    <?php endif; ?>
	                
		<div class="gkTabContainer0">
	        <div class="gkTabContainer1">
	            <div class="gkTabContainer2">
	                <?php $this->moduleRender($active_tab); ?>
	            </div>
	        </div>
	    </div>
		
		
		<?php if($this->config['tabs_position'] == 'bottom') : ?>
		<div class="gkTabsWrapper">
			<ul class="gkTabs <?php echo $this->config['tabs_position']; ?>">
				<?php for($i = 0; $i < count($this->tabs_titles); $i++) : ?>
				<?php $active_class = ($active_tab == $i + 1) ? $active_class = ' active"' : '"'; ?>
				<li <?php echo 'class="gkTab-'.($i+1) . $active_class; ?>><span><?php echo $this->tabs_titles[$i]; ?></span></li>
				<?php endfor; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>
		
	<?php if($this->config['buttons'] == 1) : ?>
	<div class="gkTabButtonNext">next</div>
	<div class="gkTabButtonPrev">prev</div>
	<?php endif; ?>
</div>