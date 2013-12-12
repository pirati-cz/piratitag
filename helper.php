<?php
/**
 *
 * Pirati: Tag helper
 *
 * @author    Vaclav Malek <vaclav.malek@pirati.cz> 
 *
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_TAB')) define('DOKU_TAB', "\t");

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_TEMP')) define('DOKU_TEMP',DOKU_INC.'data/tmp/');

require_once('piratitag.class.php');

class helper_plugin_piratitag extends DokuWiki_Plugin {

     public function getTags($query){
          $piratitag = new Piratitag();
          return $piratitag->getTags($query);
     }

     public function updateTags($query,$params){
          $piratitag = new Piratitag();
          return $piratitag->updateTags($query,$params);
     }

     public function renderTags($tags){
          $piratitag = new Piratitag();
          return $piratitag->renderTags($tags);
     }
}

