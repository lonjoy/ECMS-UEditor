<?php
/*
 * -+------------------------------------------------------------------------+-
 * | Author : pkkgu ��QQ��910111100��
 * | Contact: http://t.qq.com/ly0752
 * | LastUpdate: 2012-12-14
 * -+------------------------------------------------------------------------+-
 * �۹�CMS6.6�������UEditor1.2.4 GBK+UTF-8
 * ͼƬ�����������ϴ���Ϳѻ������WORDͼƬ��Զ�̱���ͼƬ����Ļ��ͼ
 * ͼƬ,type=1��FLASH,type=2����ý��,type=3������ֵΪ����
 * ͼƬ,ptype=0��Զ�̱���ͼƬ��ptype=1������WORDͼƬ��ptype=2��Ϳѻ��ptype=3��Ϳѻ������ptype=4����Ļ��ͼ��ptype=5
 */
$field    = $field?$field:'newstext';                         // �ֶ��� Ĭ�ϵ۹������ֶ�newstext
$classid  = $ue_classid?$ue_classid:$classid;                 // ��ĿID
$filepass = $filepass?$filepass:time();                       // ��ʱ����ID����ϢID
$ueurl    = $ueurl?$ueurl:$public_r['newsurl']."e/data/ecmseditor/ueditor/";// UE�༭����ַ
$up_php   = $up_php?$up_php:$ueurl."php/upload.php";          // �ϴ���������ַ ǰ����û���¼��֤��֧�� POST��cookies���ַ�ʽ
$getmark  = $getmark?$getmark:0;                              // 1��ˮӡ 0��ˮӡ
$getsmall = $getsmall?$getsmall:0;                            // 1������ͼ 0������ͼ ͬʱҪ���ÿ�͸�,���ʱ����ǰ��[s]
$picwidth = $width?$width:100;                                // ��
$picheight= $height?$height:100;                              // ��
$toolbars = $toolbars?',toolbars:[['.$toolbars.']]':'';       // ������Ŀ
$ue_width = $ue_width?$ue_width:'true';
$ue_height= $ue_height?$ue_width:'true';
$ue_jquery= $ue_jquery?$ue_jquery:0; 

if($enews=="MAddInfo"||$enews=="MEditInfo") // ǰ̨
{
	$muserid        = (int)getcvar('mluserid');           // �û�ID  
	$musername      = RepPostVar(getcvar('mlusername'));  // �û���  
	$mlrnd          = RepPostVar(getcvar('mlrnd'));       // ��֤����� 
	$file_img_enews = 'Ue_file_user';       //������ͼƬ��Ϳѻ�����ϴ�enews����
	$awl_enews      = 'Ue_srcawl_user';     //Ϳѻenews����
	$show_img_enews = 'Ue_show_user';       //���߹���ͼƬenws����
	$filer          = $empire->fetch1("select qaddtransize,qaddtranimgtype,qaddtranfilesize,qaddtranfiletype from {$dbtbpre}enewspublic limit 1");
	//ǰ̨ͼƬ
	$img_size       = $filer[qaddtransize]/1024;                     //qaddtransize      ǰ̨Ͷ���ϴ����ͼƬ��С KB
	$img_type       = str_replace("|",";*",$filer[qaddtranimgtype]); //qaddtranimgtype  	ǰ̨Ͷ���ϴ�ͼƬ��չ������
	$img_type       = substr($img_type,1,strlen($img_type)-3);
	//ǰ̨����
	$file_size      = $filer[qaddtranfilesize]/1024;                 //qaddtranfilesize  ǰ̨Ͷ����󸽼���С KB
	$file_type      = str_replace("|",";*",$filer[qaddtranfiletype]);//qaddtranfiletype  ǰ̨Ͷ���ϴ�������չ������
	$file_type      = substr($file_type,1,strlen($file_type)-3);
	/******************** ��������ǰ̨��֧�� ********************/
	$mladmin        = '';
}
else if($enews=="AddNews"||$enews=="EditNews")  //��̨̨
{
	//����û���Ϣ��
	$muserid            = (int)getcvar('loginuserid',1);              //�û�ID  
	$musername          = RepPostVar(getcvar('loginusername',1));     //�û���  
	$mlrnd              = RepPostVar(getcvar('loginrnd',1));          //��֤�����  
	$ecmsdodbdata       = RepPostVar(getcvar('ecmsdodbdata',1));  
	$eloginlic          = RepPostVar(getcvar('eloginlic',1));         //�û����֤������  
	$loginadminstyleid  = (int)getcvar('loginadminstyleid',1);        //���ID  
	$loginecmsckpass    = RepPostVar(getcvar('loginecmsckpass',1));   //�������  
	$loginlevel         = (int)getcvar('loginlevel',1);               //��ID  
	$logintime          = RepPostVar(getcvar('logintime',1));         //��½ʱ��UNIXʱ���
	$truelogintime      = (int)getcvar('truelogintime',1);
	$l="|"; 
	$mladmin          = $muserid.$l.$musername.$l.$mlrnd.$l.$loginlevel.$l.$loginadminstyleid.$l.$truelogintime.$l.$ecmsdodbdata.$l.$logintime.$l.$eloginlic.$l.$loginecmsckpass;
	$mladmin          = ',"mladmin":"'.$mladmin.'"';
	$file_img_enews   = 'Ue_file_admin';               //������ͼƬ��Ϳѻ�����ϴ�enews����
	$awl_enews        = 'Ue_srcawl_admin';             //Ϳѻenews����
	$show_img_enews   = 'Ue_show_admin';               //���߹���ͼƬenws����
	//$filer            = $empire->fetch1("select filesize,filetype from {$dbtbpre}enewspublic limit 1");
	//��̨ͼƬ
	$img_size         = $public_r['filesize']/1024;
	$img_type         = '*.gif;*.jpeg;*.png;*.jpg;*.bmp';
	//��̨����
	$file_size        = $img_size;
	$file_type        = str_replace("|",";*",$public_r['filetype']);
	$file_type        = substr($file_type,1,strlen($file_type)-3);
}else{echo "enews���������д���";}
//POST ��cookies
$file_ext   = '"classid":"'.$classid.'","filepass":"'.$filepass.'","getmark":"'.$getmark.'","mluserid":"'.$muserid.'","mlusername":"'.$musername.'","mlrnd":"'.$mlrnd.'"'.$mladmin;
//POST ����cookies
$file_ext_1 = '"classid":"'.$classid.'","filepass":"'.$filepass.'","getmark":"'.$getmark.'"';
//GET ����cookies
$file_ext_2 = '&classid='.$classid.'&filepass='.$filepass.'&getmark='.$getmark;
//����Сͼ
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
<!-- ѹ���� -->
<script type="text/javascript" charset="utf-8" src="<?=$ueurl?>editor_all_min.js"></script>

