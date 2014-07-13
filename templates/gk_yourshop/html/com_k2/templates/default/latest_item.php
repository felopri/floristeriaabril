<?php

/**
 * @version		$Id: latest_item.php 785 2011-04-28 12:39:17Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<!-- Start K2 Item Layout -->
<div class="latestItemView">
	<div class="itemContainer">
		<div class="catItemView clearfix">
			<div class="catItemContent">
			<!-- Plugins: BeforeDisplay -->
			<?php echo $this->item->event->BeforeDisplay; ?>
			<!-- K2 Plugins: K2BeforeDisplay -->
			<?php echo $this->item->event->K2BeforeDisplay; ?>
			
			<div class="catItemHeader">
			  <?php if($this->item->params->get('latestItemTitle')): ?>
			  <!-- Item title -->
			  <h2 class="catItemTitle">
			  	<?php if ($this->item->params->get('latestItemTitleLinked')): ?>
				<a href="<?php echo $this->item->link; ?>"><?php echo $this->item->title; ?></a>
			  	<?php else: ?>
			  		<?php echo $this->item->title; ?>
			  	<?php endif; ?>
			  </h2>
			  <?php endif; ?>
			  
			  <div class="catItemAdditionalInfo">
			  	<?php if($this->item->params->get('catItemDateCreated')): ?>
			  	<!-- Date created -->
			  	<div class="itemDate">
			  		<?php echo JHTML::_('date', $this->item->created , JText::_('l, d F Y')); ?>
			  	</div>
			  	<?php endif; ?>
			  	
			  	<?php if($this->item->params->get('latestItemCategory')): ?>
			  	<!-- Item category name -->
			  	<div class="catItemCategory">
			  		<span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
			  		<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->name; ?></a>
			  	</div>
			  	<?php endif; ?>
			  	
			  	<?php if($this->item->params->get('latestItemCommentsAnchor') && ( ($this->item->params->get('comments') == '2' && !$this->user->guest) || ($this->item->params->get('comments') == '1')) ): ?>
			  		<?php if(!empty($this->item->event->K2CommentsCounter)):?>
			  			<!-- K2 Plugins: K2CommentsCounter -->
			  			<?php echo $this->item->event->K2CommentsCounter; ?>
			  		<?php else: ?>
			  			<?php if($this->item->numOfComments > 0): ?>
			  			<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor" class="catComments">
			  				<?php echo $this->item->numOfComments; ?> <?php echo ($this->item->numOfComments>1) ? JText::_('K2_COMMENTS') : JText::_('K2_COMMENT'); ?>
			  			</a>
			  			<?php else: ?>
			  			<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor" class="catComments">
			  				<?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?>
			  			</a>
			  			<?php endif; ?>
			  		<?php endif; ?>
			  	<?php endif; ?>
			  </div>
		  </div>
		
		  <!-- Plugins: AfterDisplayTitle -->
		  <?php echo $this->item->event->AfterDisplayTitle; ?>
		  <!-- K2 Plugins: K2AfterDisplayTitle -->
		  <?php echo $this->item->event->K2AfterDisplayTitle; ?>
		
		<?php if($this->item->params->get('latestItemImage') && !empty($this->item->image)): ?>
		<!-- Item Image -->
		<div class="catItemImageBlock">
			  <span class="catItemImage">
			   <a href="<?php echo $this->item->link; ?>" title="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>">
		    	<img src="<?php echo $this->item->image; ?>" alt="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>" style="width:<?php echo $this->item->imageWidth; ?>px;height:auto;" />
		    </a>
			  </span>
		</div>
		<?php endif; ?>
		
		  <div class="catItemBody">
			  <!-- Plugins: BeforeDisplayContent -->
			  <?php echo $this->item->event->BeforeDisplayContent; ?>
			  <!-- K2 Plugins: K2BeforeDisplayContent -->
			  <?php echo $this->item->event->K2BeforeDisplayContent; ?>
	
			  <?php if($this->item->params->get('latestItemIntroText')): ?>
			  <!-- Item introtext -->
			  <div class="catItemIntroText">
			  	<?php echo $this->item->introtext; ?>
			  </div>
			  <?php endif; ?>
			  <!-- Plugins: AfterDisplayContent -->
			  <?php echo $this->item->event->AfterDisplayContent; ?>
			  <!-- K2 Plugins: K2AfterDisplayContent -->
			  <?php echo $this->item->event->K2AfterDisplayContent; ?>
		  </div>
		
		  <?php if($this->params->get('latestItemVideo') && !empty($this->item->video)): ?>
		  <!-- Item video -->
		  <div class="catItemVideoBlock">
		  	<h3><?php echo JText::_('K2_RELATED_VIDEO'); ?></h3>
			  <span class="catItemVideo<?php if($this->item->videoType=='embedded'): ?> embedded<?php endif; ?>"><?php echo $this->item->video; ?></span>
		  </div>
		  <?php endif; ?>
		  <?php if($this->item->params->get('latestItemTags')): ?>
			  	<div class="catItemLinks">
			  		  <?php if($this->item->params->get('latestItemTags') && count($this->item->tags)): ?>
			  		  <!-- Item tags -->
			  		  <div class="catItemTagsBlock">
			  			  <span><?php echo JText::_('K2_TAGGED_UNDER'); ?></span>
			  			  <ul class="catItemTags">
			  			    <?php foreach ($this->item->tags as $tag): ?>
			  			    <li><a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a></li>
			  			    <?php endforeach; ?>
			  			  </ul>
			  		  </div>
			  		  <?php endif; ?>
			  	</div>
			  	<?php endif; ?>
		  <?php if ($this->item->params->get('latestItemReadMore')): ?>
		  <!-- Item "read more..." link -->
		  <div class="catItemReadMore">
		  	<a class="k2ReadMore" href="<?php echo $this->item->link; ?>">
		  		<?php echo JText::_('K2_READ_MORE'); ?>
		  	</a>
		  </div>
		  <?php endif; ?>
		  	<!-- Plugins: AfterDisplay -->
		  	<?php echo $this->item->event->AfterDisplay; ?>
		  	<!-- K2 Plugins: K2AfterDisplay -->
		  	<?php echo $this->item->event->K2AfterDisplay; ?>
	  </div>


			
		</div>
	</div>
</div>
<!-- End K2 Item Layout -->