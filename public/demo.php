<?php

//header('Content-type:text/html;charset=utf-8');
        
        // $sql_op_x = "select count(*) from atm_rkjxdj";
        
        // $result=mysqli_query($con,$sql_op_x);
        // $row = mysqli_fetch_array($result);
        // $op = $row[0];
        // echo $op;

        // $i=0;
        // $num = 3;
        // $test = "SH822007080001";
        // $arr = [];
        // for ($i;$i<$num;$i++){
        // array_push($arr,$test);
        // }
        // $s_sl = array($arr);

        // echo "<pre>";
        // print_r($s_sl);

        // $x_sl = '2.00';
        // intval($x_sl);

        // echo "<pre>";
        // print_r($x_sl);

        //模拟--比较数组的差集
        // $a1=array("0"=>"SH822007080001","1"=>"SH822007080002");//接口传递的数据
        // $a2=array("0"=>"SH822007080001","1"=>'SH822007080002');//目标表存在的数据
        
        // $result=array_diff($a1,$a2);
        // $cha_yi = current($result);//差集数组第一个下标的值
        // echo $cha_yi;

        // if ($cha_yi == null){

        //     echo "<pre>";
        //     echo "数据已存在插入失败！";
        //     print_r($cha_yi);
        //     print_r($a2);
    
        // }else{
        //     echo "<pre>";
        //     echo "插入成功！";
        // }
    //    $sql_op_x = "SELECT ukid FROM atm_rkjxdj where purchaseOrderId =
    //                     (SELECT purchaseOrderId FROM atm_rkjxdj where ukid = '26802')";       
    //         $result=mysqli_query($con,$sql_op_x);
    //         $row = mysqli_fetch_all($result,MYSQLI_ASSOC);
    //         print_r($row);

    //         $op = count($row);
    //         echo $op;


    //         mysqli_close($con);

    // $con = oci_connect('CRPDTA','ddky.com',"(DESCRIPTION =(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 172.16.61.13)	
    // (PORT = 1521)))(CONNECT_DATA =(SERVICE_NAME = OERPTST)))",'UTF8');
   
    // if (!$con) {
    //     $e = oci_error();
    //     print htmlentities($e["message"]);
    //     exit;
    // }else {
    // echo "连接oracle成功！"; 
    // }

    // $sql = "SELECT to_char(ordertime,'YYYY/MM/DD') FROM jky_purchase_order WHERE rownum = 1"; 
    // $result=oci_parse($con,$sql); 
    // oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    // $row = oci_fetch_array($result);
    // $a = $row[0];
    // echo $a;


    // $sql = "SELECT UKID FROM ATM_RKJXDJ WHERE PURCHASEORDERID = 'ATMCGD00000444'";       
    //         $STMT=oci_parse($con,$sql);
    //         oci_execute($STMT,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    //         $row = oci_fetch_all ($STMT,$results);//从结果集中获取所有行作为关联数组
    //         print_r($results); 


    //oci_close($con);

    // $time = date('Y-m-d H:m:s',strtotime('0 day'));
    // echo $time;
    //phpinfo();

    // $a = -1;
    // $b = abs($a);
    // echo $b; 

    //echo md5('1111111111111111111111111111111111111111111111111111111111111111111111111111');
    
    //echo md5('f0d1483f1f2e49cea3dfd48c8d7e3c23appkey187657bizcontent{"brandname":"衣服","catename":"拉链","costvaluationmethod":0,"goodsalias":"花之春","goodsattr":1,"goodsmemo":"货品备注","goodsname":"行李包 花之春","goodsnameen":"spring","goodsno":"h06-2","isbatchmanagement":0,"iscustomizproduction":0,"isdoorservice":0,"ispaidservice":0,"isperiodmanage":0,"ispickupcard":0,"isprosaleproduct":0,"isproxysale":0,"isserialmanagement":0,"outskucode":"8714wtst","ownerid":"jackyun_dev","ownername":"jackyun_dev","shelflife":0,"shelfliftunit":"","skubarcode":"76620423621","skuheight":3,"skuid":"","skulength":3,"skuname":"无特殊字符","skuweight":20,"skuwidth":4,"unitname":"件"}contenttypejsonmethoderp.goods.skuimporttimestamp2019-07-30 15:18:30versionf0d1483f1f2e49cea3dfd48c8d7e3c23');

    // $bizcontent = array(
    //     "isBatchManagement"     => 0,
    //     "goodsNameEn"           => "string",
    //     "ownerCode"             => "34343",
    //     "costValuationMethod"   => 0,
    //     "isPeriodManage"        => 0,
    //     "cateName"              => "笔记本",
    //     "isDoorService"         => 0,
    //     "isCustomizProduction"  => 0,
    //     "outSkuCode"            => "12232",
    //     "skuName"               => "白色L",
    //     "goodsAlias"            => "string",
    //     "isProxySale"           => 0,
    //     "skuHeight"             => "string",
    //     "shelfLiftUnit"         => "年",
    //     "shelfLife"             => 3,
    //     "goodsName"             => "吉客云冰丝衬衫",
    //     "goodsNo"               => "1196",
    //     "brandName"             => "string",
    //     "unitName"              => "件",
    //     "skuLength"             => "string",
    //     "isPaidService"         => 0,
    //     "isPickupCard"          => 0,
    //     "goodsMemo"             => "string",
    //     "skuWidth"              => "string",
    //     "cateCode"              => "0101",
    //     "goodsAttr"             => 1,
    //     "isSerialManagement"    => 0,
    //     "isProsaleProduct"      => 0,
    //     "isProductionMaterials" => 0,
    //     "skuBarcode"            => "G010B-L",
    //     "skuWeight"             => "string"
    // );

    // $jsondate = json_encode($bizcontent,true);
    // print_r($jsondate);
    //print_r(strtolower($jsondate));//转小写

    // $postfields = array(
    //     "method"=>"erp.goods.skuimportbatch",
    //     "appkey"=>"438b2f6ff103422a98a9349507293bb2",
    //     "version"=>"1.0",
    //     "contenttype"=>"json",
    //     "timestamp"=>"2015-01-01 12:00:00",
    //     "bizcontent"=>$bizcontent
    // );

    // print_r(implode(",",$postfields));

    // $timestamp = date('Y-m-d h:i:s', time());
    // echo $timestamp;
    // require("connect.php");

    // $sql_nextval = "SELECT jky_fe8spm11_s.nextval FROM DUAL";

    // $result=oci_parse($con,$sql_nextval); 
    // oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    // $nextval=oci_fetch_row($result)[0];

    // echo $nextval;
     
    // echo date("Y-m-d H:i:s",time());
    // echo date("Y-m-d H:i:s",strtotime("-1 day"));
    // echo date('Y-m-d H:i:s',time());

    echo phpinfo();










?>
