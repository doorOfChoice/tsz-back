<?php
require_once __DIR__ . '/lib/core.php';

use \Slim\App;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,PUT,DELETE,GET');

$config = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$conn = new App($config);

$conn->group("/api/v1", function(){
    //==============
    //=   标签控制  =
    //==============
    $this->group("/tagname", function(){
        $tagsC = "\TagsController";
        /*Method   : GET
         *introduce: 获取服务器上的所有标签
         *return :{
              success : 200, {tags: []}
              fail    : 404
          }
         */
        $this->get("[/{start:[0-9]+}/{count:[0-9]+}]", "{$tagsC}:getAll");
        /*Method   : POST
         *introduce: 向服务器新增标签
          *return :{
              success : 200,
              fail    : 401, 403, 404
          }
        */
        $this->post("", "{$tagsC}:post");
         /*Method: DELETE
          *introcude: 删除服务器上的制定标签
          *return :{
              success : 200,
              fail    : 401, 403, 404
          }
         */
        $this->delete("/{tagname}", "{$tagsC}:delete");
    });



    //==============
    //=   图片控制  =
    //==============
    $this->group("/pictures", function(){
        $picturesC = "\PicturesController";
       
         /*Method   : GET
         *introduce: 获取服务器上的指定图片, 可限制范围
         *return :{
              success : 200, {data: []}
              fail    : 404
          }
         */
        $this->get("/specify/{string}/{type:[0-9]+}[/{start:[0-9]+}/{count:[0-9]+}]", 
                   "{$picturesC}:getSpecify");
        
         /*Method   : GET
         *introduce: 获取服务器上的所有图片, 可限制范围
         *return :{
              success : 200, {data: []}
              fail    : 404
          }
         */
        $this->get("[/{start:[0-9]+}/{count:[0-9]+}]", "{$picturesC}:getAll");
        /*Method   : POST
         *introduce: 向服务器新增图片
          *return :{
              success : 200,
              fail    : 401, 403, 404
          }
        */ 
        
        $this->post("", "{$picturesC}:post");
        
        /*Method   : DELETE
         *introduce: 向服务器删除指定图片
          *return :{
              success : 200,
              fail    : 401, 403, 404
          }
        */
        $this->delete("/{hash}", "{$picturesC}:delete");

        //检测是否有同一张图片
        $this->get("/hash/{hash}", "{$picturesC}:check");
    });
});

$conn->run();

