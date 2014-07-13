<style type="text/css">

/***************************************************************************************/
/*
/*		Designed by 'AS Designing'
/*		Web: http://www.asdesigning.com
/*		Email: info@asdesigning.com
/*		License: ASDE Commercial
/*
/**************************************************************************************/

/**************************************************************************************/
/**************************************************************************************/
/*   Elements
/**************************************************************************************/
/**************************************************************************************/


body
{
	font-family: <?php echo $body_fontfamily; ?>;
	font-size: <?php echo $body_fontsize; ?>;
	font-style: <?php echo $body_fontstyle; ?>;
	font-weight: <?php echo $body_fontweight; ?>;
	background-color: <?php echo $body_bgcolor; ?>;
}

/**************************************************************************************/
/*   Header
/**************************************************************************************/
/**************************************************************************************/

#header
{
	font-size: <?php echo $body_fontsize; ?>;
	font-weight: <?php echo $body_fontweight; ?>;
	font-style: <?php echo $body_fontstyle; ?>;
	font-family: <?php echo $body_fontfamily; ?>; 
}

#header .content
{
	width: <?php echo $content_width; ?>px;		
}


/**************************************************************************************/
/*   Header Row 1					      											  */


#header .row1
{
	color: <?php echo $logo_txtcolor; ?>;
}

#header .row1 .content
{
	width: <?php echo $page_width; ?>px;		
}

#header .row1 #companyname
{
}

#header.home #companyname a,
#header .row1 #companyname,
#header .row1 #companyname a
{
	font-family: <?php echo $body_hfontfamily; ?>;
    font-size: <?php echo $logo_txtfontsize; ?>;
    font-style: <?php echo $logo_txtfontstyle; ?>;
    font-weight: <?php echo $logo_txtfontweight; ?>;
	color: <?php echo $logo_txtcolor; ?>;
}

#header.home #companyname a:hover,
#header .row1 #companyname a:hover
{
	color: <?php echo $logo_txtcolor; ?>;
}

#header.home .slogan,
#header .row1 .slogan
{
	font-family: <?php echo $body_hfontfamily; ?>;
    font-size: <?php echo $slogan_txtfontsize; ?>;
    font-style: <?php echo $slogan_txtfontstyle; ?>;
    font-weight: <?php echo $slogan_txtfontweight; ?>;
	color: <?php echo $slogan_txtcolor; ?>;
}


/**************************************************************************************/
/**************************************************************************************/
/*   Content
/**************************************************************************************/
/**************************************************************************************/

#content
{
	width: <?php echo $content_width; ?>px;	
}


/**************************************************************************************/
/*   Column Main 
/**************************************************************************************/
/**************************************************************************************/

#colmain
{
	width: <?php echo $main_width; ?>px;
}

#colmain #component
{
	width: <?php echo $main_componentwidth; ?>px;
}

#colmain #component .innerborder
{
	border: 1px solid none;
}

#colmain .cols-2 .column-1,
#colmain .cols-2 .column-2
{
    width: <?php echo $main_blogcols2width;  ?>px;
}

#colmain .cols-3 .column-1,
#colmain .cols-3 .column-2,
#colmain .cols-3 .column-3
{
    width: <?php echo $main_blogcols3width;  ?>px;
}

#colmain .cols-4 .column-1,
#colmain .cols-4 .column-2,
#colmain .cols-4 .column-3,
#colmain .cols-4 .column-4
{
    width: <?php echo $main_blogcols4width;  ?>px;
}

/**************************************************************************************/
/*   Footer
/**************************************************************************************/
/**************************************************************************************/

#footer
{
	margin: 0px 0px 0px 0px;
	padding: 0px 0px 0px 0px;
	overflow: hidden;
}

/**************************************************************************************/
/*  Breadcrums				  														  */


.breadrow .content
{
	width: <?php echo $content_width; ?>px;	
}

.breadrow #search input
{
	font-family: <?php echo $body_fontfamily; ?>;
	font-size: <?php echo $body_fontsize; ?>;
	font-style: <?php echo $body_fontstyle; ?>;
	font-weight: <?php echo $body_fontweight; ?>;	
}


</style>