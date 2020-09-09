<?php
/**
 * 上传销售单信息/jky_sale_order
 */
 //调用处理数据方式
 require("PostSign.php");

 //获取销售单信息
function get_saleOrder(){
  include("connect.php");
     
    $sql="SELECT UKID,(trim(GOODSNO)),GOODSNAME,SKUPROPERITESNAME,UNITNAME,ZT,LASTUPDATEON,
    CITY,INNO,TO_CHAR(TRADETIME,'yyyy-mm-dd'),SHOPNAME,WAREHOUSENAME,(TRIM(CUSTOMERNAME)),TRADETYPE,TOTALFEE,
    PAYMENT,RECEIVERNAME,PHONE,MOBILE,CITYNAME,ADDRESS,KCOO||DCTO||DOCO,SELLCOUNT,SELLTOTAL,SELLPRICE,
    WAREHOUSECODE,CUSTOMERACCOUNT
    from jky_sale_order  WHERE ZT = 'N' and  KCOO||DCTO||DOCO= 
        (SELECT KCOO||DCTO||DOCO FROM jky_sale_order WHERE zt = 'N' AND ROWNUM = 1 ) ";

    $result=oci_parse($con,$sql); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    return $result; 
  
    oci_free_statement($result);
    oci_close($con);
  
  }
 
  function update_saleOrder($ukid,$zt,$returncode,$msg,$subcode,$contextid,$tradeNo){

    include("connect.php"); 
    //根绝销售单号修改 返回执行  添加吉客云销售单号
    $sql=" update jky_sale_order set zt = '".$zt."',code = '".$returncode."',msg = '".$msg."',CONTEXTID = '".$contextid
        ."',subcode = '".$subcode ."',TRADENO = '".$tradeNo."',LASTUPDATEON = SYSDATE"
        ." where ukid = '".$ukid."'";

      $result=oci_parse($con,$sql);
      oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
    
      oci_close($con);
  
    return $result;
  
  }

function upload_saleOrder($res){

  while ($row=oci_fetch_row($res)){

    //先拼接明细  变量赋值
    $tradeOrderDetails=array(
      "goodsNo"                => $row[1],    //货品编号
      "goodsName"              => $row[2],    //货品名称
      "specName"               => $row[3],    //规格名称
      "barcode"                => $row[1],    //条码
      "unit"                   => $row[4],    //单位
      "sellPrice"              => $row[24],     //单价
      "sellCount"              => $row[22],     //数量
      "sellTotal"              => $row[14]      //总金额

    );

    $tradeOrderDetails1[]=$tradeOrderDetails;

    $a = array(
      "warehouseCode"          => $row[25],  //仓库编码
      "warehouseName"          => $row[11],  //仓库名称
      "tradeTime"              => $row[9],   //下单时间
      "shopName"               => "仁和堂经销商渠道",  //$row[12] 店铺名称  -- 客户名称   wms直营网店
      "tradeType"              => $row[13],  //$row[13]订单类型(1=零售业务2=代发货3=预售订单5=代销售)
      "totalFee"               => $row[14],  //商品金额
      "payment"                => $row[14],  //应收金额
      "receiverName"           => "收件人",//$row[16],  //收货人
      "phone"                  => "13945678900",//$row[17],  //电话
      "mobile"                 => "13945678900",//$row[18],  //手机
      "state"                  => "江西",//$row[19],     //省
      "city"                   => "樟树",//$row[19],  //城市
      "address"                => "二楼展厅办公室",//$row[20],  //详细地址
      "onlineTradeNo"          => $row[21],  //销售单号
      "customerName"           => $row[12],//客户名称
      "customerAccount"        => $row[26],//客户编码
      "tradeOrderDetails"     => $tradeOrderDetails1
     
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
    $method='oms.trade.ordercreate';
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
    $bizcontent ='{"tradeorder":'.$bizcontent.'}';

    //调用请求
    $json_array=post_sign($method,$bizcontent);
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
        
  $returncode = $json_array["code"];
  $msg = $json_array["msg"];
  $subcode = $json_array["subCode"];
  $contextid= $json_array["result"]["contextId"];
  $tradeNo= $json_array["result"]["data"]["tradeOrder"]["tradeNo"];


  //遍历返回结果集数组
  $arrlength=count($Rows);
  for($x=0;$x<$arrlength;$x++)
  {
  update_saleOrder($Rows[$x]["ukid"],$zt,$returncode,$msg,$subcode,$contextid,$tradeNo);
  }
  return $json_array;

}

//主程序
$result = get_saleOrder();

echo upload_saleOrder($result);

?>
