ECMS-UEditor
============

帝国CMS整合百度编辑器UEditor
UEditor Version 1.2.4
EmpireCMS Version v6.6


UEditor1.2.4整合帝国ECMS6.6以插件形式替换帝国ECMS中的默认编辑器。

1.带前台和后台图片、附件批量上传、涂鸦、插入WORD图片、远程保存图片、屏幕截图。

2.PHP上传处理部自动区分后台管理员，前台会员

3.上传权限控制附件和图片类型、大小等整合了帝国ECMS系统配置

4.图片、附件和涂鸦存等文件放目录已整合帝国ECMS

5.使用方便，只需要修改字段HTML即可，无需要修改其他

6.兼容性处理、解决某些浏览器dialogs可能错位问题(这问题是帝国程序问题)"/e/admin/AddNews.php"，第333行“<html>” 改为
  
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">


//================================使用方法：================================================

使用方法：
一、系统——管理数据表——管理字段——修改newstext字段——“输入表单”和“投稿表单”html代码，换成下面代码

    <?php include(ECMS_PATH.'/e/data/ecmseditor/ueditor/php/editor.php');?>
    
二、将UEditor文件夹放到\e\data\ecmseditor\目录下

三、就是这么简单。***初级用用户到此，帝国ECMS6.6+UEditor1.2.4就已经整合完成！


//================================高级应用=================================================


<?php

//自定义编辑器字段（不设置默认帝国新闻字段newstext）例如：简介字段加上编辑器

$field      = 'smalltext';

// 1加水印 0不水印 (使用水印功能，要很先设置“系统参数设置——图片设置”相关参数)

$getmark    = 1;

//附件存入指定栏目

$ue_classid = 1;

//编辑器工具栏设置

$toolbars   = "'FullScreen','Source','Undo','Redo','Bold'";

//编辑器宽度 值true、false和数值

$ue_width   = '1000';

//编辑器高度 值true、false和数值

$ue_height  = '320';

//不设默认引用JQurey-1.8.3，值为1不引用

$ue_jquery=1;

include(ECMS_PATH.'/e/data/ecmseditor/ueditor/php/editor.php');

?>

//==========UEditor 修改记录 整合系统可以忽略以下内容，升级编辑器是可以参考 ===============

下面为增加的文件(GBK和UTF-8代码完全一样，仅页面编码不同，自动识别UTF-8编码，自动转换编码)

php/editor.php           实例化页面

php/upload.php           提交处理分类

php/upload_ecms_fun.php  帝国ECMS6.6处理附件部分

jquery-1.8.3.min.js      JQuery本地化(需要1.8.3版本，以下报错)

下面的文件建议删除

php/imageUp.php

php/scrawlUp.php

php/fileUp.php

php/getRemoteImage.php

php/imageManager.php

php/getContent.php

------------------------------------------------------------------------------------------

一、图片和 WORD转存增加三项：附加参数，图片类型，图片大小

dialogs/image/image.html

dialogs/imagworde/image.html

ext:'{"param1":"value1", "param2":"value2"}',

改为

ext:editor.img_ext,

*.gif;*.jpeg;*.png;*.jpg

改为

'+editor.img_type+'

maxSize:4,

改为

maxSize:editor.img_size,


------------------------------------------------------------------------------------------
二、在线浏览图片和添加背景图片时POST增加附加参数 

dialogs/image/image.js

dialogs/background/background.js

action:"get",

改为

data:editor.show_img_ext,

------------------------------------------------------------------------------------------

三、附件增加三项：附加参数，附件类型，附件大小 

dialogs/attachment/attachment.html

post_params:{"PHPSESSID":"<?php echo session_id(); ?>"},

改为

post_params:editor.file_ext,

file_types:"*.*",

改
file_types:editor.file_type,

file_size_limit:"100 MB",

改为

file_size_limit:editor.file_size+" MB", 

------------------------------------------------------------------------------------------

四、涂鸦 dialogs/scrawl/scrawl.js （POST涂鸦数据时加附加参数）涂鸦可以取得cookies

content:base64,

增加一行

data:editor.scrawl_data,

------------------------------------------------------------------------------------------
五、配置文件 editor_all.js和editor_all_min.js （远程保存图片的附加参数）

onerror:callbacks["error"]

增加

,data:editor.img_url_ext



2012.12.12

1、更新GB2312编码printerror_pkkgu函数反回中文乱码问题

2、改为基于JQuery下使用（为了解决整合帝国在IE6下错误：Internet Explorer cannot open the Internet site）

2012.12.13

1、修复GB2312下中文用户名称验证不成功的问题！

2012.12.14

1、优化化后端处理代码（涂鸦和入库等等）

2、upload.php增加utf-8和gbk编码设置，值为1时当前编码为GB2312 值0时当前编码为UTF-8

3、增加$toolbars参数，UEditor工具栏目设置 例如：'FullScreen', 'Source', 'Undo', 'Redo','Bold'

4、UE编辑器宽度高度设置$ue_width、$ue_height值为true、false和数值

2013.12.15

1、自动识别域名级别,不要需要单独配置

2、修复远程保存图片BUG

3、修复编辑器图片背景一处错误

4、增加是否引用JQuery设置

2013.12.17

1、修复使用图片水印会出现错误
