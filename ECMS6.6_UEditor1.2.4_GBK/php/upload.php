<?php
/**
 * -+------------------------------------------------------------------------+-
 * | Author : pkkgu ��QQ��910111100��
 * | Contact: http://t.qq.com/ly0752
 * | LastUpdate: 2012-12-14
 * -+------------------------------------------------------------------------+-
 */ 
require("../../../../class/connect.php");
require("../../../../class/db_sql.php");
require("../../../../class/functions.php");
require("../../../../data/dbcache/class.php");
require "../../../".LoadLang("pub/fun.php");
require("upload_ecms_fun.php");
$link=db_connect();
$empire=new mysqlquery();
$editor=3;
//�������� 1ΪGB2312 0ΪUTF-8
if($phome_db_char=='utf8'){
	$utf_gbk=0;
}else{
	$utf_gbk=1;
}
$enews=$_POST['enews'];
$ptype=$_POST['ptype'];
if(empty($enews))
{
	$enews=$_GET['enews'];
	$ptype=$_GET['ptype'];
	if($ptype==4||$ptype==5) //Ϳѻ����,���߽�ͼ
	{
		$_POST=$_GET;
		/*
		$POST=$_POST;
		$GET=$_GET;
		$_POST=array_merge($POST,$GET);
		*/
	}
}
/**
 * һ��ͼƬ�����������ϴ���Ϳѻ���������߽�ͼ��Զ�̱���ͼƬ��enws=Ue_file_user��Ue_file_admin
 * ����Ϳѻ���� ptype=4����Ļ��ͼ ptype=5 ���Ӳ���������GET��ʽ
 * �������߹���ͼƬenws=Ue_show_user��Ue_show_admin
 * �ġ���Ļ��ͼ��Ϳѻ��Ϳѻ�����ϴ�֧��ֱ�ӻ�ȡcookies
 */
if($enews=="Ue_file_user"){ //ǰ̨
	require("../../../../class/delpath.php");
	require("../../../../class/t_functions.php");
	require("../../../../data/dbcache/MemberLevel.php");
	require("../../../../class/user.php");
	eCheckAccessDoIp_pkkgu('postinfo'); //��֤�ύIP pkkgu
	$muserid   = (int)$_POST['mluserid'];
	$musername = RepPostVar($_POST['mlusername']);
	$musername = doUtfAndGbk_pkkgu($utf_gbk,0,$musername);
	$mrnd      = RepPostVar($_POST['mlrnd']);
	if(!$muserid||!$musername||!$mrnd)
	{
		$muserid   = (int)getcvar('mluserid');           // �û�ID  
		$musername = RepPostVar(getcvar('mlusername'));  // �û���  
		$mlrnd     = RepPostVar(getcvar('mlrnd'));       // ��֤����� 
	}
	$file      = $_FILES['upfile']['tmp_name'];
    $file_name = $_FILES['upfile']['name'];
    $file_type = $_FILES['upfile']['type'];
    $file_size = $_FILES['upfile']['size'];
	if($ptype==1)
	{
		$tranurl   = RepPostStr($_POST['tranurl']);
		TranMoreFile_Q_pkkgu($_POST,$muserid,$musername,$mrnd,$tranurl);
	}else{
		DoQTranFile_pkkgu($_POST,$file,$file_name,$file_type,$file_size,$muserid,$musername,$mrnd,0,$tranurl);
	}
}
else if($enews=="Ue_file_admin"){ //��̨
	//��֤�û�
	$muserid   = (int)$_POST['mluserid'];
	$musername = RepPostVar($_POST['mlusername']);
	$musername = doUtfAndGbk_pkkgu($utf_gbk,0,$musername);
	$mrnd      = RepPostVar($_POST['mlrnd']);
	$mladmin   = RepPostVar($_POST['mladmin']);
	$lur       = is_login_pkkgu($muserid,$musername,$mrnd,$mladmin);
	$userid    = $lur['userid'];
	$username  = $lur['username'];
	$rnd       = $lur['rnd'];
	$file      = $_FILES['upfile']['tmp_name'];
	$file_name = $_FILES['upfile']['name'];
	$file_type = $_FILES['upfile']['type'];
	$file_size = $_FILES['upfile']['size'];
	if($public_r['phpmode']){
		include("../../class/ftp.php");
		$incftp=1;
	}
	if($ptype==1)
	{
		$tranurl = RepPostStr($_POST['tranurl']);
		TranMoreFile_H_pkkgu($_POST,$userid,$username,$rnd,$tranurl);
	}else{
		TranFile_pkkgu($_POST,$file,$file_name,$file_type,$file_size,$userid,$username,$rnd,$tranurl);
	}
}
else if($enews=="Ue_show_user"){ //������ʾͼƬ ǰ̨
	require("../../../../class/user.php");
	eCheckAccessDoIp_pkkgu('postinfo'); //��֤�ύIP pkkgu
	$muserid   = (int)$_POST['mluserid'];
	$musername = RepPostVar($_POST['mlusername']);
	$musername = doUtfAndGbk_pkkgu($utf_gbk,0,$musername);
	$mrnd      = RepPostVar($_POST['mlrnd']);
	Show_Image_User_pkkgu($_POST,$muserid,$musername,$mrnd);
}
else if($enews=="Ue_show_admin"){ //������ʾͼƬ ��̨
	//��֤�û�
	$muserid   = (int)$_POST['mluserid'];
	$musername = RepPostVar($_POST['mlusername']);
	$musername = doUtfAndGbk_pkkgu($utf_gbk,0,$musername);
	$mrnd      = RepPostVar($_POST['mlrnd']);
	$mladmin   = RepPostVar($_POST['mladmin']);
	$lur       = is_login_pkkgu($muserid,$musername,$mrnd,$mladmin);
	$userid    = $lur['userid'];
	$username  = $lur['username'];
	$rnd       = $lur['rnd'];
	Show_Image_Admin_pkkgu($_POST,$userid,$username,$rnd);

}
else{printerror_pkkgu("ErrorUrl","",1);}
db_close();
$empire=null;
?>