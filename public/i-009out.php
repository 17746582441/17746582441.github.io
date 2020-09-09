<?php
/**
 * 入库单信息查询/FE47111
 */

 //调用curl方式 方法
require("PostSign.php");
// $startDate = date("Y-m-d H:i:s",strtotime("-1 day"));
$startDate = "2020-08-27 15:00:00";
$endDate = date("Y-m-d H:i:s");

//统一的查询返回方法
function GET($SELECT){
    include("connect.php"); 
    $RESULT = oci_parse($con,$SELECT); 
    oci_execute($RESULT,OCI_DEFAULT); 
    return $RESULT;
    oci_free_statement($RESULT);
    oci_close($con);
}

//获取接口总页码
function get_page(){
    global $endDate;
    global $startDate;
    include("connect.php");    
    $method='erp.storage.outcount';
    $bizcontent = '{"pageSize":10,"startDate":'.'"'.$startDate.'"'.','.'"endDate":'.'"'.$endDate.'"'.'}';
    $json_array = post_sign($method,$bizcontent);
    //打印获取的结果
    echo '<br>';
    $page = $json_array["result"]["data"]["page"];
    echo '总页数为：'.$page.'<br>';
    echo '开始时间1：'.$startDate.'；'.'结束时间1：'.$endDate.'<br>';
    return $page;
}

