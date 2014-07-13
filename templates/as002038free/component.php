<?php 

/*******************************************************************************************/
/*
/*		Designed by 'AS Designing'
/*		Web: http://www.asdesigning.com
/*		Email: info@asdesigning.com
/*
/*******************************************************************************************/

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.content.css" type="text/css" />

	<?php 
    include 'params.php';
    include 'styles.php';
    ?>

	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/tmpl.component.css" type="text/css" />

</head>

<body>
	<div id="cmp_content">
    	<div id="cmp_colmain">
            <div id="component" >
                <jdoc:include type="message" />
                <jdoc:include type="component" />
            </div>
        </div>
    </div>
</body>
</html>
