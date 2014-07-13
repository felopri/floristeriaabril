<?php
/*
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2011 Add This, LLC                                         |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 3 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program.  If not, see <http://www.gnu.org/licenses/>.    |
 * +--------------------------------------------------------------------------+
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.version');

/**
 * plgContentAddThis
 *
 * Creates AddThis sharing button with each and every posts.
 * Reads the user settings and creates the button accordingly.
 *
 * @author angel
 * @version 1.1.3
 */
class plgContentAddThis extends JPlugin {

   /**
    * Constructor
    *
    * Loads the plugin settings and assigns them to class variables
    *
    * @param reference $subject
    * @param object $config
    */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->setBaseURL();
        $this->setPageProtocol();
        $this->populateParams();
    }

    /**
     * onPrepareContent
     *
     * Content creation listening event for Joomla 1.5 version
     *
     * @param reference $article
     * @param reference $params
     * @param integer $limitstart
     * @return void
     * @see http://docs.joomla.org/Reference:Content_Events_for_Plugin_System#5.5.2_onPrepareContent
     */
    public function onPrepareContent(&$article, &$params, $limitstart)
    {
    	$this->createAddThis($article);
    }

    /***
     * onContentBeforeDisplay
     *
     * Content creation listening event for Joomla 1.6 version
     *
     * @param reference $item
     * @param reference $article
     * @return void
     */
    public function onContentBeforeDisplay($item, &$article)
	{
		$this->createAddThis($article);
    }

    /**
     * Creates configuration script and addthis button code while content is being prepared
     * and appends it to the article or post
     *
     * @param object $article
     * @return void
     */
    private function createAddThis($article)
    {
    	//Creating div elements for AddThis
		$outputValue = " <div class='joomla_add_this'>";
		$outputValue .= "<!-- AddThis Button BEGIN -->" . PHP_EOL;

		//Creates addthis configuration script
	    $outputValue .= "<script type='text/javascript'>\r\n";
	    $outputValue .= "var addthis_product = 'jlp-1.2';\r\n";
		$outputValue .="var addthis_config =\r\n{";
		$configValue = $this->prepareConfigValues();

    	//Removing the last comma and end of line characters
    	if("" != trim($configValue))
		{
		  	$outputValue .= implode( ',', explode( ',', $configValue, -1 ));
		}
		$outputValue .= "}</script>". PHP_EOL;

        //Creates the button code depending on the button style chosen
        $buttonValue = "";

		if("custom_button_code" == $this->arrParamValues["button_style"])
		{
			$buttonValue = $this->prepareCustomCode($article);
		}
		//Generates the button code for toolbox
        else if("toolbox" == $this->arrParamValues["button_style"])
        {
        	 $buttonValue .= $this->getToolboxScript($this->arrParamValues["toolbox_services"], $article);
        }
        //Generates button code for rest of the button styles
        else
		{
			$buttonValue .= "<a href='" . $this->pageProtocol ."://www.addthis.com/bookmark.php' ".
				" onmouseover=\"return addthis_open(this,'', '". urldecode($this->getArticleUrl($article))."', '".$this->escapeText($article->title)."' )\" ".
			" onmouseout='addthis_close();' onclick='return addthis_sendto();'>";
		    $buttonValue .= "<img src='";
		    //Custom image for button
			if ("custom_button_image" == trim($this->arrParamValues["button_style"]))
	    	{
		        if ("" == trim($this->arrParamValues["custom_url"]))
		        {
		            $buttonValue .= $this->pageProtocol . "://s7.addthis.com/static/btn/v2/" .  $this->getButtonImage('lg-share',$this->arrParamValues["addthis_language"]);
		        }
	        	else $buttonValue .= $this->arrParamValues["custom_url"];
	    	}
	    	//Pointing to addthis button images
	    	else
		    {
				$buttonValue .= $this->pageProtocol . "://s7.addthis.com/static/btn/v2/" . $this->getButtonImage($this->arrParamValues["button_style"],$this->arrParamValues["addthis_language"]);
			}
			$buttonValue .= "' border='0' alt='AddThis Social Bookmark Button' />";
			$buttonValue .= "</a>". PHP_EOL;
		}
		$outputValue .= $buttonValue;

		//Adding AddThis script to the page
		$outputValue .= "<script type='text/javascript' src='" . $this->pageProtocol . "://s7.addthis.com/js/250/addthis_widget.js'></script>\r\n";
		$outputValue .= "<!-- AddThis Button END -->". PHP_EOL;
		$outputValue .= "</div>";

        //Regular expression for finding the custom tag which disables AddThis button in the article.
        $switchregex = "#{addthis (on|off)}#s";

		if(class_exists("JSite"))
		{
			//Gets frontpage
			$menu =& JSite::getMenu();
			//Sets the visibility of AddThis button in frontpage depending on user's settings
			if(($menu->getActive() == $menu->getDefault()) && ($this->arrParamValues["show_frontpage"] == 0)) {
			  $hide_frontpage = true;
			  $outputValue = "";
			}
		}

        //Ensuring the custom tag is not present in the article text.
        //Positioning button according to the position chosen
        if(isset($article->text))
           $article->text = strpos($article->text, '{addthis off}') == false ? "top" == $this->arrParamValues["position"] ? $outputValue . $article->text : $article->text.$outputValue : preg_replace($switchregex, '', $article->text);
        elseif(isset($article->introtext))
           $article->introtext = strpos($article->introtext, '{addthis off}') == false ? "top" == $this->arrParamValues["position"] ? $outputValue . $article->introtext : $article->introtext.$outputValue : preg_replace($switchregex, '', $article->introtext);
    }

	/**
     * getToolboxScript
     *
     * Preparing AddThis toolbox code
     *
     * @param string $services  comma seperated list of services
     * @param object $article
     * @return string Returns the script for rendering the selected services in toolbox
    */
    private function getToolboxScript($services, $article)
    {
    	//Deciding the toobox icon dimensions
    	$dimensionStyle = $this->arrParamValues["icon_dimension"] == "16" ? '' : ' addthis_32x32_style';
    	//Toolbox main div element, holds the url and title for sharing
    	$toolboxScript  = "<div class='addthis_toolbox" . $dimensionStyle . " addthis_default_style' addthis:url='". urldecode($this->getArticleUrl($article)) . "' addthis:title='" . htmlspecialchars($article->title, ENT_QUOTES) . "'>";
    	$serviceList = explode(",", $services);
    	//Adding the services one by one
    	for ( $i = 0, $max_count = sizeof( $serviceList ); $i < $max_count; $i++ )
    	{
			$toolboxScript .= "<a class='addthis_button_" . $serviceList[$i] . "'></a>";
		}
		//Adding more services button in user selected mode - (Expanded | Compact || share counter)
		$toolboxScript .= ("expanded" == $this->arrParamValues["toolbox_more_services_mode"] || "compact" == $this->arrParamValues["toolbox_more_services_mode"]) ? "<a class='addthis_button_" . $this->arrParamValues["toolbox_more_services_mode"] ."'>Share</a>" : "<a class='addthis_" . $this->arrParamValues["toolbox_more_services_mode"] ." addthis_pill_style'></a>";
		$toolboxScript .= "</div>";
		return $toolboxScript;
    }

    /**
    * getArticleUrl
    *
    * Gets the static url for the article
    *
    * @param object $article - Joomla article object
    * @return string returns the permalink of a particular post or page
    **/
    private function getArticleUrl(&$article)
    {
        if (!is_null($article))
        {
            require_once( JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');
			if(isset($article->id) && isset($article->catid))
			{
				$url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
				return JRoute::_($this->baseURL . $url, true, 0);
			}
			else
			{
			    return $this->baseURL;
			}
        }
    }

	/**
     * escapeText
     *
     * Escapes single quotes
     *
     * @param string $text - string to be escaped
     * @return string returns text with special characters encoded
     */
    private function escapeText($text)
    {
    	$cleanedText = htmlspecialchars($text);
    	return str_replace("'", "\'", $cleanedText);
    }

    /**
     * getButtonImage
     *
     * This is used for preparing the image button name.
     *
     * @param string $name - Button style of addthis button selected
     * @param string $language - The language selected for addthis button
     * @return string returns the button image file name
     */
    private function getButtonImage($name, $language)
    {
       $buttonImage = "";
       if ("sm-plus" == $name)
            $buttonImage = $name . '.gif';
       elseif ($language != 'en')
       {
            if (in_array($name, array("lg-share", "lg-bookmark", "lg-addthis")))
                $buttonImage = 'lg-share-' . $language . '.gif';
            elseif(in_array($name, array("sm-share", "sm-bookmark")))
                $buttonImage = 'sm-share-' . $language . '.gif';
       }
       else
            $buttonImage = $name . '-' . $language . '.gif';
       return $buttonImage;
    }

    /**
     * populateParams
     *
     * Gets the plugin parameters and holds them as a collection
     *
     * @return void
     */
     private function populateParams()
     {
     	$version = new JVersion;
        $joomlaVersion = $version->RELEASE;

     	// Loading plugin parameters for Joomla 1.5
        if($joomlaVersion < 1.6){
	        $plugin = JPluginHelper::getPlugin('content', 'addthis');
	        $params = new JParameter($plugin->params);
	    }

        $arrParams = array("profile_id", "button_style", "custom_url", "custom_button_code", "toolbox_services", "icon_dimension",
        				   "addthis_brand", "addthis_header_color", "addthis_header_background", "addthis_services_compact",
        				   "addthis_services_exclude", "addthis_services_expanded", "addthis_services_custom", "addthis_offset_top",
        				   "addthis_offset_left", "addthis_hover_delay", "addthis_click", "addthis_hover_direction",
        				   "addthis_use_addressbook", "addthis_508_compliant", "addthis_data_track_clickback",
        				   "addthis_hide_embed", "addthis_language", "position", "show_frontpage", "toolbox_more_services_mode",
        				   "addthis_use_css", "addthis_ga_tracker");
        foreach ( $arrParams as $key => $value ) {
			$this->arrParamValues[$value] = $joomlaVersion > 1.5 ? $this->params->def($value): $params->get($value);
		}
     }

    /**
     * prepareConfigValues
     *
     * Prepares configuration values for AddThis button from user saved settings
     *
     * @return void
     */
    private function prepareConfigValues()
    {
    	$configValue = "";
		$arrConfigs = array("profile_id" => "pubid", "addthis_brand" => "ui_cobrand", "addthis_header_color" => "ui_header_color",
							"addthis_header_background" => "ui_header_background", "addthis_services_compact" => "services_compact",
							"addthis_services_exclude" => "services_exclude", "addthis_services_expanded" => "services_expanded",
							"addthis_services_custom" => "services_custom", "addthis_offset_top" => "ui_offset_top",
							"addthis_offset_left" => "ui_offset_left", "addthis_hover_delay" => "ui_delay", "addthis_click" => "ui_click",
							"addthis_hover_direction" => "ui_hover_direction", "addthis_use_addressbook" => "ui_use_addressbook",
							"addthis_508_compliant" => "ui_508_compliant", "addthis_data_track_clickback" => "data_track_clickback",
							"addthis_hide_embed" => "ui_hide_embed", "addthis_language" => "ui_language",
							"addthis_use_css" => "ui_use_css", "addthis_ga_tracker" => "data_ga_tracker");

		foreach ( $arrConfigs as $key => $value ) {
		   if(in_array($value, array("pubid", "ui_cobrand", "ui_header_color", "ui_header_background", "services_compact",
		               "services_exclude", "services_expanded", "ui_language")) && ($this->arrParamValues[$key] != ""))
		           $configValue .= $value . ":'" . $this->arrParamValues[$key] . "'," . PHP_EOL;
		   elseif(in_array($value, array("ui_offset_top", "ui_offset_left", "ui_delay", "ui_hover_direction", "data_ga_tracker",
		               "services_custom")) && ($this->arrParamValues[$key] != ""))
				   $configValue .= $value . ":" . $this->arrParamValues[$key] . "," .  PHP_EOL;
		   elseif(in_array($value, array("ui_click", "ui_use_addressbook", "ui_508_compliant", "data_track_clickback", "ui_hide_embed",
		               "ui_use_css", )) && ($this->arrParamValues[$key] != ""))
				   $configValue .= "1" == $this->arrParamValues[$key]? $value . ":true," . PHP_EOL : (("ui_use_css" == $value || "data_track_clickback" == $value) ? $value . ":false," . PHP_EOL : "");
		}
		return $configValue;
    }

	/**
	 *	Gets the current page protocol
	 *
	 * @return void
	 */
    private function setPageProtocol()
    {
    	$arrVals = explode(":", $this->baseURL);
		$this->pageProtocol = $arrVals[0];
    }

	/**
	 * Setting the base url
	 *
	 * @return void
	 */
    private function setBaseURL(){
	    $uri = &JURI::getInstance();
        $this->baseURL = $uri->toString(array('scheme', 'host', 'port'));
    }

	/**
	 * Adding inline sharing to custom code
	 *
	 * @param object article
	 * @return string custom code with inline sharing parameters
	 */
    private function prepareCustomCode($article)
    {
		$userEnteredCode = $this->arrParamValues["custom_button_code"];
		$modifiedCode = preg_replace("[<script[^>]*?.*?</script>]", "", $userEnteredCode);
		if (strpos($modifiedCode, 'addthis_toolbox') !== false) {
			$offset = 0;
			do {
				$searchAgain = false;
				$divTagStart = strpos($modifiedCode, '<div', $offset);
				if ($divTagStart !== false) {
					$divTagEnd = strpos($modifiedCode, '>', $divTagStart);
					if ($divTagEnd !==  false) {
						$length = $divTagEnd - $divTagStart + 1;
						$divOpeningTag = substr($modifiedCode, $divTagStart, $length);
						if (strpos($divOpeningTag, 'addthis_toolbox') !== false) {
							$extraAttributes = ' addthis:url="' . urldecode($this->getArticleUrl($article)) . '"';
							$extraAttributes .= ' addthis:title="' . htmlspecialchars($article->title, ENT_QUOTES) . '"';
							$newDivOpeningTag = substr($divOpeningTag, 0, -1) . $extraAttributes . '>';
							$modifiedCode = str_replace($divOpeningTag, $newDivOpeningTag, $modifiedCode);
						}
						else {
							$offset = $divTagEnd;
							$searchAgain = true;
						}
					}
				}
			} while (!empty($searchAgain));
		}
		return $modifiedCode;
    }
}
