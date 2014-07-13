<?php
/**
 * @package Sj Carousel 
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;
JHtml::stylesheet('modules/mod_sj_carousel/assets/css/mod_sj_carousel.css');
if( !defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1" ){
	JHtml::script('modules/mod_sj_carousel/assets/js/jquery-1.8.2.min.js');
	JHtml::script('modules/mod_sj_carousel/assets/js/jquery-noconflict.js');
	define('SMART_JQUERY', 1);
}
JHtml::script('modules/mod_sj_carousel/assets/js/bootstrap-carousel.js');
ImageHelper::setDefault($params);
$options=$params->toObject();

$count = (int)$options->count;
if ( $count <= (int)count($list) ) {
	$count = (int)count($list);
}?>

<script type="text/javascript">
window.addEvent("domready", function(){
	if (typeof jQuery != "undefined" && typeof MooTools != "undefined" ) {
		Element.implement({
			slide: function(how, mode){
				return this;
			}
		});
	}
});
</script>
	
<?php if(!empty($list)){?>
	<?php if(!empty($options->pretext)) { ?>
	<div class="pre-text">
		<?php echo $options->pretext; ?>
	</div>
	<?php } ?>
    <div id="myCarousel" class="carousel slide"><!-- Carousel items -->
		<ol class="carousel-indicators">
			<?php for( $j= 0; $j<$count; $j++){?>
				<li data-target="#myCarousel" data-slide-to="<?php echo $j;?>" class="<?php if($j == 0){echo "active";}?>"></li>
			<?php }?>
		</ol>   
	    <div class="carousel-inner">
	    <?php $i=0; foreach($list as $item){$i++;?>
		    <div class="<?php if($i==1){echo "active";}?> item">
	    		<?php $img = SjCarouselHelper::getAImage($item, $params);
    				echo SjCarouselHelper::imageTag($img);?>
    			<?php if( $options->item_title_display == 1 || $options->show_introtext == 1 ){?>		    
	    		<div class="carousel-caption">
	    		<?php if( $options->item_title_display == 1 ){?>
		    		<h4>
			    		<a href="<?php echo $item->link ?>" <?php echo SjCarouselHelper::parseTarget($options->item_link_target);?>>
			    			<?php echo $item->title;?>
			    		</a>
		    		</h4>
	    		<?php }if( $options->show_introtext == 1 ){?>
		    		<p><?php echo $item->displayIntrotext;?></p>
	    		<?php }?>
	    		</div>
	    		<?php }?>
		    </div>
	    <?php }?>
	    </div><!-- Carousel nav -->
	    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
	    <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
    </div>
	<?php if(!empty($options->posttext)) {  ?>
	<div class="post-text">
		<?php echo $options->posttext; ?>
	</div>
<?php }}else{ echo JText::_('Has no content to show!');}?>
<?php $auto = $options->play;
	$interval = $options->interval;
	if($auto == 0){
		$interval = 0;
	}else {
		$interval = $options->interval;
	}	
?>  
<script>
//<![CDATA[    					
	jQuery(function($){
	    $('.carousel').carousel({
		    interval: <?php echo $interval;?>,
		    pause:'<?php echo $options->pause_hover;?>'
	    })
	});
//]]>	
</script>    
