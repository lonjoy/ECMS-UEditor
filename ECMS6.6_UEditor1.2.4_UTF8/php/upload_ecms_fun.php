<?php
/*
 * -+------------------------------------------------------------------------+-
 * | Author : pkkgu （QQ：910111100）
 * | Contact: http://t.qq.com/ly0752
 * | LastUpdate: 2012-12-14
 * -+------------------------------------------------------------------------+-
 */ 

/********************************************************************** 公共部分 **********************************************************************/
/********************************************************************** 公共部分 **********************************************************************/
/**
 * 编码转换
 * @param $num   值为非0时执行转换操作
 * @param $phome 0为UTF-8转为GBK2312 1为GBK转为UTF-8
 * @param $str   要转换的字符串
 * doUtfAndGbk_pkkgu(1,0,$str);
 */
function doUtfAndGbk_pkkgu($num=0,$phome=0,$str){
	if(empty($num))//正常编码
	{
		return $str;
    }
	if(!function_exists("iconv"))//是否支持iconv
	{
		$fun="DoIconvVal";
		$code="UTF8";
		$targetcode="GB2312";
	}
	else
	{
		$fun="iconv";
		$code="UTF-8";
		$targetcode="GBK";
	}
	if(empty($phome))
	{
		$str=$fun($code,$targetcode,$str);
	}
	else
	{
		$str=$fun($targetcode,$code,$str);
	}
	return addslashes($str);
}
// 远程保存
function DoTranUrl_pkkgu($url,$classid){
	global $public_r,$class_r,$tranpicturetype,$tranflashtype,$mediaplayertype,$realplayertype,$efileftp_fr;
	//处理地址
	$url=trim($url);
	$url=str_replace(" ","%20",$url);
    $r[tran]=1;
	//附件地址
	$r[url]=$url;
	//文件类型
	$r[filetype]=GetFiletype($url);
	if(CheckSaveTranFiletype($r[filetype]))
	{
		$r[tran]=0;
		return $r;
	}
	//是否已上传的文件
	$havetr=CheckNotSaveUrl($url);
	if($havetr)
	{
		$r[tran]=0;
		return $r;
	}
	$string=ReadFiletext($url);
	if(empty($string))//读取不了
	{
		$r[tran]=0;
		return $r;
	}
	//文件名
	$r[insertfile]=ReturnDoTranFilename($file_name,$classid);
	$r[filename]=$r[insertfile].$r[filetype];
	//日期目录
	$r[filepath]=FormatFilePath($classid,$mynewspath,0);
	$filepath=$r[filepath]?$r[filepath].'/':$r[filepath];
	//存放目录
	$fspath=ReturnFileSavePath($classid);
	$r[savepath]=ECMS_PATH.$fspath['filepath'].$filepath;
	//附件地址
	$r[url]=$fspath['fileurl'].$filepath.$r[filename];
	//缩图文件
	$r[name]=$r[savepath]."small".$r[insertfile];
	//附件文件
	$r[yname]=$r[savepath].$r[filename];
	WriteFiletext_n($r[yname],$string);
	$r[filesize]=@filesize($r[yname]);
	//返回类型
	if(strstr($tranflashtype,','.$r[filetype].','))
	{
		$r[type]=2;
	}
	elseif(strstr($tranpicturetype,','.$r[filetype].','))
	{
		$r[type]=1;
	}
	elseif(strstr($mediaplayertype,','.$r[filetype].',')||strstr($realplayertype,','.$r[filetype].','))//多媒体
	{
		$r[type]=3;
	}
	else
	{
		$r[type]=0;
	}
	//FileServer
	if($public_r['openfileserver'])
	{
		$efileftp_fr[]=$r['yname'];
	}
	return $r;
}
// 建立目录函数
function DoMkdir_pkkgu($path){
	global $public_r;
	//不存在则建立
	if(!file_exists($path))
	{
		//安全模式
		if($public_r[phpmode])
		{
			$pr[0]=$path;
			FtpMkdir($ftpid,$pr,0777); //建立ftp目录
			$mk=1;
		}
		else
		{
			$mk=@mkdir($path,0777);
			@chmod($path,0777);
		}
		if(empty($mk))
		{
			echo $path;
			printerror_pkkgu("CreatePathFail","history.go(-1)");
		}
	}
	return true;
}
// 格式化附件目录
function FormatFilePath_pkkgu($classid,$mynewspath,$enews=0){
	global $public_r;
	if($enews)
	{
		$newspath=$mynewspath;
	}
	else
	{
		$newspath=date($public_r['filepath']);
	}
	if(empty($newspath))
	{
		return "";
	}
	$fspath=ReturnFileSavePath($classid); //返回附件目录
	$path=ECMS_PATH.$fspath['filepath'];
	$returnpath="";
	$r=explode("/",$newspath);
	$count=count($r);
	for($i=0;$i<$count;$i++){
		if($i>0){
			$returnpath.="/".$r[$i];
		}
		else{
			$returnpath.=$r[$i];
		}
		$createpath=$path.$returnpath;
		$mk=DoMkdir_pkkgu($createpath);//建立目录函数 pkkgu
		if(empty($mk)){
			printerror_pkkgu("CreatePathFail","");
		}
	}
	return $returnpath;
}
// 上传文件 $ecms=1时为涂鸦上传
function DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid,$ecms=0){
	global $public_r,$class_r,$doetran,$efileftp_fr;
	//文件类型
	$r[filetype]=GetFiletype($file_name); //取得文件扩展名
	//文件名
	$r[insertfile]=ReturnDoTranFilename($file_name,$classid); //返回上传文件名
	$r[filename]=$r[insertfile].$r[filetype];
	//日期目录
	$r[filepath]=FormatFilePath_pkkgu($classid,$mynewspath,0); //格式化附件目录 pkkgu
	$filepath=$r[filepath]?$r[filepath].'/':$r[filepath];
	//存放目录
	$fspath=ReturnFileSavePath($classid); //返回附件目录
	$r[savepath]=ECMS_PATH.$fspath['filepath'].$filepath;
	//附件地址
	$r[url]=$fspath['fileurl'].$filepath.$r[filename];
	//缩图文件
	$r[name]=$r[savepath]."small".$r[insertfile];
	//附件文件
	$r[yname]=$r[savepath].$r[filename];
	$r[tran]=1;
	//验证类型
	if(CheckSaveTranFiletype($r[filetype]))
	{
		if($doetran)
		{
			$r[tran]=0;
			return $r;
		}
		else
		{
			printerror_pkkgu('TranFail','');
		}
	}
	if(empty($ecms))
	{
		//上传文件
		$cp=@move_uploaded_file($file,$r[yname]);
		if(empty($cp))
		{
			if($doetran)
			{
				$r[tran]=0;
				return $r;
			}
			else
			{
				printerror_pkkgu('TranFail','');
			}
		}
	}
	DoChmodFile($r[yname]); //设置上传文件权限
	$r[filesize]=(int)$file_size;
	//FileServer
	if($public_r['openfileserver'])
	{
		$efileftp_fr[]=$r['yname'];
	}
	return $r;
}
// 水印 生成小图
function Getmark_Getsmall_pkkgu($classid,$type,$no,$getsmall,$getmark,$r,$add,$userid,$username){
	//global $empire,$dbtbpre,$public_r,$class_r;
	if($type==1&&($add['getsmall']||$add['getmark']))
	{
		@include_once(ECMS_PATH."e/class/gd.php");
	}
	//缩略图
	if($type==1&&$add['getsmall'])
	{
		GetMySmallImg($classid,$no,$r[insertfile],$r[filepath],$r[yname],$add[width],$add[height],$r[name],$add['filepass'],$add['filepass'],$userid,$username);
	}
	//水印
	if($type==1&&$add['getmark'])
	{
		GetMyMarkImg($r['yname']);
	}
}
/**
 * 返回成功提示
 * $type      附件类型
 * $ptype     图片附加类型
 * $url       附件地址
 * $MD5_name  附件加密名称或者pictitle图片描述
 * $old_name  附件原名名称
 * $filetype  附件类型 (.jpg .rar 等等，swfupload上传附件时使用)
 */
