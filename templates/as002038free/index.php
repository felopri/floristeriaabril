<?php 

/*******************************************************************************************/
/*
/*		Designed by 'AS Designing'
/*		Web: http://www.asdesigning.com
/*		Email: info@asdesigning.com
/*		License: GNU/GPL
/*
/*******************************************************************************************/

defined( '_JEXEC' ) or die( 'Restricted access' );

/* The following line loads the MooTools JavaScript Library */
JHTML::_('behavior.framework', true);

/* The following line gets the application object for things like displaying the site name */
$app = JFactory::getApplication();

/* The following lines get active menu */
$menu = $app->getMenu();
$menu_active = $menu->getActive();

/* The following lines get language tags */
$lang = JFactory::getLanguage();

$page_title = $this->getTitle();

$home_page = 0;
$featured_page = 0;
$featured_view = 0;
if (JRequest::getVar('view') == 'featured')
{
	$featured_view = 1;	
}
if (JRequest::getVar('view') != 'reset' &&
    JRequest::getVar('view') != 'registration' &&
	JRequest::getVar('view') != 'remind' &&
	JRequest::getVar('view') != 'login' &&
	JRequest::getVar('view') != 'detail' &&	
	JRequest::getVar('searchword') == '')
{
	$menu_params = JSite::getMenu()->getParams($menu_active->id);
	
	if (($menu_active == $menu->getDefault()) || ($menu_active == $menu->getDefault($lang->getTag()))) 
	{
		$home_page = 1;
	}
	
	$featured_page = 0;
	$featured_page += (bool) $menu_params->get('num_leading_articles');
	$featured_page += (bool) $menu_params->get('num_columns');
	$featured_page += (bool) $menu_params->get('num_intro_articles');
}


if (!$featured_view)
{
	$featured_page = 1;	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >

<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />

	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.content.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.header.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.sidebars.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.footer.css" type="text/css" />

    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/googlemap.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/phocagallery.css" type="text/css" />    

<?php if($this->params->get('jQuery_load')): ?>

	<script type="text/javascript" src="templates/<?php echo $this->template ?>/scripts/jquery-1.7.1.min.js"></script>

<?php endif; ?>

	<script type="text/javascript" src="templates/<?php echo $this->template ?>/scripts/general.js"></script>

<?php 

include 'ie6warning.php';
include 'params.php';
include 'styles.php';

echo $cufon_fontfamily;
?>
  
    
</head>

<body>

    <!-- HEADER ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  -->
	<div class="wrapper">	

    <!-- HEADER ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  -->
    <div id="header">
    	<div class="row0">
            <div class="content">
				<?php 
                if($this->countModules('position-4')): 
                ?>
            	<div class="row2col1">
                    <jdoc:include type="modules" name="position-4" />                        
                </div>
		        <?php endif; ?>
            </div>        
        </div>
        <div class="row1">
	        <div class="content">
                <div class="row1col1">
                    <div id="companyname">
                        <?php if(!$logo_type): ?>
                        <a href="<?php echo $this->baseurl; ?>" > 
                            <img src="<?php echo $logo_img; ?>" alt="AS Templates"/>            
                        </a>
                        <?php else: ?> 
                        <div class="companyname">
                            <a href="<?php echo $this->baseurl ?>"><?php echo $logo_txt; ?></a>
                            <div class="slogan">
                                <?php echo $slogan_txt; ?>
                            </div>								
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row1col2">
                    <div id="topmenu">
                        <jdoc:include type="modules" name="position-1"/>
                    </div>
                </div>
            </div>
        </div>
                
        <div class="row3">
			<?php 
            if($this->countModules('slider')): 
            ?>
			<jdoc:include type="modules" name="slider" />
	        <?php endif; ?>
		</div>
    </div>
    <!-- END OF HEADER ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    
    <!-- CONTENT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    <div class="clear"></div>
    <div id="content">  
      
        <!-- COLUMN LEFT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
        <?php if($leftcolumn) : ?> 
        <div id="colleft">
	        <div id="colleft_rows_123">
				<?php if($this->countModules('position-0')): ?>
                <div class="row0">
                    <jdoc:include type="modules" name="position-0" style="xhtml"/>
                </div>
                <?php endif; ?>
				<?php if($this->countModules('position-40')): ?>
                <div class="row1">
                    <jdoc:include type="modules" name="position-40" style="xhtml"/>
                </div>
                <?php endif; ?>
                <?php if($this->countModules('position-41')): ?>
                <div class="row2">
                    <jdoc:include type="modules" name="position-41" style="xhtml"/>
                </div>
                <?php endif; ?>
                <?php if($this->countModules('position-42')): ?>
                <div class="row3">
                    <jdoc:include type="modules" name="position-42" style="xhtml"/>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- END OF COlUMN LEFT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
                
        <!-- COLUMN MAIN ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
        <div id="colmain" >
        
            <jdoc:include type="message" />
    
			<?php if($featured_page): ?>
            <div id="component">
               	<jdoc:include type="component"  />
            </div>
            <div class="clear"></div>
            <?php endif; ?>
    
            <?php if($this->countModules('position-5')): ?>
            <div id="adsense">
            	<div class="innerborder">
                	<jdoc:include type="modules" name="position-5" style="xhtml"/>
                </div>
            </div>
            <div class="clear"></div>
            <?php endif; ?>

        </div>
        <!-- END OF COLUMN MAIN ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

    </div>
    <div class="clear"></div>

    <!-- FOOTER ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
	<div id="footer">

		<?php 
        if($this->countModules('position-2')): 
        ?>
        <div class="breadrow">
            <div class="content">
                <div id="breadcrumb">
                    <jdoc:include type="modules" name="position-2" />	
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <?php endif; ?>
                    
    	<div class="row3"> 
			<div class="wrapper">	        
                <div class="content">
                    <div class="row3col1">
                    <!-- DO NOT REMOVE OR CHANGE THE CONTENT BELOW, THIS THEME MAY NOT WORK PROPERLY -->
                    
                        <div id="ascopy">
                        <a href="http://www.astemplates.com/" target="_blank">
                            Designed by:&nbsp;&nbsp;AS DESIGNING
                        </a>
                        </div>
                    
                    <!-- DO NOT REMOVE OR CHANGE THE CONTENT ABOVE, THIS THEME MAY NOT WORK PROPERLY -->
                    </div>
    
                    <div class="row3col2">            
                        <div id="trademark">
                            Copyright &copy; <?php echo date('Y'); ?> <?php echo $app->getCfg('sitename'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <!-- END OF FOOTER ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    
    </div>
    
</body>
</html>

