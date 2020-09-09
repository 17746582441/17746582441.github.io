<?php
/*
 * @Author: 李志勇
 * @Date: 2020-07-25 15:57:28
 * @LastEditTime: 2020-08-31 17:42:44
 * @LastEditors: （谁改的请在此处留上大名^-^）
 * @Description: 采购单查询/FE74111
 * @FilePath: \www.rhtapi.com\public\i-004a.php
 */ 

//调用连接
require("PostSign.php");

$startDate = date("Y-m-d H:i:s",time());
$endDate = date("Y-m-d H:i:s",strtotime("-10 day"));

function download_FE8SPM11(){
    
    global $startDate;
    global $endDate;

    $a = array(
        //"pageSize"      =>2,
        "pageIndex"     =>0,
        "revwStatus"    =>"1",
        // "startDate"     =>$startDate,
        // "endDate"       =>$endDate
    );
    
    
    $dataList = $a;
        
    //接口方法名
    $method='erp.purch.get';
    //数据表数据
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
    //调用连接
    $json_array = post_sign($method,$bizcontent);

    return $json_array;

}

  //插入入库回传表FE8SPM11
function insert_FE8SPM11($FE8SPM11_array = array()){

    include("connect.php"); 
    $cmf_arr = array_column($FE8SPM11_array["result"]["data"]["purchOrder"],'orderNum');
    echo "<pre>";
    print_r($cmf_arr);//die;
    array_multisort($cmf_arr,SORT_ASC,$FE8SPM11_array["result"]["data"]["purchOrder"]);
    
    echo "<pre>";
    print_r($FE8SPM11_array);
    die;

    $sql_nextval = "SELECT jky_fe8spm11_s.nextval FROM DUAL";//获取UKID
    $result=oci_parse($con,$sql_nextval); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    $nextval=oci_fetch_row($result)[0];

    $SDPMPN=date("Y-m-d H:i:s");//获取当前时间 --  存储过程需要根据这个时间来删除数据
    
    $i = 0;
    $num = count($FE8SPM11_array["result"]["data"]["purchOrder"]);
    echo $num;
    for($i;$i<$num;$i++){  

        $orderNum = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["orderNum"];//采购单号
        $quantity = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["quantity"];//数量
        $skuProperitesName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["skuProperitesName"];//规格
        $companyName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["companyName"];//公司名称
        $goodsName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["goodsName"];//商品名称
        $goodsNo = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["goodsNo"];//商品编号
        $warehouseName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["warehouseName"];//仓库名称
        $warehouseCode = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["warehouseCode"];//仓库编号
        $vendCode = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["vendCode"];//供应商编号
        $vendName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["vendName"];//供应商名称
        $unitName = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["unitName"];//包装单位名称
        $headId = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["headId"];//订单对应主键
        $id = $FE8SPM11_array["result"]["data"]["purchOrder"][$i]["id"];//订单对应明细主键             
        
        $orderNum_q = $FE8SPM11_array["result"]["data"]["purchOrder"][$i-1]["orderNum"];//上一个订单号       

        $SDUK01 = $SDUK01+1;
        if($orderNum!=$orderNum_q){ 
            $SDUK01=1;//复位明细行到1
            $sql_nextval = "SELECT jky_fe8spm11_s.nextval FROM DUAL";//赋值同一订单的UKID
            $result=oci_parse($con,$sql_nextval); 
            oci_execute($result,OCI_DEFAULT);  
            $nextval=oci_fetch_row($result)[0];
        }

        $insert_sql = "INSERT INTO FE8SPM11_T(SDUKID,SDVR01,SDVR02,SDSHAN,
                                            SDMCU,SDMCU2,SDE8DSC2,SDDL07,
                                            SDLITM,SDE8NAME,SDUOM1,
                                            SDUORG,SDCREC,SDEV01,SDEV02,SDE8RMK200,SDDL08,SDUK01)
        
        VALUES('".$nextval."','08','08',(SELECT TO_NUMBER(MCU) FROM JKY_WAREHOUSE WHERE WAREHOUSECODE = '".$warehouseCode."' AND ROWNUM = 1),
        '".$vendCode."',(SELECT TO_NUMBER(MCU) FROM JKY_WAREHOUSE WHERE WAREHOUSECODE = '".$warehouseCode."' AND ROWNUM = 1),'".$skuProperitesName."','".$id."',
        (SELECT NVL((SELECT JMLITM FROM FE8J4101 WHERE trim(JME8JOC) = '".$goodsNo."' AND JME8CITY = 'RHT' AND ROWNUM = 1),'".$goodsNo."') FROM FE8J4101 WHERE ROWNUM = 1),
        '".$goodsName."',(SELECT TRIM(DRKY) FROM CRPCTL.F0005 WHERE DRSY = '00' AND DRRT = 'UM' AND TRIM(DRDL01) = '".$unitName."'),
        ('".$quantity."'*100),('".$quantity."'*100),'Y','N','".$orderNum."','".$SDPMPN."','".$SDUK01."')";

        echo "<pre>";
        echo $orderNum; 
        $insert2=oci_parse($con,$insert_sql);
        if (oci_execute($insert2,OCI_COMMIT_ON_SUCCESS) === TRUE) {
            $y++;
        } else {  
            $z++;
        }
        
    }//die;

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

$result = download_FE8SPM11();
if ($result != null){
    echo insert_FE8SPM11($result)."条插入成功";
}

?>
