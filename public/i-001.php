<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-21 09:02:54
 * @LastEditTime: 2020-08-22 12:33:19
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 上传商品信息/JKY_goods
 * @FilePath: \www.rhtapi.com\public\i-001.php
 */ 

//调用连接
require("PostSign.php"); 

 //获取商品信息
function get_goods(){

  include("connect.php");
     
    $sql="SELECT ukid,goodsName,(trim(goodsNo)),goodsAlias,cateCode,
          cateName,brandName,(trim(goodsMemo)),shelfLife,shelfLiftUnit,isBatchManagement,
          isPeriodManage,unitName,(trim(outSkuCode)),skuName,skuBarcode,ownerCode
          FROM JKY_goods 
          WHERE ZT = 'N'  
          AND ROWNUM = 1";

    $result=oci_parse($con,$sql); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）

    return $result; 
  
    oci_free_statement($result);
    oci_close($con);
  
}

function update_goods($ukid,$zt,$code,$msg,$subCode,$contextId,$success,$errorMessage){

  include("connect.php"); 

  $sql=" update JKY_goods set zt = '".$zt."',code = '".$code."',msg = '".$msg."',
  subCode = '".$subCode."',contextId = '".$contextId."',success = '".$success."',errorMessage = '".$errorMessage."'  
  where ukid = '".$ukid."'";

    $result=oci_parse($con,$sql);
    oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
  
    oci_close($con);

  return $result;

}

function upload_goods($res){
  
  while ($row=oci_fetch_row($res)){

        $a = array(
        "goodsName"           => $row[1],
        "goodsNo"             => $row[2],
        "goodsAlias"          => $row[3],
        "cateCode"            => $row[4],
        "cateName"            => $row[5],
        "brandName"           => $row[6],
        "goodsMemo"           => $row[7],
        "shelfLife"           => $row[8],
        "shelfLiftUnit"       => $row[9],
        "isBatchManagement"   => $row[10],
        "isPeriodManage"      => $row[11],
        "unitName"            => $row[12],
        "outSkuCode"          => $row[13],
        "skuName"             => $row[14],
        "skuBarcode"          => $row[15],
        "ownerCode"           => $row[16]
        );

        $dataList[] = $a;
        $a = array(
        "ukid"=>$row[0]
        );
        $Rows[]=$a;
    }
    if($a==null){
      echo "没有查询到数据，结束脚本！";die;
    }
        
    //接口方法名
    $method='erp.goods.skuimportbatch';
    //数据表数据
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
    //调用连接
    //$bizcontent = '{"bizdata":'.$bizcontent."}";
    $json_array = post_sign($method,$bizcontent);

    echo "<pre>"."返回结果--"."<br>";
    print_r($json_array);//die;
  
  //处理返回结果
    if ($json_array["code"]=="200"){
      $zt = "Y";
     } 
     else
     {
      $zt = "E";
     }
  //打印成功与否和状态
  $test = $json_array["code"];
  print_r($test);
  print_r($zt);//die;

  $code = $json_array["code"];
  $msg = $json_array["msg"];
  $subCode = $json_array["subCode"];
  $contextId = $json_array["result"]["contextId"];
  
  // 遍历返回结果集数组
  
  $arrlength=count($Rows);
   
  for($x=0;$x<$arrlength;$x++)
  {
  
    $arrlen=count($json_array["result"]["data"]);
    
    $success="";
    $errorMessage="";
  
    for($y=0;$y<$arrlen;$y++){

    $success = $json_array["result"]["data"][$y]["success"];
    $errorMessage = $json_array["result"]["data"][$y]["errorMessage"];

    }
    update_goods($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId,$success,$errorMessage);
  }
  
    return $json_array;

}

//主程序
$result = get_goods();

echo upload_goods($result);

?>
