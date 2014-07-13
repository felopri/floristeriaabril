<?php
  defined('_JEXEC') or die('Restricted access'); // no direct access
?>
<div class="breadcrumbs<?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1) && $count!=1)
	{
		echo '<span class="showHere"> Estás aquí:</span>';
	}
    // default skin
    for ($i = 0; $i < $count; $i++) {
    	// If not the last item in the breadcrumbs add the separator
    	if ($i < $count -1) {

    		if(!empty($list[$i]->link)) {
          if ($i == 0 && $homePath != '') {
            echo '<a href="'.$list[$i]->link.$homePath.'" class="pathway">'.$list[$i]->name.'</a>';
          } else {
          	echo '<a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a>';
          }
    		} else {
    			echo $list[$i]->name;
    		};
    		if ($i < $count -2)
          echo ' '.$separator.' ';

    	}  else if ($showLast && $count > 1) { // when $i == $count -1 and 'showLast' is true

          echo ' '.$separator.' ';
          if ( ($cutLast) && (strlen($list[$i]->name) > $cutAt) ) { // when cutLast is true and length of breadcrumb is bigger than cutAt
    	      echo substr($list[$i]->name, 0 , $cutAt).$cutChar;
          } else {
            echo $list[$i]->name;
          };

    	} else if ($count == 1) {
        /*if ($clickHome) {
          if ($homePath != '') {
            echo '<a href="'.$list[0]->link.$homePath.'" class="pathway">'.$list[0]->name.'</a>';
          } else {
          	echo '<a href="'.$list[0]->link.'" class="pathway">'.$list[0]->name.'</a>';
          }
        } else {
          echo $list[0]->name;
        }*/
    	};
  	}; //endfor
?>
</div>

