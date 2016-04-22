<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

class ShortlinkControllerShortlink extends JControllerLegacy {
   /**
    * constructor (registers additional tasks to methods)
    * @return void
    */
   function __construct() {
      parent::__construct();

      // Register Extra tasks
      $this->registerTask('add', 'edit');
   }

   /**
    * display the edit form
    * @return void
    */
   function edit() {
      JRequest::setVar('view', 'shortlink');
      JRequest::setVar('layout', 'form');
      JRequest::setVar('hidemainmenu', 1);

      parent::display();
   }

   /**
    * save a record (and redirect to main page)
    * @return void
    */
   function save() {
      $model = $this->getModel('shortlink');

      if ($model->store($post)) {
         $msg = JText::_('Shortlink saved!');
      } else {
         $msg = JText::_('Error saving Shortlink');
      }

      $link = 'index.php?option=com_shortlink';
      $this->setRedirect($link, $msg);
   }

   /**
    * remove record(s)
    * @return void
    */
   function remove() {
      $model = $this->getModel('shortlink');
      if (!$model->delete()) {
         $msg = JText::_('Error: One or more Shortlinks could not be deleted');
      } else {
         $msg = JText::_('Shortlink(s) deleted');
      }

      $this->setRedirect('index.php?option=com_shortlink', $msg);
   }

   /**
    * cancel editing a record
    * @return void
    */
   function cancel() {
      $msg = JText::_('Operation cancelled');
      $this->setRedirect('index.php?option=com_shortlink', $msg);
   }
   
   function rename() {
      $source_path = JRequest::getVar('path_old');
      $target_path = JRequest::getVar('path_new');

      // replace directory separators with locally valid ones
      $target_path = preg_replace('#\\\\#', DIRECTORY_SEPARATOR, $target_path);
      $target_path = preg_replace('#/#', DIRECTORY_SEPARATOR, $target_path);

      $pattern_path = '#.+?\\' . DS . '.+?#';

      $matches = preg_match($pattern_path, $source_path);
      if (!$matches || !file_exists($source_path)) {
         $this->closeAjax(JText::_("ERR_WRONG_PATH_OLD"));
         return;
      }

      $matches = preg_match($pattern_path, $target_path);

      // replace directory separators with locally valid ones
      $regExp = $this->replaceDS($_SERVER["DOCUMENT_ROOT"]);
      $regExp = preg_quote($regExp);
      $regExp = '$^' . $regExp . '$i';

      // Check if the new path is not out of the domain root!
      // TODO symlinks don't work here
      if (!$matches || empty($target_path) || !preg_match($regExp, $target_path)) {
         // TODO: If moving to new path is not possible either don't let save the settings or
         // set the new path to old path!
         $this->closeAjax(JText::_("ERR_WRONG_PATH_NEW"));
         return;
      }

      $result = copy($source_path, $target_path);
      if ($result) {
         $lines = file($target_path);

         $handle = fopen($target_path, 'w');

         foreach ($lines as $index => $line) {
            $matches = preg_match("/define\('JPATH_BASE', .*\);/", $line);
            if ($matches) {
               // use JPATH_ROOT always for path to joomla:
               $tmp = $this->replaceDS(JPATH_ROOT);
               $tmp = preg_replace('#\\' . DIRECTORY_SEPARATOR . '#', '\'.DIRECTORY_SEPARATOR.\'', $tmp);

               fwrite($handle, "define('JPATH_BASE', '" . $tmp . "' );\n");
            }
            else {
               fwrite($handle, $line);
            }
         }

         fclose($handle);

         unlink($source_path);

         // TODO language
         $this->closeAjax(JText::_("Success moving " . $source_path . " to " . $target_path));
      }
      else {
         // TODO language
         $this->closeAjax(JText::_("Error moving " . $source_path . " to " . $target_path));
      }

      // $mainframe should already be closed here (AJAX request)!
      return;
   }

   function closeAjax($msg = '') {
      if ($msg) {
         echo $msg;
      }

      // exit Joomla - this is only an AJAX request
      $mainframe = JFactory::getApplication();
      $mainframe->close();
   }

   function replaceDS($path) {
      // replace directory separators with locally valid ones
      $tmp = preg_replace('#\\\\#', DIRECTORY_SEPARATOR, $path);
      $tmp = preg_replace('#/#', DIRECTORY_SEPARATOR, $path);
      return $tmp;
   }
}