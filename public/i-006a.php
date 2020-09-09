<?php
/**
 * 查询销售单信息 jde销售单创建/jky_sale_order
 */
//调用处理数据方式
 
require("PostSign.php");
$startConsignTime = date("Y-m-d H:i:s",strtotime("-15 day"));
$endConsignTime = date("Y-m-d H:i:s");

//获取下发成功的销售单信息 
function get_saleOrder(){
     include("connect.php");
     $sql=" Select TRADENO  ,ORDERNUM  
          from jky_sale_order  WHERE NVL(ZT,' ') = 'X' group by ORDERNUM,TRADENO ";
     $result=oci_parse($con,$sql); 
     oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
     return $result; 
     oci_free_statement($result);
     oci_close($con);
}
//获取销售表id
function get_FE8SPM11_id(){
include("connect.php");
     $sql="  select jky_fe8spm11_s.nextval from dual ";
     $result=oci_parse($con,$sql); 
     oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
     return $result; 
     oci_free_statement($result);
     oci_close($con);
}
     
//统一的查询返回方法
function GET($SELECT){
     include("connect.php"); 
     $RESULT = oci_parse($con,$SELECT); 
     oci_execute($RESULT,OCI_DEFAULT); 
     return $RESULT;
     oci_free_statement($RESULT);
     oci_close($con);
}

