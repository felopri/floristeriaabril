<?php 
/*
* Pixel Point Creative - Slick Login
* License: GNU General Public License version
* See: http://www.gnu.org/copyleft/gpl.html
* Copyright (c) Pixel Point Creative LLC.
* More info at http://www.pixelpointcreative.com
* Last Updated: 3/13/13
*/

class JFormFieldUpgradecheck extends JFormField {
	
	var   $_name = 'Upgradecheck';
	
	protected function getInput()
	{
		return ' ';
	}	
	
	protected function getLabel()
	{
		//check for cURL support before we do anyting esle.
		if(!function_exists("curl_init")) return 'cURL is not supported by your server. Please contact your hosting provider to enable this capability.';
		//If cURL is supported, check the current version available.
		else 
				$version = 1.3;
				$target = 'http://www.pixelpointcreative.com/upgradecheck/slicklogin/index.txt';
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $target);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_HEADER, false);
				$str = curl_exec($curl);
				curl_close($curl);
				
						
				$message = '<div style="float:left;clear: both;"><label style="max-width:100%"><b>Installed Version '.$version.'</b> ';
				
				//If the current version is out of date, notify the user and provide a download link.
				if ($version < $str)
					$message = $message . '&nbsp;&nbsp;|&nbsp;&nbsp;<b>Latest Version '.$str.'</b><br />
					<a href="index.php?option=com_installer&view=update">Update</a> &nbsp;&nbsp;|&nbsp; &nbsp;<a href="http://www.pixelpointcreative.com/support.html" target="_blank">Get Help</a> &nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.pixelpointcreative.com/changelogs/slicklogin.txt" target="_blank">View the Changelog</a></label></div>';
				//If the current version is up to date, notify the user. 	
				elseif (($version == $str) || ($version > $str))
				$message = $message . '</br>There are no updates available at this time.</br>Having Trouble?  <a href="http://www.pixelpointcreative.com/support.html" target="_blank">Get Help</a> </label></div>';
				echo'<div style="float:left;clear: both;"><img width="184" height="100" src="../modules/mod_slick_login_vm/elements/logo.png" title="Slick Login" alt="Slick Login"></div>';  
				return 
				$message;
				
											
	  }
}
