<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// Get an instance of a controller prefixed by Shortlink
$controller = JControllerLegacy::getInstance('Shortlink');
 
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));
 
// Redirect if set by the controller
$controller->redirect();
?>