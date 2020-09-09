<?php
/*
 * @Author: 李志勇
 * @Date: 2020-08-24 10:23:42
 * @LastEditTime: 2020-08-24 11:53:51
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 采购单取消
 * @FilePath: \www.rhtapi.com\public\i-004b.php
 */


//调用curl方式 方法
require("PostSign.php");

 //获取客户信息 
 function get_pucrchOrder(){
    //变量
    include("connect.php");
      $sql = "SELECT DISTINCT (trim(FS.SDE8RMK200)) FROM FE8SPM11_S FS,FE8SPM11 F1 WHERE FS.ZT = 'N' AND FS.SDE8RMK200 = F1.SDE8RMK200 AND F1.SDDCTO IS NOT NULL";
      $result = oci_parse($con,$sql); 
      oci_execute($result,OCI_DEFAULT);  
      return $result; 
      oci_free_statement($result);
      oci_close($con);
  }

function UPLATE($UPDATE){
  include("connect.php"); 
  $RESULT = oci_parse($con,$UPDATE); 
  oci_execute($RESULT,OCI_COMMIT_ON_SUCCESS); 
  oci_free_statement($RESULT);
  oci_close($con);
}

function update_purchOrder($res){
  while($row = oci_fetch_row($res)){
    $SDE8RMK200 = $row[0];

    $method='erp.purch.cancel';
    //$bizcontent = '{"bizdata":'.'"'.$SDE8RMK200.'"'.'}';
    $json_array = post_sign($method,$SDE8RMK200);

    echo "<pre>"."返回结果--"."<br>";
    print_r($json_array);//die;

    $code = $json_array["code"];
    $msg = $json_array["msg"];
    $subCode = $json_array["subCode"];
    $contextid = $json_array['result']['contextId'];
  
    if($code =="200"){
      $zt = "Y";
    }else{
      $zt = "E";
    }//die;

    $update = "UPDATE FE8SPM11_S SET ZT = '$zt',CODE = '$code',MSG = '$msg',CONTEXTID = '$contextid',
               subCode = '$subCode'
               WHERE SDE8RMK200 = '$SDE8RMK200'";

    UPLATE($update);
  }     

 
}

$result = get_pucrchOrder();
echo update_purchOrder($result);

?> 