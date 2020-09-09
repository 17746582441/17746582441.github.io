<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-25 15:57:28
 * @LastEditTime: 2020-09-02 14:38:57
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 到货单信息查询/FE74111
 * @FilePath: \www.rhtapi.com\public\i-010.php
 */ 

//调用连接
require("PostSign.php");

$startDate = date("Y-m-d H:i:s",time());
$endDate = date("Y-m-d H:i:s",strtotime("-10 day"));

function download_ARRIVAL_NOTICE(){
    
    global $startDate;
    global $endDate;

    $a = array(
        "pageSize"      =>20,
        "pageIndex"     =>0
        //"revwStatus"    =>"1",
        // "startDate"     =>$startDate,
        // "endDate"       =>$endDate
    );   
    
    $dataList = $a;
        
    //接口方法名
    $method='wms.jde.selectnotice';
    //数据表数据
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
    //调用连接
    $json_array = post_sign($method,$bizcontent);

    echo "<pre>"."返回结果--"."<br>";
    print_r($json_array);die;

    return $json_array;

}

  //插入到货信息查询表JKY_ARRIVAL_NOTICE
function insert_ARRIVAL_NOTICE($ARRIVAL_NOTICE_array = array()){

    include("connect.php"); 

    $SDPMPN=date("Y-m-d H:i:s");//获取当前时间 --  存储过程需要根据这个时间来删除数据
    
    $i = 0;
    $num = count($ARRIVAL_NOTICE_array["result"]["data"]);
    echo $num;
    for($i;$i<$num;$i++){  

        $ownerId = $ARRIVAL_NOTICE_array["result"]["data"][$i]["ownerId"];
        $ownerName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["ownerName"];
        $warehouseId = $ARRIVAL_NOTICE_array["result"]["data"][$i]["warehouseId"];
        $warehouseName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["warehouseName"];
        $stockinNo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["stockinNo"];
        $stockinId = $ARRIVAL_NOTICE_array["result"]["data"][$i]["stockinId"];
        $noticeId = $ARRIVAL_NOTICE_array["result"]["data"][$i]["noticeId"];
        $noticeNo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["noticeNo"];
        $noticeStatus = $ARRIVAL_NOTICE_array["result"]["data"][$i]["noticeStatus"];
        $noticeStatusMsg = $ARRIVAL_NOTICE_array["result"]["data"][$i]["noticeStatusMsg"];
        $isClose = $ARRIVAL_NOTICE_array["result"]["data"][$i]["isClose"];
        $isCloseMsg = $ARRIVAL_NOTICE_array["result"]["data"][$i]["isCloseMsg"];
        $goodsNo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["goodsNo"];
        $goodsName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["goodsName"];
        $skuName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["skuName"];
        $skuBarcode = $ARRIVAL_NOTICE_array["result"]["data"][$i]["skuBarcode"];
        $unitName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["unitName"];
        $isCertified = $ARRIVAL_NOTICE_array["result"]["data"][$i]["isCertified"];
        $skuCount = $ARRIVAL_NOTICE_array["result"]["data"][$i]["skuCount"];
        $rejectCount = $ARRIVAL_NOTICE_array["result"]["data"][$i]["rejectCount"];
        $noticeDate = $ARRIVAL_NOTICE_array["result"]["data"][$i]["noticeDate"];
        $operatorName = $ARRIVAL_NOTICE_array["result"]["data"][$i]["operatorName"];
        $memo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["memo"];
        $relNo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["relNo"];
        $batchNumber = $ARRIVAL_NOTICE_array["result"]["data"][$i]["batchNumber"];
        $productDate = $ARRIVAL_NOTICE_array["result"]["data"][$i]["productDate"];
        $expireDate = $ARRIVAL_NOTICE_array["result"]["data"][$i]["expireDate"];
        $validity = $ARRIVAL_NOTICE_array["result"]["data"][$i]["validity"];
        $shelfLife = $ARRIVAL_NOTICE_array["result"]["data"][$i]["shelfLife"];
        $shelfLifeUnit = $ARRIVAL_NOTICE_array["result"]["data"][$i]["shelfLifeUnit"];
        $approvalNo = $ARRIVAL_NOTICE_array["result"]["data"][$i]["approvalNo"];
        $productionDepart = $ARRIVAL_NOTICE_array["result"]["data"][$i]["productionDepart"];
         

        $insert_sql = "INSERT INTO JKY_ARRIVAL_NOTICE(OWNERID,OWNERNAME,WAREHOUSEID,WAREHOUSENAME,
                                                    STOCKINNO,STOCKINID,NOTICEID,NOTICENO,NOTICESTATUS,
                                                    NOTICESTATUSMSG,ISCLOSE,ISCLOSEMSG,GOODSNO,GOODSNAME,
                                                    SKUNAME,SKUBARCODE,UNITNAME,ISCERTIFIED,SKUCOUNT,
                                                    REJECTCOUNT,NOTICEDATE,OPERATORNAME,MEMO,RELNO,
                                                    BATCHNUMBER,PRODUCTDATE,EXPIREDATE,VALIDITY,SHELFLIFE,
                                                    SHELFLIFEUNIT,APPROVALNO,PRODUCTIONDEPART,ZT)
        
        VALUES('".$ownerId."','".$ownerName."','".$warehouseId."','".$warehouseName."',
        '".$stockinNo."','".$stockinId."','".$noticeId."','".$noticeNo."','".$noticeStatus."',
        '".$noticeStatusMsg."','".$isClose."','".$isCloseMsg."','".$goodsNo."','".$goodsName."',
        '".$skuName."','".$skuBarcode."','".$unitName."','".$isCertified."','".$skuCount."',
        '".$rejectCount."','".$noticeDate."','".$operatorName."','".$memo."','".$relNo."',
        '".$batchNumber."','".$productDate."','".$expireDate."','".$validity."','".$shelfLife."',
        '".$shelfLifeUnit."','".$approvalNo."','".$productionDepart."','N')";

        echo "<pre>";
        echo $relNo; 
        $insert2=oci_parse($con,$insert_sql);
        if (oci_execute($insert2,OCI_COMMIT_ON_SUCCESS) === TRUE) {
            $y++;
        } else {  
            $z++;
        }
        
    }die;

    //调用存储过程--将替换表中的数据写入正式表中去重 
    $tablename = "FE8SPM11";   
    echo $SDPMPN;
    
    try {
        $sql = 'BEGIN JKY_BUS_INBOUND.JKY_E7_SAVE(:p_tablename, :p_lastupdateon); END;';
        $stmt = oci_parse($con,$sql);
        $tablename = "FE8SPM11";
        oci_bind_by_name($stmt,':p_tablename',$tablename,32);
        oci_bind_by_name($stmt,':p_lastupdateon',$SDPMPN,30);
        oci_execute($stmt);
        echo "<pre>";
        echo '获取数据为：'.oci_bind_by_name($stmt,':p_tablename',$tablename,32).'<br>';
  
   } catch (Exception $th) {
       echo '错误信息：'.$th->getMessage().'<br>';
  }
        oci_close($con);
        return $y;

}

//主程序

$result = download_ARRIVAL_NOTICE();
if ($result != null){
    echo insert_ARRIVAL_NOTICE($result)."条插入成功";
}

?>