function ok_print($type,$url,$MD5_name,$old_name='',$filetype='',$ptype=0)
{
	if($type==1) //图片
	{
		/**
		 * 向浏览器返回数据json数据
		 * {
		 *   'url'      :'a.jpg',   //保存后的文件路径
		 *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
		 *   'original' :'b.jpg',   //原始文件名
		 *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
		 * }
		 */
		//涂鸦
		if($ptype==3)
		{
			echo '{"url":"'.$url.'","state":"SUCCESS"}';
		}
		//涂鸦背景
		elseif($ptype==4)
		{
			echo "<script>parent.ue_callback('".$url."','SUCCESS')</script>";
		}
		//其他图片类型
		else
		{
			echo "{'url':'".$url."','title':'".$MD5_name."','original':'".$old_name."','state':'SUCCESS'}";
		}
	}else{ //附件
		/**
		 * 向浏览器返回数据json数据
		 * {
		 *   'url'      :'a.rar',        //保存后的文件路径
		 *   'fileType' :'.rar',         //文件描述，对图片来说在前端会添加到title属性上
		 *   'original' :'编辑器.jpg',   //原始文件名
		 *   'state'    :'SUCCESS'       //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
		 * }
		 */
		echo '{"url":"'.$url.'","fileType":"'.$filetype.'","original":"'.$old_name.'","state":"SUCCESS"}';
	}
	db_close();
	$empire=null;
	exit();
}
/**
 * 返回错误提示
 * $ecms      0后台，1前台
 */
