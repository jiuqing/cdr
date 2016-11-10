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

$action = $_GET['action'];
$actions = array('tk', 'up', 'fd' ,'test');
//判断是否正确的请求
if(! in_array($action, $actions, true)){
	//错误
	exit;
}

$upload = new upload();
$upload->$action();


/**
 * 上传类
 * @author 
 */
class upload
{
	private $_tokenPath = '';           //令牌保存目录
	private $_filePath  = '';            //上传文件保存目录
	private $_userInfo  = array();
	private $_allows = array();    //允许支持的文件格式

	private $_process_list = array();

	public function __construct () {
		session_start();
		$this->_userInfo = $_SESSION['user-login'];

		if (!$this->_userInfo) exit('need login');

		$this->_tokenPath = DATA_PATH . 'tokens' . DS;
		$this->_filePath = UPLOAD_PATH . 'user' . DS;

		$this->_allows = array('psd' , 'ai' , 'jpg' , 'jpeg');
	}
	/**
	 * @method test
	 * @return [type] [description]
	 */
	public function test() {
		$tempFile = '/data/wwwroot/default/data/attachment/2016-03-13/cdr_cpw3trwmslrf.220x220.jpg';
		
		$sourceName = "菊花#设计#素材#logo#vis.jpg";
		$atts = $this->_zip_and_imagick ($tempFile,$sourceName);

		
		echo '<pre>888';
		print_r($sourceName);
		var_dump($atts);die;

		#颜色配置库
		$libColorConfig = LIB_PATH . "config.lib.php";
		if (!file_exists($libColorConfig)) {
			return array_merge($imageAttrs,$info);
		}
		require_once ($libColorConfig);

        ################识别属性以及生成缩略图开始################
        try {
        	$im = new imagick_lib();
        	$imageAttrs = $im->getInfo($tempFile);
        } catch (Exception $e) {
        	var_dump($e->getMessage());
        }
        #################识别属性以及生成缩略图结束################
        die;
                #
		#引入类库
		$libFile = LIB_PATH . "autocat.lib.php";
		if (!file_exists($libFile)) {
			die('Libaray does not exist:' . $libFile);
		}
		
		include_once ($libFile);

		#######################模拟自动分类数据结束
		$arrFileName = array('新年卡片','矢量素材','新年卡片','新年快乐','铃铛','礼盒');
        $catid = intval(autocat_lib::autoCategory($arrFileName));
        
		#数据库连接
		//$con = mysql_connect(_DBHOST_,_DBUSER_,_DBPSWD_);
		
		if ($catid) {
			$info['cid3'] = $catid;
			$con = mysql_connect(_DBHOST_,_DBUSER_,_DBPSWD_);
	    	$db  = mysql_select_db(_DBNAME_,$con);

	        //二级分类
	        $query = mysql_query("SELECT `pid` FROM `pre_material_category` WHERE `cid` = {$catid}");
	        $s   = mysql_fetch_assoc($query);
	        if (!empty($s)) {
	        	$info['cid2'] = $s['pid'];
	        }

	        //一级分类
	        $query = mysql_query("SELECT `pid` FROM `pre_material_category` WHERE `cid` = {$info['cid2']}");
	        $f   = mysql_fetch_assoc($query);
	        if (!empty($f)) {
	        	$info['cid1'] = $f['pid'];
	        }
		}

		var_dump($info);
		var_dump($catid);
    	
	}
	/**
	 * 获取令牌
	 */
	public function tk(){
		$file['name'] = $_GET['name'];                  //上传文件名称
		$file['size'] = $_GET['size'];                  //上传文件总大小
		$file['token'] = md5(json_encode($file['name'] . $file['size']));
		//判断是否存在该令牌信息
		if(! file_exists($this->_tokenPath . $file['token'] . '.token')){
		
			$file['up_size'] = 0;                       //已上传文件大小
			$pathInfo    = pathinfo($file['name']);

			//目录按用户ID分级
			$path = $this->_filePath . $this->_userInfo['uid'] . DS . date('Ymd') . DS;

			//生成文件保存子目录
			if(! is_dir($path)){
				mkdir($path, 0755, true);
			}

			

			//上传文件保存目录
			$file['filePath']     = $path . $file['token'] .'.'. $pathInfo['extension'];
			$file['modified']     = $_GET['modified'];      //上传文件的修改日期

			//保存令牌信息
			$this->setTokenInfo($file['token'], $file);
		}
		$result['token'] = $file['token'];
		$result['success'] = true;
		//$result['server'] = '';

		echo json_encode($result);
		exit;
	}
	
	
	/**
	 * 上传接口
	 */
	public function up(){
		if('html5' == $_GET['client']){
			$this->html5Upload();
		}
		elseif('form' == $_GET['client']){
			$this->flashUpload();
		}
		else {
			//错误
			exit;
		}

	}
	
