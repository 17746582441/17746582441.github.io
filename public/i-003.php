<?php
/**
 * 上传供应商信息/JKY_vendor
 */

 //调用处理数据方式
 require ("PostSign.php");

 //获取供应商信息
function get_vendors(){
  include("connect.php");
     
    $sql="SELECT UKID,VENDID,CODE,NAME,ADDRESS,ZT,LINKMANLISTID ,LINKMAN,
          LINKTEL,PAYACCOUNTLISTID,PURCHUSERLISTID,COMPANYID,COMPANYNAME,
          DEPARTID,DEPARTNAME,PURCHUSERID,PURCHUSERNAME,CALLMODE 
          from JKY_vendor
          WHERE ZT = 'N' and rownum = 1";

    $result=oci_parse($con,$sql); 
    oci_execute($result,OCI_DEFAULT);  //执行（没有事务提交的SQL语句参数）
    return $result; 
  
    oci_free_statement($result);
    oci_close($con);
  
  }

  function update_vendors($ukid,$zt,$code,$msg,$subCode,$vendId,$contextId){

    include("connect.php"); 
  
    $sql=" UPDATE JKY_vendor set zt = '".$zt."',returncode = '".$returncode."',msg = '".$msg."',
           subCode = '".$subCode."',VENDID = '".$vendId."',CONTEXTID = '".$contextId."'
           where ukid = '".$ukid."'";

      $result=oci_parse($con,$sql);
      oci_execute($result,OCI_COMMIT_ON_SUCCESS);  //执行(带有事务的SQL语句参数)
    
      oci_close($con);
  
    return $result;
  
  }

function upload_vendors($res){
  
  while ($row=oci_fetch_row($res)){
 
        $vendPayAccountList=array(
          
            "id"                 => "111111",
            "companyId"          => "111111",
            "companyName"        => "付款账号下的公司名称test",
            "accountTypeCode"    =>'账户类型编码test',
            "accountTypeName"    =>"账户类型名称test",
            "accName"            =>"账户名称test"
          
        );
        $vendPayAccountList1[]=$vendPayAccountList;

        $vendPurchUserList=array(
          "vendId"            => $row[1], 
          "id"                => $row[10],
          "companyId"         => $row[11],
          "companyName"       => "公司名称test",//$row[12],
          "departId"          => $row[13],
          "departName"        => "部门test",//$row[14],
          "purchUserId"       => $row[15],
          "purchUserName"     => "采购员test",//$row[16]
        );
        $vendPurchUserList1[]=$vendPurchUserList;

        $vendLinkmanList=array(
          "vendId"           => $row[1],
          "linkman"          => "1",//$row[7],
          "linktel"          => $row[8]
        );
        $vendLinkmanList1[]=$vendLinkmanList;

        $a = array(
        "vendId"           => $row[1],
        "code"             => $row[2],
        "name"             => $row[3],
        "address"          => $row[4],
        "leader"           => $row[7],
        "tel"              => $row[8],
        "vendLinkmanList"       => $vendLinkmanList1,
        "vendPayAccountList"    => $vendPayAccountList1,
        "vendPurchUserList"     => $vendPurchUserList1,
        "className"             =>"测试test"
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
    $method='erp.vend.create';
    //数据表数据
    $bizcontent = json_encode($dataList,JSON_UNESCAPED_UNICODE);
    //调用请求
    $json_array=post_sign( $method,$bizcontent);
    //$bizcontent = '{"bizdata":'.$bizcontent."}";
    echo "<pre>";
    print_r($json_array);//die;

    //处理返回结果
    if ($json_array["code"]=="200"){
      $zt = "Y";
    } else{
      $zt = "E";
    }

    $code = $json_array["code"];
    $msg = $json_array["msg"];
    $subCode = $json_array["subCode"];
    $vendId=$json_array["result"]["data"]["vendId"];
    $contextId=$json_array["result"]["contextId"];

     //遍历返回结果集数组
    $arrlength=count($Rows);
    for($x=0;$x<$arrlength;$x++)
    {
    update_vendors($Rows[$x]["ukid"],$zt,$code,$msg,$subCode,$vendId,$contextId);
    }
    return $json_array;

}

//主程序
$result = get_vendors();

echo upload_vendors($result);

?>
