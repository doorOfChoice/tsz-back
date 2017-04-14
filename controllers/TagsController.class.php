<?php

class TagsController extends Controller{
    
    public function __construct(){
        parent::__construct();
    }

    //Get获取标签
    public function getAll($req, $rep, $param){
        $start   = isset($param['start'])? $param['start'] : null;
        $count   = isset($param['count'])? $param['count'] : null;

        $isLimit = count($param) === 2;

        $sql = 'SELECT tagname FROM ' . TABLE_TAGS
             . ($isLimit ? " LIMIT $start,$count" : '');
        
        $exec = $this->sql->prepare($sql);
        $bool = $exec->execute();    
        
        $array = $exec->fetchAll(PDO::FETCH_CLASS);

        if($bool){
            $rows = [];
            foreach($array as $row){
                $rows[] = $row->tagname;
            }
            return $rep->WithJson(['data'=>$rows], 200);
        }
        return $rep->withStatus(404);    
    }

    //POST新增标签
    public function post($req, $rep, $param){
       $body = $req->getParsedBody();
       
       $tagname = isset($body['tagname']) ? $body['tagname'] : NULL;
       $tagname = explode(',', $tagname);
       
           
       $tags      = $tagname;
       $exec = $this->sql->prepare(SQL_INSERT_TO_TAGS);
       foreach($tags as $key=>$tag){
                //检测到非法参数,不接受
         if(!$this->is_str($tag))
           return $rep->withStatus(406);
         $exec->execute([$tag]);
       }

       return $rep->withStatus(201);
       
    }   
}