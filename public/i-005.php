<?php
/**
 * 上传采购退货单信息/JKY_PurchaseReturn_ORDER
 */
//调用处理数据方式
require("PostSign.php");

    //获取采购退货单信息
    function get_PurchaseReturn(){
      include("connect.php");
       
      $sql="SELECT UKID,COMPANYCODE,COMPANYNAME,purchType,VENDID,VENDNAME,BUSIUSERID,CURRENCYCODE,
      CURRENCYRATE,DEPTID,BUSINAME,DEPETNAME,WAREHOUSECODE,WAREHOUSENAME,GOODSID,SKUID,abs(OUTQUANTITY),
      to_char(ORDERTIME,'yyyy-mm-dd'),OUTSTATUS,SETTQUANTITY,GOODSNAME,SKUBARCODE,SENDCOMPANYNAME,SEND,
      SENDTEL,SENDPHONE,SENDCOUNTRYID,SENDCOUNTRYNAME,SENDPROVINCEID,SENDPROVINCENAME,SENDCITYID,
      SENDCITYNAME,SENDADDRESS,RECEIVECOMPANYNAME,RECEIVETEL,RECEIVEPHONE,RECEIVECOUNTRYID,RECEIVEADDRESS,
      RECEIVECOUNTRYNAME,RECEIVEPROVINCEID,RECEIVEPROVINCENAME,RECEIVECITYID,RECEIVECITYNAME,
      KCOO,dcto,doco,OUTSKUCODE,unitName   
       from  JKY_PurchaseReturn_ORDER  WHERE ZT = 'N' AND kcoo||dcto||doco = 
       (SELECT kcoo||dcto||doco FROM JKY_PurchaseReturn_ORDER WHERE zt = 'N'  AND ROWNUM = 1)";
  
      $result=oci_parse($con,$sql); 
      oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
      return $result; 
    
      oci_free_statement($result);
      oci_close($con);
    
    }
    //修改采购退货单回传信息
    function update_PurchaseReturn($ukid,$zt,$code,$msg,$subCode,$contextId,$orderNum){

        include("connect.php"); 

        $sql=" update JKY_PurchaseReturn_ORDER set zt = '".$zt."',code = '".$code."',msg = '".$msg."',
        CONTEXTID = '".$contextId."',subcode = '".$subCode ."',ordernum = '".$orderNum."'
        where ukid = '".$ukid."'";

          $result=oci_parse($con,$sql);
          oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
        
          oci_close($con);
      
        return $result;
      
      }
      //处理数据
      function upload_PurchaseReturn($res){
      
        while ($row=oci_fetch_row($res)){

            //先拼接明细  变量赋值
            $list=array(
              "outSkuCode"                => $row[46],//$row[14],    //外部货品编号
             // "skuId"                  => "1223",//$row[15],    //规格ID
              "quantity"               => $row[16],    // 退货数量
              "unitName"               => $row[47],//$row[21],    //货品单位 暂无
              "recDate"                => $row[17],     //预计出库日期
           //   "outStatus"              => $row[18],    //出库状态 后台生成
           //   "settQuantity"           => $row[19],     //结算数量 (后台生成)
           //   "goodsName"              => $row[20],     //商品名字
           //   "skuBarcode"             => $row[21]      //条码
              "batchNo"                =>"20200828a"
        
            );
            $list_s[]=$list;

            $pruchExpressInfo=array(
                "sendCompanyName"                => "test",//$row[22],    //发货公司名字
                "send"                           => $row[23],    //发件人
                "sendTel"                        => $row[24],    //发件人电话
                "sendPhone"                      => $row[25],     //发件人手机号
                "sendCountryId"                  => $row[26],    //发件人国家id
                "sendCountryName"                => $row[27],     //发件人国家
                "sendProvinceId"                 => $row[28],     //发件人省id
                "sendProvinceName"               => $row[29],      //发件人省名字
                "sendCityId"                     => $row[30],      //发件人市id
                "sendCityName"                   => $row[31],      //发件人市
                "sendAddress"                    => $row[32],      //发件人详细地址
                "receiveCompanyName"             => 'test',//$row[33],      //收件公司名称
                "receiveTel"                     => $row[34],      //收件人电话
                "receivePhone"                   => "13812341234",//$row[35],      //收件人手机号
                "receiveCountryId"               => $row[36],      //收件人国家id
                "receiveAddress"                 => $row[37],      //收件人详细地址
                "receiveCountryName"             => $row[38],      //收件人国家
                "receiveProvinceId"              => $row[39],      //收件人省id
                "receiveProvinceName"            => $row[40],      //收件人省
                "receiveCityId"                  => $row[41],      //收件人市id
                "receiveCityName"                => $row[42] ,     //收件人市
                "isCertified"                    =>"1"             //是否正品0-否 1-是
            
          
              );
              $pruchExpressInfo_s[]=$pruchExpressInfo;
        
            $a = array(
           //   "companyId"                => $row[1],   //公司id
           //   "companyName"              => $row[2],  //$row[12]  公司名字 
              "purchType"                => $row[3],  //退货类型 001 普退退货 002 资产退货 003委外退货
              "purchCode"                => $row[3],  //退货类型(退货类型 001 普退退货 002 资产退货 003委外退货)
              "vendId"                   => $row[4],  //供应商ID
              "vendName"                 => $row[5],  //供应商名字
              "busiUserid"               =>"66",// $row[6],  //业务员ID
              "currencyCode"             => "CNY",//$row[7],  //币种编号
              "currencyName"             => "人民币",//$row[7],  //币种名称 暂无
              "currencyRate"             => "1",//$row[8],  //汇率
              "revwStatus"               => "1",//$row[8],  //采购退货单审核状态0-待递交 1-待审核 2-执行中
              
            //  "deptId"                   => $row[9],     //部门ID
              "busiName"                   => $row[10],  //业务员名字
            //  "deptName"                 => $row[11],  //部门名字           
            //  "warehouseId"              => $row[12],   //退货仓库ID
            //  "warehouseName"            => $row[13],   //出库名字
            "warehouseCode"                => $row[12],//仓库编号
            "vendCode"                     => $row[4],//,//供应商编码 "SXZYCtest" 
            "departCode"                   => "ZD_09",//$row[9],     //部门编码
            "companyCode"                  => $row[1],     //公司编码              
            "list"                         => $list_s,
            "pruchExpressInfo"             => $pruchExpressInfo_s
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
        $method='erp.purchreturn.create';
        //数据表数据
        $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
        //调用请求
        $json_array=post_sign( $method,$bizcontent);

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
  
    $code = $json_array["code"];
    $msg = $json_array["msg"];
    $subCode = $json_array["subCode"];
    $contextId= $json_array["result"]["contextId"];
    $orderNum= $json_array["result"]["data"]["orderNum"];


    //遍历返回结果集数组
  $arrlength=count($Rows);
  for($x=0;$x<$arrlength;$x++)
  {
    update_PurchaseReturn($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId,$orderNum);
  }

    return $json_array;
   
}

//主程序
$result = get_PurchaseReturn();
echo upload_PurchaseReturn($result);

?>