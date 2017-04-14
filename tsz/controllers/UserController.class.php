<?php

class UserController extends TagsController {

    public function __construct(){
        parent::__construct();
    }

    //新增一个session用户
    public function setCookie($req, $rep, $next){
       
        if(!isset($_COOKIE['tszshift'])){
            $tszshift = md5(rand() . ''  . time());
            setcookie('tszshift', $tszshift);
            $_COOKIE['tszshift'] = $tszshift;
            $this->redis->set($tszshift, 0);
            $this->redis->setTimeout($tszshift, 3600*24);
           
        }

        $rep = $next($req, $rep);
        
        return $rep;
    }
}