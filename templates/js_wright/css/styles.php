<?php
header("Content-type: text/css");
$headerstyle = '#header span#graphic a,#header h1#graphic a {'
        . 'height: '.$header_height.'px;'
        . 'margin: '.$header_top_pad.'px '.$header_right_pad.'px '.$header_bot_pad.'px '.$header_left_pad.'px;'
				. 'background: url('.$background.') no-repeat left center;'
				. '}';
$this->document->addStyleDeclaration($headerstyle);
?>
