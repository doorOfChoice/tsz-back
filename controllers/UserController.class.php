<?php

class UserController extends TagsController {

    public function __construct(){
        parent::__construct();
    }

    //新增一个session用户
    public function postSession($req, $rep, $param){
        $this->redis->set(session_id(), 0);
        $this->redis->setTimeout(session_id(), 1800);
        return $rep->withStatus(201);
    }
}