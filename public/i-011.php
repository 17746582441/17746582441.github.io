<?php
/**
 * 批次库存查询/JKY_batchstock_get    JKY_batchstock
 */

 //调用curl方式 方法
 require("PostSign.php");

//获取记录表中的UKID
function getukid(){
    include("connect.php"); 
    $ukid_get_sql = "select JKY_batchstock_get_s.nextval from dual" ;
    $result = oci_parse($con,$ukid_get_sql);
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    oci_close($con);
    return $result;
}

function insert_stockquantity_get ($json_array){
    include("connect.php"); 
    $goodsStockQuantity = $json_array["result"]["data"]["goodsStockQuantity"];
    $num = count($goodsStockQuantity);
    $code = $json_array["code"];
    $msg = $json_array["msg"];
    $subcode = $json_array["subCode"];
    $contextid = $json_array["result"]["contextId"];
    $lastupdateon = date("Y-m-d H:i:s");
    $result = getukid();
    if($result != null){
        while($row=oci_fetch_row($result)){
            $ukid = $row["0"];
        };
    }
    if ($num > 0){
        $zt = "Y";
        $getukid = $ukid;
    } else {
        $zt = "E";
    }
    $insertget_sql = "insert into JKY_batchstock_get(UKID,WAREHOUSECODE,GOODSNO, GOODSNAME, 
    SKUNAME, OUTSKUCODE, SKUBARCODE, UNITNAME, PAGEINDEX, PAGESIZE, ZT, CODE,MSG,SUBCODE,CONTEXTID,DATA,LASTUPDATEON) 
    values ('".$ukid."','', '', '','', '', '','','', '', '".$zt."', '".$code."', '".$msg."',
    '".$subcode."', '".$contextid."','',to_date ( '".$lastupdateon."' , 'YYYY-MM-DD HH24:MI:SS' ))";

    $insert2 = oci_parse($con,$insertget_sql);
    if(oci_execute($insert2, OCI_COMMIT_ON_SUCCESS)){
        echo '插入数据成功';
        //关闭连接
        oci_close($con);
        return $getukid;
    }else{
        echo '插入数据失败';
    }
      
}


function get_stockquantity(){
    include("connect.php");  

    $pagesize=20;
    //先用同样的参数获取总条数
    $bizcontent = '{"pagesize": "20",  "pageindex": "0"}';
    $method="erp.batchstockquantity.get";

    //调用请求
    $json_array_count=post_sign( $method,$bizcontent);
    $sumcounts=$json_array_count["result"]["pageInfo"]["total"];
    $totalPageNum = ($sumcounts  +  $pagesize  - 1) / $pagesize;
    $totalPageNum=intval($totalPageNum);

    for($t=0;$t<$totalPageNum;$t++){
        $method='erp.batchstockquantity.get';
        $bizcontent = '{"pagesize": "20",  "pageindex": '.'"'.$t.'"'.'}';
        $json_array = post_sign($method,$bizcontent);
        //打印获取的结果
        //print_r($json_array);
        //打印成功与否和状态
        $test = $json_array["code"];
        print_r($test);
        echo '<br>';
        $code = $json_array["code"];
        $msg = $json_array["msg"];
        $subCode = $json_array["subCode"];
        $goodsStockQuantity = $json_array["result"]["data"]["goodsStockQuantity"];
        $num = count($goodsStockQuantity);
        //获取记录表的UKID
        $ukid = insert_stockquantity_get($json_array);
        if($ukid != null){
            include("connect.php"); 
            $lastupdateon = date("Y-m-d H:i:s");
            for($x=0; $x<$num; $x++){
                $quantityid = $goodsStockQuantity[$x]["quantityId"];
                $warehouseId = $goodsStockQuantity[$x]["warehouseId"];
                $warehousename = $goodsStockQuantity[$x]["warehouseName"];
                $warehousecode = $goodsStockQuantity[$x]["warehouseCode"];
                $goodsid = $goodsStockQuantity[$x]["goodsId"];
                $goodsno = $goodsStockQuantity[$x]["goodsNo"];
                $goodsname = $goodsStockQuantity[$x]["goodsName"];
                $skuid = $goodsStockQuantity[$x]["skuId"];
                $skuname = $goodsStockQuantity[$x]["skuName"];
                $skubarcode = $goodsStockQuantity[$x]["skuBarcode"];
                $unitname = $goodsStockQuantity[$x]["unitName"];
                $outskucode = $goodsStockQuantity[$x]["outSkuCode"];
                $batchno = $goodsStockQuantity[$x]["batchNo"];
                $currentquantity = $goodsStockQuantity[$x]["currentQuantity"];
                $defectivequanity = $goodsStockQuantity[$x]["defectiveQuanity"];
                $lockedquantity = $goodsStockQuantity[$x]["lockedQuantity"];
                
                $insert_sql = "insert into JKY_batchstock(ukid,quantityid,warehouseId, warehousename, 
                    warehousecode, goodsid, goodsno, goodsname, skuid, skuname, skubarcode, unitname,outskucode,
                    batchno,currentquantity,defectivequanity,lockedquantity,lastupdateon) 
                    values ('".$ukid."','".$quantityid."','".$warehouseId."', '".$warehousename."','".$warehousecode."', '".$goodsid."',
                    '".$goodsno."','".$goodsname."', '".$skuid."', '".$skuname."', '".$skubarcode."', '".$unitname."',
                    '".$outskucode."', '".$batchno."','".$currentquantity."','".$defectivequanity."', 
                    '".$lockedquantity."',to_date('".$lastupdateon."' , 'YYYY-MM-DD HH24:MI:SS' ))";
                $result = oci_parse($con,$insert_sql);
                oci_execute($result,OCI_COMMIT_ON_SUCCESS);  
            };
            oci_close($con);
        }
    }
    

}
echo get_stockquantity();
?>
