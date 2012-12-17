<?php
/*
 * -+------------------------------------------------------------------------+-
 * | Author : pkkgu ��QQ��910111100��
 * | Contact: http://t.qq.com/ly0752
 * | LastUpdate: 2012-12-14
 * -+------------------------------------------------------------------------+-
 */ 

/********************************************************************** �������� **********************************************************************/
/********************************************************************** �������� **********************************************************************/
/**
 * ����ת��
 * @param $num   ֵΪ��0ʱִ��ת������
 * @param $phome 0ΪUTF-8תΪGBK2312 1ΪGBKתΪUTF-8
 * @param $str   Ҫת�����ַ���
 * doUtfAndGbk_pkkgu(1,0,$str);
 */
function doUtfAndGbk_pkkgu($num=0,$phome=0,$str){
	if(empty($num))//��������
	{
		return $str;
    }
	if(!function_exists("iconv"))//�Ƿ�֧��iconv
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
// Զ�̱���
function DoTranUrl_pkkgu($url,$classid){
	global $public_r,$class_r,$tranpicturetype,$tranflashtype,$mediaplayertype,$realplayertype,$efileftp_fr;
	//�����ַ
	$url=trim($url);
	$url=str_replace(" ","%20",$url);
    $r[tran]=1;
	//������ַ
	$r[url]=$url;
	//�ļ�����
	$r[filetype]=GetFiletype($url);
	if(CheckSaveTranFiletype($r[filetype]))
	{
		$r[tran]=0;
		return $r;
	}
	//�Ƿ����ϴ����ļ�
	$havetr=CheckNotSaveUrl($url);
	if($havetr)
	{
		$r[tran]=0;
		return $r;
	}
	$string=ReadFiletext($url);
	if(empty($string))//��ȡ����
	{
		$r[tran]=0;
		return $r;
	}
	//�ļ���
	$r[insertfile]=ReturnDoTranFilename($file_name,$classid);
	$r[filename]=$r[insertfile].$r[filetype];
	//����Ŀ¼
	$r[filepath]=FormatFilePath($classid,$mynewspath,0);
	$filepath=$r[filepath]?$r[filepath].'/':$r[filepath];
	//���Ŀ¼
	$fspath=ReturnFileSavePath($classid);
	$r[savepath]=ECMS_PATH.$fspath['filepath'].$filepath;
	//������ַ
	$r[url]=$fspath['fileurl'].$filepath.$r[filename];
	//��ͼ�ļ�
	$r[name]=$r[savepath]."small".$r[insertfile];
	//�����ļ�
	$r[yname]=$r[savepath].$r[filename];
	WriteFiletext_n($r[yname],$string);
	$r[filesize]=@filesize($r[yname]);
	//��������
	if(strstr($tranflashtype,','.$r[filetype].','))
	{
		$r[type]=2;
	}
	elseif(strstr($tranpicturetype,','.$r[filetype].','))
	{
		$r[type]=1;
	}
	elseif(strstr($mediaplayertype,','.$r[filetype].',')||strstr($realplayertype,','.$r[filetype].','))//��ý��
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
// ����Ŀ¼����
function DoMkdir_pkkgu($path){
	global $public_r;
	//����������
	if(!file_exists($path))
	{
		//��ȫģʽ
		if($public_r[phpmode])
		{
			$pr[0]=$path;
			FtpMkdir($ftpid,$pr,0777); //����ftpĿ¼
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
// ��ʽ������Ŀ¼
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
	$fspath=ReturnFileSavePath($classid); //���ظ���Ŀ¼
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
		$mk=DoMkdir_pkkgu($createpath);//����Ŀ¼���� pkkgu
		if(empty($mk)){
			printerror_pkkgu("CreatePathFail","");
		}
	}
	return $returnpath;
}
// �ϴ��ļ� $ecms=1ʱΪͿѻ�ϴ�
function DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid,$ecms=0){
	global $public_r,$class_r,$doetran,$efileftp_fr;
	//�ļ�����
	$r[filetype]=GetFiletype($file_name); //ȡ���ļ���չ��
	//�ļ���
	$r[insertfile]=ReturnDoTranFilename($file_name,$classid); //�����ϴ��ļ���
	$r[filename]=$r[insertfile].$r[filetype];
	//����Ŀ¼
	$r[filepath]=FormatFilePath_pkkgu($classid,$mynewspath,0); //��ʽ������Ŀ¼ pkkgu
	$filepath=$r[filepath]?$r[filepath].'/':$r[filepath];
	//���Ŀ¼
	$fspath=ReturnFileSavePath($classid); //���ظ���Ŀ¼
	$r[savepath]=ECMS_PATH.$fspath['filepath'].$filepath;
	//������ַ
	$r[url]=$fspath['fileurl'].$filepath.$r[filename];
	//��ͼ�ļ�
	$r[name]=$r[savepath]."small".$r[insertfile];
	//�����ļ�
	$r[yname]=$r[savepath].$r[filename];
	$r[tran]=1;
	//��֤����
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
		//�ϴ��ļ�
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
	DoChmodFile($r[yname]); //�����ϴ��ļ�Ȩ��
	$r[filesize]=(int)$file_size;
	//FileServer
	if($public_r['openfileserver'])
	{
		$efileftp_fr[]=$r['yname'];
	}
	return $r;
}
// ˮӡ ����Сͼ
function Getmark_Getsmall_pkkgu($classid,$type,$no,$getsmall,$getmark,$r,$add,$userid,$username){
	//global $empire,$dbtbpre,$public_r,$class_r;
	if($type==1&&($add['getsmall']||$add['getmark']))
	{
		@include_once(ECMS_PATH."e/class/gd.php");
	}
	//����ͼ
	if($type==1&&$add['getsmall'])
	{
		GetMySmallImg($classid,$no,$r[insertfile],$r[filepath],$r[yname],$add[width],$add[height],$r[name],$add['filepass'],$add['filepass'],$userid,$username);
	}
	//ˮӡ
	if($type==1&&$add['getmark'])
	{
		GetMyMarkImg($r['yname']);
	}
}
/**
 * ���سɹ���ʾ
 * $type      ��������
 * $ptype     ͼƬ��������
 * $url       ������ַ
 * $MD5_name  �����������ƻ���pictitleͼƬ����
 * $old_name  ����ԭ������
 * $filetype  �������� (.jpg .rar �ȵȣ�swfupload�ϴ�����ʱʹ��)
 */
function ok_print($type,$url,$MD5_name,$old_name='',$filetype='',$ptype=0)
{
	if($type==1) //ͼƬ
	{
		/**
		 * ���������������json����
		 * {
		 *   'url'      :'a.jpg',   //�������ļ�·��
		 *   'title'    :'hello',   //�ļ���������ͼƬ��˵��ǰ�˻���ӵ�title������
		 *   'original' :'b.jpg',   //ԭʼ�ļ���
		 *   'state'    :'SUCCESS'  //�ϴ�״̬���ɹ�ʱ����SUCCESS,�����κ�ֵ��ԭ��������ͼƬ�ϴ�����
		 * }
		 */
		//Ϳѻ
		if($ptype==3)
		{
			echo '{"url":"'.$url.'","state":"SUCCESS"}';
		}
		//Ϳѻ����
		elseif($ptype==4)
		{
			echo "<script>parent.ue_callback('".$url."','SUCCESS')</script>";
		}
		//����ͼƬ����
		else
		{
			echo "{'url':'".$url."','title':'".$MD5_name."','original':'".$old_name."','state':'SUCCESS'}";
		}
	}else{ //����
		/**
		 * ���������������json����
		 * {
		 *   'url'      :'a.rar',        //�������ļ�·��
		 *   'fileType' :'.rar',         //�ļ���������ͼƬ��˵��ǰ�˻���ӵ�title������
		 *   'original' :'�༭��.jpg',   //ԭʼ�ļ���
		 *   'state'    :'SUCCESS'       //�ϴ�״̬���ɹ�ʱ����SUCCESS,�����κ�ֵ��ԭ��������ͼƬ�ϴ�����
		 * }
		 */
		echo '{"url":"'.$url.'","fileType":"'.$filetype.'","original":"'.$old_name.'","state":"SUCCESS"}';
	}
	db_close();
	$empire=null;
	exit();
}
/**
 * ���ش�����ʾ
 * $ecms      0��̨��1ǰ̨
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
// д�����ݿ�
function File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=0){
	global $empire,$public_r,$dbtbpre,$utf_gbk;
	$filename=RepPostStr($file_name);
	$add_name='';
	if($type==1) //ͼƬ������ͼƬ�����ϴ� noֵΪpictitle
	{
		if(empty($ptype))
		{
			$pictitle=RepPostStr($add['pictitle']);
			if($pictitle) //ͼƬ����
			{
				$filename=$pictitle;
			}
		}
		else if($ptype==1)
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[Զ��]';
		}
		elseif($ptype==3)
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[Ϳѻ]';
		}
		elseif($ptype==4)
		{
			$add_name='[Ϳѻ����]';
		}
		else if($ptype==5) //��Ļ��ͼ
		{
			$filename=RepPostStr($r[filename]);
			$add_name='[��ͼ]';
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
/********************************************************************** ��̨ͼƬ�͸������� **********************************************************************/
/********************************************************************** ��̨ͼƬ�͸������� **********************************************************************/
// �Ƿ��½
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
	//COOKIE��֤
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
	//��½��ʱ
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
// ��̨�ļ�������֤ pkkgu
function ChckeFileType_H_pkkgu($type,$filetype,$file_size){
	global $public_r,$tranpicturetype,$tranflashtype;
	if(CheckSaveTranFiletype($filetype)) //������չ����֤
	{
		printerror_pkkgu("NotQTranFiletype");
	}
	//�����.php�ļ�
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
	if($type==1)//�ϴ�ͼƬ
	{
		if(!strstr($tranpicturetype,','.$filetype.','))
		{
			printerror_pkkgu("NotTranImg","history.go(-1)");
		}
	}
	elseif($type==2)//�ϴ�flash
	{
		if(!strstr($tranflashtype,','.$filetype.','))
		{
			printerror_pkkgu("NotTranFlash","history.go(-1)");
		}
	}
	elseif($type==3)//�ϴ���ý��
	{}
	else//�ϴ�����
	{}
}
// ��̨ �ϴ�����
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
	if($ptype==3) //Ϳѻ
	{
		$r=DoTranFile_pkkgu('','srcawl.png','.png',0,$classid,1);
		$base64Data=$add['content'];
		if(empty($r[tran])&&empty($r[yname])&&empty($base64Data))
		{
			printerror_pkkgu("TranFail");
		}
		$content=base64_decode($base64Data);
		$r[filesize]=file_put_contents($r[yname],$content); //�����ļ� �����ļ���С
		if (empty($r[filesize])) {
			printerror_pkkgu("�ϴ����ɹ�".$r[yname]);
		}
	}
	else
	{
		$filetype=GetFiletype($file_name);//ȡ���ļ�����
		ChckeFileType_H_pkkgu($type,$filetype,$file_size); //��̨�ļ�������֤ pkkgu
		//�����ϴ�
		$r=DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail");
		}
	}
	//д�����ݿ�
	$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=0);
	//ˮӡ ����Сͼ
	Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username);
	if($sql)
	{
		//�ϴ��ɹ�����ǰ��
		ok_print($type,$r['url'],$sql,$file_name,$filetype,$ptype);
	}
	else
	{
		printerror_pkkgu("InTranRecordFail","history.go(-1)");
	}
}
// ��̨ ����Զ�̱���ͼƬ
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
		$filetype=GetFiletype($tranurl);//ȡ���ļ�����
		ChckeFileType_H_pkkgu($type,$filetype,0); //��̨�ļ�������֤ pkkgu
		//����Զ��ͼƬ
		$r=DoTranUrl_pkkgu($tranurl,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail");
		}
		//д�����ݿ�
		$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,'[Զ��]',$type,$ptype,$filepass,$HQ=0);
		$tmpNames[]=$r['url'];
		Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // ˮӡ ����Сͼ
	}
	if($sql)
	{
		//�ϴ��ɹ�����ǰ��
		echo "{'url':'".implode("ue_separate_ue",$tmpNames )."','tip':'Զ��ͼƬץȡ�ɹ���','srcUrl':'".$uri."'}";
		db_close();
		$empire=null;
		exit();
	}
	else
	{
		printerror_pkkgu("InTranRecordFail","history.go(-1)");
	}
}
/********************************************************************** ǰ̨ͼƬ�͸������� **********************************************************************/
/********************************************************************** ǰ̨ͼƬ�͸������� **********************************************************************/
//----------------------------------�Ƿ��½
//ת���Ա��
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
	//Ĭ�ϻ�Ա��
	if(empty($cr[$user_group]))
	{
		$usql=$empire->query("update ".$user_tablename." set ".$user_group."='$user_groupid' where ".$user_userid."='".$cr[$user_userid]."'");
		$cr[$user_group]=$user_groupid;
	}
	//�Ƿ����
	if($cr[$user_userdate])
	{
		if($cr[$user_userdate]-time()<=0)
		{
			OutTimeZGroup_pkkgu($cr[$user_userid],$cr[$user_zgroup]); //ת���Ա�� pkkgu
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
//��֤�ύIP
function eCheckAccessDoIp_pkkgu($doing){
	global $public_r,$empire,$dbtbpre;
	$pr=$empire->fetch1("select opendoip,closedoip,doiptype from {$dbtbpre}enewspublic limit 1");
	if(!strstr($pr['doiptype'],','.$doing.','))
	{
		return '';
	}
	$userip=egetip();
	//����IP
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
	//��ֹIP
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
//�������Ƿ��㹻
function MCheckEnoughFen_pkkgu($userfen,$userdate,$fen){
	if(!($userdate-time()>0))
	{
		if($userfen+$fen<0)
		{
			printerror_pkkgu("HaveNotFenAQinfo","history.go(-1)",1);
		}
	}
}
//���Ͷ����
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
//���û�Ͷ����֤
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
//Ͷ��Ȩ�޼��
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
	//�Ƿ��½
	if($ecms==1||$ecms==2||($r['qaddgroupid']&&$r['qaddgroupid']<>','))
	{
		$user=islogin_pkkgu($userid,$username,$rnd); //�Ƿ��½ pkkgu
		//��֤�»�ԱͶ��
		if($isadd==1&&$public_r['newaddinfotime'])
		{
			qCheckNewMemberAddInfo_pkkgu($user[registertime]); //���û�Ͷ����֤ pkkgu
		}
	}
	//��Ա��
	if($r['qaddgroupid']&&$r['qaddgroupid']<>',')
	{
		if(!strstr($r['qaddgroupid'],','.$user[groupid].','))
		{
			printerror_pkkgu("HaveNotLevelAQinfo","history.go(-1)",1);
		}
	}
	if($isadd==1)
	{
		//����Ƿ��㹻����
		if($r['addinfofen']<0&&$user['userid'])
		{
			MCheckEnoughFen_pkkgu($user['userfen'],$user['userdate'],$r['addinfofen']);//�������Ƿ��㹻 pkkgu
		}
		//���Ͷ����
		if($r['qaddgroupid']&&$r['qaddgroupid']<>','&&$level_r[$user[groupid]]['dayaddinfo'])
		{
			$r['checkaddnumquery']=DoQCheckAddNum_pkkgu($user['userid'],$user['groupid']); //���Ͷ���� pkkgu
		}
	}
	//���
	if(($ecms==0||$ecms==1)&&$userid)
	{
		if(!$user[groupid])
		{
			$user=islogin_pkkgu($userid,$username,$rnd); //�Ƿ��½ pkkgu
		}
		if($level_r[$user[groupid]]['infochecked'])
		{
			$r['checkqadd']=1;
			$r['qeditchecked']=0;
		}
	}
	return $r;
}
//ǰ̨�ļ�������֤ pkkgu
function ChckeFileType_Q_pkkgu($type,$filetype,$file_size){
	global $empire,$dbtbpre,$public_r,$tranpicturetype,$tranflashtype;
	if(CheckSaveTranFiletype($filetype)) //������չ����֤
	{
		printerror_pkkgu("NotQTranFiletype","",9);
	}
	$pr=$empire->fetch1("select qaddtran,qaddtransize,qaddtranimgtype,qaddtranfile,qaddtranfilesize,qaddtranfiletype from {$dbtbpre}enewspublic limit 1");
	if($type==1)//ͼƬ
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
	else//����
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
// ǰ̨ �ϴ�����
function DoQTranFile_pkkgu($add,$file,$file_name,$file_type,$file_size,$userid,$username,$rnd,$ecms=0,$tranurl=''){
	global $empire,$dbtbpre,$public_r;
	if($public_r['addnews_ok'])//�ر�Ͷ��
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
	//��֤Ȩ��
	$userid=(int)$userid;
	$username=RepPostVar($username);
	$rnd=RepPostVar($rnd);
	DoQCheckAddLevel_pkkgu($classid,$userid,$username,$rnd,0,0); //Ͷ��Ȩ�޼�� pkkgu
	if(!$username){
		$username='�ο�';
	}
	if($ptype==3) //Ϳѻ
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
		$r[filesize]=file_put_contents($r[yname],$content); //�����ļ� �����ļ���С
		if (empty($r[filesize])) {
			printerror_pkkgu("�ϴ����ɹ�".$r[yname]);
		}
	}
	else
	{
		$filetype=GetFiletype($file_name);//ȡ���ļ�����
		ChckeFileType_Q_pkkgu($type,$filetype,$file_size); //ǰ̨�ļ����ͼ�� pkkgu
		//�����ϴ�
		$r=DoTranFile_pkkgu($file,$file_name,$file_type,$file_size,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail","",9);
		}
	}
	//д�����ݿ�
	$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,$file_name,$type,$ptype,$filepass,$HQ=1);
	Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // ˮӡ ����Сͼ
	//�༭��
	if($sql)
	{
		ok_print($type,$r['url'],$sql,$file_name,$filetype,$ptype);
	}
	else
	{
		printerror_pkkgu("EmptyQTranFile","history.go(-1)");
	}
}
// ǰ̨ ����Զ�̱���ͼƬ
function TranMoreFile_Q_pkkgu($add,$userid,$username,$rnd,$tranurl){
	global $empire,$dbtbpre,$public_r;
	if($public_r['addnews_ok'])//�ر�Ͷ��
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
	//��֤Ȩ��
	$userid=(int)$userid;
	$username=RepPostVar($username);
	$rnd=RepPostVar($rnd);
	DoQCheckAddLevel_pkkgu($classid,$userid,$username,$rnd,0,0); //Ͷ��Ȩ�޼�� pkkgu
	if(!$username){
		$username='�ο�';
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
		$filetype=GetFiletype($tranurl);//ȡ���ļ�����
		ChckeFileType_Q_pkkgu($type,$filetype,0); //ǰ̨�ļ����ͼ�� pkkgu
		//����Զ��ͼƬ
		$r=DoTranUrl_pkkgu($tranurl,$classid);
		if(empty($r[tran]))
		{
			printerror_pkkgu("TranFail","",9);
		}
		//д�����ݿ�
		$sql=File_Insert_Sql_pkkgu($add,$r,$username,$classid,'[Զ��]',$type,$ptype,$filepass,$HQ=0);
		$tmpNames[]=$r['url'];
		Getmark_Getsmall_pkkgu($classid,$type,$filename,$getsmall,$getmark,$r,$add,$userid,$username); // ˮӡ ����Сͼ
	}
	//�༭��
	if($sql)
	{
		 echo "{'url':'".implode("ue_separate_ue",$tmpNames )."','tip':'Զ��ͼƬץȡ�ɹ���','srcUrl':'".$uri."'}";
	}
	else
	{
		printerror_pkkgu("EmptyQTranFile","history.go(-1)",1);
	}
}
/********************************************************************** ���߲��� **********************************************************************/
/********************************************************************** ���߲��� **********************************************************************/
// ǰ̨��Ա��������ʾͼƬ
function Show_Image_User_pkkgu($add,$userid,$username,$rnd){
	global $empire,$dbtbpre,$public_r,$class_r;
	$user=islogin_pkkgu($userid,$username,$rnd); //�Ƿ��½ pkkgu
	$classid=(int)$add['classid'];
	$type=1;
	$add="";
	//��¼
	$user=islogin_pkkgu($userid,$username,$rnd);
	if($user[username])
	{
		$add.=" and adduser='[pkkgu_QT]".$user[username]."'";
	}
	else
	{
		printerror_pkkgu("NotCheckedUser");
	}
	//��Ŀ
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
		/*//��ǰ��Ϣ
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
		$filesize=ChTheFilesize($r[filesize]);//�ļ���С
		$filetype=GetFiletype($r[filename]);//ȡ���ļ���չ��
		//�ļ�
		$fspath=ReturnFileSavePath($r[classid],$r[fpath]);
		$filepath=$r[path]?$r[path].'/':$r[path];
		$file_url.=$file=$fspath['fileurl'].$filepath.$r[filename].'ue_separate_ue';
	}
	echo $file_url;
}
// ��̨����Ա��������ʾͼƬ
function Show_Image_admin_pkkgu($add,$userid,$username,$rnd){
	global $empire,$dbtbpre,$public_r,$class_r;
	$classid=(int)$add['classid'];
	$type=1;
	$add="";
	//��Ŀ
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
		/*//��ǰ��Ϣ
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
		$filesize=ChTheFilesize($r[filesize]);//�ļ���С
		$filetype=GetFiletype($r[filename]);//ȡ���ļ���չ��
		//�ļ�

		$fspath=ReturnFileSavePath($r[classid],$r[fpath]);
		$filepath=$r[path]?$r[path].'/':$r[path];
		$file_url.=$file=$fspath['fileurl'].$filepath.$r[filename].'ue_separate_ue';
	}
	echo $file_url;
	db_close();
	$empire=null;
}
?>