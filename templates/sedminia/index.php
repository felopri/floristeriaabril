<?php
/****************************************************
#####################################################
##-------------------------------------------------##
##         SEDMINIA  TEMPLATE                      ##
##-------------------------------------------------##
## Copyright = globbersthemes.com- 2012            ##
## Date      = 	mars 2012                          ##
## Author    = globbers                            ##
## Websites  = http://www.globbersthemes.com       ##
##                                                 ##
#####################################################
****************************************************/

defined('_JEXEC') or die;

/* The following line loads the MooTools JavaScript Library */
JHtml::_('behavior.framework', true);

/* The following line gets the application object for things like displaying the site name */
$app = JFactory::getApplication();
$csite_name	= $app->getCfg('sitename');
$path = $this->baseurl.'/templates/'.$this->template;

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<jdoc:include type="head" />

		<?php //setting caption image
        $caption1 = $this->params->get("caption1", "Every time we embrace...");
        $caption2 = $this->params->get("caption2", "Whenever i look into your eyes...");
		$caption3 = $this->params->get("caption3", "You are always on my mind...");
		$slidedisable	= $this->params->get("slidedisable");
        ?>
		
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/defaut.css" type="text/css" />
		<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/scroll.js"></script>
		<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/jquery.js"></script>
        <script type="text/javascript" src="templates/<?php echo $this->template ?>/js/superfish.js"></script>
        <script type="text/javascript" src="templates/<?php echo $this->template ?>/js/hover.js"></script>	
		<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/slideshow.js"></script>
		<script type="text/javascript" src="templates/<?php echo $this->template ?>/js/DD_roundies_0.0.2a-min.js"></script>
		
		<script type="text/javascript">
        DD_roundies.addRule('.items-leading h2,.items-leading h2 a ,.items-row h2,.items-row h2 a ,.item-page h2,.item-page h2 a ', '5px', true);
		DD_roundies.addRule('.items-leading,.items-row .item, .item-page ,.category-desc ', '5px', true);
		DD_roundies.addRule('#slide,.pagination li a, ul.pagenav li, #footer, #ftb-f', '5px', true);
		
        </script>

	<script type="text/javascript">	
        $(document).ready(function() {
	    $(' .navigation ul  ').superfish({
		  delay:       800,                            
		  animation:   {opacity:'show',height:'show'},  
		  speed:       'normal',                          
		  autoArrows:  false,                           
		  dropShadows: true                           
	   });
	   });
    </script> 

	<script type="text/javascript">
	    $(document).ready(function() {
        $('#s3slider').s3Slider({
        timeOut: 8000 });
        }); 
    </script>

	</head>



<body>
    <div class="pagewidth">
	    <div id="sitename">				    	             	   
		    <a href="index.php"><img src="templates/<?php echo $this->template ?>/images/logo.png" width="322" height="120" alt="logotype" /></a>				            
		</div>
		   <div class="clr"></div>
			<div id="topmenu">
			    <div class="navigation">
                    <jdoc:include type="modules" name="position-1" />
                </div>
			</div>
			<?php $menu = JSite::getMenu(); ?>            
            <?php $lang = JFactory::getLanguage(); ?>            
            <?php if ($menu->getActive() == $menu->getDefault($lang->getTag())) { ?>            
            <?php if ($this->params->get( 'slidedisable' )) : ?>   
            <?php include "slideshow.php"; ?><?php endif; ?>            
             <?php } ?>
			    <div id="main">
				    <jdoc:include type="component" />
				</div>
				    <?php if ($this->countModules('position-3') ||  $this->countModules('position-6')) { ?>
	                    <div id="footer">
		                    <div class="box">
				                <jdoc:include type="modules" name="position-3" style="xhtml" />
			                </div>
			                <div class="box">
				                <jdoc:include type="modules" name="position-6" style="xhtml" />
						    </div>
			            </div>
		            <?php } ?>
					<div id="ftb-f">
			            <div class="ftb">
				            <?php echo date( 'Y' ); ?>&nbsp; <?php echo $csite_name; ?>&nbsp;&nbsp;<?php require("template.php"); ?>
                       </div>
				            <div id="topb">
                                <div class="top_button">
                                    <a href="#" onclick="scrollToTop();return false;">
						            <img src="templates/<?php echo $this->template ?>/images/top.png" width="30" height="30" alt="top" /></a>
                                </div>
			                </div>
                     </div>			
	
	    
    </div>		
						        
</body>
</html>
