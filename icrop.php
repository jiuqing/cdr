<?php
define('REQUEST_TIME', $_SERVER['REQUEST_TIME']);
define('DS', DIRECTORY_SEPARATOR);
define('IS_TEST', flase);
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . DS);
define('LIB_PATH', BASE_PATH . 'include' . DS . 'lib' . DS);
define('UPLOAD_PATH',BASE_PATH . 'data' . DS . 'attachment' . DS);
define('DATA_PATH', BASE_PATH . 'data' . DS);

define('_DBHOST_', '127.0.0.1');
define('_DBUSER_', 'root');
define('_DBPSWD_', 'aliyun_mysql_pwd-JQ');
define('_DBNAME_', 'cdr_v1');

$type = 'default';
/**
 * [$type description]
 * @var [type]
 */
if (isset($_GET['crop_type'])) {
	$type = $_GET['crop_type'];
}


define('CROP_TYPE', $type);
session_start();
$_SESSION['crop_type'] = $type;

?>

<!DOCTYPE html>
<html lang="zh-CN">

	<head>
		<meta charset=utf-8 />
		<meta name="renderer" content="webkit" />
		<meta http-equiv="X-UA-Compatible" content="IE=8,9,10">
		<link href="shearphoto_common/css/ShearPhoto.css" rel="stylesheet" type="text/css" media="all">
		<script src="http://apps.bdimg.com/libs/jquery/1.6.4/jquery.min.js"></script>
		<script type="text/javascript" src="shearphoto_common/js/ShearPhoto.js"></script>
		
		<script type="text/javascript" src="shearphoto_common/js/webcam_ShearPhoto.js"></script>
		<!--在线拍照那个FLASH的接口，非技术性文件-->
		<script type="text/javascript" src="shearphoto_common/js/alloyimage.js"></script>
		<!--图片特效处理,他只负责特效，其他功能与这个文件完全无关，这个JS从腾讯AI文件  如你不要特效的话，顺带删除这个文件，在hendle.js设置关闭特效-->
		<script type="text/javascript" src="shearphoto_common/js/handle.js"></script>
		<!--设置和处理对象方法的JS文件，要修改设置，请进入这个文件-->
	</head>

	<body>
		<!--头部结束-->
		<div id="shearphoto_loading">程序加载中......</div>
		
		<div id="shearphoto_main">
			<!--效果开始.............如果你不要特效，可以直接删了........-->
			<div class="Effects" id="shearphoto_Effects" autocomplete="off">
				<strong class="EffectsStrong">截图效果</strong>
				<a href="javascript:;" StrEvent="原图" class="Aclick"><img src="shearphoto_common/images/Effects/e0.jpg" />原图</a>
				<a href="javascript:;" StrEvent="美肤"><img src="shearphoto_common/images/Effects/e1.jpg" />美肤效果</a>
				<a href="javascript:;" StrEvent="素描"><img src="shearphoto_common/images/Effects/e2.jpg" />素描效果</a>
				<a href="javascript:;" StrEvent="自然增强"><img src="shearphoto_common/images/Effects/e3.jpg" />自然增强</a>
				<a href="javascript:;" StrEvent="紫调"><img src="shearphoto_common/images/Effects/e4.jpg" />紫调效果</a>
				<a href="javascript:;" StrEvent="柔焦"><img src="shearphoto_common/images/Effects/e5.jpg" />柔焦效果</a>
				<a href="javascript:;" StrEvent="复古"><img src="shearphoto_common/images/Effects/e6.jpg" />复古效果</a>
				<a href="javascript:;" StrEvent="黑白"><img src="shearphoto_common/images/Effects/e7.jpg" />黑白效果</a>
				<a href="javascript:;" StrEvent="仿lomo"><img src="shearphoto_common/images/Effects/e8.jpg" />仿lomo</a>
				<a href="javascript:;" StrEvent="亮白增强"><img src="shearphoto_common/images/Effects/e9.jpg" />亮白增强</a>
				<a href="javascript:;" StrEvent="灰白"><img src="shearphoto_common/images/Effects/e10.jpg" />灰白效果</a>
				<a href="javascript:;" StrEvent="灰色"><img src="shearphoto_common/images/Effects/e11.jpg" />灰色效果</a>
				<a href="javascript:;" StrEvent="暖秋"><img src="shearphoto_common/images/Effects/e12.jpg" />暖秋效果</a>
				<a href="javascript:;" StrEvent="木雕"><img src="shearphoto_common/images/Effects/e13.jpg" />木雕效果</a>
				<a href="javascript:;" StrEvent="粗糙"><img src="shearphoto_common/images/Effects/e14.jpg" />粗糙效果</a>
			</div>
			<!--primary范围开始-->
			<div class="primary">
				<!--main范围开始-->
				<div id="main">
					<div class="point">
					</div>
					<!--选择加载图片方式开始-->
					<div id="SelectBox">
						<form id="ShearPhotoForm" enctype="multipart/form-data" method="post" target="POSTiframe">
							<input name="shearphoto" type="hidden" value="我要传参数" autocomplete="off">
							
							<a href="javascript:;" id="selectImage">
								<input type="file" name="UpFile" autocomplete="off" />
							</a>
						</form>
						<a href="javascript:;" id="PhotoLoading"></a>
						<!-- 如果是用户头像则允许调用摄像头 -->
						<?php if (CROP_TYPE == 'user_header') { ?>
						<a href="javascript:;" id="camerasImage"></a>
						<?php } ?>
					</div>
					<!--选择加载图片方式结束 -->
					<div id="relat">
						<div id="black">
						</div>
						<div id="movebox">
							<div id="smallbox">
								<img src="shearphoto_common/images/default.gif" class="MoveImg" />
								
							</div>
							<!--动态边框开始-->
							<i id="borderTop">
                                </i>

							<i id="borderLeft">
                                </i>

							<i id="borderRight">
                                </i>

							<i id="borderBottom">
                                </i>
							<!--动态边框结束-->
							<i id="BottomRight">
                                </i>
							<i id="TopRight">
                                </i>
							<i id="Bottomleft">
                                </i>
							<i id="Topleft">
                                </i>
							<i id="Topmiddle">
                                </i>
							<i id="leftmiddle">
                                </i>
							<i id="Rightmiddle">
                                </i>
							<i id="Bottommiddle">
                                </i>
						</div>
						<img src="shearphoto_common/images/default.gif" class="BigImg" />
						<!--MAIN上的大图-->
					</div>
				</div>
				<!--main范围结束-->
				<div style="clear: both"></div>
				<!--工具条开始-->
				<div id="Shearbar">
					<a id="LeftRotate" href="javascript:;">
						<em>
                        </em> 向左旋转
					</a>
					<em class="hint L">
                </em>
					<div class="ZoomDist" id="ZoomDist">
						<div id="Zoomcentre">
						</div>
						<div id="ZoomBar">
						</div>
						<span class="progress">
                        </span>
					</div>
					<em class="hint R">
                </em>
					<a id="RightRotate" href="javascript:;">
                        向右旋转
                        <em>
                        </em>
                </a>
					<p class="Psava">
						<a id="againIMG" href="javascript:;">重新选择</a>
						<a id="saveShear" href="javascript:;">保存截图</a>
					</p>
				</div>
				<!--工具条结束-->
			</div>
			<!--primary范围结束-->
			<div style="clear: both"></div>
		</div>
		<!--shearphoto_main范围结束-->

		<!--相册-->
		<div id="photoalbum">
			<h1>相册</h1>
			<i id="close"></i>
			<ul>
				<li><img src="shearphoto_common/file/photo/1.jpg" serveUrl="file/photo/1.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/2.jpg" serveUrl="file/photo/2.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/3.jpg" serveUrl="file/photo/3.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/4.jpg" serveUrl="file/photo/4.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/5.jpg" serveUrl="file/photo/5.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/6.jpg" serveUrl="file/photo/6.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/7.jpg" serveUrl="file/photo/7.jpg" /></li>
				
				<li><img src="shearphoto_common/file/photo/8.jpg" serveUrl="file/photo/8.jpg" /></li>
				
			</ul>
		</div>
		<!--相册-->

		<?php if (CROP_TYPE == 'user_header') {?>
			<!--拍照-->
			<div id="CamBox">
				<p class="lens"></p>
				<div id="CamFlash"></div>
				<p class="cambar">
					<a href="javascript:;" id="CamOk">拍照</a>
					<a href="javascript:;" id="setCam">设置</a>
					<a href="javascript:;" id="camClose">关闭</a>
					<span style="clear:both;"></span>
				</p>
				<div id="timing"></div>
			</div>
			<!--拍照-->
		<?php } ?>



