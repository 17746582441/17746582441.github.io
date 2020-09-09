<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-24 08:17:36
 * @LastEditTime: 2020-08-27 16:32:39
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 上传采购订单（OP）/jky_purchase_order
 * @FilePath: \www.rhtapi.com\public\i-004.php
 */ 
 
//调用连接
require("PostSign.php"); 

 //获取商品信息
function get_purchase_order(){

  include("connect.php");
  $ordertime = date('Y-m-d H:i:s',time());//制单日期
     
    //获取一个采购订单的所有明细行
    $sql="SELECT ukid,companyCode,ordertype,vendcode,(trim(vendname)),revwstatus,depetname,
          purchtype,ordernum,purchcode,purtotalprice,purfee,purfeetype,'".$ordertime."',
          (trim(goodsno)),(trim(goodsname)),(trim(skuproperitesname)),unitname,goodsid,skuid,skubarcode,
          quantity,outskucode,warehouseid,to_char(recdate,'YYYY-MM-DD'),price,amount,taxrate,warehousename,
          notaxamount,payableamount,taxprice,rowremark,kcoo||dcto||doco,warehouseCode
          FROM jky_purchase_order WHERE ZT = 'N' AND kcoo||dcto||doco = 
          (SELECT kcoo||dcto||doco FROM jky_purchase_order WHERE zt = 'N' AND ROWNUM = 1)";

    $result=oci_parse($con,$sql); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）

    return $result; 
  
    oci_free_statement($result);
    oci_close($con);
  
}

function update_purchase_order($ukid,$zt,$code,$msg,$subCode,$contextId,$orderNum){

  include("connect.php"); 

  $sql=" update jky_purchase_order set zt = '".$zt."',code = '".$code."',msg = '".$msg.
       "',subCode = '".$subCode."',contextId ='".$contextId."',orderNum = '".$orderNum."'
      where ukid = '".$ukid."'";

    $result=oci_parse($con,$sql);
    oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
  
    oci_close($con);

  return $result;

}

function upload_purchase_order($res){
  
  while ($row=oci_fetch_row($res)){
        
        $list = array(
          "outSkuCode"          =>$row[22],//JDE第二项目号
          "price"               =>$row[25],
          //"skuId"               =>$row[19],
          "taxrate"             =>$row[27],
          "unitName"            =>$row[17],
          "rowRemark"           =>$row[32],
          "recDate"             =>$row[24],
          "noTaxAmount"         =>$row[29],
          "payableAmount"       =>$row[30],
          "amount"              =>$row[26],
          "quantity"            =>$row[21]
          //"skuBarcode"          =>$row[20]
        );
        $list_s[] = $list;
        
        $a = array(
        "busiUserid"          =>"0",//采购员ID
        "busiName"            =>"张三",//采购员名称
        "currencyCode"        =>"CNY",//币种
        "currencyName"        =>"人民币",//币种名称
        "currencyRate"        =>"1",//币种汇率
        "warehouseCode"       =>$row[34],//仓库编号
        "departCode"          =>"ZD_09",//部门编号
        "companyCode"         =>$row[1],//公司编号
        "vendCode"            =>$row[3],
        "orderTime"           =>$row[13],
        "purchCode"           =>$row[9],
        "purTotalPrice"       =>$row[10],
        "purFeeType"          =>$row[12],
        "list"                =>$list_s,
        "purchType"           =>$row[7],
        //"busiUserid"          =>"123",//业务员ID，暂时固定值代替
        //"currencyRate"        =>"1",//汇率，暂时固定值代替
        "purFee"              =>$row[11]
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
    $method='erp.purch.create';
    //数据表数据
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);  
    //调用连接
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
  $orderNum = $json_array["result"]["data"]["orderNum"];
  
  //遍历返回结果集数组
  $arrlength=count($Rows);
   
  for($x=0;$x<$arrlength;$x++)
  {
    update_purchase_order($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId,$orderNum);
  }
    return $json_array;
}

//主程序
$result = get_purchase_order();
echo upload_purchase_order($result);

?>
