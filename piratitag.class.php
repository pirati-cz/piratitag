<?php

if(!defined('DOKU_INC')) die('no DOKU_INC defined!');
if(!defined('DOKU_TPL')) die('no DOKU_TPL defined!');
if(!defined('DOKU_PLUGIN')) die('no DOKU_PLUGIN defined');

include('database.class.php');
//include('template.class.php');

class Piratitag {

     // system
     private $lang = array();
     private $conf = array();
     //
     private $info = null;
     private $jsinfo = null;
     private $act = null;
     private $id = null;
     private $text = null;

     // parsed
     private $parsed = false;
     private $tag = null;
     private $namespace = 'unknow'; // not parsed
     private $taskid = 0;

     //
     private $db = null;
     private $template = null;
     private $helper = null;
     private $settings = null;
     private $groups = null;
     
     function __construct(){
         global $conf;
          global $ID;
          global $ACT;
          global $INFO;
          global $JSINFO;
          global $TEXT;
          $this->info =& $INFO;
          $this->jsinfo =& $JSINFO;
          $this->id =& $ID;
          $this->act =& $ACT;
          $this->text =& $TEXT;
          
          // plugin lang
          $path = DOKU_PLUGIN.'piratitag/lang/';
          $lang = array();
          @include($path.'en/lang.php');
          if ($conf['lang'] != 'en') @include($path.$conf['lang'].'/lang.php');
          $this->lang = $lang;

          // plugin conf
          $path = DOKU_PLUGIN.'piratitag/conf/';
          $cnf = array();
          if(@file_exists($path.'default.php')){
               include($path.'default.php');
          }
          $this->conf = $conf;
      
          //
          //if($this->isAuth()) $this->settings = $this->getDb()->getSettings($this->getUserGaid());
     }

     /***** ACTIONS *****/
     
     public function boostmeAction(&$event, $param){
     }

     public function boostjsAction(&$event, $param){
         //$this->jsinfo['user']['gaid'] = $this->info['userinfo']['id'];
         //$this->jsinfo['user']['username'] = $this->info['userinfo']['username'];
     }
     public function updateTagsAction(&$event, $param){
          $tags = array();
          if($this->getConf('blogtng')){
               $blog = $this->getBlogTngHelper();
               $pid = md5($this->getHelper()->getID());
               $blog->load($pid);
               $tags = $blog->tags;
          }
          
          $cnt = preg_match_all("/<pirati_tags>(.+)<\/pirati_tags>/",$event->data[0][1],$m);
          if($cnt>0) $tags = array_merge($tags,explode(',',$m[1][0]));

          $tags = array_unique(array_map('mb_strtolower',array_map('trim',$tags)));
          $this->getDb()->insertTags(
               array('page'=>$this->getHelper()->getID()),
               array('tags'=>$tags)
          );
     }

     public function ajaxAction(&$event, $param){
          //if(auth_quickaclcheck($this->id) < AUTH_READ) return false;
          //$event->preventDefault();
          ////$event->stopPropagation();
          
          //switch($event->data){
               //case 'piratitask_save': $this->ajaxActionSave(); break;
          //}
     }
     
     /* ajax actions */
     public function ajaxActionUpdate(){
     }

     /***** SYNTAX *****/
     public function renderTagsSyntax($renderer=null,$tags=array()){
          if(!is_null($renderer))  {
               $tags = $this->getTags(array(
                    'page' => $this->getHelper()->getID()
               ));
          }

          $out = '<ul class="piratitag">';
          foreach($tags as $i=>$t){
               $out .= '<li class="label category-'.$t['category'].'"'.(isset($t['params']['color'])?' style="background-color:#'.$t['params']['color'].'"':'').'"><a href="?do=piratitag&amp;tag='.$t['id'].'">'.hsc($t['tag']).'</a></li>';
          }
          $out .= '</ul>';

          if(is_null($renderer)) return $out;
          else $renderer->doc .= $out;
          //$template = $this->getTemplate($renderer);
          //$template->renderTasksSyntax();
     }

     /***** HELPERS *****/

     // other classes
     public function getDb(){
          if(!is_null($this->db)) return $this->db;
          $this->db = new PiratitagDatabase($this);
          return $this->db;
     }
     public function getTemplate($renderer = null){
          if(!is_null($this->template)) return $this->template;
          //$this->template = new Template($this);
          //$this->template->setRenderer($renderer);
          return $this->template;
     }
     public function getHelper(){
          if(!is_null($this->helper)) return $this->helper;
          $this->helper = plugin_load('helper','piratihelper');
          return $this->helper;
     }
     public function getBlogTngHelper(){
          if(!is_null($this->blogtng)) return $this->blogtng;
          $this->blogtng = plugin_load('helper','blogtng_tags');
          return $this->blogtng;
     }

     // globals
     public function getLang($string){
          return $this->lang[$string];
     }
     public function getConf($string){
          return $this->conf[$string];
     }

     //
     public function getTags($query=array()){
          return $this->getDb()->getTags($query);
     }
 
     public function updateTags($where=array(),$data=array()){
          $this->getDb()->updateTags($where,$data);
     }

     public function renderTags($tags=array()){
          return $this->renderTagsSyntax(null,$tags);
     }
}