	/**
	 * HTML5上传
	 */
	protected function html5Upload(){
		$fileNameAndKeys = $zipAndImageAttrs = array();
		$token = $_GET['token'];
		$fileInfo = $this->getTokenInfo($token);
		
		if($fileInfo['size'] > $fileInfo['up_size']){
			//取得上传内容
			$data = file_get_contents('php://input', 'r');
			if(! empty($data)){
				//上传内容写入目标文件
				$fp = fopen($fileInfo['filePath'], 'a');
				flock($fp, LOCK_EX);
				fwrite($fp, $data);
				flock($fp, LOCK_UN);
				fclose($fp);
				//累积增加已上传文件大小
				$fileInfo['up_size'] += strlen($data);
				if($fileInfo['size'] > $fileInfo['up_size']){
					$this->setTokenInfo($token, $fileInfo);
				}
				else {
					//上传完成后删除令牌信息
					@unlink($this->_tokenPath . $token . '.token');

					/** 20160309新增 */
					$fileNameAndKeys = $this->_autocate($fileInfo['name']);
					$zipAndImageAttrs = $this->_zip_and_imagick($fileInfo['filePath'],$fileInfo['name']);
				}
			}
		}
		$result['start'] = $fileInfo['up_size'];
		$result['success'] = true;
		

		$result = array_merge($result,$fileNameAndKeys,$zipAndImageAttrs);
		

		echo json_encode($result);
		exit;
	}

	/**
	 * FLASH上传
	 */
	public function flashUpload(){
	
		//$result['start'] = $fileInfo['up_size'];
		$result['success'] = false;

		echo json_encode($result);
		exit;
	}
	
	/**
	 * 生成文件内容
	 */
	protected function setTokenInfo($token, $data){
		file_put_contents($this->_tokenPath . $token . '.token', json_encode($data));
	}

	/**
	 * 获取文件内容
	 */
	protected function getTokenInfo($token){
		$file = $this->_tokenPath . $token . '.token';

		if(file_exists($file)){
			return json_decode(file_get_contents($file), true);
		}
		return false;
	}

	/**
	 * [根据文件名规则自动读取标题/关键字以及自动分类]
	 * @method autocate
	 * @param  [type]   $strFileName [具有一定规则的文件名]
	 * @return [type]   [description]
	 */
	private function _autocate($strFileName) {
		#去掉.ext
        $strFormatFileName = preg_replace('/\.\w+/' , '' , $strFileName);

		##############自动分类处理开始###############
        $info = array();
        $strFormatFileName = str_replace( array(' ',',','，','#'),array('#','#','#','#'),$strFormatFileName );
        $info['cid3'] = $info['cid2'] = $info['cid1'] = 0;
        //#作为分隔符进行拆分
        $pos = strpos($strFormatFileName, '#');
        if ( $pos === false) {
            $arrFileName = array($strFormatFileName);
            $info['title'] = $info['keywords'] = $arrFileName[0];
        }else{
            $arrFileName = explode('#',$strFormatFileName);
            $info['title'] = $arrFileName[0];
            foreach ($arrFileName as $k => $v) {
                if ($k > 0) {
                    $info['keywords'] .= $v . ' ';
                }
            }
        }

        #默认返回的信息
		$default_info = array(
			'cid3' => 0,
			'cid2' => 0,
			'cid1' => 0,
			'title' => $info['title'],
			'keywords' => $info['keywords'],
		);
		
		$libFile = LIB_PATH . "autocat.lib.php";
		if (!file_exists($libFile)) {
			return $default_info;
		}
		
		#引入类库
		require_once ($libFile);
        $catid = intval(autocat_lib::autoCategory($arrFileName));

        if ($catid) {
			$info['cid3'] = $catid;
			try {
				$con = mysql_connect(_DBHOST_,_DBUSER_,_DBPSWD_);
		    	$db  = mysql_select_db(_DBNAME_,$con);

		        //二级分类
		        $query = mysql_query("SELECT `pid` FROM `pre_material_category` WHERE `cid` = {$catid}");
		        $s   = mysql_fetch_assoc($query);
		        if (!empty($s)) {
		        	$info['cid2'] = $s['pid'];
		        }

		        //一级分类
		        $query = mysql_query("SELECT `pid` FROM `pre_material_category` WHERE `cid` = {$info['cid2']}");
		        $f   = mysql_fetch_assoc($query);
		        if (!empty($f)) {
		        	$info['cid1'] = $f['pid'];
		        }
			}catch (Exception $e) {
        		return $default_info;
        	}
			
		}
        ##############自动分类处理结束###############
        return $info;
	}

