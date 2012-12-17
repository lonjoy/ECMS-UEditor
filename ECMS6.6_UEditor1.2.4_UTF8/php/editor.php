<?php
/*
 * -+------------------------------------------------------------------------+-
 * | Author : pkkgu （QQ：910111100）
 * | Contact: http://t.qq.com/ly0752
 * | LastUpdate: 2012-12-14
 * -+------------------------------------------------------------------------+-
 * 帝国CMS6.6深度整合UEditor1.2.4 GBK+UTF-8
 * 图片、附件批量上传、涂鸦、插入WORD图片、远程保存图片、屏幕截图
 * 图片,type=1、FLASH,type=2、多媒体,type=3、其它值为附件
 * 图片,ptype=0、远程保存图片，ptype=1、插入WORD图片，ptype=2、涂鸦，ptype=3、涂鸦背景，ptype=4、屏幕截图，ptype=5
 */
$field    = $field?$field:'newstext';                         // 字段名 默认帝国新闻字段newstext
$classid  = $ue_classid?$ue_classid:$classid;                 // 栏目ID
$filepass = $filepass?$filepass:time();                       // 临时附件ID或信息ID
$ueurl    = $ueurl?$ueurl:$public_r['newsurl']."e/data/ecmseditor/ueditor/";// UE编辑器地址
$up_php   = $up_php?$up_php:$ueurl."php/upload.php";          // 上传处理程序地址 前后端用户登录验证已支持 POST和cookies两种方式
$getmark  = $getmark?$getmark:0;                              // 1加水印 0不水印
$getsmall = $getsmall?$getsmall:0;                            // 1有缩略图 0无缩略图 同时要设置宽和高,入库时名称前加[s]
$picwidth = $width?$width:100;                                // 宽
$picheight= $height?$height:100;                              // 高
$toolbars = $toolbars?',toolbars:[['.$toolbars.']]':'';       // 工具栏目
$ue_width = $ue_width?$ue_width:'true';
$ue_height= $ue_height?$ue_width:'true';
$ue_jquery= $ue_jquery?$ue_jquery:0; 

if($enews=="MAddInfo"||$enews=="MEditInfo") // 前台
{
	$muserid        = (int)getcvar('mluserid');           // 用户ID  
	$musername      = RepPostVar(getcvar('mlusername'));  // 用户名  
	$mlrnd          = RepPostVar(getcvar('mlrnd'));       // 认证码加密 
	$file_img_enews = 'Ue_file_user';       //附件、图片和涂鸦背景上传enews变量
	$awl_enews      = 'Ue_srcawl_user';     //涂鸦enews变量
	$show_img_enews = 'Ue_show_user';       //在线管理图片enws变量
	$filer          = $empire->fetch1("select qaddtransize,qaddtranimgtype,qaddtranfilesize,qaddtranfiletype from {$dbtbpre}enewspublic limit 1");
	//前台图片
	$img_size       = $filer[qaddtransize]/1024;                     //qaddtransize      前台投稿上传最大图片大小 KB
	$img_type       = str_replace("|",";*",$filer[qaddtranimgtype]); //qaddtranimgtype  	前台投稿上传图片扩展名限制
	$img_type       = substr($img_type,1,strlen($img_type)-3);
	//前台附件
	$file_size      = $filer[qaddtranfilesize]/1024;                 //qaddtranfilesize  前台投稿最大附件大小 KB
	$file_type      = str_replace("|",";*",$filer[qaddtranfiletype]);//qaddtranfiletype  前台投稿上传附件扩展名限制
	$file_type      = substr($file_type,1,strlen($file_type)-3);
	/******************** 以下设置前台不支持 ********************/
	$mladmin        = '';
}
else if($enews=="AddNews"||$enews=="EditNews")  //后台台
{
	//后端用户信息串
	$muserid            = (int)getcvar('loginuserid',1);              //用户ID  
	$musername          = RepPostVar(getcvar('loginusername',1));     //用户名  
	$mlrnd              = RepPostVar(getcvar('loginrnd',1));          //认证码加密  
	$ecmsdodbdata       = RepPostVar(getcvar('ecmsdodbdata',1));  
	$eloginlic          = RepPostVar(getcvar('eloginlic',1));         //用户许可证书名称  
	$loginadminstyleid  = (int)getcvar('loginadminstyleid',1);        //风格ID  
	$loginecmsckpass    = RepPostVar(getcvar('loginecmsckpass',1));   //密码加密  
	$loginlevel         = (int)getcvar('loginlevel',1);               //组ID  
	$logintime          = RepPostVar(getcvar('logintime',1));         //登陆时间UNIX时间戳
	$truelogintime      = (int)getcvar('truelogintime',1);
	$l="|"; 
	$mladmin          = $muserid.$l.$musername.$l.$mlrnd.$l.$loginlevel.$l.$loginadminstyleid.$l.$truelogintime.$l.$ecmsdodbdata.$l.$logintime.$l.$eloginlic.$l.$loginecmsckpass;
	$mladmin          = ',"mladmin":"'.$mladmin.'"';
	$file_img_enews   = 'Ue_file_admin';               //附件、图片和涂鸦背景上传enews变量
	$awl_enews        = 'Ue_srcawl_admin';             //涂鸦enews变量
	$show_img_enews   = 'Ue_show_admin';               //在线管理图片enws变量
	//$filer            = $empire->fetch1("select filesize,filetype from {$dbtbpre}enewspublic limit 1");
	//后台图片
	$img_size         = $public_r['filesize']/1024;
	$img_type         = '*.gif;*.jpeg;*.png;*.jpg;*.bmp';
	//后台附件
	$file_size        = $img_size;
	$file_type        = str_replace("|",";*",$public_r['filetype']);
	$file_type        = substr($file_type,1,strlen($file_type)-3);
}else{echo "enews参数设置有错误！";}
//POST 带cookies
$file_ext   = '"classid":"'.$classid.'","filepass":"'.$filepass.'","getmark":"'.$getmark.'","mluserid":"'.$muserid.'","mlusername":"'.$musername.'","mlrnd":"'.$mlrnd.'"'.$mladmin;
//POST 不带cookies
$file_ext_1 = '"classid":"'.$classid.'","filepass":"'.$filepass.'","getmark":"'.$getmark.'"';
//GET 不带cookies
$file_ext_2 = '&classid='.$classid.'&filepass='.$filepass.'&getmark='.$getmark;
//生成小图
if($file_img)
{
	$pic_small_ext_0 = ',"getsmall":"'.$getsmall.'","width":"'.$picwidth.'","height":"'.$picheight.'"';
	$file_ext   = $file_ext.$pic_small_ext_0;
	$file_ext_1 = $file_ext_1.$pic_small_ext_0;
	$file_ext_2 = $file_ext_2.'&getsmall='.$getsmall.'&width='.$picwidth.'&height='.$picheight;
}
if(empty($ue_jquery))
{
	echo '<script src="'.$ueurl.'php/jquery-1.8.3.min.js" charset=""></script>';
}

