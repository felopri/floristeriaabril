<?php
/**
 * @package Sj Module Tabs
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 * 
 */
defined('_JEXEC') or die;

JHtml::stylesheet('modules/mod_sj_moduletabs/assets/css/moduletabs.css');
if( !defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1" ){
	JHtml::script('modules/mod_sj_moduletabs/assets/js/jquery-1.8.2.min.js');
	JHtml::script('modules/mod_sj_moduletabs/assets/js/jquery-noconflict.js');
	define('SMART_JQUERY', 1);
}
JHtml::script('modules/mod_sj_moduletabs/assets/js/jsmart.moduletabs.js');

// params object
$options = $params->toObject();
$uniqueid = 'moduletabs_'.rand().time();
$css_posiotion = $options->tab_position . '-position';

// check category loaded.
foreach ($listmodules as $first_module){
	$mod_preload = $first_module->id;
	break;
}
if(JRequest::getInt('moduletabs_' . $module->id . '_preload', -1, 'cookie')!=-1){
	$mod_preload = JRequest::getInt('moduletabs_' . $module->id . '_preload', -1, 'cookie');
}elseif(isset($options->preload_module) && isset($listmodules[$options->preload_module])) {
	$mod_preload = $options->preload_module;
}

?>

<?php ob_start(); ?>
	<div class="tabs-container">
		<ul class="tabs">
		<?php 
			foreach($listmodules as $_module) {
				$css_selected = $_module->id == $mod_preload ? 'tab selected' : 'tab'; 
		?>
			<li><div class="<?php echo $css_selected; ?>"><?php echo $_module->title ?></div></li>
		<?php 
			}
		?>
		</ul>
	</div>
<?php $tabs_markup = ob_get_contents(); ?>
<?php ob_end_clean(); ?>

<div id="<?php echo $uniqueid; ?>" class="moduletabs <?php echo $css_posiotion?> clearfix">
	
	<?php
		// show tabs here if (top, left);
		if (in_array($options->tab_position, array('top', 'left'))){
			echo $tabs_markup;
		} 
	?>
	<div class="tabs-content-wrap">
		<div class="tabs-content">
			<div class="tabs-content-inner">
			<?php 
				foreach($listmodules as $_module) {
					$is_preload = $_module->id == $mod_preload;
					$css_selected = $is_preload ? 'tab-content selected' : 'tab-content'; 
			?>
				<div class="<?php echo $css_selected; ?>"  title="sj_module_2load:<?php echo $_module->id; ?>">
					<?php 
						if (!empty($_module->content)){
							echo $_module->content;
						} else {
							?>
							<div class="ajax_loading"></div>
							<?php 
						}
					?>
				</div>
			<?php 
				}
			?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<?php
		// show tabs here if (right, bottom);
		if (in_array($options->tab_position, array('right', 'bottom'))){
			echo $tabs_markup;
		} 
	?>

</div>

<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($){
		$('<?php echo "#$uniqueid"; ?>').jsmart_moduletabs({
			sj_module: '<?php echo $module->module; ?>',
			sj_module_id: '<?php echo $module->id; ?>',
			ajax_url : '<?php echo JURI::current(); ?>',
			ajaxUpdate: function(element, options){
				var loading = $('.ajax_loading', element);
				if (loading.length){
					// show ajax indicator
					
					var category_id = $(element).attr('title').replace('sj_module_2load:', '');					
					ajax_options = {
						sj_module_2load:	category_id,
						sj_module_id:		options.sj_module_id,
						sj_module:			options.sj_module
					};
					$.ajax({
						type: 'POST',
						url : options.ajax_url,
						data: ajax_options,
						success: function(data, status, jqXHR){
							$(element).html(data);
							$(element).data('fx2height', $(element).height());
							$(element).attr('title', '');
						},
				        dataType: 'html'
					});
				}
			}
		});
	});
	//]]>
</script>