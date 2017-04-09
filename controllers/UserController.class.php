<?php

class UserController extends TagsController {

    public function __construct(){
        parent::__construct();
    }

    //新增一个session用户
    public function setCookie($req, $rep, $param){
        
        $this->redis->set(session_id(), 0);
        $this->redis->setTimeout(session_id(), 3600*24*30);
        return $rep->withStatus(201);
    }
}