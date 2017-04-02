<?php

class PicturesController extends Controller{
    
    public function __construct(){
        parent::__construct();
    }

    public function getAll($req, $rep, $param){
        
        $start   = isset($param['start'])? $param['start'] : null;
        $count   = isset($param['count'])? $param['count'] : null;
        
        $isLimit = count($param) === 2;
        
        $sql = "SELECT {$GLOBALS['allfile']} FROM " . TABLE_FILE
             . ($isLimit ? " LIMIT $start,$count" : '');
        
        $exec = $this->sql->prepare($sql);
        $bool = $exec->execute();    
        //var_dump($exec->errorInfo());
        $array = $exec->fetchAll(PDO::FETCH_CLASS);

        if($bool && count($array) !== 0)
            return $rep->WithJson(['data'=>$array], 200);

        return $rep->withStatus(404);  
    }

    /**
     * param: $req=>请求
     *        $rep=>响应
     *        $param=>参数
     * introduce: 根据条件获取全部或者部分图片
     * return : 有数据返回200和对应数据数组
     *          无数据返回404
     * 
    **/
    public function getSpecify($req, $rep, $param){
        $type   = isset($param['type'])   ? $param['type'] : NULL;
        $string = isset($param['string']) ? $param['string'] : NULL;
        //属性映射
        $attrname = [NAME => 'filename', TAG => 'tagname'];
        
        if($type && $string && $this->is_str($type) && $this->is_str($string)){
            if(array_key_exists($type, $attrname)){
                //根据条件构建限制语句
                $limit = '';
                if(count($param) === 4)
                    $limit = " LIMIT {$param['start']}, {$param['count']} ";

               switch($type){
                   case NAME: $sql = SQL_FIND_PIC_BY_NAME . $limit; break;
                   case TAG : $sql = SQL_FIND_PIC_BY_TAG  . $limit; break;
                   default  : $sql = NULL;
               }
               
               $exec  = $this->sql->prepare($sql);
               $bool  = $exec->execute([$string]);
               $array = $exec->fetchAll(PDO::FETCH_CLASS);

               if(!$bool || count($array) === 0){
                   return $rep->withStatus(404);
               }
               return $rep->withJson(['data'=>$array], 200);
               
            }
        }
    }
    /**
     * param: $req=>请求
     *        $rep=>响应
     *        $param=>参数
     * introduce: 上传图片，并将图片的Hash值作为文档的主键
     * return : 上传成功返回201
     *          上传失败返回406
     * 
    **/
    public function post($req, $rep, $param){
       $body = $req->getParsedBody();

       $url       = isset($body['url'])        ? $body['url']       : NULL;
       $durl      = isset($body['deleteUrl'])  ? $body['deleteUrl'] : NULL;
       $tags      = isset($body['tags'])       ? $body['tags']      : NULL;
       $size      = isset($body['size'])       ? $body['size']      : NULL;
       $width     = isset($body['width'])      ? $body['width']     : NULL;
       $height    = isset($body['height'])     ? $body['height']    : NULL;
       $username  = isset($body['username'])   ? $body['username']  : DEFAULT_USERNAME;
       $filename  = isset($body['filename'])   ? $body['filename']  : NULL;
       $timestamp = isset($body['timestamp'])  ? $body['timestamp'] : NULL;
       if($this->is_url_up($url)      && 
          $this->is_url_de($durl)     &&
          $this->is_tags($tags)       &&
          $this->is_number($size)     &&
          $this->is_number($width)    &&
          $this->is_number($height)   &&
          $this->is_number($timestamp)&&
          $this->is_str($username)    ){
          //图片插入预处理器
          $exec_insert_pic = $this->sql->prepare(SQL_INSERT_TO_FILE);

          //图片插入不成功直接终止页面
          if(!$exec_insert_pic->execute([
              $url,$durl,$size,$width,$height,$username,$filename,$timestamp])){
                
              return $rep->withStatus(406);
          }
          
          $pid = $this->sql->lastinsertid();

          //创建PID->TID关系预处理器
          $exec_chip       = $this->sql
                           ->prepare(SQL_INSERT_TO_CHIP);
          //查询指定Tag预处理器
          $exec_find_tag   = $this->sql
                           ->prepare(SQL_INSERT_TO_TAGS);
                   
          //插入Tag预处理器
          $exec_insert_tag = $this->sql
                           ->prepare(SQL_INSERT_TO_TAGS);
          
          //$tags_array      = $this->distinct(preg_split("/\|/", $tags));
          ##JSON测试
          
          $tags_array      = $this->distinct($tags);
          foreach($tags_array as $key=>$tag){
              
            //有非法字符, 不接受请求
            if(!$this->is_str($tag))
                return $rep->withStatus(406);
                
            //标签是否存在
            $exec_find_tag->execute([$tag]);
            
            $array = $exec_find_tag->fetchAll();

            //存在则直接创建关系
            ##注意: 这里用rowCount()将不能用fetchAll取得属性
            if(count($array) !== 0){
                $tid = $array['tid'];
            //不存在则先插入Tag，再创建关系
            }else{
                $exec_insert_tag->execute([$tag]);
                
                $tid = $this->sql->lastInsertId();
            }
            $exec_chip->execute([$pid, $tid]);
            
          }
          return $rep->withStatus(201);
       }
       return $rep->withStatus(406);
    }
    /**
     * param: $req=>请求
     *        $rep=>响应
     *        $param=>参数
     * introduce: 删除指定Hash下的图片， 并且从tags中删除图片
     * return : 删除成功返回200
     *          文件不存在返回404
     * 
    **/

    public function delete($req, $rep, $param){
       
    }


    public function check($req, $rep, $param){
        
    }

    

}