<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-21 09:02:54
 * @LastEditTime: 2020-08-24 09:16:18
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 上传首营商品信息/JKY_goods_gsp
 * @FilePath: \www.rhtapi.com\public\i-001a.php
 */ 

//调用连接
require("PostSign.php"); 

 //获取商品信息
function get_goods_gsp(){

  include("connect.php");
     
    $sql="SELECT UKID,(trim(outSkuCode)),(trim(skuBarcode)),unitName,(trim(registrationNumber)),
                 to_char(approvalDate,'YYYY-MM-DD'),productionDepart,linkPhone,storageConditionCode,
                 storageConditionName,(trim(FIRSTGOODSNAME)),(trim(FIRSTSKUNAME))
          FROM JKY_GOODS_GSP 
          WHERE ZT = 'N'  
          AND ROWNUM = 1";

    $result=oci_parse($con,$sql); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    
      return $result; 
  
    oci_free_statement($result);
    oci_close($con);
}

function update_goods_gsp($ukid,$zt,$code,$msg,$subCode,$contextId){

  include("connect.php"); 

  $sql=" update JKY_GOODS_GSP set zt = '".$zt."',code = '".$code."',msg = '".$msg."',
  subCode = '".$subCode."',contextId = '".$contextId."'  
  where ukid = '".$ukid."'";

    $result=oci_parse($con,$sql);
    oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
  
    oci_close($con);

  return $result;

}

function upload_goods_gsp($res){
  
  while ($row=oci_fetch_row($res)){
        
        
        $a = array(
        "outSkuCode"             => $row[1],
        //"skuBarcode"             => $row[2],
        "unitName"               => $row[3],
        "registrationNumber"     => $row[4],
        "approvalDate"           => $row[5],//date('Y-m-d',strtotime('2 year')),//批文有效期
        "productionDepart"       => $row[6],
        "linkPhone"              => "13833338888",//$row[7],
        "storageConditionCode"   => $row[8],
        "storageConditionName"   => $row[9],
        "firstGoodsName"         => $row[10],
        "firstSkuName"           => $row[11],
        "quality"                => "质量良好"
        );

        $dataList = $a;
        $a = array(
        "ukid"=>$row[0]
        );
        $Rows[]=$a;
    }
    if($a==null){
      echo "没有查询到数据，结束脚本！";die;
    }
        
    //接口方法名
    $method='erp.gspfirstsku.create';
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
    update_goods_gsp($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId);
  }
  
    return $json_array;

}

//主程序
$result=get_goods_gsp();
echo upload_goods_gsp($result);


?>
