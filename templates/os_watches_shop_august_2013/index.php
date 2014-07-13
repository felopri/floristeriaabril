<?php
$app  = JFactory::getApplication();
$doc  = JFactory::getDocument();
$user = JFactory::getUser();
$templateparams     = $app->getTemplate(true)->params;
$this->language = $doc->language;
$this->direction = $doc->direction;

//---Detecting Active Variables---//
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->getCfg('sitename');

// Social icons
$twitter     = $this->params->get("twitter");
$facebook    = $this->params->get("facebook");
$flickr      = $this->params->get("flickr");
$friendfeed  = $this->params->get("friendfeed");
$delicious   = $this->params->get("delicious");
$digg        = $this->params->get("digg");
$lastfm      = $this->params->get("lastfm");
$linkedin    = $this->params->get("linkedin");
$youtube     = $this->params->get("youtube");
$feed        = $this->params->get("feed");
$pinterest   = $this->params->get("pinterest");
$google      = $this->params->get("google");
$dribbble    = $this->params->get("dribbble");
$vimeo       = $this->params->get("vimeo");
$blogger     = $this->params->get("blogger");
$myspace     = $this->params->get("myspace");
$yahoo       = $this->params->get("yahoo");

// Copyrights
$copyrights      = $this->params->get("copyrights");
$copyrights_path   = $this->params->get("copyrights_path");

// Add Stylesheets
$doc->addStyleSheet($this->baseurl."/templates/".$this->template."/css/style.css");
$doc->addStyleSheet($this->baseurl."/templates/".$this->template."/bootstrap/css/bootstrap.css");
$doc->addStyleSheet($this->baseurl."/templates/".$this->template."/bootstrap/css/bootstrap-responsive.css");

// Add Script
$doc->addScript($this->baseurl."/templates/".$this->template."/javascript/custom.js");
$doc->addScript($this->baseurl."/templates/".$this->template."/bootstrap/js/bootstrap.js");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/javascript/jquery.min.js"></script>

	<script>jQuery.noConflict();</script>
		<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Comfortaa:300,400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Dosis:400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Francois+One' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700,300italic&subset=latin,cyrillic-ext' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Oxygen' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Prosto+One' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Quicksand:400,700,300' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:300italic,400italic,700italic,300,400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Share' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,600,700,900,200italic,300italic,400italic,600italic,700italic,900italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Ubuntu+Condensed' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700,300italic,400italic,500italic,700italic' rel='stylesheet' type='text/css'>
	    <jdoc:include type="head" />
    <?php echo $this->params->get('tracking_code')?>
    <script type="text/javascript" src="<?php echo $this->baseurl; ?>/components/com_virtuemart/assets/js/facebox.js"></script>
    <!--[if IE 7]><link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/style_ie7.css" type="text/css"><![endif]-->
    <!--[if IE 8]><link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/style_ie8.css" type="text/css"><![endif]-->
    <!--[if IE 9]><link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/style_ie9.css" type="text/css"><![endif]-->

</head>
<style type="text/css">
body {
    font-family:<?php echo $this->params->get('body_font', 'Arial, sans-serif') ?>;
    background-color:<?php echo $this->params->get('body_color')?>; 
    background-image: url('<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/<?php echo $this->params->get('body_background')?>');
}

a {
    color:<?php echo $this->params->get('link_color')?>;
    text-decoration:<?php echo $this->params->get('underline', 'underline')?>;
    font-family:<?php echo $this->params->get('links_font', 'Arial, sans-serif')?>;
}

a:hover {
    color:<?php echo $this->params->get('link_hover_color')?>;
    text-decoration:<?php echo $this->params->get('hover_underline')?>;
}

.menu a {
    color:<?php echo $this->params->get('menu_color')?>;
    text-decoration:<?php echo $this->params->get('menu_underline', 'underline')?>;
    font-family:<?php echo $this->params->get('menu_font', 'Arial, sans-serif')?>;
}

.menu a:hover {
    color:<?php echo $this->params->get('menu_hover_color')?>;
    text-decoration:<?php echo $this->params->get('menu_hover_underline')?>;
}

.footer a {
    color:<?php echo $this->params->get('footer_color')?>;
    text-decoration:<?php echo $this->params->get('footer_underline', 'underline')?>;
    font-family:<?php echo $this->params->get('footer_font', 'Arial, sans-serif')?>;
}

.footer a:hover {
    color:<?php echo $this->params->get('footer_hover_color')?>;
    text-decoration:<?php echo $this->params->get('footer_hover_underline')?>;
}

h1 {font-family:<?php echo $this->params->get('h1_font', 'Arial, sans-serif')?>;}
h2 {font-family:<?php echo $this->params->get('h2_font', 'Arial, sans-serif')?>;}
h3 {font-family:<?php echo $this->params->get('h3_font', 'Arial, sans-serif')?>;}
h4 {font-family:<?php echo $this->params->get('h4_font', 'Arial, sans-serif')?>;}
h5 {font-family:<?php echo $this->params->get('h5_font', 'Arial, sans-serif')?>;}
h6 {font-family:<?php echo $this->params->get('h6_font', 'Arial, sans-serif')?>;}
#mod_ss,
#header,
#soc_copy,
#wrapper {
    max-width:<?php echo is_numeric($this->params->get('wrapper_width')) ? $this->params->get('wrapper_width')."px" : $this->params->get('wrapper_width'); ?>;
    margin:<?php echo $this->params->get('wrapper_position'); ?>;
}

