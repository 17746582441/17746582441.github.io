<?php

function httpRequest($method,$postData,$postfields = null,$debug = false) {

      //请求接口名
      $method='erp.vend.get';
      //请求数据
      $postData='{"pageindex":"1","pagesize": "10"}';
      //版本
      $version='1.0';
      //APPKEY
      $APPKEY = "38617590";
      //AppeSecret
      $APPSECRET = "09e6f0c4a77b4e68abadb4256ca31103";
      //请求URL
      $GATEWAY = "http://differcom.gnway.cc:21017/open/openapi/do";


      //生成sign签  按照吉客云的规则拼接字符串 生成加密sign签
      $pwdM=$APPSECRET."appkey". $APPKEY."bizcontent".$postData."contenttype"."json"."method".$method
      ."timestamp".date('Y-m-d H:i:s')."version".$version.$APPSECRET;
      $sign= md5($pwdM);
      echo "<pre>";
      print_r($sign);

      $datetimest="timestamp=".date('Y-m-d H:i:s');
      //在按照传输数据生成传输字符串
      $data=$datetimest."&appkey=".$APPKEY."&bizcontent=". $postData."&contenttype=json&method=".$method.
            "&sign=".$sign."&version=".$version;


          //创建请求连接
          $ci = curl_init();
          // curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "post"); /* //设置请求方式 */ // 设置这个参数会导致请求到405 的页面
          curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
          curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
          curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ci, CURLOPT_URL, $GATEWAY);
          curl_setopt($ci, CURLOPT_POSTFIELDS, $data);
          // curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);// 设置这个参数会导致请求到400 的页面

          //发送请求  获取返回值
          $response = curl_exec($ci);
          $requestinfo = curl_getinfo($ci);
          $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

          $json_array = json_decode($response,true);
          echo "<pre>";
          print_r($json_array);

          curl_close($ci);

      return $response;
    }

  $myTest ->httpRequest();

?>