<?php

class PicturesController extends Controller{
    
    public function __construct(){
        parent::__construct();
    }
    

    public function createPictures($datas, $start=0, $count=null)
    {
       $final = [];
       
       foreach($datas as $index=>$object)
       {
         $pid = $object->pid;
         if(!array_key_exists($pid, $final))
         {
            $final[$pid]['tags'] = [];
            foreach($object as $key=>$value)
            {
                if($key !== 'pid' && $key !== 'tid' && $key !== 'deleteUrl')
                {
                    if($key === 'tagname')
                        $final[$pid]['tags'][] = $value;
                    else
                        $final[$pid][$key]  = $value;    
                }
            }
         }else{
             $final[$pid]['tags'][] = $object->tagname;
         }
       }

       //var_dump($final);
       return array_slice(array_values($final), $start, $count);    
    }

    /**
     * @param: $req=>请求
     * @param  $rep=>响应
     * @param  $param=>参数
     * 获取图片/可分段
     * @return : 有数据返回200和对应数据数组
     *          无数据返回404
     * 
    **/

    public function getAll($req, $rep, $param){
        $shift   = $this->get_shift();
       
        $start   = isset($param['start'])? $param['start'] + $shift : null;
        $count   = isset($param['count'])? $param['count']  : null;
        
        //判断是否应该要限制范围
        $isLimit = count($param) === 2;

        $sql = SQL_FIND_PIC 
             . ' ORDER BY timestamp DESC';
        //echo $sql;
        $exec = $this->sql->prepare($sql);
        $bool = $exec->execute();    

        $array =$this->createPictures($exec->fetchAll(PDO::FETCH_CLASS),
                $isLimit ? $start : 0, $isLimit ? $count : null);
        
        if($bool)
            return $rep->WithJson(['data'=>$array], 200);

        return $rep->withStatus(404);  
    }

   /**
     * @param: $req=>请求
     * @param  $rep=>响应
     * @param  $param=>参数
     * 根据名称查找图片
     * @return : 有数据返回200和对应数据数组
     *          无数据返回404
     * 
    **/
    public function getByName($req, $rep, $param){
      $isLimit = count($param) === 3; 
      
      $filename = isset($param['filename']) ? $param['filename'] : null;

      if($isLimit){
        $shift   = $this->get_shift();
        $start   = isset($param['start'])? $param['start'] + $shift : null;
        $count   = isset($param['count'])? $param['count']  : null;
      }

      if($this->is_str($filename)){
        $exec = $this->sql->prepare(SQL_FIND_PIC_BY_NAME);
        $result = $exec->execute([$filename]);
        $array  = $this->createPictures($exec->fetchAll(PDO::FETCH_CLASS),
                $isLimit ? $start : 0, $isLimit ? $count : null);;
        
        if(!$result){
            return $rep->withStatus(404);
        }

        return $rep->withJson(['data'=>$array], 200);
      }

      return $rep->withStatus(404);
    }

     /**
     * @param: $req=>请求
     * @param  $rep=>响应
     * @param  $param=>参数
     * 根据标签查找图片
     * @return : 有数据返回200和对应数据数组
     *          无数据返回404
     * 
    **/
    public function getByTags($req, $rep, $param){
      $isLimit = count($param) === 2; 
      
      if($isLimit){
        $shift   = $this->get_shift();
        $start   = isset($param['start'])? $param['start'] + $shift : null;
        $count   = isset($param['count'])? $param['count']  : null;
      }
      
      if(count($_GET) === 0){
        return $rep->withStatus(404);
      }
      //检查标签的合法性, 失败用404拒绝
      foreach($_GET as $key => $value){
          if(!$this->is_str($value))
            return $rep->withStatus(404);
          $_GET[$key] = '"' . $value . '"';   
      }  
      
      //构建标签
      $tags = implode(',', $_GET);
      //构建标签查询的语句
      $sql =  SQL_FIND_PIC . ' AND ' . TABLE_TAGS . '.tagname IN ' . "({$tags})" . ' ORDER BY timestamp '; 
           
      $exec = $this->sql->prepare($sql);

      $result = $exec->execute();
      $array  = $this->createPictures($exec->fetchAll(PDO::FETCH_CLASS),
                $isLimit ? $start : 0, $isLimit ? $count : null);;
     
      //查询失败后返回404
      if(!$result )
        return $rep->withStatus(404);

      return $rep->withJson(['data' => $array], 200);        
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
       
       $tempTags = explode(',', $body['tags']);

       $validTags = empty(trim($body['tags'])) || $this->is_tags(tempTags);
       
       $body['tags'] = $tempTags;

       $url       = isset($body['url'])        ? $body['url']       : NULL;
       $durl      = isset($body['deleteUrl'])  ? $body['deleteUrl'] : NULL;
       $tags      = isset($body['tags'])       ? $body['tags']      : NULL;
       $size      = isset($body['size'])       ? $body['size']      : 0;
       $width     = isset($body['width'])      ? $body['width']     : 0;
       $height    = isset($body['height'])     ? $body['height']    : 0;
       $username  = isset($body['username'])   ? $body['username']  : DEFAULT_USERNAME;
       $filename  = isset($body['filename'])   ? $body['filename']  : NULL;
       $timestamp = isset($body['timestamp'])  ? $body['timestamp'] : NULL;
       
       if($this->is_url_up($url)      && 
          $validTags                  &&
          $this->is_url_de($durl)     &&
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
                           ->prepare(SQL_FIND_TAG_BY_NAME);
                   
          //插入Tag预处理器
          $exec_insert_tag = $this->sql
                           ->prepare(SQL_INSERT_TO_TAGS);
          
          //$tags_array      = $this->distinct(preg_split("/\|/", $tags));
          ##JSON测试
          
          $tags_array      = $this->distinct($tags);

          foreach($tags_array as $key=>$tag){
              
            //有非法字符, 不接受请求
            if(!$this->is_str($tag))
               continue;
                
            //标签是否存在
            $exec_find_tag->execute([$tag]);
            
            $array = $exec_find_tag->fetchAll();

            //存在则直接创建关系
            ##注意: 这里用rowCount()将不能用fetchAll取得属性
            if(count($array) !== 0){
                $tid = $array[0]['tid'];
            //不存在则先插入Tag，再创建关系
            }else{
                $exec_insert_tag->execute([$tag]);
                
                $tid = $this->sql->lastInsertId();
            }
            //echo $pid . '_' . $tid . '|';
            $exec_chip->execute([$pid, $tid]);
            
          }

          $this->increase_shift();
          
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
    
       $bool = $this->sql->exec(SQL_DELETE_ALL_FILES);

       return $rep->withStatus($bool ? 404 : 403);
    }

}
