<?php

/*
 * You can change this to a normal override. This is just in place to help manage
 * the default set of overrides we have in our template framework.
 */

$app = JFactory::getApplication();

require_once(JPATH_THEMES.'/'.$app->getTemplate().'/'.'wright'.'/'.'html'.'/'.'overrider.php');
require(Overrider::getOverride('mod_articles_archive'));