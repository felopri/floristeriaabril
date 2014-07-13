<?php 

/*******************************************************************************************/
/*
/*		Designed by 'AS Designing'
/*		Web: http://www.asdesigning.com
/*		Email: info@asdesigning.com
/*		License: ASDE Commercial
/*
/*******************************************************************************************/


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// General Parameters


$page_width 				= 960;
$content_width 				= 940;
$padding 					= 30;
$sidebar_width 				= 212;
$sidebar_modulepadding		= 25;
$main_sepwidth 				= 30;
$main_modulepadding			= 25;
$footer_sidepadding		 	= 30;
$footer_modulepadding 		= 25;


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Body patterns and colors

$body_bgcolor 			= '#' . $this->params->get('body_bgcolor');

$body_fontfamily 		= $this->params->get('body_fontfamily'); 
$body_hfontfamily 		= $this->params->get('body_hfontfamily');
$cufon_fontfamily		= '';

switch($body_hfontfamily)
{
	case 'arial':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Arial_400.font.js"></script>';
		break;	
	case 'aller':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/aller.js"></script>';
		break;	
	case 'amerika-sans':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/amerika-sans.js"></script>';
		break;	
	case 'anivers':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/anivers.js"></script>';
		break;	
	case 'antipasto':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/antipasto.js"></script>';
		break;	
	case 'caviar-dreams':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/caviar-dreams.js"></script>';
		break;	
	case 'courier':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Courier_New_400.font.js"></script>';
		break;			
	case 'dejaweb':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/dejaweb.js"></script>';
		break;	
	case 'oregon':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/oregon.js"></script>';
		break;	
	case 'ptsans':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/ptsans.js"></script>';
		break;	
	case 'times':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Times_New_Roman_400.font.js"></script>';
		break;	
	case 'tahoma':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Tahoma_400.font.js"></script>';
		break;	
	case 'tuffy':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/tuffy.js"></script>';
		break;	
	case 'verdana':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Verdana_400.font.js"></script>';
		break;	
	case 'waukegan':
		$cufon_fontfamily = '<script type="text/javascript" src="templates/' . $this->template . '/scripts/fonts/Waukegan_LDO_Extended_Black_900.font.js"></script>';		
} 

if($cufon_fontfamily)
{
	$cufon_fontfamily = 
		'<script type="text/javascript" src="templates/' . $this->template . '/scripts/cufon.js"></script>'
		. $cufon_fontfamily . 
		'<script type="text/javascript">
        	Cufon.replace(".companyname, #topmenu, #topmenu_home, h1, h2, h3, h4, h5, h6", {hover: true});
    	</script>';
}


$body_fontsize 			= $this->params->get('body_fontsize'); 
$body_fontweight 		= $this->params->get('body_fontweight'); 
$body_fontstyle 		= $this->params->get('body_fontstyle'); 


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


$bgreadmore_ico 		= $this->baseurl . '/templates/' . $this->template . '/images/bg.readmore.ico.png';
$listimg_footer			= $this->baseurl . '/templates/' . $this->template . '/images/listimg.footer.png';


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Header parameters
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Header - Logo


$logo_type 			= $this->params->get('logo_type',0); 
$logo_img 			= $this->baseurl . '/templates/' . $this->template . '/images/companylogo.png';

if ($this->params->get('logo_img')) 
{ 
	$logo_img = $this->params->get('logo_img');

	if($logo_img == 'companylogo.png') 
	{
		$logo_img = $this->baseurl . '/templates/' . $this->template . '/images/companylogo.png';
	}
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


$logo_txt 					= htmlspecialchars($this->params->get('logo_txt')); 
$logo_txtfontsize 			= $this->params->get('logo_txtfontsize'); 
$logo_txtfontstyle 			= $this->params->get('logo_txtfontstyle'); 
$logo_txtfontweight 		= $this->params->get('logo_txtfontweight'); 
$logo_txtcolor 				= '#' . $this->params->get('logo_txtcolor');


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Header - Slogan


$slogan_txt 				= htmlspecialchars($this->params->get('slogan_txt')); 
$slogan_txtfontsize 		= $this->params->get('slogan_txtfontsize'); 
$slogan_txtfontstyle 		= $this->params->get('slogan_txtfontstyle'); 
$slogan_txtfontweight 		= $this->params->get('slogan_txtfontweight'); 
$slogan_txtcolor 			= '#' . $this->params->get('slogan_txtcolor');


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Left Column Parameters
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


$leftcolumn = 0;
$leftcolumn += (bool) $this->countModules('position-0');
$leftcolumn += (bool) $this->countModules('position-40');
$leftcolumn += (bool) $this->countModules('position-41');
$leftcolumn += (bool) $this->countModules('position-42');


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Main column parameters
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Main Column - Dimensions


$main_width = $content_width;

if ($leftcolumn)
{
	$main_width = $content_width - $sidebar_width - $padding;
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


$main_blogcols2width = 0;
$main_blogcols3width = 0;
$main_blogcols4width = 0;

$main_componentwidth = $main_width;

$main_blogcols2width = ($main_componentwidth - $padding) / 2;
$main_blogcols3width = ($main_componentwidth - $padding * 2) / 3;
$main_blogcols4width = ($main_componentwidth - $padding * 3) / 4;


?>

