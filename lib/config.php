<?php
//============================================
//****************数据库信息区域****************
//============================================
//端口号
define('PORT', 3306);
//主机号
define('HOST', "localhost");
//数据库名
define('DBNAME', 'test');

define("USERNAME", 'root');

define("PASSWORD", '1997');

//用户集合
define('TABLE_USER', ' tsz_user ');
//图片集合
define('TABLE_FILE', ' tsz_file ');
//标签集合
define('TABLE_TAGS', ' tsz_tags ');

define('TABLE_CHIP', ' tsz_chip ');

define("DEFAULT_USERNAME", "anonymous");

//============================================
//****************×全局变量区域*****************
//============================================
define('ERROR_USER_EXIST', '帐号已经存在');
define('ERROR_USER_FORMAT', '用户格式错误,只能为英文和字母,长度6~18');
define('ERROR_PASS_FORMAT', '密码格式错误,只能为英文和字母,长度6~24');
define('ERROR_PASS_WRONG', '密码错误');
define('ERROR_UPLOAD', '上传出现未知错误');
define('ERROR_LOGOUT', '注销失败');
define('ERROR_OUTDATE', '用户信息已经过期, 请重新登录');
define('ERROR_INVALID', '非法参数');
define('ERROR_FILE_EXIST', '重复添加');
define('ERROR_DATABASE', '数据库操作失败');
define('ERROR_FILE_NOT_EXIST', '文件不存在');
define('STATUS_FAIL', 500);
define('STATUS_GOOD', 200);

define("DS", "/");
define("ROOT", dirname(__DIR__) . DS);
define("CONTROLLER", ROOT . 'controllers' . DS);

//==============================================
//==============================================
//==============================================
define('NAME', '1');
define('TAG',  '2');

//==============================================
