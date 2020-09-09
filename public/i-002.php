<?php
/**
 * 上传客户信息/JKY_CUSTOMER
 */

 //调用curl方式 方法
 require("PostSign.php");

 //获取客户信息 
function get_customer(){
  //变量
  include("connect.php");
    $sql = "SELECT ukid,(trim(customercode)),nickname,address,detailedaddress,channelid,
            sourcechannel,customeraccount,contactsname,contactsphone
            FROM JKY_CUSTOMER
            WHERE ZT = 'N'
            AND ROWNUM=1";
    
    //接受结果集
    $result = oci_parse($con,$sql);  
    oci_execute($result,OCI_DEFAULT);//执行（没有事务提交的SQL语句参数）  
    return $result; 

    oci_free_statement($result);
    oci_close($con);
  
  }

  function update_customer($ukid,$zt,$code,$msg,$subCode,$contextId){
    include("connect.php");
    $sql = " UPDATE JKY_CUSTOMER set zt = '".$zt."',code = '".$code."',msg = '".$msg."',
             subcode = '".$subCode."',contextId = '".$contextId."' 
            where ukid = '".$ukid."'";

    $result = oci_parse($con,$sql);
    oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)

    oci_close($con);
    return $result;
  
  }

function upload_customer($res){
  
  while ($row = oci_fetch_row($res)){

    $a11 = array( 
      "channelid"           => '949888347190133760', //$row[5],
      "sourcechannel"       => '仁和堂经销商渠道',//$row[6],
      "customerAccount"     => $row[1]
    );

    $a1[] = $a11;

    $a22 = array(

      "contactsname"        => $row[8],
      "contactsphone"       => $row[9]
    );

    $a2[] = $a22;

    $a = array(
        "customercode"        => $row[1],
        "nickname"            => $row[2],
        "customerType"        => '5f1e6d563b2c4900014ca92e',
        "address"             => $row[3],
        "detailedaddress"     => $row[4],
        "customerSourceArr"   => $a1,
        "contactsModelArr"    => $a2
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
        
    $method='crm.customer.add';
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE); 
    $json_array = post_sign($method,$bizcontent);
    echo "<pre>";
    print_r($json_array);//die;

    //处理返回结果
    if ($json_array["code"]=="200"){
      $zt = "Y";
    } else{
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

      //遍历返回结果集数组
    $arrlength=count($Rows);
    for($x=0;$x<$arrlength;$x++)
    {
      update_customer($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$contextId);
    }
    
}

//主程序
$result = get_customer();
echo upload_customer($result);

?>