	/**
	 * [压缩文件成包并用imagic库识别属性]
	 * @method _zip_and_imagick
	 * @param  [type]           $tempFile [上传的文件绝对路径]
	 * @return [type]           [description]
	 */
	private function _zip_and_imagick ($tempFile,$sourceName) {
		#默认返回
		$imageAttrs = array(
        	'width' => 0,
        	'height' => 0,
        	'shape' => 0,
        	'color' => 0,
        	'thumb' => 'iconNoImg.png',
        	'thumb_w' => 320,
        	'thumb_h' => 0,
        	'resolution' => 0,
        	'wcm' => 0,
        	'hcm' => 0,
        	'fid' => 0,
        );
		
        ##############压缩开始#######################
        $pathinfo = explode('.', $sourceName);
        $saveName = iconv("UTF-8","GB2312//IGNORE",$pathinfo[0]);
        $ext = $pathinfo[1];

        $zipA = array('savedir' => '', 'savename' => '');
        $zipA['savedir']  = 'archive' . DS . date("Ym/d",REQUEST_TIME) . DS;

        $newName = 'bupic_com_' . date("YmdHis") . '_' . self::randstr(5);
        $zipA['savename'] = $newName . '.zip';

        $zipFolder  = UPLOAD_PATH . $zipA['savedir'];

        if (!is_dir($zipFolder)) mkdir($zipFolder,0755,1);
        $zipFile = UPLOAD_PATH . $zipA['savedir'] . $zipA['savename'];

        #文件存在
        if (file_exists($tempFile)) {
        	$info['code'] = 0;
        	$zip = new ZipArchive();
	        if($zip->open($zipFile,ZipArchive::CREATE)===TRUE){
	            $zip->addFromString($saveName . '.' .$ext, file_get_contents($tempFile));
	            $zip->close();
	        }

	        $info['archive']     = $zipA['savedir'] . $zipA['savename'];
	        if (is_file($zipFile)) {
	            $info['archivesize'] = filesize($zipFile);
	        }else{
	            $info['archivesize'] = 0;
	        }
	        ##############压缩结束#######################

	        #如果是不支持的文件格式直接返回strtolower($ext) == 'cdr'
	        if ( !in_array(strtolower($ext), $this->_allows , true) ) {
                return array_merge($imageAttrs,$info);
            }

            $libFile = LIB_PATH . "imagick.lib.php";
			if (!file_exists($libFile)) {
				return array_merge($imageAttrs,$info);
			}
			#引入类库
			require_once ($libFile);

			#颜色配置库
			$libColorConfig = LIB_PATH . "config.lib.php";
			if (!file_exists($libColorConfig)) {
				return array_merge($imageAttrs,$info);
			}
			require_once ($libColorConfig);
			
	        ################识别属性以及生成缩略图开始################
	        try {
	        	$im = new imagick_lib();
	        	$imageAttrs = $im->getInfo($tempFile);
	        } catch (Exception $e) {
	        	return array_merge($imageAttrs,$info);
	        }
	        #################识别属性以及生成缩略图结束################
       
	        #删除源文件（临时文件）
	        unlink($tempFile);

        }else{
        	return array_merge($imageAttrs,$info);
        }
        
        return array_merge($imageAttrs,$info);
	}

	/**
	 * [生成随机字符]
	 * @method randstr
	 * @param  [type]  $num [description]
	 * @return [type]  [description]
	 */
	private static function randstr($num) {
		$str = '';
		$genu = str_shuffle('KLMNaABGHIbcCDEFdefSTUVghiQRWXjklPYZmnopqrstuvwlxzJO');
		for ($i=0;$i<$num;$i++) {
			$str .= $genu[$i];
		}
		return $str;
	}


}//endclass