</style>
<body class="site pattern0 <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " ";?> <?php if ($this->params->get('fluidContainer')) { echo "fluid"; } ?>">
<jdoc:include type="modules" name="mod_sale_top" style="none" /> <!-- This position for sale panel on ordasoft page. Do not delete! -->
 <jdoc:include type="modules" name="stylechanger" />

      <div class="row-fluid header_box">
	<div id="mod_ss" class="container">

	      <div id="logo">
		  <a href="<?php echo $this->params->get('logo_link')?>"><img style=" border:none; width:<?php echo $this->params->get('logo_width')?>px; height:<?php echo $this->params->get('logo_height')?>px; " src="<?php echo $this->params->get('logo_file')?>" alt="Logo" /></a>
	      </div>

	  <?php if ($this->countModules('search_in_shop')): ?>
	      <div class="search_in_shop">
		    <jdoc:include type="modules" name="search_in_shop" style="xhtml" />
	      </div>
	  <?php endif; ?>

	</div>
      </div> <!-- header_box -->

     <div id="header">
	<div class="container">
	      <?php if ($this->countModules('shopping_cart')): ?>
		<div class="shopping_cart">
		    <jdoc:include type="modules" name="shopping_cart" style="xhtml" />
		</div>
	      <?php endif; ?>

	      <div class="navbar">
		  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
		    <span class="icon-bar"></span>
		    <span class="icon-bar"></span>
		    <span class="icon-bar"></span>
		  </a>
	      </div>

	      <div class="nav-collapse collapse globalMenu">
		<jdoc:include type="modules" name="main_menu" style="xhtml" />
	      </div>

	</div>
      </div> <!-- header -->

	  <?php if ($this->countModules('bxSlider')): ?>
		    <div class="row-fluid">
			<div class="bxSlider">
			      <jdoc:include type="modules" name="bxSlider" style="xhtml" />
			</div>
		  </div>
	  <?php endif; ?>

  <div id="wrapper" class="container">

	    <jdoc:include type="modules" name="breadcrumbs" style="xhtml" />
	    <jdoc:include type="message" />

	  <?php if ($this->countModules('login_form') || $this->countModules('category') || $this->countModules('currencies_selector') || $this->countModules('manufacturer')): ?>

		<div class="sidebar">
		    <div class="login_form">
			<jdoc:include type="modules" name="login_form" style="xhtml" />
		    </div>
		    <div class="currencies_selector">
			<jdoc:include type="modules" name="currencies_selector" style="xhtml" />
		    </div>
		    <div class="category_class">
			<jdoc:include type="modules" name="category" style="xhtml" />
		    </div>
		    <div class="manufacturer">
			<jdoc:include type="modules" name="manufacturer" style="xhtml" />
		    </div>
		</div>

	  <?php endif; ?>

	  <?php if ($this->countModules('products')): ?>
		<div class="products">
		    <jdoc:include type="modules" name="products" style="xhtml" />
		</div>
	  <?php endif; ?>

		<div id="component">
		  <jdoc:include type="modules" name="location_map" style="xhtml" />
		  <jdoc:include type="component" />
		</div>

  </div><!--/wrapper-->

    <div class="footer row-fluid">
      <div id="soc_copy" class="container">

	<div class="copyrights span6">
	  <?php if ($copyrights && $copyrights_path != null) { ?>
	      <a href="<?php echo $copyrights_path; ?>" target="_blank" rel="nofollow"><?php echo $copyrights; ?></a>
	  <?php } ?>
       </div>

      <div class="social span6">
	<div class="soc_icons_box">
	      <ul class="soc_icons" >
		  <?php if ($twitter != null) { ?><li><a href="<?php echo $twitter; ?>" class="twitter" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($facebook != null) { ?><li><a href="<?php echo $facebook; ?>" class="facebook" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($flickr != null) { ?><li><a href="<?php echo $flickr; ?>" class="flickr" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($friendfeed != null) { ?><li><a href="<?php echo $friendfeed; ?>" class="friendfeed" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($delicious != null) { ?><li><a href="<?php echo $delicious; ?>" class="delicious" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($digg != null) { ?><li><a href="<?php echo $digg; ?>" class="digg" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($lastfm != null) { ?><li><a href="<?php echo $lastfm; ?>" class="lastfm" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($linkedin != null) { ?><li><a href="<?php echo $linkedin; ?>" class="linked-in" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($youtube != null) { ?><li><a href="<?php echo $youtube; ?>" class="youtube" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($feed != null) { ?><li><a href="<?php echo $feed; ?>" class="feed" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($pinterest != null) { ?><li><a href="<?php echo $pinterest; ?>" class="pinterest" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($google != null) { ?><li><a href="<?php echo $google; ?>" class="google" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($dribbble != null) { ?><li><a href="<?php echo $dribbble; ?>" class="dribbble" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($vimeo != null) { ?><li><a href="<?php echo $vimeo; ?>" class="vimeo" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($blogger != null) { ?><li><a href="<?php echo $blogger; ?>" class="blogger" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($myspace != null) { ?><li><a href="<?php echo $myspace; ?>" class="myspace" target="_blank" rel="nofollow"></a></li><?php } ?>
		  <?php if ($yahoo != null) { ?><li><a href="<?php echo $yahoo; ?>" class="yahoo" target="_blank" rel="nofollow"></a></li><?php } ?>
	      </ul>
	  </div>
	</div>

    </div> <!-- soc_copy -->
 </div> <!-- footer -->

</body>

</html>