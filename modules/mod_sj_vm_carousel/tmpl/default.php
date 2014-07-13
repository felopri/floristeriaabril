<?php
/**
 * @package Sj Carousel for Virtuemart
 * @version 1.0.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */

defined( '_JEXEC' ) or die;

JHtml::stylesheet('modules/'.$module->module.'/assets/css/style.css');
if(class_exists('vmJsApi')){
    vmJsApi::jQuery();
}else {
    if( !defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1" ){
        JHtml::script('modules/'.$module->module.'/assets/js/jquery-1.8.2.min.js');
        JHtml::script('modules/'.$module->module.'/assets/js/jquery-noconflict.js');
        define('SMART_JQUERY', 1);
    }

}
JHtml::script('modules/'.$module->module.'/assets/js/transition.js');
JHtml::script('modules/'.$module->module.'/assets/js/carousel.js');

$count_item = count($list);

ImageHelper::setDefault($params);
$currency = CurrencyDisplay::getInstance();
$tag_id = 'sj_vm_carousel_'.rand().time();

$interval = $params->get('interval');
$play = (int)$params->get('play',1);
$interval = ($play == 1)?$interval:0;

$start = $params->get('start',1);
$start = ($start <=0 || $start > count($list))?0:$start-1;

$pause_hover = $params->get('pause_hover', 'hover');
?>
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

<?php if($params->get('pretext') != ' ') { ?>
    <div class="pre-text"><?php echo $params->get('pretext'); ?></div>
<?php } ?>

<?php if(!empty($list)) { ?>

<div id="<?php echo $tag_id;?>" class="sj-carousel slide">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <?php for($j=0; $j<$count_item;$j++) { ?>
            <li data-target="#<?php echo $tag_id;?>" data-slide-to="<?php echo $j;?>" class="<?php echo $j== $start ?" active ":"" ;?>"></li>
        <?php }?>
    </ol>
    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <?php $i=-1; foreach($list as $item) { $i++;
            $class = ($i == $start)?" active":"";
            ?>
            <div class="item <?php echo $class; ?>">

                <div class="carousel-image">
                    <?php $img = SjCarouselHelper::getVmImage($item, $params);
                    if($img){
                        ?>
                        <a href="<?php echo $item->link;?>" title="<?php echo $item->title ?>" <?php echo SjCarouselHelper::parseTarget($params->get('item_link_target')); ?>>
                            <?php   echo SjCarouselHelper::imageTag($img);?>
                        </a>
                    <?php } ?>
                </div>

                <div class="carousel-caption">

                        <?php if( $params->get( 'item_title_display' ) == 1) { ?>
                            <div class="carousel-title">
                                <a href="<?php echo $item->link;?>" title="<?php echo $item->title ?>" <?php echo SjCarouselHelper::parseTarget($params->get('item_link_target')); ?>>
                                    <?php echo SjCarouselHelper::truncate($item->title, $params->get('item_title_max_characs'));?>
                                </a>
                            </div>
                        <?php } ?>

                        <?php if((int)$params->get('item_price_display',1)){ ?>
                            <div class="carousel-price">
                                <?php
                                if (!empty($item->prices['salesPrice'])) {
                                    echo $currency->createPriceDiv ('salesPrice', JText::_( "Price: " ), $item->prices, false, false, 1.0, true);
                                }
                                if (!empty($item->prices['salesPriceWithDiscount'])) {
                                    $currency = CurrencyDisplay::getInstance( );
                                    echo $currency->createPriceDiv ('salesPriceWithDiscount', JText::_( "Price: " ), $item->prices, false, false, 1.0, true);
                                }?>
                            </div>
                        <?php } ?>

                         <?php if( $params->get('item_desc_display') == 1 ){?>

                            <div class="carousel-desc">
                               <?php echo $item->_description;?>
                            </div>

                             <?php if( $params->get('item_readmore_display') == 1 ){?>
                                 <div class="carousel-readmore">
                                     <a href="<?php echo $item->link;?>" title="<?php echo $item->title ?>" <?php echo SjCarouselHelper::parseTarget($params->get('item_link_target')); ?>>
                                         <?php echo $params->get('item_readmore_text');?>
                                     </a>
                                 </div>
                             <?php }?>

                         <?php }?>



                </div>
            </div>
        <?php }?>
    </div>
    <a class="left carousel-control" href="#<?php echo $tag_id;?>" data-slide="prev">
        &lsaquo;
    </a>
    <a class="right1 carousel-control" href="#<?php echo $tag_id;?>" data-slide="next">
        &rsaquo;
    </a>
</div>

<?php }
else {  echo JText::_('Has no content to show!'); ?>
<?php }?>

<?php if($params->get('posttext') != '') {  ?>
    <div class="post-text"><?php echo $params->get('posttext'); ?></div>
<?php }?>

<script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready(function($){
        $( "#<?php echo $tag_id;?>" ).carouselMo({
            interval: <?php echo $interval;?>,
            pause: '<?php echo ($pause_hover == 'hover')?"hover":"null"; ?>',
            start: <?php echo $start;?>
            });
    });
    //]]>
</script>