?>
<script type="text/javascript" charset="utf-8">
	window.UEDITOR_HOME_URL = "<?=$ueurl?>";
</script>
<script type="text/javascript" charset="utf-8" src="<?=$ueurl?>editor_config.js"></script>
<!-- <script type="text/javascript" charset="utf-8" src="<?=$ueurl?>editor_all.js"></script> -->
<!-- 压缩版 -->
<script type="text/javascript" charset="utf-8" src="<?=$ueurl?>editor_all_min.js"></script>

<script type="text/plain" id="pkkgu_<?=$field?>" name="<?=$field?>">
<?=$ecmsfirstpost==1?"":stripSlashes($r[$field])?>
</script>
<script type="text/javascript">
	var up_php = "<?=$up_php?>";
	var editorOption = {
		imageUrl:up_php                //图片上传提交地址
		,imagePath:""
		//涂鸦背景上传地址(&ptype=4，涂鸦背景以GET传送附加参数,$a=1用于处理UE系统最后增加的?action=tmpImg)
		,scrawlUrl:up_php+"?type=1&enews=<?=$file_img_enews?><?=$file_ext_2?>&ptype=4&a=1"
		,scrawlPath:""
		,fileUrl:up_php                //附件上传提交地址
		,filePath:""
		,catcherUrl:up_php             //处理远程图片抓取的地址
		,catcherPath:""
		,catchFieldName:"tranurl"      //远程保存图片KEY
		,imageManagerUrl:up_php        //图片在线管理的处理地址
		,imageManagerPath:""
		,snapscreenHost:"<?=$_SERVER['SERVER_NAME']?>"   //屏幕截图的server端文件所在的网站地址或者ip，请不要加http://
		//屏幕截图的server端保存程序
		,snapscreenServerUrl:up_php+"?type=1&enews=<?=$file_img_enews?><?=$file_ext_2?>&ptype=5"
		,snapscreenPath:""
		,wordImageUrl:up_php           //word转存提交地址
		,wordImagePath:""
		,initialContent:''             //编辑器初始化清空
		//,toolbars:[['FullScreen', 'Source', 'Undo', 'Redo','Bold']]
		//,toolbars:[["bold","italic"],["undo","redo"],["insertimage"]]
		,pageBreakTag:'[!--empirenews.page--]' //分页
		<?=$toolbars?>
		,initialFrameWidth:<?=$ue_width?>
		,initialFrameHeight:<?=$ue_height?>
	};
	var editor = new UE.ui.Editor(editorOption);
	$(document).ready(function(){
		editor.render('pkkgu_<?=$field?>'); 
		//$type 1上传图片 2上传flash 3上传多媒体 5上传涂鸦 其它值为附件
		editor.img_ext     = '{"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext?>,"ptype":"0"}'; //图片附加参数(注意单引号)
		editor.img_size    = '<?=$img_size?>';                                                          //图片附件大小
		editor.img_type    = '<?=$img_type?>';                                                          //图片类型
	
		editor.img_url_ext = {"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext_1?>,"ptype":"1"}; //远程保存图片附加参数
		editor.show_img_ext= {"type":"0","enews":"<?=$show_img_enews?>",<?=$file_ext_1?>};               //在线管理图片附加参数enews变量 show_user前台 show_admin后
		editor.scrawl_data = {"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext_1?>,"ptype":"3"}; //涂鸦POST附加参数
		
		editor.file_ext    = {"type":"0","enews":"<?=$file_img_enews?>",<?=$file_ext?>};               //附件附加参数
		editor.file_size   = '<?=$file_size?>';                                                         //附件大小
		editor.file_type   = '<?=$file_type?>';                                                         //附件类型
   })
</script>
