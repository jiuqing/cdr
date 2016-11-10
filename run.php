<?php
/**
 * 命令行:批量任务处理
 * 第一个参数为动作名
 * 其它待扩展
 * 用法：php -f run.php createsignature
 */
#非命令行模式退出
if ( !$argv ) exit(0);

#数据库操作兼容
$GLOBALS['ecs'] = $GLOBALS['db'] = new db_ext;


/**
 * 初始化一些需要的变量
 */
#用户ID，某些场景下可能遇到
$_SESSION['user_id'] = 0;

#成功和失败次数
$succ = $fail = 0;

#错误数据容器
$error    = array();

/**
 *  生成素材签名
 */
if ($argv[1] == 'createsignature') {
	$db = db::getInstance();
	$list = $db->select('pre_material',array(),array('id'));
	foreach ($list as $v) {
		$data['signature'] = sha1( uniqid() );
		if ($db->update('pre_material',$data, array('id'=>$v['id']) )) {
			$succ++;
			echo 'Material ID:' . $v['id'] .  ' Update Succ' . PHP_EOL;
		}else{
			$fail++;
			echo 'Material ID:' . $v['id'] .  ' Update Fail' . PHP_EOL;
			continue;
		}
	}
	echo 'Static Result:SUCC(' .$succ. ') FAIL('.$fail.')';
}



###########################GENERAL OUTPUT INFO##############################
if ( !empty($error) ) print_r($error);
echo 'Static Result:SUCC(' .$succ. ') FAIL('.$fail.')';
echo 'End';








































############################################### CLASS AREA ###################################
/**
 * 单例数据库操作
 */
class db {
	public $conn;
	public static $sql;
	public static $instance=null;
	private function __construct(){
		$db = array(
		        'host'=>'127.0.0.1',
		        'user'=>'root',
		        'password'=>'',
		        'database'=>'cdr_v1',
		);

		$this->conn = mysql_connect($db['host'],$db['user'],$db['password']);
		if(!mysql_select_db($db['database'],$this->conn)){
			echo "失败";
		};
		mysql_query('set names utf8',$this->conn);
	}

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new db;
		}
		return self::$instance;
	}

	/**
	 * 查询数据库
	 */
	public function select($table,$condition=array(),$field = array() ){
		$where='';
		if(!empty($condition)){
			foreach($condition as $k=>$v){
				$where.=$k."='".$v."' and ";
			}
			$where='where '.$where .'1=1';
		}
		$fieldstr = '';
		if(!empty($field)){

			foreach($field as $k=>$v){
				$fieldstr.= $v.',';
			}
			 $fieldstr = rtrim($fieldstr,',');
		}else{
			$fieldstr = '*';
		}
		self::$sql = "select {$fieldstr} from {$table} {$where}";
		$result=mysql_query(self::$sql,$this->conn);
		$resuleRow = array();
		$i = 0;
		while($row=mysql_fetch_assoc($result)){
			foreach($row as $k=>$v){
				$resuleRow[$i][$k] = $v;
			}
			$i++;
		}
		return $resuleRow;
	}
	/**
	 * 添加一条记录
	 */
	 public function insert($table,$data){
	 	$values = '';
	 	$datas = '';
	 	foreach($data as $k=>$v){
	 		$values.=$k.',';
	 		$datas.="'$v'".',';
	 	}
	 	$values = rtrim($values,',');
	 	$datas   = rtrim($datas,',');
	 	self::$sql = "INSERT INTO  {$table} ({$values}) VALUES ({$datas})";
	 	return self::$sql;
		if(mysql_query(self::$sql)){
			return mysql_insert_id();
		}else{
			return false;
		};
	 }

	 /**
	  * 修改一条记录
	  */
	public function update($table,$data,$condition=array()){
		$where='';
		if(!empty($condition)){

			foreach($condition as $k=>$v){
				$where.=$k."='".$v."' and ";
			}
			$where='where '.$where .'1=1';
		}
		$updatastr = '';
		if(!empty($data)){
			foreach($data as $k=>$v){
				$updatastr.= $k."='".$v."',";
			}
			$updatastr = 'set '.rtrim($updatastr,',');
		}
		self::$sql = "update {$table} {$updatastr} {$where}";
		return mysql_query(self::$sql);
	}

	/**
	 * 删除记录
	 */
	 public function delete($table,$condition){
	 	$where='';
		if(!empty($condition)){

			foreach($condition as $k=>$v){
				$where.=$k."='".$v."' and ";
			}
			$where='where '.$where .'1=1';
		}
		self::$sql = "delete from {$table} {$where}";
		return mysql_query(self::$sql);

	 }

	public static function getLastSql(){
		echo self::$sql;
	}

	public function query ($sql) {
		return mysql_query($sql);
	}

	public function getOne ($sql) {

	}

	public function getRow($sql, $limited = false) {
		if ($limited == true) {
			$sql = trim ( $sql . ' LIMIT 1' );
		}
		$res = $this->query ( $sql );
		if ($res !== false) {
			return mysql_fetch_assoc ( $res );
		} else {
			return false;
		}
	}

	public function getAll($sql) {
		$res = $this->query ( $sql );
		if ($res !== false) {
			$arr = array ();
			while ( $row = mysql_fetch_assoc ( $res ) ) {
				$arr [] = $row;
			}
			return $arr;
		} else {
			return false;
		}
	}

}

/** 数据库操作扩展 */
class db_ext extends db{
	public $_instance;

	public function __construct () {
		$this->_instance =  db::getInstance();
	}

	public static function table ($table_name) {
		return 'ecs_' . $table_name;
	}
}



############################################### FUNCTION AREA ###################################
?>

