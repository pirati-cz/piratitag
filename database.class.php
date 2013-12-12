<?php

class PiratitagDatabase {
     
     private $piratitag = null;
     private $db = null;

     public function __construct($piratitag){
          $this->piratitag = $piratitag;
     }

     public function getDbPath(){
          @mkdir(DOKU_INC.'data/piratitag',0710,true); 
          return DOKU_INC.'data/piratitag/'.$this->piratitag->getConf('db');
     }

     public function getDb(){
          if(!is_null($this->db)) return $this->db;
          $this->db = new SQLite3($this->getDbPath());
          $this->db->busyTimeout(5000);
          $this->createTables();
          return $this->db;
     }

     public function closeDb(){
          $this->db->close();
          $this->db = null;
     }

     public function createTables(){
          // tables?
          // tags
          $table = $this->db->querySingle('SELECT name FROM sqlite_master WHERE name="tags"');
          if(is_null($table)){
               /**
                *   id - primary key
                *   category - type of tag
                *   tag - tag name
                *   page - page id
                *   ekey - id of extern system
                *   params - others... ( serialize data )
                *
                * */
               $this->db->exec('CREATE TABLE tags (id INTEGER PRIMARY KEY AUTOINCREMENT, category INTEGER NOT NULL, tag VARCHAR(20) NOT NULL, page VARCHAR(255), ekey VARCHAR(255), params TEXT)');
               $this->db->exec('CREATE INDEX category_idx ON tags (category)');
               $this->db->exec('CREATE INDEX tag_idx ON tags (tag)');
               $this->db->exec('CREATE INDEX page_idx ON tags (page)');
               $this->db->exec('CREATE INDEX ekey_idx ON tags (data)');
          }

          // tag params
          // $table = $this->db->querySingle('SELECT name FROM sqlite_master WHERE name="params"');
          // if(is_null($table)) $this->db->exec('CREATE TABLE params (id INTEGER PRIMARY KEY AUTOINCREMENT, tag VARCHAR(30) NOT NULL, color VARCHAR(6) NOT NULL)');
     }

     public function updateTags($where=array(),$data=array()){
          $q = '';
          foreach($data as $col=>$val){
               if(!empty($q)) $q .= ',';
               $q .= $col.'= :c_'.$col;
          }
          $w = '';
          foreach($where as $col=>$val){
               if(!empty($w)) $w .= ' AND ';
               $w .= $col.'= :w_'.$col;
          }

          $stmt = $this->getDb()->prepare('UPDATE tags SET '.$q.' WHERE '.$w);
          
          foreach($data as $col=>$val){
               if($col=='page' or $col=='ekey' or $col=='params')
                    $stmt->bindValue(':c_'.$col,$this->getDb()->escapeString($val),SQLITE3_TEXT);
               else if($col=='category')
                    $stmt->bindValue(':c_'.$col,$this->getDb()->escapeString($val),SQLITE3_INTEGER);
          }
          
          foreach($where as $col=>$val){
               if($col=='page' or $col=='ekey' or $col=='params')
                    $stmt->bindValue(':w_'.$col,$this->getDb()->escapeString($val),SQLITE3_TEXT);
               else if($col=='category')
                    $stmt->bindValue(':w_'.$col,$this->getDb()->escapeString($val),SQLITE3_INTEGER);
          }

          $ret = $stmt->execute();
          $this->closeDb();
          return $ret;
     }