function get_orders(){
     include("connect.php");
     global $startConsignTime;
     global $endConsignTime;

     $resultData=get_saleOrder();
     //存放获取销售单号参数
     $reqTradeNos="";
     while ($row=oci_fetch_row($resultData)){
          $tradeNo=$row[1];
          $JKYTRADENO=$row[0];
          $reqTradeNos=$reqTradeNos.$JKYTRADENO.",";
     }
     //截取字符串
     $reqTradeNos = substr($reqTradeNos,0,(strlen($reqTradeNos)-1));

     //每次请求条数
     $pagesize=20;
     //先用同样的参数获取总条数
     $bizcontent='{"tradeNo": "'.$reqTradeNos.'","fields":"tradeno","pageindex":0,"tradeStatus":"6000",
          "startconsigntime": "'.$startConsignTime.'","endconsigntime": "'.$endConsignTime.'","hasTotal":"1"}';
     $method="oms.trade.fullinfoget";
    
     //调用请求
     $json_array_count=post_sign( $method,$bizcontent);
     $sumcounts=$json_array_count["result"]["data"]["totalResults"];
     $totalPageNum = ($sumcounts  +  $pagesize  - 1) / $pagesize;

     $totalPageNum=intval($totalPageNum);
   
     $SDPMPN=date("Y-m-d H:i:s");//获取当前时间 --  存储过程需要根据这个时间来删除数据

     //循环总的页数
     for($t=0;$t<$totalPageNum;$t++){
          echo "<br>当前页数：".$t."  总页数：".$totalPageNum;
          $bizcontent='{"tradeNo": "'.$reqTradeNos.'","pagesize": '.$pagesize.',"fields":"tradeno,tradeTime,orderno,shopId,shopname,companyname,warehouseName,logisticname,mainpostid,goodsDetail.goodsNo,
               goodsDetail.goodsName,goodsDetail.unit,goodsDetail.specName,goodsDetail.sellTotal,goodsDetail.sellCount,goodsDetail.sellPrice
               ,tradeFrom,tradeType,goodsDetail.goodsName,goodsDetail.goodsName,goodsDetail.goodsName,customerAccount,customerName,goodsDetail.subTradeId,tradeStatus,tradeTime,
               goodsDetail.sourceTradeNo,goodsDetail.platCode,goodsDelivery.id,goodsDelivery.subTradeId,goodsDelivery.tradeId,goodsDelivery.specId,goodsDelivery.goodsId,goodsDelivery.sendCount,goodsDelivery.batchNo"
               ,"tradeStatus":"6000","pageindex": '.$t.',"startconsigntime": "'.$startConsignTime.'","endconsigntime": "'.$endConsignTime.'","hasTotal":"0"}';
         // $method="oms.trade.fullinfoget";
         //调用请求
         $json_array=post_sign( $method,$bizcontent); 
          echo '<br>';
          //处理返回结果
          if ($json_array["code"]=="200"){
               $zt2 = "Y";
          } else{
               $zt2 = "E";
          }
          $returncode = $json_array["code"];
          $msg = $json_array["msg"];
          $subCode = $json_array["subCode"];
          $contextid= $json_array["result"]["contextid"];
          $data= $json_array["result"]["data"]["trades"];
          $num=count($data);//获取明细业务数据
          echo "<br>时间：".$SDPMPN;
          for($i=0;$i<$num;$i++){
               $tradeTime=$data[$i]["tradeTime"];
               $shopCode=$data[$i]["shopCode"];
               $tradeFrom=$data[$i]["tradeFrom"];
               $tradeNo=$data[$i]["tradeNo"];
               $shopName=$data[$i]["shopName"];
               $shopTypeCode=$data[$i]["shopTypeCode"];
               $tradeId=$data[$i]["tradeId"];
               $tradeType=$data[$i]["tradeType"];
               $goodsDetail=$data[$i]["goodsDetail"];
               $goodsDelivery=$data[$i]["goodsDelivery"];
               $customerAccount=$data[$i]["customerAccount"]; 
               $tradeStatus=$data[$i]["tradeStatus"];   //订单状态
               $warehouseCode = $data[$i]["warehouseCode"];  //仓库编号
               if($warehouseCode != null){
                    $SELECT_MCU = "SELECT MCU FROM JKY_WAREHOUSE WHERE WAREHOUSECODE = '$warehouseCode'";
                    $mcu = oci_fetch_row(GET($SELECT_MCU))[0];
               }
               $shopid = $data[$i]["shopId"];
               $SDE8RMK200=$tradeNo;
               //获取自增id
               $resultData_id= get_FE8SPM11_id();
               $row_id=oci_fetch_row($resultData_id)[0];

               if($goodsDelivery != null){
                    $num_d=count($goodsDelivery);
                    // echo "<br>发货数量".$num_d;
               }
               if($goodsDetail != null){
                    $num_de=count($goodsDetail);
                    // echo "<br>销售数量".$num_de;
               }
               $sourceTradeNo='';
               for ($g=0;$g<$num_de;$g++){

                    $SaleSubTradeId=$data[$i]["goodsDetail"][$g]["subTradeId"];

                    // echo "<br>存在销售商品行，继续处理发运行。商品行数：".$num_de;

                    echo "<br>SaleSubTradeId：".$SaleSubTradeId."<br>";
                    $deliveryExists = "N";
                    
                    for ($v=0;$v<$num_d;$v++){
                         $subTradeId = $goodsDelivery[$v]["subTradeId"];  //明细id
                         $tradeId = $goodsDelivery[$v]["tradeId"]; 
                         $specId = $goodsDelivery[$v]["specId"]; 
                         $goodsId = $goodsDelivery[$v]["goodsId"]; 
                         $sendCount = $goodsDelivery[$v]["sendCount"];  //货品发货明细  数量
                         $batchNo = $goodsDelivery[$v]["batchNo"]; //货品发货明细  批次号
                         $deliveryId = $goodsDelivery[$v]["id"]; //发货行主键id唯一，去重
                         echo '批次号：'.$batchNo.'<br>';
                         if($tradeType!=""&&$tradeType=="1"&&$SaleSubTradeId==$subTradeId){ 
                              $insert_sql = "INSERT INTO JKY_B2C_SALET (tradeTime,tradeFrom,tradeNo,shopName,tradeType,
                              LASTUPDATE,tradeId,subTradeId,specId,goodsId,sendCount,batchNo,warehouseCode,mcu,deliveryId,shopid)
                              values(to_date('$tradeTime','yyyy-MM-dd hh24:mi:ss'),$tradeFrom,'$tradeNo','$shopName','$tradeType',
                              '$SDPMPN','$tradeId','$subTradeId','$specId','$goodsId','$sendCount','$batchNo',
                              '$warehouseCode',lpad(TRIM('".$mcu."'),12,' '),'$deliveryId','$shopid')";
                              $result = oci_parse($con,$insert_sql);
                              oci_execute($result,OCI_COMMIT_ON_SUCCESS);
                              oci_close($con);
                              echo "<br> B2C新增---".$insert_sql;
                              $deliveryExists = "Y";
                         }  
                    }  
                    if($deliveryExists == "N"){
                         echo "<br> 不进行批次管理的商品行，没有delivery";
                         // 不进行批次管理的商品行，没有delivery
                         $tradeId = $data[$i]["tradeId"]; 
                         $sendCount = $data[$i]["goodsDetail"][$g]["sellCount"];  //货品商品明细  数量
                         $batchNo = " "; //货品商品明细  批次号
                         $deliveryId = "0"; //发货行主键id唯一，去重
                         echo '批次号：'.$batchNo.'<br>';
                         if($tradeType!=""&&$tradeType=="1"){ 
                              $insert_sql = "INSERT INTO JKY_B2C_SALET (tradeTime,tradeFrom,tradeNo,shopName,tradeType,
                              LASTUPDATE,tradeId,subTradeId,specId,goodsId,sendCount,batchNo,warehouseCode,mcu,deliveryId,shopid)
                              values(to_date('$tradeTime','yyyy-MM-dd hh24:mi:ss'),$tradeFrom,'$tradeNo','$shopName','$tradeType',
                              '$SDPMPN','$tradeId','$SaleSubTradeId','$specId','$goodsId','$sendCount','$batchNo',
                              '$warehouseCode',lpad(TRIM('".$mcu."'),12,' '),'$deliveryId','$shopid')";
                              $result = oci_parse($con,$insert_sql);
                              oci_execute($result,OCI_COMMIT_ON_SUCCESS);
                              oci_close($con);
                              echo "<br> B2C新增---".$insert_sql;
                         }  
                         
                    }

                    for ($z=0;$z<$num_de;$z++){
                         $goodsNo = $data[$i]["goodsDetail"][$z]["goodsNo"];
                         //替换商品id
                         if($goodsNo!=null){
                              $select_goodsNo="select JMLITM from FE8J4101 where JME8JOC ='$goodsNo'";
                              // $JdegoodsNos=get_saleOrder_item($goodsNo);
                              $JdegoodsNos_id=oci_fetch_row(GET($select_goodsNo))[0];
                              if($JdegoodsNos_id!=null ){
                                   $goodsNo=$JdegoodsNos_id;
                              }
                         }
                         $unit = $data[$i]["goodsDetail"][$z]["unit"];
                         if($unit != null){
                              $SELECT_UNIT= "SELECT TRIM(DRKY) FROM CRPCTL.F0005 WHERE TRIM(DRDL01) = TRIM('$unit') AND DRSY = '00'AND DRRT = 'UM'";
                              $unit = oci_fetch_row(GET($SELECT_UNIT))[0];
                         }
                         $specName = $data[$i]["goodsDetail"][$z]["specName"];
                         $sellCount = $data[$i]["goodsDetail"][$z]["sellCount"];  //货品详情   数量
                         $goodsName = $data[$i]["goodsDetail"][$z]["goodsName"];
                         // $tradeId = $data[$i]["goodsDetail"][$z]["tradeId"];
                         $sellPrice = $data[$i]["goodsDetail"][$z]["sellPrice"];  //单价
                         $subTradeId = $data[$i]["goodsDetail"][$z]["subTradeId"];  //明细id
                         $sellTotal = $data[$i]["goodsDetail"][$z]["sellTotal"];  //总价格
                         $sourceTradeNo = $data[$i]["goodsDetail"][$z]["sourceTradeNo"];//jde 订单号  用来区分jde（有字符串） 或者 中台数据（纯数字）
                         $v_num=$z+1;
                         if($sourceTradeNo!=''&&$sourceTradeNo!=null){
                              $isZT=substr($sourceTradeNo,0,2);
                              if($isZT=="ZT"){
                                   echo $sourceTradeNo."是中台销售单,保存！";
                                   if($tradeType!=""&&$tradeType=="9"){ //B2B类型对应 tradeType =9  批发业务，B2C对应 1=零售业务
                                        $insert_sql = "INSERT INTO FE8SPM11_t (SDUKID,SDNLID,SDMCU,SDMCU2,SDLITM,SDE8NAME,SDUOM1,SDE8DSC2,SDUORG,SDCREC,SDDRQJ,SDOPDJ,SDEV01,SDEV02,
                                        SDUPRC,SDVR01,SDE8RMK200,SDPID,SDJOBN,SDUPMJ,SDTDAY,SDUK01,SDVR02,SDSHAN,SDDL08,SDDL07) 
                                        values($row_id,'0',lpad(TRIM('".$mcu."'),12,' '),'$customerAccount','$goodsNo','$goodsName','$unit','$specName',($sellCount*100),($sellCount*100),f_date_to_julian(to_date('$tradeTime','yyyy-MM-dd hh24:mi:ss')),f_date_to_julian(to_date('$tradeTime','yyyy-MM-dd hh24:mi:ss')),'Y','N',
                                                  ($sellPrice*10000),'07','$SDE8RMK200','oms_trade','DB',f_date_to_julian (SYSDATE),TO_CHAR(SYSDATE,'HH24MISS'),'$v_num'
                                                  ,'07','$customerAccount','$SDPMPN','$subTradeId')";
                                        $result = oci_parse($con,$insert_sql);
                                        oci_execute($result,OCI_COMMIT_ON_SUCCESS);
                                        oci_close($con);
                                        echo "<br> B2B新增---".$insert_sql;   
                                   }
                              }else{
                                   echo "<br>".$sourceTradeNo."这一单是jde的销售单，不保存！";
                              }
                         }else if($tradeType!=""&&$tradeType=="1"){ 
                              $update_sql="update JKY_B2C_SALET set goodsNo='$goodsNo' ,unit='$unit',specName='$specName',sellCount='$sellCount',sellTotal='$sellTotal',ZT='N'
                              ,goodsName='$goodsName',sellPrice='$sellPrice' where subTradeId = '$subTradeId' and LASTUPDATE='$SDPMPN' "; 
                              $result = oci_parse($con,$update_sql);
                              oci_execute($result,OCI_COMMIT_ON_SUCCESS);
                              oci_close($con);
                              echo "<br> B2C修改商品明细---".$update_sql;
                         }  
                    }
               }
          }
     };
    //调用存储过程--将替换表中的数据写入正式表中去重
     try {
          $sql = 'BEGIN JKY_BUS_INBOUND.JKY_E7_SAVE(:p_tablename, :p_lastupdateon); END;';
          $stmt = oci_parse($con,$sql);
          $tablename = "FE8SPM11";
          oci_bind_by_name($stmt,':p_tablename',$tablename,32);
          oci_bind_by_name($stmt,':p_lastupdateon',$SDPMPN,30);
          oci_execute($stmt);
          echo '获取数据为：'.oci_bind_by_name($stmt,':p_tablename',$tablename,32).'<br>';
          oci_close($con);
     } catch (Exception $th) {
         echo '错误信息：'.$th->getMessage().'<br>';
     }  
     
     try {
          $sql_c = 'BEGIN JKY_BUS_INBOUND.JKY_E7_SAVE(:p_tablename, :p_lastupdateon); END;';
          $stmt_c = oci_parse($con,$sql_c);
          $tablename_c = "b2cSaleOrder";
          oci_bind_by_name($stmt_c,':p_tablename',$tablename_c,32);
          oci_bind_by_name($stmt_c,':p_lastupdateon',$SDPMPN,30);
          oci_execute($stmt_c);
          echo '获取数据为ss：'.oci_bind_by_name($stmt_c,':p_tablename',$tablename_c,32).'<br>';
          oci_close($con);
     }catch (Exception $th) {
          echo '错误信息：'.$th->getMessage().'<br>';
     }  
 

}

//主程序
$result = get_orders();
echo $result;
?>