<script type="text/plain" id="pkkgu_<?=$field?>" name="<?=$field?>">
<?=$ecmsfirstpost==1?"":stripSlashes($r[$field])?>
</script>
<script type="text/javascript">
	var up_php = "<?=$up_php?>";
	var editorOption = {
		imageUrl:up_php                //ͼƬ�ϴ��ύ��ַ
		,imagePath:""
		//Ϳѻ�����ϴ���ַ(&ptype=4��Ϳѻ������GET���͸��Ӳ���,$a=1���ڴ���UEϵͳ������ӵ�?action=tmpImg)
		,scrawlUrl:up_php+"?type=1&enews=<?=$file_img_enews?><?=$file_ext_2?>&ptype=4&a=1"
		,scrawlPath:""
		,fileUrl:up_php                //�����ϴ��ύ��ַ
		,filePath:""
		,catcherUrl:up_php             //����Զ��ͼƬץȡ�ĵ�ַ
		,catcherPath:""
		,catchFieldName:"tranurl"      //Զ�̱���ͼƬKEY
		,imageManagerUrl:up_php        //ͼƬ���߹���Ĵ����ַ
		,imageManagerPath:""
		,snapscreenHost:"<?=$_SERVER['SERVER_NAME']?>"   //��Ļ��ͼ��server���ļ����ڵ���վ��ַ����ip���벻Ҫ��http://
		//��Ļ��ͼ��server�˱������
		,snapscreenServerUrl:up_php+"?type=1&enews=<?=$file_img_enews?><?=$file_ext_2?>&ptype=5"
		,snapscreenPath:""
		,wordImageUrl:up_php           //wordת���ύ��ַ
		,wordImagePath:""
		,initialContent:''             //�༭����ʼ�����
		//,toolbars:[['FullScreen', 'Source', 'Undo', 'Redo','Bold']]
		//,toolbars:[["bold","italic"],["undo","redo"],["insertimage"]]
		,pageBreakTag:'[!--empirenews.page--]' //��ҳ
		<?=$toolbars?>
		,initialFrameWidth:<?=$ue_width?>
		,initialFrameHeight:<?=$ue_height?>
	};
	var editor = new UE.ui.Editor(editorOption);
	$(document).ready(function(){
		editor.render('pkkgu_<?=$field?>'); 
		//$type 1�ϴ�ͼƬ 2�ϴ�flash 3�ϴ���ý�� 5�ϴ�Ϳѻ ����ֵΪ����
		editor.img_ext     = '{"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext?>,"ptype":"0"}'; //ͼƬ���Ӳ���(ע�ⵥ����)
		editor.img_size    = '<?=$img_size?>';                                                          //ͼƬ������С
		editor.img_type    = '<?=$img_type?>';                                                          //ͼƬ����
	
		editor.img_url_ext = {"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext_1?>,"ptype":"1"}; //Զ�̱���ͼƬ���Ӳ���
		editor.show_img_ext= {"type":"0","enews":"<?=$show_img_enews?>",<?=$file_ext_1?>};               //���߹���ͼƬ���Ӳ���enews���� show_userǰ̨ show_admin��
		editor.scrawl_data = {"type":"1","enews":"<?=$file_img_enews?>",<?=$file_ext_1?>,"ptype":"3"}; //ͿѻPOST���Ӳ���
		
		editor.file_ext    = {"type":"0","enews":"<?=$file_img_enews?>",<?=$file_ext?>};               //�������Ӳ���
		editor.file_size   = '<?=$file_size?>';                                                         //������С
		editor.file_type   = '<?=$file_type?>';                                                         //��������
   })
</script>