     public function insertTags($query=array(),$data=array()){ //page,$tags = array(),$ekey='',$category=0){
          // delete
          $q = '';
          foreach($query as $col=>$val){
               if(!empty($q)) $q .= ' AND ';
               $q .= $col.' = :'.$col;
          }
          $stmt = $this->getDb()->prepare('DELETE FROM tags WHERE '.$q);
          foreach($query as $col=>$val){
               $stmt->bindValue(':'.$col,$this->getDb()->escapeString($val));
          }
          $stmt->execute();

          // insert
          $cols = array();
          $vals = array();

          $cols[] = 'category'; $vals[] = ':category';
          if(isset($data['page']) or isset($query['page'])){ $cols[] = 'page'; $vals[] = ':page'; }
          if(isset($data['ekey']) or isset($query['ekey'])){ $cols[] = 'ekey'; $vals[] = ':ekey'; }
          if(isset($data['params']) or isset($query['params'])){ $cols[] = 'params'; $vals[] = ':params'; }
          if(isset($data['tags']) or isset($query['params'])){ $cols[] = 'tag'; $vals[] = ':tag'; }

          $stmt = $this->getDb()->prepare('INSERT INTO tags ('.implode(',',$cols).') VALUES ('.implode(',',$vals).')');

          if(in_array(':category',$vals)){
               $category = (isset($data['category'])?$data['category']:(isset($query['category'])?$query['category']:0));
               $stmt->bindValue(':category',$this->getDb()->escapeString($category),SQLITE3_INTEGER);
          }
          if(in_array(':page',$vals)){
               $page = (isset($data['page'])?$data['page']:(isset($query['page'])?$query['page']:''));
               $stmt->bindValue(':page',$this->getDb()->escapeString($page),SQLITE3_TEXT);
          }
          if(in_array(':ekey',$vals)){
               $ekey = (isset($data['ekey'])?$data['ekey']:(isset($query['ekey'])?$query['ekey']:''));
               $stmt->bindValue(':ekey',$this->getDb()->escapeString($ekey),SQLITE3_TEXT);
          }
          if(in_array(':params',$vals)){
               $params = (isset($data['params'])?$data['params']:(isset($query['params'])?$query['params']:array()));
               $stmt->bindValue(':params',$this->getDb()->escapeString(serialize($data['params'])),SQLITE3_TEXT);
          }
          if(in_array(':tag',$vals)){
               foreach($data['tags'] as $t){
                    $stmt->bindValue(':tag',$this->getDb()->escapeString($t),SQLITE3_TEXT);
                    $ret =  $stmt->execute();
               }
          } else $ret = $stmt->execute();

          $this->closeDb();
          return $ret;
     }

     /*
     public function updateTagsParams($data,$params=array()){
          $data = mb_strtolower(trim($data));
          // update
          $stmt = $this->getDb()->prepare('UPDATE tags SET category = :category, params = :params WHERE data = :data');
          //trigger_error(E_USER_ERROR,var_export($params,true));
          if(isset($params['category'])){
               $category=$params['category'];
               unset($params['category']);
          } else $category=0;
          $stmt->bindValue(':data',$this->getDb()->escapeString($data),SQLITE3_TEXT);
          $stmt->bindvalue(':category',$this->getdb()->escapestring($category),SQLITE3_INTEGER);
          $stmt->bindValue(':params',$this->getDb()->escapeString(serialize($params)),SQLITE3_TEXT);
          $ret =  $stmt->execute();
          $this->closeDb();
          return true;
     }*/

     public function getTags($query=array()){
          $q = '';
          
          if(isset($query['id'])) $q .= 'id= :id';
          if(isset($query['category'])) $q .= (!empty($q)?' AND ':'').'category = :category';
          if(isset($query['page'])) $q .= (!empty($q)?' AND ':'').'page = :page';
          if(isset($query['tag'])) $q .= (!empty($q)?' AND ':'').'tag = :tag';
          if(isset($query['ekey'])) $q .= (!empty($q)?' AND ':'').'ekey = :ekey';

          $stmt = $this->getDb()->prepare('SELECT * FROM tags WHERE '.$q.' ORDER BY tag');
          foreach($query as $col=>$val){
               if($col=='page' or $col=='tag' or $col=='ekey')
                    $stmt->bindValue(':'.$col,$this->getDb()->escapeString($val),SQLITE3_TEXT);
               else
                    $stmt->bindValue(':'.$col,$this->getDb()->escapeString($val),SQLITE3_INTEGER);
          }

          $res = $stmt->execute();
          $rows = array();
          while($row = $res->fetchArray(SQLITE3_ASSOC)){
               $params = unserialize($row['params']);
               switch($row['tag']){
                    case 'e-government': $params['color'] = 'ff4500'; break;
               }
               $rows[] = array('id'=>$row['id'],'category'=>$row['category'],'tag'=>$row['tag'],'params'=>$params);
          }
          $this->closeDb();
          return $rows;
     }
}
