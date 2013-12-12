<?php
/**
*
* Pirati: Task
* 
* @author      Vaclav Malek <vaclav.malek@pirati.cz>
*/
//if($_SERVER['REMOTE_ADDR']!='94.74.236.204') return false;

// must be run within DokuWiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_PLUGIN.'piratitag/piratitag.class.php');

/**
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
*/
class syntax_plugin_piratitag_tags extends DokuWiki_Syntax_Plugin {

     private $started = false;

     function getType(){ return 'formatting'; }
     function getPType(){ return 'block'; }
     //function getAllowedTypes(){ return array('formatting', 'substition', 'disabled'); }
     function getAllowedTypes(){ return array(); }
     function getSort(){ return 190; }
     function connectTo($mode){ $this->Lexer->addEntryPattern('<pirati_tags>',$mode,'plugin_piratitag_tags'); }
     function postConnect() { $this->Lexer->addExitPattern('</pirati_tags>','plugin_piratitag_tags'); }

     /**
      * Handle the match
      */
     function handle($match, $state, $pos, &$handler){
          switch ($state) {
              case DOKU_LEXER_ENTER:
                   return array($state, $match);
              case DOKU_LEXER_UNMATCHED:
               //$piratitask = new Piratitask();
               //$data = $piratitask->getParsedData($match);
               return array($state, $data);
              case DOKU_LEXER_EXIT:
               return array($state, $match);
          }
          return array();
     }

     /**
      * Create output
     */
     function render($mode, &$renderer, $data) {
          $renderer->info['cache'] = false;
          if($mode == 'xhtml'){
               list($state, $match) = $data;
               switch($state){
                    case DOKU_LEXER_ENTER:
                         $this->started = true;
                         break;
                    case DOKU_LEXER_UNMATCHED:
                         if($this->started){
                              $piratitag = new Piratitag();
                              $piratitag->renderTagsSyntax($renderer);
                              $this->started = false;
                         }
                         break;
                    case DOKU_LEXER_EXIT:
                         return true;
                         break;
               }
          }
     }
}
