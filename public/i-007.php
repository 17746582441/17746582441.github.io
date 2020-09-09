<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-28 09:30:27
 * @LastEditTime: 2020-08-29 19:07:38
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 上传销售退货订单（SL）/JKY_STOCKIN_ORDER
 * @FilePath: \www.rhtapi.com\public\i-007.php
 */ 

//调用连接 
require("PostSign.php");

 //获取商品信息
function get_stockin_order(){

  include("connect.php");
     
    //获取一个采购订单的所有明细行
    $sql="SELECT ukid,INWAREHOUSECODE,INTYPE,RELDATAID,APPLYUSERNAME,APPLYDEPARTNAME,APPLYDATE,
                OPERATOR,MEMO,SOURCE,CURRENCYRATE,(trim(OUTSKUCODE)),ISCERTIFIED,SKUCOUNT,UNITNAME,SKUPRICE,
                (trim(GOODSNO)),RELDETAILID,ISBATCH,ISSERIAL,BATCHNO,abs(QUANTITY),SERIALNO,QUANTITYSERIALNO
          FROM JKY_STOCKIN_ORDER WHERE ZT = 'N' AND RELDATAID = 
          (SELECT RELDATAID FROM JKY_STOCKIN_ORDER WHERE zt = 'N' AND ROWNUM = 1)";

    $result=oci_parse($con,$sql);
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    
    return $result; 
  
    oci_free_statement($result);
    oci_close($con);
  
}

function update_stockin_order($ukid,$zt,$code,$msg,$subCode,$contextId,$inno){

  include("connect.php"); 

  $sql=" update jky_stockin_order set zt = '".$zt."',code = '".$code."',msg = '".$msg.
       "',subCode = '".$subCode."',contextId ='".$contextId."',inno = '".$inno."'
      where ukid = '".$ukid."'";

    $result=oci_parse($con,$sql);
    oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
  
    oci_close($con);

  return $result;

}

function upload_stockin_order($res){
  
  while ($row=oci_fetch_row($res)){
    
    $batchList = array(
        "productionDate"  =>"2020-06-12",//生产日期
        //"shelfLiftUnit": "月",
        //"shelfLife": 12,
        "expirationDate"  =>"2021-06-11",//到期日期
        "batchNo"               =>$row[20],
        "quantity"              =>$row[21]
        //"batchMemo": "备注信息"
    );
    //$batchList_s[] = $batchList;
    
    $serialList = array(
        "quantity"                =>$row[21],
        "serialNo"                =>$row[22]
    );
    //$serialList_s[] = $serialList;

    $stockInDetailViews = array(   
        "skuPrice"                  =>$row[15],
        "relDetailId"               =>$row[17],
        "isBatch"                   =>$row[18],
        "outSkuCode"                =>$row[11],
        "isSerial"                  =>$row[19],
        "batchList"                 =>$batchList,
        //"skuId": "1020000031",
        "skuCount"                    =>$row[13],
        "goodsNo"                     =>$row[16],
        "unitName"                    =>$row[14],
        "isCertified"                 =>$row[12],
        //"skuBarcode": "B1D0012",
        "serialList"                  =>$serialList
      );
      $stockInDetailViews_s[] = $stockInDetailViews;

      $a = array(
        //"vendCode": "vendCode",
        "memo"                          =>$row[8],
        "source"                        =>"OPEN",//$row[9],//入库申请单来源
        "operator"                      =>$row[7],
        "relDataId"                     =>$row[3],
        //"applyDepartCode": "applyDepartCode",
        "inType"                        =>$row[2],
        "channelCode"                   =>"008",
        //"applyCompanyCode": "applyCompanyCode",
        "inWarehouseCode"               =>$row[1],//仓库编号
        "applyUserName"                 =>$row[4],
        "stockInDetailViews"            =>$stockInDetailViews_s,
        "applyDepartName"               =>$row[5],
        "currencyRate"                  =>$row[10],
        "applyDate"                     =>$row[6]
        //"currencyCode": "CNY"
      );

        $dataList = $a;

        $a = array(
        "ukid"=>$row[0]
        );
        $Rows[]=$a;
    }
        
    //接口方法名
    $method='erp.storage.stockincreate';
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
  $inno = $json_array["result"]["data"]["inNo"];
  
  //遍历返回结果集数组
  
  $arrlength=count($Rows);
   
  for($x=0;$x<$arrlength;$x++)
  {
  
    update_stockin_order($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId,$inno);

  }
  
    return $json_array;

}

//主程序
$result = get_stockin_order();
echo upload_stockin_order($result);

?>