function get_goodsdocout($page){
    global $endDate;
    global $startDate;
    include("connect.php");
    $method='erp.storage.goodsdocout';
    $ILPMPN = date("Y-m-d H:i:s");
    for($t = 0;$t<$page;$t++){
        $bizcontent = '{"pagesize": 10,"pageindex": '.$t.',"startDate": '.'"'.$startDate.'"'.',"endDate":'.'"'.$endDate.'"'.'}';
        $json_array = post_sign($method,$bizcontent); 
        echo '<br>';
        $data = $json_array['result']['data'];
        $datanum = count($data);    
        for($x = 0; $x < $datanum; $x++){
            //根据吉客云的仓库编码  获取JDE仓库编号
            $ILMCU1 = $json_array['result']['data'][$x]["warehouseCode"];
            if($ILMCU1 != null){
                $SELECT = "SELECT MCU FROM JKY_WAREHOUSE WHERE WAREHOUSECODE = '$ILMCU1'";
                $ILMCU = oci_fetch_row(GET($SELECT))[0]; 
            }
            $ILKCO = $json_array['result']['data'][$x]["companyId"];
            if($ILKCO == "936838168287511040"){
                $ILKCO = "06010";
            }else if($ILKCO == "951469221682578176"){
                $ILKCO = "06011";
            }else if($ILKCO == "11"){
                $ILKCO = "06012";
            }
            $ILDOC = substr($json_array['result']['data'][$x]["goodsdocNo"],3);
            $ILDCT = substr($json_array['result']['data'][$x]["inouttype"],1)+20;//入库截取后两位+20
            $ILRCD = null;
            //销售B2B
            if($ILDCT == 21){
                $ILDOCO = $json_array['result']['data'][$x]["billNo"];//原始单号    销售使用billNo
                $channelId = $json_array['result']['data'][$x]["channelId"];
                //销售JDEB2B
                if($channelId == "949888347190133760") {
                    $SELECT_KDD = "SELECT DISTINCT KCOO, DCTO, DOCO FROM JKY_SALE_ORDER WHERE TRADENO = '$ILDOCO'";
                    $ILKCOO = oci_fetch_row(GET($SELECT_KDD))[0];
                    $ILDCTO = oci_fetch_row(GET($SELECT_KDD))[1];
                    $ILDOCO = oci_fetch_row(GET($SELECT_KDD))[2];
                //销售中台B2B
                } else if($channelId == "955717218606515200"){
                    $SELECT_KDD = "SELECT DISTINCT SDKCOO,SDDCTO,SDDOCO FROM FE8SPM11 WHERE SDE8RMK200 = '$ILDOCO'";
                    // while($row = oci_fetch_row(GET($SELECT_KDD))){
                    //     $ILKCOO = row[0];
                    //     $ILDCTO = row[1];
                    //     $ILDOCO = row[2];
                    // }
                    $ILKCOO = oci_fetch_row(GET($SELECT_KDD))[0];
                    $ILDCTO = oci_fetch_row(GET($SELECT_KDD))[1];
                    $ILDOCO = oci_fetch_row(GET($SELECT_KDD))[2];
                //销售B2C
                } else {               
                    $ILKCOO = null;
                    $ILDOCO = null;
                    $ILDCTO = "ED";
                }             
            //采购退货
            } else if($ILDCT == 25){
                $ILDOCO = $json_array['result']['data'][$x]["sourceBillNo"];//原始单号
                if($ILDOCO != null){
                    $SELECT_KDD = "SELECT DISTINCT KCOO,DCTO,DOCO FROM JKY_PurchaseReturn_ORDER WHERE ORDERNUM = '$ILDOCO'";
                    // $R_KDD = GET($SELECT_KDD);
                    // while(($row = oci_fetch_row($R_KDD)) != false){
                    //     $ILKCOO = $row[0];
                    //     $ILDCTO = $row[1];
                    //     $ILDOCO = $row[2];
                    // }
                    $ILKCOO = oci_fetch_row(GET($SELECT_KDD))[0];
                    $ILDCTO = oci_fetch_row(GET($SELECT_KDD))[1];
                    $ILDOCO = oci_fetch_row(GET($SELECT_KDD))[2];
                }
            //调拨出库（移位单）
            }else if($ILDCT == 22){
                $ILKCOO = null;
                $ILDOCO = null;
                $ILDCTO = "IT";
                //判断调拨出库是否从不合格品库发出
                $SELECT_LOCN = "SELECT DISTINCT LOCN FROM JKY_WAREHOUSE WHERE WAREHOUSECODE = '$ILMCU1'";
                $ILLOCN = oci_fetch_row(GET($SELECT_LOCN))[0];    
            }else if($ILDCT == 24){
                //库存领用   
                $ILRCD1 = $json_array['result']['data'][$x]["inOutReason"];
                echo '<br>原因：'.$ILRCD1.'<br>';
                if($ILRCD1 == "仓库药品抽检领用"){
                    $SELECT_ILRCD= "SELECT AIURCD FROM FE80901 WHERE AIDL01 = '$ILRCD1'";
                    echo '<br>sql语句：'.$SELECT_ILRCD.'<br>';
                    $ILRCD2 = oci_fetch_row(GET($SELECT_ILRCD))[0];
                    echo '<br>领编号：'.$ILRCD2.'<br>';
                    if($ILRCD2 != null){
                        echo '<br>领用编号：'.$ILRCD2.'<br>';
                        $ILDCTO = "II";
                        $ILRCD = $ILRCD2;
                    }
                }
                // if($ILRCD == "质检领用"){
                    
                // }
            }
            $ILTRDJ = date('Y-m-d H:i:s',$json_array['result']['data'][$x]["inOutDate"]/1000);
            //来往单位编号
            $ILAN8 = $json_array['result']['data'][$x]["vendCustomerCode"];
            // if($ILAN8 != null){  //吉客云返回JDE供应商编号
            //     $SELECT_ILAN8= "SELECT DISTINCT VENDID FROM JKY_PurchaseReturn_ORDER WHERE COMPANYID = '$ILAN8'";
            //     $ILAN8 = oci_fetch_row(GET($SELECT_ILAN8))[0];
            // }
            $ILCRDJ = date('Y-m-d H:i:s',$json_array['result']['data'][$x]["gmtCreate"]/1000);
            $ILSBL = $json_array['result']['data'][$x]["channelId"]; //ILSBL暂定这样   获取渠道ID
            $ILRE = $json_array['result']['data'][$x]["redStatus"];
            for($y = 0; $y < $data = count($json_array['result']['data'][$x]["goodsDocDetailList"]); $y++){
                $ILUKID = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["recId"];  //出库明细id
                $ILUSER = "N";
                $ILDGL = date('Y-m-d H:i:s',$json_array['result']['data'][$x]["goodsDocDetailList"][$y]["productionDate"]/1000);
                //需将吉客云的商品编码转换为JDE的商品编码       有的话进行转换   
                $ILLITM = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["goodsNo"];
                if($ILLITM != null){
                    $SELECT_ILLITM = "SELECT DISTINCT JMLITM FROM FE8J4101 WHERE JME8JOC = '$ILLITM'";
                    $ILLITM1 = oci_fetch_row(GET($SELECT_ILLITM))[0];
                    if($ILLITM1 != null){
                        $ILLITM = $ILLITM1;
                    }
                    $SELECT_II = "SELECT DISTINCT IMITM,IMAITM FROM F4101 WHERE IMLITM = '$ILLITM'";
                    // while ($row = oci_fetch_row(GET($SELECT_II))) {
                    //     $ILITM = $row[0];
                    //     $ILAITM = $row[1];
                    // }
                    $ILITM = oci_fetch_row(GET($SELECT_II))[0];
                    $ILAITM = oci_fetch_row(GET($SELECT_II))[1];
                }
                $ILPLOT = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["batchNo"];
                $ILLOTN = NULL; 
                //根据$ILPLOT转化为生产编号  传入条件是：仓库（JDE）+商品编号（JDE）+批次（吉客云）
                if($ILPLOT != null && $ILLITM != null && $ILMCU != null){
                    $SELECT_ILLOTN = "SELECT MAX(IOLOTN) FROM F4108 WHERE TRIM(IOLOT1) = '$ILPLOT' AND TRIM(IOLITM) = '$ILLITM' AND TRIM(IOMCU) = TRIM('$ILMCU')";
                    $ILLOTN = oci_fetch_row(GET($SELECT_ILLOTN))[0];
                    echo '<br>批号：'.$ILLOTN.'<br>';
                }
                $quantity = '-';
                $ILTRQT1 = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["quantity"]*100;
                $ILTRQT = $quantity.$ILTRQT1;
                echo '数量：'.$ILTRQT.'<br>';
                $ILVPEJ = date('Y-m-d H:i:s',$json_array['result']['data'][$x]["goodsDocDetailList"][$y]["expirationDate"]/1000);
                // 来源单据明细Id
                $ILLPNU = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["sourceDetailId"];
                $ILTREX = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["comment"];
                //ILTRUM（单位）unitName，需转换为code
                $ILTRUM = $json_array['result']['data'][$x]["goodsDocDetailList"][$y]["unitName"];
                if($ILTRUM != null){
                    $SELECT_ILTRUM= "SELECT TRIM(DRKY) FROM CRPCTL.F0005 WHERE TRIM(DRDL01) = TRIM('$ILTRUM') AND DRSY = '00'AND DRRT = 'UM'";
                    $ILTRUM = oci_fetch_row(GET($SELECT_ILTRUM))[0];
                }
                if($ILDCTO != null){
                    $insert_sql = "INSERT INTO FE74111T(ILITM,ILLITM,ILAITM,ILMCU,ILLOCN,ILLOTN,ILPLOT,ILKCO,ILDOC,ILDCT,ILDGL,ILDCTO,ILDOCO,ILKCOO,ILTRDJ,ILTRUM,
                    ILAN8,ILTREX,ILRCD,ILTRQT,ILUKID,ILUSER,ILCRDJ,ILSBL,ILRE,ILVPEJ,ILSVDT,ILLPNU,ILPMPN)
                    VALUES ('".$ILITM."','".$ILLITM."','".$ILAITM."',lpad(TRIM('".$ILMCU."'),12,' '),
                    '".$ILLOCN."','".$ILLOTN."','".$ILPLOT."','".$ILKCO."','".$ILDOC."',
                    '".$ILDCT."',f_date_to_julian(to_date('$ILDGL','yyyy-MM-dd hh24:mi:ss')),
                    '".$ILDCTO."','".$ILDOCO."','".$ILKCOO."',
                    f_date_to_julian(to_date('$ILTRDJ','yyyy-MM-dd hh24:mi:ss')),'".$ILTRUM."',
                    '".$ILAN8."','".$ILTREX."','".$ILRCD."','".$ILTRQT."','".$ILUKID."',
                    '".$ILUSER."',f_date_to_julian(to_date('$ILCRDJ','yyyy-MM-dd hh24:mi:ss')),
                    '".$ILSBL."','".$ILRE."',f_date_to_julian(to_date('$ILVPEJ','yyyy-MM-dd hh24:mi:ss')),
                    f_date_to_julian(SYSDATE),'".$ILLPNU."','".$ILPMPN."')";
                    $result = oci_parse($con,$insert_sql);
                    oci_execute($result,OCI_COMMIT_ON_SUCCESS);
                    oci_close($con);
                }                                 
            }
        }
    }
    try{
        $sql = 'BEGIN JKY_BUS_INBOUND.JKY_E7_SAVE(:p_tablename, :p_lastupdateon); END;';
        $stmt = oci_parse($con,$sql);
        $tablename = "FE74111";
        oci_bind_by_name($stmt,':p_tablename',$tablename,32);
        oci_bind_by_name($stmt,':p_lastupdateon',$ILPMPN,30);
        oci_execute($stmt);
        oci_close($con);
    }catch (Exception $th) {
        echo '错误信息：'.$th->getMessage().'<br>';
   }  
   
}

$page = get_page();
if($page != null && $page !== 0){
    echo get_goodsdocout($page);
}
?>
