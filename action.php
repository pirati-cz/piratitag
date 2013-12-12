<?php
/**
 *
 * Pirati: Tag
 *
 * @author Vaclav Malek <vaclav.malek@pirati.cz>
 *
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');
require_once(DOKU_PLUGIN.'piratitag/piratitag.class.php');

class action_plugin_piratitag extends DokuWiki_Action_Plugin
{
     function register(&$controller){
          $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
          $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, 'update_tags', array ());
          //$controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'boostme');
          //$controller->register_hook('DOKUWIKI_STARTED','AFTER',$this,'boostjs');
          //$controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajax');
     }

     /**
      *  
      *  Inserts a toolbar button
     **/
     public function insert_button(& $event, $param) {
          $event->data[] = array (
               'type' => 'format',
               'title' => $this->getLang('addtags'),
               'icon' => '/lib/plugins/piratitag/img/tagbutton.png',
               'open' => '<pirati_tags>',
               'close' => '</pirati_tags>',
               'block' => true,
               'sample' => $this->getLang('inserttext')
          );
     }

     public function update_tags(& $event, $param){
          $piratitag = new Piratitag();
          $piratitag->updateTagsAction($event,$param);
     }

     //public function boostme(&$event, $param){
     //     $this->init();
       //   $this->piratitask->boostmeAction($event, $param);
     //}
     //public function boostjs(&$event, $param){
          //$this->init();
          //$this->piratitask->boostjsAction($event, $param);
     //}
     //public function ajax(&$event, $param){
          //global $ID;
          //global $INFO;
          //$ID = cleanID($_POST['id']);
          //$INFO = pageinfo();
          //$this->init();
          //$this->piratitask->ajaxAction($event, $param);
     //}
}
