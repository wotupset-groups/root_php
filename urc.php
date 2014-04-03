<?php 
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL & ~E_NOTICE); //所有錯誤中排除NOTICE提示
$phpself=basename($_SERVER["SCRIPT_FILENAME"]);//被執行的文件檔名
$phplink="http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]."";
$mode = $_POST["mode"];
$url_org = $_POST["url_org"];
$enc_key = $_POST["enc_key"];
$query_string=$_SERVER['QUERY_STRING'];

//$enc_key='123';
$ref_sec=5; //轉址時間
//$htmlhead
$htmlhead=<<<EOT
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Tera</title>
</head><body>
EOT;
//$form
$post_url=$phpself."?".$query_string;
if($query_string){//網址有參考值
	$tmp=$query_string;
}else{
	$tmp='<input type="text" name="url_org" id="url_org" size="20" value="" placeholder="http">';
}
$form=<<<EOT
<form id='form1' action='$post_url' method="post" autocomplete="off">
<input type="hidden" name="mode" value="reg">
$tmp<br>
<input type="text" name="enc_key" id="enc_key" size="20" value="" placeholder="pass">
<input type="submit" value="送出"/>
</form>
EOT;

//$htmlend
$htmlend=<<<EOT
</body></html>
EOT;
$echo_body='';
$tmp='';
switch($mode){
	case 'reg':
		if($query_string){//網址有參考值
			$url_dec=passport_decrypt($query_string,$enc_key);//解密
			$echo_body.="<pre>$url_dec</pre>";
			$echo_body.="<a href='$phpself'>&#10006;BACK</a>";
			if(!preg_match("/^http/i", $url_dec)){die('xHttp');}//檢查值必須有http
			header("refresh:1; url=$url_dec");
		}else{//網址沒有參考值
			$url_org=trim($url_org);//去頭尾空白
			if(!preg_match("/^http/i", $url_org)){die('xHttp');}//檢查值必須有http
			$enc_key=trim($enc_key);//去頭尾空白
			$url_enc=passport_encrypt($url_org,$enc_key);//加密
			$url_enc='http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$url_enc;
			$echo_body.="<pre>$url_enc</pre>";
			$echo_body.="<a href='$phpself'>&#10006;BACK</a> ";
		}
	break;
	default:
		if($query_string){//網址有參考值
			$url_dec=passport_decrypt($query_string,$enc_key);//解密
			//header("refresh:$ref_sec; url=$url_dec");
			$echo_body.="<pre><span id='url_text'></span></pre>"."\n";
			$echo_body.=$form;
			$echo_body.="<a href='./$phpself'>&#10006;BACK</a>"."<br/>\n";
		}else{//網址沒有參考值
			$echo_body.=$form;
			$echo_body.="<a href='./'>&#10006;ROOT</a>";
		}
	break;
}
echo $htmlhead;
echo $echo_body;
echo $htmlend;

function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}
function passport_decrypt($txt, $key) {
	$txt = passport_key(base64_decode($txt), $key);
	$tmp = '';
	for ($i = 0; $i < strlen($txt); $i++) {
		$tmp .= $txt[$i] ^ $txt[++$i];
	}
	return $tmp;
}
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}
function passport_encode($array) {
	$arrayenc = array();
	foreach($array as $key => $val) {
		$arrayenc[] = $key.'='.urlencode($val);
	}
	return implode('&', $arrayenc);
}
/*
$chk_time_key='abc123';
$chk_time_enc=passport_encrypt($time,$chk_time_key);
$chk_time_dec=passport_decrypt($chk_time_enc,$chk_time_key);
echo $time.' '.$chk_time_enc.' '.$chk_time_dec;
*/
?> 
