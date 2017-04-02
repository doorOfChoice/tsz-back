<?php

class Controller {
    protected $sql;

    public function __construct(){
        $conn = 'mysql:dbname=' . DBNAME . ';host=' . HOST . ';port=' . PORT;

        $this->sql = new PDO($conn, USERNAME, PASSWORD);
        //创建基础表
        $this->sql->exec(FILE_CREATE);
        $this->sql->exec(TAGS_CREATE);
        $this->sql->exec(CHIP_CREATE); 
    }

    //判断请求元素是否是>=0的数字
    public function is_number($str){
        return $str !== NULL &&
               strlen(trim($str)) !== 0 &&
               preg_match_all("/^[0-9]+$/", $str);
    }

    //判断是否为合法字符串
    public function is_str($str){
        return $str !== NULL &&
               strlen(trim($str)) !== 0 &&
               preg_match_all("/^[a-z|A-Z|0-9|\u{4e00}-\u{9fa5}$]+$/", $str);
    }

    //检测标签JSON里面有没有非法字符
    public function is_tags($tags){
        $rows = [];
        foreach($tags as $key=>$tag){
            if(!$this->is_str($tag)){
                return FALSE;
            }
        }
        return TRUE;
    }
    //匹配sm.ms上传文件地址
    public function is_url_up($str){
        $rex = "/^https:\/\/ooo.0o0.ooo\/[0-9]{4}\/(1[0-2]|0[1-9])\/(0[1-9]|[1-3][0-9])\/[a-z|A-Z|0-9]{13}\.(jpg|png|gif|jpeg|svg)$/";
        return $str !== NULL &&
               strlen(trim($str)) !== 0 &&
               preg_match_all($rex, $str);

    }
    //匹配sm.ms删除文件地址    
     public function is_url_de($str){
       
        $rex = "/^https:\/\/sm.ms\/api\/delete\/[0-9|a-z|A-Z]{15}$/";
        return $str !== NULL &&
               strlen(trim($str)) !== 0 &&
               preg_match_all($rex, $str);

    }
    //获取数组中不同的值
     public function distinct(array $tags){
        $row = [];
        foreach($tags as $key=>$tag){
            if(!array_key_exists($tag, $row))
                $row[] = $tag;       
        }

        return $row;
    }
}
