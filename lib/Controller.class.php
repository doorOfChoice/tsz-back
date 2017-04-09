<?php

class Controller {
    protected $sql;
    protected $redis;
    public function __construct(){
        $conn = 'mysql:dbname=' . DBNAME . ';host=' . HOST . ';port=' . PORT;

        $this->sql = new PDO($conn, USERNAME, PASSWORD);
        //创建mysql基础表
        $this->sql->exec(FILE_CREATE);
        $this->sql->exec(TAGS_CREATE);
        $this->sql->exec(CHIP_CREATE); 

        //建立redis链接
        $this->redis = new Redis();
        $this->redis->pconnect("127.0.0.1", 6379);


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

    //增加redis数据库里面所有临时用户的下标
    public function increase_shift(){
       $allkeys = $this->redis->keys('*');
       
       foreach($allkeys as $index=>$key){
           $this->redis->incr($key);
       }
    }
    //获取当前用户的偏移
    public function get_shift(){
        $shift = $this->redis->get(session_id());
        //延长生命周期
        $this->redis->setTimeout(session_id(), 3600*24*30);
        return $shift ? $shift : 0;
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
