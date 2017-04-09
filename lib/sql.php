<?php
$GLOBALS['file'] = ['url', 'size', 'width', 'height', 'filename', 'username', 'timestamp'];
$GLOBALS['allfile'] = implode(',', $GLOBALS['file']);

//创建标签表
define('TAGS_CREATE', 'CREATE TABLE IF NOT EXISTS ' . TABLE_TAGS . '(
    tid INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    tagname VARCHAR(60) NOT NULL UNIQUE
)');

//创建文件表
define('FILE_CREATE', 'CREATE TABLE IF NOT EXISTS ' . TABLE_FILE . '(
    pid INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(100) NOT NULL UNIQUE,
    deleteUrl VARCHAR(100) NOT NULL UNIQUE,
    size INT   ,
    width INT  ,
    height INT ,
    filename VARCHAR(60) NOT NULL ,
    username VARCHAR(60)  ,
    timestamp INT NOT NULL
)');

//创建关系表
define('CHIP_CREATE', 'CREATE TABLE IF NOT EXISTS ' . TABLE_CHIP . '(
    tid INT NOT NULL ,
    pid INT NOT NULL ,
    FOREIGN KEY(tid) REFERENCES '. TABLE_TAGS . '(tid) ON DELETE CASCADE,
    FOREIGN KEY(pid) REFERENCES '. TABLE_FILE . '(pid) ON DELETE CASCADE
)');

//查找指定标签的图片
define('SQL_FIND_PIC_BY_TAG', "
SELECT {$GLOBALS['allfile']} FROM " . TABLE_FILE . ' WHERE pid IN
(
  SELECT pid FROM '. TABLE_CHIP .' WHERE tid IN
    (
     SELECT tid FROM '. TABLE_TAGS.' WHERE tagname=?
    )
)');

//查找指定的标签
define('SQL_FIND_TAG_BY_NAME', 'SELECT * FROM' . TABLE_TAGS . 'WHERE tagname=?');

//查找指定名称的图片
define('SQL_FIND_PIC_BY_NAME', 
"SELECT {$GLOBALS['allfile']} FROM ". TABLE_FILE .' WHERE filename=? ');

//插入图片
define('SQL_INSERT_TO_FILE', 'INSERT INTO' . TABLE_FILE 
. '(url, deleteUrl, size, width, height, username,filename, timestamp) ' 
. ' VALUES(?,?,?,?,?,?,?,?)');

//插入关系
define("SQL_INSERT_TO_CHIP", 'INSERT INTO' . TABLE_CHIP . '(pid, tid) VALUES(?,?)');

//插入标签
define('SQL_INSERT_TO_TAGS', 'INSERT INTO' . TABLE_TAGS . '(tagname) VALUES(?)');

define('SQL_DELETE_ALL_FILES', 'DELETE FROM ' . TABLE_FILE);