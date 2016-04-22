<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class ShortlinkModelShortlink extends JModelLegacy {
   function getShortlink($phrase) {
      $db = $this->getDbo();

      $query = 'SELECT * FROM #__shortlink';
      $query .= ' WHERE phrase = ' . $db->quote($db->escape($phrase), false);

      $db->setQuery($query);
      $shortlink = $db->loadObject();

      if ($shortlink) {
         // update statistics
         $now =& JFactory::getDate();

         $shortlink->counter++;
         $shortlink->last_call = $now->toSql();
         $db->updateObject('#__shortlink', $shortlink, 'id', true);
      }

      return $shortlink;
   }

   function getArticleUrl($artid) {
      $db =$this->getDbo();
      $mainframe = &JFactory::getApplication();

      if (!class_exists('ContentHelperRoute')) {
         require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_content' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'route.php');
      }

      $query = "SELECT a.id, a.title AS arttitle, a.alias AS artalias, c.id as catid, c.alias AS catalias";
      $query .= " FROM #__content AS a ";
      $query .= " LEFT JOIN #__categories AS c ON a.catid = c.id ";
      $query .= " WHERE a.state=1 ";
      $query .= " AND a.id=" . (int)$artid;

      $db->setQuery($query);
      $article = $db->loadObject();

      $result = null;

      if ($article) {
         if (empty($article->catid) || empty($article->catalias)) {
            $result = ContentHelperRoute::getArticleRoute($article->id . ':' . $article->artalias);
         }
         else {
            $result = ContentHelperRoute::getArticleRoute($article->id . ':' . $article->artalias, $article->catid . ':' . $article->catalias);
         }

         if (strpos($result, '&Itemid=') < 0) {
            $itemid = $mainframe->getItemid($article->id);
            if ($itemid) {
               $result .= "&Itemid=" . $itemid;
            }
         }
      }

      return $result;
   }
}

?>