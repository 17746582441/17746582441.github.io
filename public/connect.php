<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-22 16:38:33
 * @LastEditTime: 2020-09-03 14:38:46
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 
 * @FilePath: \www.rhtapi.com\public\connect.php
 */

$con = oci_connect('CRPDTA','ddky.com',"(DESCRIPTION =(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.61.13)	
    (PORT = 1521)))(CONNECT_DATA =(SERVICE_NAME = OERPTST)))",'UTF8');
   
    if (!$con) {
    $e = oci_error();
    print htmlentities($e["message"]);
    exit;
    }else {
    echo "连接oracle成功！"; 
    }

?>