function printerror_pkkgu($customMsg,$url=0,$ecms=0){
	global $utf_gbk;
	if(empty($ecms)){
		@include "../../../".LoadLang("pub/message.php");
		$msg=$message_r[$customMsg];
	}else{
		@include "../../../".LoadLang("pub/q_message.php");
		$msg=$qmessage_r[$customMsg];
	}
	if(empty($msg)){
		$msg=$customMsg;
	}
	$msg=doUtfAndGbk_pkkgu($utf_gbk,1,$msg);
	$msg=array("state"=>$msg);
	echo json_encode($msg);
	db_close();
	$empire=null;
	exit();
}
// 写入数据库
function File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=0){
	global $empire,$public_r,$dbtbpre,$utf_gbk;
	$filename=RepPostStr($file_name);
	$add_name='';
	if($type==1) //图片带标题图片批量上传 no值为pictitle
	{
		if(empty($ptype))
		{
			$pictitle=RepPostStr($add['pictitle']);
			if($pictitle) //图片描述
			{
				$filename=$pictitle;
			}
		}
		else if($ptype==1)
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[远程]';
		}
		elseif($ptype==3)
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[涂鸦]';
		}
		elseif($ptype==4)
		{
			$add_name='[涂鸦背景]';
		}
		else if($ptype==5) //屏幕截图
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[截图]';
		}
	}
	$filenameg=$add_name.doUtfAndGbk_pkkgu($utf_gbk,0,$filename);
	$filetime=date("Y-m-d H:i:s");
	$r[filesize]=(int)$r[filesize];
	$classid=(int)$classid;
	if(empty($HQ)){
		$username="[pkkgu_H]".$username;
	}else{
		$username="[pkkgu_Q]".$username;
	}
	$sql=$empire->query("insert into {$dbtbpre}enewsfile(filename,filesize,adduser,path,filetime,classid,no,type,id,cjid,fpath) values('$r[filename]','$r[filesize]','$username','$r[filepath]','$filetime','$classid','$filenameg','$type','$filepass','$filepass','$public_r[fpath]');");
	return $filename;
}
/********************************************************************** 后台图片和附件部分 **********************************************************************/
/********************************************************************** 后台图片和附件部分 **********************************************************************/
// 是否登陆
function is_login_pkkgu($uid=0,$uname='',$urnd='',$mladmin=''){
	global $empire,$public_r,$dbtbpre;
	$userid=$uid?$uid:getcvar('loginuserid',1);
	$username=$uname?$uname:getcvar('loginusername',1);
	$rnd=$urnd?$urnd:getcvar('loginrnd',1);
	$userid=(int)$userid;
	$username=RepPostVar($username);
	$rnd=RepPostVar($rnd);
	if(!$userid||!$username||!$rnd)
	{
		printerror_pkkgu("NotLogin","index.php");
	}
	$mladmin=RepPostVar($mladmin);
	$admin_arr=explode("|",$mladmin);
	$admin=array();
	$admin['loginuserid']       =$admin_arr[0];
	$admin['loginusername']     =$admin_arr[1];
	$admin['loginrnd']          =$admin_arr[2];
	$admin['loginlevel']        =$admin_arr[3];
	$admin['loginadminstyleid'] =$admin_arr[4];
	$admin['truelogintime']     =$admin_arr[5];
	$admin['ecmsdodbdata']      =$admin_arr[6];
	$admin['logintime']         =$admin_arr[7];
	$admin['eloginlic']         =$admin_arr[8];
	$admin['loginecmsckpass']   =$admin_arr[9];
	$groupid=(int)getcvar('loginlevel',1);
	$groupid?"":$groupid=$admin['loginlevel']; // pkkgu
	$adminstyle=(int)getcvar('loginadminstyleid',1);
	$adminstyle?"":$adminstyle=$admin['adminstyle']; // pkkgu
	if(!strstr($public_r['adminstyle'],','.$adminstyle.','))
	{
		$adminstyle=$public_r['defadminstyle']?$public_r['defadminstyle']:1;
	}
	$truelogintime=(int)getcvar('truelogintime',1);
	$truelogintime?"":$truelogintime=$admin['truelogintime']; // pkkgu
	//COOKIE验证
	$loginusername=(int)getcvar('loginusername',1);
	$loginusername?"":$loginusername=$admin['loginusername']; // pkkgu
	if($loginusername)
	{
		//$cdbdata=getcvar('ecmsdodbdata',1)?1:0; //pkkgu
		if(getcvar('ecmsdodbdata',1))
		{
			$cdbdata=1;
		}else if($admin['ecmsdodbdata']){
			$cdbdata=1;
		}else{
			$cdbdata=0;
		}
		DoChECookieRnd_pkkgu($userid,$username,$rnd,$cdbdata,$groupid,$adminstyle,$truelogintime,$admin['loginecmsckpass']); // pkkgu
	}
	//db
	$adminr=$empire->fetch1("select userid,groupid,classid from {$dbtbpre}enewsuser where userid='$userid' and username='".$username."' and rnd='".$rnd."' and checked=0 limit 1");
	if(!$adminr['userid'])
	{
		printerror_pkkgu("SingleUser","index.php");
	}
	//登陆超时
	$logintime=$admin['logintime'];
	$logintime?"":$logintime=getcvar('logintime',1); // pkkgu
	if($logintime)
	{
		if(time()-$logintime>$public_r['exittime']*60)
		{
			printerror_pkkgu("LoginTime","index.php"); //pkkgu
	    }
		esetcookie("logintime",time(),0,1);
	}
	$eloginlic=getcvar('eloginlic',1);
	$eloginlic?"":$eloginlic=$admin['eloginlic']; // pkkgu
	if($eloginlic<>"empirecmslic")
	{
		printerror_pkkgu("NotLogin","index.php");
	}
	$ur[userid]=$userid;
	$ur[username]=$username;
	$ur[rnd]=$rnd;
	$ur[groupid]=$adminr[groupid];
	$ur[adminstyleid]=(int)$adminstyle;
	$ur[classid]=$adminr[classid];
	return $ur;
}
function DoChECookieRnd_pkkgu($userid,$username,$rnd,$dbdata,$groupid,$adminstyle,$truelogintime,$loginecmsckpass){
	global $do_ecookiernd,$do_ckhloginip,$do_ckhloginfile;
	$ip=$do_ckhloginip==0?'127.0.0.1':egetip();
	$ecmsckpass=md5(md5($rnd.$do_ecookiernd).'-'.$ip.'-'.$userid.'-'.$username.'-'.$dbdata.$rnd.$groupid.'-'.$adminstyle);
	if($ecmsckpass<>$loginecmsckpass)
	{
		printerror_pkkgu("NotLogin","index.php");
	}
	if(empty($do_ckhloginfile))
	{
		DoECheckFileRnd_pkkgu($userid,$username,$rnd,$dbdata,$groupid,$adminstyle,$truelogintime,$ip);
	}
}
function DoECheckFileRnd_pkkgu($userid,$username,$rnd,$dbdata,$groupid,$adminstyle,$truelogintime,$ip){
	global $do_ecookiernd,$do_ckhloginip;
	$file=ECMS_PATH.'e/data/adminlogin/user'.$userid.'_'.md5(md5($username.'-empirecms!check.file'.$truelogintime.'-'.$rnd.$do_ecookiernd).'-'.$ip.'-'.$userid.'-'.$rnd.$adminstyle.'-'.$groupid.'-'.$dbdata).'.log';
	if(!file_exists($file))
	{
		printerror_pkkgu('NotLogin','index.php');
	}
}
// 后台文件类型验证 pkkgu
function ChckeFileType_H_pkkgu($type,$filetype,$file_size){
	global $public_r,$tranpicturetype,$tranflashtype;
	if(CheckSaveTranFiletype($filetype)) //保留扩展名验证
	{
		printerror_pkkgu("NotQTranFiletype");
	}
	//如果是.php文件
	if(CheckSaveTranFiletype($filetype))
	{
		printerror_pkkgu("TranPHP","history.go(-1)");
	}
	$type_r=explode("|".$filetype."|",$public_r['filetype']);
	if(count($type_r)<2)
	{
		printerror_pkkgu("TranFiletypeFail","history.go(-1)");
	}
	if($file_size>$public_r['filesize']*1024)
	{
		printerror_pkkgu("TranFilesizeFail","history.go(-1)");
	}
	if($type==1)//上传图片
	{
		if(!strstr($tranpicturetype,','.$filetype.','))
		{
			printerror_pkkgu("NotTranImg","history.go(-1)");
		}
	}
	elseif($type==2)//上传flash
	{
		if(!strstr($tranflashtype,','.$filetype.','))
		{
			printerror_pkkgu("NotTranFlash","history.go(-1)");
		}
	}
	elseif($type==3)//上传多媒体
	{}
	else//上传附件
	{}
}
// 后台 上传附件
function TranFile_pkkgu($add,$file,$file_name,$file_type,$file_size,$userid,$username,$rnd,$tranurl){
	global $empire,$public_r,$loginrnd,$dbtbpre;
	$filepass=(int)$add['filepass'];
	$classid=(int)$add['classid'];
	$type=(int)$add['type'];
	$ptype=(int)$add['ptype'];
	if(!$filepass||!$classid)
	{
		printerror_pkkgu("EmptyQTranFile");
	}
	if($ptype==3) //涂鸦
	{
		$r=DoTranFile_pkkgu('','srcawl.png','.png',0,$classid,1);
		$base64Data=$add['content'];
		if(empty($r[tran])&&empty($r[yname])&&empty($base64Data))
		{
			printerror_pkkgu("TranFail");
		}
		$content=base64_decode($base64Data);
		$r[filesize]=file_put_contents($r[yname],$content); //生成文件 返回文件大小
		if (empty($r[filesize])) {
			printerror_pkkgu("上传不成功".$r[yname]);
		}
	}
	else
	{
		$filetype=GetFiletype($file_name);//取得文件类型
		ChckeFileType_H_pkkgu($type,$filetype,$file_size); //后台文件类型验证 pkkgu
		//本地上传
		$r=DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail");
		}
	}
	//写入数据库
	$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=0);
	//水印 生成小图
	Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username);
	if($sql)
	{
		//上传成功返回前端
		ok_print($type,$r['url'],$sql,$file_name,$filetype,$ptype);
	}
	else
	{
		printerror_pkkgu("InTranRecordFail","history.go(-1)");
	}
}
// 后台 批量远程保存图片
function TranMoreFile_H_pkkgu($add,$userid,$username,$rnd,$tranurl){
	global $empire,$public_r,$loginrnd,$dbtbpre;
	$filepass=(int)$add['filepass'];
	$classid=(int)$add['classid'];
	$type=(int)$add['type'];
	$ptype=(int)$add['ptype'];
	if(!$filepass||!$classid)
	{
		printerror_pkkgu("EmptyQTranFile");
	}
	if(empty($tranurl)||$tranurl=="http://")
	{
		printerror_pkkgu("EmptyHttp","history.go(-1)");
	}
	$tmpNames=array();
	$uri=$tranurl;
	$arr_url=explode("ue_separate_ue",$tranurl);
	$count=count($arr_url);
	for($i=0;$i<=$count-1;$i++)
	{
		$tranurl=$arr_url[$i];
		$filetype=GetFiletype($tranurl);//取得文件类型
		ChckeFileType_H_pkkgu($type,$filetype,0); //后台文件类型验证 pkkgu
		//保存远程图片
		$r=DoTranUrl_pkkgu($tranurl,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail");
		}
		//写入数据库
		$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,'[远程]',$type,$ptype,$filepass,$HQ=0);
		$tmpNames[]=$r['url'];
		Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // 水印 生成小图
	}
	if($sql)
	{
		//上传成功返回前端
		echo "{'url':'".implode("ue_separate_ue",$tmpNames )."','tip':'远程图片抓取成功！','srcUrl':'".$uri."'}";
		db_close();
		$empire=null;
		exit();
	}
	else
	{
		printerror_pkkgu("InTranRecordFail","history.go(-1)");
	}
}
/********************************************************************** 前台图片和附件部分 **********************************************************************/
/********************************************************************** 前台图片和附件部分 **********************************************************************/
//----------------------------------是否登陆
//转向会员组
function OutTimeZGroup_pkkgu($userid,$zgroupid){
	global $empire,$user_tablename,$user_group,$user_zgroup,$user_userdate,$user_userid;
	if($zgroupid)
	{
		$sql=$empire->query("update ".$user_tablename." set ".$user_group."='".$zgroupid."',".$user_userdate."=0 where ".$user_userid."='$userid'");
	}
	else
	{
		$sql=$empire->query("update ".$user_tablename." set ".$user_userdate."=0 where ".$user_userid."='$userid'");
	}
}
function islogin_pkkgu($uid=0,$uname='',$urnd=''){
	global $empire,$public_r,$editor,$user_tablename,$user_userid,$user_username,$user_email,$user_userfen,$user_money,$user_group,$user_groupid,$user_rnd,$user_zgroup,$user_userdate,$user_havemsg,$ecmsreurl,$eloginurl,$user_checked,$user_registertime;
	if($uid)
	{$userid=(int)$uid;}
	else
	{$userid=(int)getcvar('mluserid');}
	if($uname)
	{$username=$uname;}
	else
	{$username=getcvar('mlusername');}
	$username=RepPostVar($username);
	if($urnd)
	{$rnd=$urnd;}
	else
	{$rnd=getcvar('mlrnd');}
	if($eloginurl)
	{$gotourl=$eloginurl;}
	else
	{$gotourl=$public_r['newsurl']."e/member/login/";}
	$petype=1;
	if(!$userid)
	{
		printerror_pkkgu("NotLogin",$gotourl,$petype);
	}
	$rnd=RepPostVar($rnd);
	$cr=$empire->fetch1("select ".$user_userid.",".$user_username.",".$user_email.",".$user_group.",".$user_userfen.",".$user_money.",".$user_userdate.",".$user_zgroup.",".$user_havemsg.",".$user_checked.",".$user_registertime." from ".$user_tablename." where ".$user_userid."='$userid' and ".$user_username."='$username' and ".$user_rnd."='$rnd' limit 1");
	if(!$cr[$user_userid])
	{
		EmptyEcmsCookie();
		if(!getcvar('returnurl'))
		{
			esetcookie("returnurl",$_SERVER['HTTP_REFERER'],0);
		}
		if($ecmsreurl==1)
		{
			$gotourl="history.go(-1)";
			$petype=9;
		}
		elseif($ecmsreurl==2)
		{
			$phpmyself=urlencode($_SERVER['PHP_SELF']."?".$_SERVER["QUERY_STRING"]);
			$gotourl=$public_r['newsurl']."e/member/login/login.php?prt=1&from=".$phpmyself;
			$petype=9;
		}
		printerror_pkkgu("NotSingleLogin",$gotourl,$petype);
	}
	if($cr[$user_checked]==0)
	{
		EmptyEcmsCookie();
		if($ecmsreurl==1)
		{
			$gotourl="history.go(-1)";
			$petype=9;
		}
		elseif($ecmsreurl==2)
		{
			$phpmyself=urlencode($_SERVER['PHP_SELF']."?".$_SERVER["QUERY_STRING"]);
			$gotourl=$public_r['newsurl']."e/member/login/login.php?prt=1&from=".$phpmyself;
			$petype=9;
		}
		printerror_pkkgu("NotCheckedUser",'',$petype);
	}
	//默认会员组
	if(empty($cr[$user_group]))
	{
		$usql=$empire->query("update ".$user_tablename." set ".$user_group."='$user_groupid' where ".$user_userid."='".$cr[$user_userid]."'");
		$cr[$user_group]=$user_groupid;
	}
	//是否过期
	if($cr[$user_userdate])
	{
		if($cr[$user_userdate]-time()<=0)
		{
			OutTimeZGroup_pkkgu($cr[$user_userid],$cr[$user_zgroup]); //转向会员组 pkkgu
			$cr[$user_userdate]=0;
			if($cr[$user_zgroup])
			{
				$cr[$user_group]=$cr[$user_zgroup];
				$cr[$user_zgroup]=0;
			}
		}
	}
	$re[userid]=$cr[$user_userid];
	$re[rnd]=$rnd;
	$re[username]=doUtfAndGbk($cr[$user_username],1);
	$re[email]=doUtfAndGbk($cr[$user_email],1);
	$re[userfen]=$cr[$user_userfen];
	$re[money]=$cr[$user_money];
	$re[groupid]=$cr[$user_group];
	$re[userdate]=$cr[$user_userdate];
	$re[zgroupid]=$cr[$user_zgroup];
	$re[havemsg]=$cr[$user_havemsg];
	$re[registertime]=$cr[$user_registertime];
	return $re;
}
//验证提交IP
function eCheckAccessDoIp_pkkgu($doing){
	global $public_r,$empire,$dbtbpre;
	$pr=$empire->fetch1("select opendoip,closedoip,doiptype from {$dbtbpre}enewspublic limit 1");
	if(!strstr($pr['doiptype'],','.$doing.','))
	{
		return '';
	}
	$userip=egetip();
	//允许IP
	if($pr['opendoip'])
	{
		$close=1;
		foreach(explode("\n",$pr['opendoip']) as $ctrlip)
		{
			if(preg_match("/^(".preg_quote(($ctrlip=trim($ctrlip)),'/').")/",$userip))
			{
				$close=0;
				break;
			}
		}
		if($close==1)
		{
			printerror_pkkgu('NotCanPostIp','history.go(-1)',1);
		}
	}
	//禁止IP
	if($pr['closedoip'])
	{
		foreach(explode("\n",$pr['closedoip']) as $ctrlip)
		{
			if(preg_match("/^(".preg_quote(($ctrlip=trim($ctrlip)),'/').")/",$userip))
			{
				printerror_pkkgu('NotCanPostIp','history.go(-1)',1);
			}
		}
	}
}
//检测点数是否足够
function MCheckEnoughFen_pkkgu($userfen,$userdate,$fen){
	if(!($userdate-time()>0))
	{
		if($userfen+$fen<0)
		{
			printerror_pkkgu("HaveNotFenAQinfo","history.go(-1)",1);
		}
	}
}
//检查投稿数
function DoQCheckAddNum_pkkgu($userid,$groupid){
	global $empire,$dbtbpre,$level_r,$public_r;
	$ur=$empire->fetch1("select userid,todayinfodate,todayaddinfo from {$dbtbpre}enewsmemberadd where userid='$userid' limit 1");
	$thetoday=date("Y-m-d");
	if($ur['userid'])
	{
		if($thetoday!=$ur['todayinfodate'])
		{
			$query="update {$dbtbpre}enewsmemberadd set todayinfodate='$thetoday',todayaddinfo=1 where userid='$userid'";
		}
		else
		{
			if($ur['todayaddinfo']>=$level_r[$groupid]['dayaddinfo'])
			{
				printerror_pkkgu("CrossDayInfo",$public_r['newsurl'],1);
			}
			$query="update {$dbtbpre}enewsmemberadd set todayaddinfo=todayaddinfo+1 where userid='$userid'";
		}
	}
	else
	{
		$query="replace into {$dbtbpre}enewsmemberadd(userid,todayinfodate,todayaddinfo) values('$userid','$thetoday',1);";
	}
	return $query;
}
//新用户投稿验证
function qCheckNewMemberAddInfo_pkkgu($registertime){
	global $user_register,$public_r;
	if(empty($public_r['newaddinfotime']))
	{
		return '';
	}
	if(empty($user_register))
	{
		$registertime=to_time($registertime);
	}
	if(time()-$registertime<=$public_r['newaddinfotime']*60)
	{
		printerror_pkkgu('NewMemberAddInfoError','',1);
	}
}
//投稿权限检测
function DoQCheckAddLevel_pkkgu($classid,$userid,$username,$rnd,$ecms=0,$isadd=0){
	global $empire,$dbtbpre,$level_r,$public_r;
	$r=$empire->fetch1("select * from {$dbtbpre}enewsclass where classid='$classid'");
	if(!$r['classid']||$r[wburl])
	{
		printerror_pkkgu("EmptyQinfoCid","",1);
	}
	if(!$r['islast'])
	{
		printerror_pkkgu("MustLast","",1);
	}
	if($r['openadd'])
	{
		printerror_pkkgu("NotOpenCQInfo","",1);
	}
	//是否登陆
	if($ecms==1||$ecms==2||($r['qaddgroupid']&&$r['qaddgroupid']<>','))
	{
		$user=islogin_pkkgu($userid,$username,$rnd); //是否登陆 pkkgu
		//验证新会员投稿
		if($isadd==1&&$public_r['newaddinfotime'])
		{
			qCheckNewMemberAddInfo_pkkgu($user[registertime]); //新用户投稿验证 pkkgu
		}
	}
	//会员组
	if($r['qaddgroupid']&&$r['qaddgroupid']<>',')
	{
		if(!strstr($r['qaddgroupid'],','.$user[groupid].','))
		{
			printerror_pkkgu("HaveNotLevelAQinfo","history.go(-1)",1);
		}
	}
	if($isadd==1)
	{
		//检测是否足够点数
		if($r['addinfofen']<0&&$user['userid'])
		{
			MCheckEnoughFen_pkkgu($user['userfen'],$user['userdate'],$r['addinfofen']);//检测点数是否足够 pkkgu
		}
		//检测投稿数
		if($r['qaddgroupid']&&$r['qaddgroupid']<>','&&$level_r[$user[groupid]]['dayaddinfo'])
		{
			$r['checkaddnumquery']=DoQCheckAddNum_pkkgu($user['userid'],$user['groupid']); //检查投稿数 pkkgu
		}
	}
	//审核
	if(($ecms==0||$ecms==1)&&$userid)
	{
		if(!$user[groupid])
		{
			$user=islogin_pkkgu($userid,$username,$rnd); //是否登陆 pkkgu
		}
		if($level_r[$user[groupid]]['infochecked'])
		{
			$r['checkqadd']=1;
			$r['qeditchecked']=0;
		}
	}
	return $r;
}
//前台文件类型验证 pkkgu
function ChckeFileType_Q_pkkgu($type,$filetype,$file_size){
	global $empire,$dbtbpre,$public_r,$tranpicturetype,$tranflashtype;
	if(CheckSaveTranFiletype($filetype)) //保留扩展名验证
	{
		printerror_pkkgu("NotQTranFiletype","",9);
	}
	$pr=$empire->fetch1("select qaddtran,qaddtransize,qaddtranimgtype,qaddtranfile,qaddtranfilesize,qaddtranfiletype from {$dbtbpre}enewspublic limit 1");
	if($type==1)//图片
	{
		if(!$pr['qaddtran'])
		{
			printerror_pkkgu("CloseQTranPic","",9);
		}
		if(!strstr($pr['qaddtranimgtype'],"|".$filetype."|"))
		{
			printerror_pkkgu("NotQTranFiletype","",9);
		}
		if($file_size>$pr['qaddtransize']*1024)
		{
			printerror_pkkgu("TooBigQTranFile","",9);
		}
		if(!strstr($tranpicturetype,','.$filetype.','))
		{
			printerror_pkkgu("NotQTranFiletype","",9);
		}
	}
	elseif($type==2)//flash
	{
		if(!$pr['qaddtranfile'])
		{
			printerror_pkkgu("CloseQTranFile","",9);
		}
		if(!strstr($pr['qaddtranfiletype'],"|".$filetype."|"))
		{
			printerror_pkkgu("NotQTranFiletype","",9);
		}
		if($file_size>$pr['qaddtranfilesize']*1024)
		{
			printerror_pkkgu("TooBigQTranFile","",9);
		}
		if(!strstr($tranflashtype,','.$filetype.','))
		{
			printerror_pkkgu("NotQTranFiletype","",9);
		}
	}
	else//附件
	{
		if(!$pr['qaddtranfile'])
		{
			printerror_pkkgu("CloseQTranFile","",9);
		}
		if(!strstr($pr['qaddtranfiletype'],"|".$filetype."|"))
		{
			printerror_pkkgu("NotQTranFiletype","",9);
		}
		if($file_size>$pr['qaddtranfilesize']*1024)
		{
			printerror_pkkgu("TooBigQTranFile","",9);
		}
	}
}
// 前台 上传附件
function DoQTranFile_pkkgu($add,$file,$file_name,$file_type,$file_size,$userid,$username,$rnd,$ecms=0,$tranurl=''){
	global $empire,$dbtbpre,$public_r;
	if($public_r['addnews_ok'])//关闭投稿
	{
		printerror_pkkgu("NotOpenCQInfo","",9);
	}
	$filepass=(int)$add['filepass'];
	$classid=(int)$add['classid'];
	$type=(int)$add['type'];
	$ptype=(int)$add['ptype'];
	if(!$filepass||!$classid)
	{
		printerror_pkkgu("EmptyQTranFile","",9);
	}
	//验证权限
	$userid=(int)$userid;
	$username=RepPostVar($username);
	$rnd=RepPostVar($rnd);
	DoQCheckAddLevel_pkkgu($classid,$userid,$username,$rnd,0,0); //投稿权限检测 pkkgu
	if(!$username){
		$username='游客';
	}
	if($ptype==3) //涂鸦
	{
		$file='';
		$file_name='srcawl.png';
		$file_type='.png';
		$file_size=1;
		$r=DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid,1);
		$base64Data=$add['content'];
		if(empty($r[tran])&&empty($r[yname])&&empty($base64Data))
		{
			printerror_pkkgu("TranFail","",9);
		}
		$content=base64_decode($base64Data);
		$r[filesize]=file_put_contents($r[yname],$content); //生成文件 返回文件大小
		if (empty($r[filesize])) {
			printerror_pkkgu("上传不成功".$r[yname]);
		}
	}
	else
	{
		$filetype=GetFiletype($file_name);//取得文件类型
		ChckeFileType_Q_pkkgu($type,$filetype,$file_size); //前台文件类型检测 pkkgu
		//本地上传
		$r=DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail","",9);
		}
	}
	//写入数据库
	$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=1);
	Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // 水印 生成小图
	//编辑器
	if($sql)
	{
		ok_print($type,$r['url'],$sql,$file_name,$filetype,$ptype);
	}
	else
	{
		printerror_pkkgu("EmptyQTranFile","history.go(-1)");
	}
}
// 前台 批量远程保存图片
function TranMoreFile_Q_pkkgu($add,$userid,$username,$rnd,$tranurl){
	global $empire,$dbtbpre,$public_r;
	if($public_r['addnews_ok'])//关闭投稿
	{
		printerror_pkkgu("NotOpenCQInfo","",9);
	}
	$filepass=(int)$add['filepass'];
	$classid=(int)$add['classid'];
	$type=(int)$add['type'];
	$ptype=(int)$add['ptype'];
	if(!$filepass||!$classid)
	{
		printerror_pkkgu("EmptyQTranFile","",9);
	}
	//验证权限
	$userid=(int)$userid;
	$username=RepPostVar($username);
	$rnd=RepPostVar($rnd);
	DoQCheckAddLevel_pkkgu($classid,$userid,$username,$rnd,0,0); //投稿权限检测 pkkgu
	if(!$username){
		$username='游客';
	}
	if(empty($tranurl)||$tranurl=="http://")
	{
		printerror_pkkgu("EmptyHttp","history.go(-1)",1);
	}
	$tmpNames=array();
	$uri=$tranurl;
	$arr_url=explode("ue_separate_ue",$tranurl);
	$count=count($arr_url);
	for($i=0;$i<=$count-1;$i++)
	{
		$tranurl=$arr_url[$i];
		$filetype=GetFiletype($tranurl);//取得文件类型
		ChckeFileType_Q_pkkgu($type,$filetype,0); //前台文件类型检测 pkkgu
		//保存远程图片
		$r=DoTranUrl_pkkgu($tranurl,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail","",9);
		}
		//写入数据库
		$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,'[远程]',$type,$ptype,$filepass,$HQ=0);
		$tmpNames[]=$r['url'];
		Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // 水印 生成小图
	}
	//编辑器
	if($sql)
	{
		 echo "{'url':'".implode("ue_separate_ue",$tmpNames )."','tip':'远程图片抓取成功！','srcUrl':'".$uri."'}";
	}
	else
	{
		printerror_pkkgu("EmptyQTranFile","history.go(-1)",1);
	}
}
/********************************************************************** 在线部分 **********************************************************************/
/********************************************************************** 在线部分 **********************************************************************/
// 前台会员显在线显示图片
function Show_Image_User_pkkgu($add,$userid,$username,$rnd){
	global $empire,$dbtbpre,$public_r,$class_r;
	$user=islogin_pkkgu($userid,$username,$rnd); //是否登陆 pkkgu
	$classid=(int)$add['classid'];
	$type=1;
	$add="";
	//登录
	$user=islogin_pkkgu($userid,$username,$rnd);
	if($user[username])
	{
		$add.=" and adduser='[pkkgu_QT]".$user[username]."'";
	}
	else
	{
		printerror_pkkgu("NotCheckedUser");
	}
	//栏目
	if($classid)
	{
		if($class_r[$classid]['islast'])
		{
			$add.=" and classid='$classid'";
		}
		else
		{
			$add.=" and ".ReturnClass($class_r[$searchclassid]['sonclass']);
		}
		/*//当前信息
		$filepass=(int)$add['filepass'];
		if($sinfo)
		{
			$add.=" and id='$filepass'";
		}*/
	}
	else
	{
		printerror_pkkgu("ErrorUrl");
	}
	$query="select fileid,filename,filesize,path,filetime,classid,no,fpath from {$dbtbpre}enewsfile where type='$type'".$add;
	$query.=" order by fileid desc limit 100";
	$sql=$empire->query($query);
	$file_url='';
	while($r=$empire->fetch($sql))
	{
		$ono=$r[no];
		$r[no]=sub($r[no],0,$sub,false);
		$filesize=ChTheFilesize($r[filesize]);//文件大小
		$filetype=GetFiletype($r[filename]);//取得文件扩展名
		//文件
		$fspath=ReturnFileSavePath($r[classid],$r[fpath]);
		$filepath=$r[path]?$r[path].'/':$r[path];
		$file_url.=$file=$fspath['fileurl'].$filepath.$r[filename].'ue_separate_ue';
	}
	echo $file_url;
}
// 后台管理员显在线显示图片
function Show_Image_admin_pkkgu($add,$userid,$username,$rnd){
	global $empire,$dbtbpre,$public_r,$class_r;
	$classid=(int)$add['classid'];
	$type=1;
	$add="";
	//栏目
	if($classid)
	{
		if($class_r[$classid]['islast'])
		{
			$add.=" and classid='$classid'";
		}
		else
		{
			$add.=" and ".ReturnClass($class_r[$searchclassid]['sonclass']);
		}
		/*//当前信息
		$filepass=(int)$add['filepass'];
		//$select_sinfo='';
		if($sinfo)
		{
			$add.=" and id='$filepass'";
		}*/
	}
	else
	{
		printerror_pkkgu("ErrorUrl");
	}
	$query="select fileid,filename,filesize,path,filetime,classid,no,fpath from {$dbtbpre}enewsfile where type='$type'".$add;
	$query.=" order by fileid desc limit 100";
	$sql=$empire->query($query);
	$file_url='';
	while($r=$empire->fetch($sql))
	{
		$ono=$r[no];
		$r[no]=sub($r[no],0,$sub,false);
		$filesize=ChTheFilesize($r[filesize]);//文件大小
		$filetype=GetFiletype($r[filename]);//取得文件扩展名
		//文件

		$fspath=ReturnFileSavePath($r[classid],$r[fpath]);
		$filepath=$r[path]?$r[path].'/':$r[path];
		$file_url.=$file=$fspath['fileurl'].$filepath.$r[filename].'ue_separate_ue';
	}
	echo $file_url;
	db_close();
	$empire=null;
}
?>