<?php

include_once 'BmobRestClient.class.php';

/**
 * BmobObject object对象类
 * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
 */
class BmobObject extends BmobRestClient
{
	
	public $_includes = array();
	private $_className = '';

	public function __construct($class = '') 
	{
		if($class != ''){
			$this->_className = $class;
		}
		else{
			$this->throwError('创建对象时请包含对象id');
		}
		parent::__construct();
	}

	/**
	 * 设置对象的属性
	 * @param array $data
	 */	
	public function setData($data = array())
	{
		
		//每次使用前先清空对象属性数组
		$this->data = array();
		if ($data) {
			foreach ($data as $name=>$value) {
				if($name != '_className'){
					$this->data[$name] = $value;
				}		
			}
		}

	}
	
	/**
	 * 添加对象
	 * @param  array $data 对象的属性数组
	 * 
	 */
	public function create($data = array()) 
	{
		//添加对象的属性
		$this->setData($data);
		
		if(count($this->data) > 0 && $this->_className != ''){
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'classes/'.$this->_className,
				'data' => $this->data,
			));
			return $sendRequest;
		}else {
			$this->throwError('创建对象时请添加对象数据或指定对象id');
		}	
		
	}

	/**
	 * 获取对象
	 * @param string $id 对象id, 当为空时表示获取所有对象
	 * @param array $condition，查询条件
	 */
	public function get($id="", $condition = array())
	{
		if ($this->_className != '') {
			if ($id) {
				$sendRequest = $this->sendRequest(array(
					'method' => 'GET',
					'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
					'condition' => $condition,
				));
			} else {
				$sendRequest = $this->sendRequest(array(
					'method' => 'GET',
					'sendRequestUrl' => 'classes/'.$this->_className,
					'condition' => $condition,
				));
			}			
			
			return $sendRequest;
		}else {
			$this->throwError('获取对象时请指定对象id');
		}	
	}
	

	/**
	 * 更新对象的属性
	 * @param string $id 对象id
	 * @param  array $data 对象的属性数组
	 */	
	public function update($id, $data=array())
	{
		
		if($this->_className != '' || !empty($id)){
			
			if ($data) {
				//添加对象的属性
				$this->setData($data);
			} else {
				$this->throwError('请指定要更新的属性');
			}
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改对象时请指定对象id');
		}	
	}
	
	/**
	 * 删除对象
	 * @param string $id 对象id
	 */		
	public function delete($id)
	{
		if($this->_className != '' || !empty($id)){
			$sendRequest = $this->sendRequest(array(
				'method' => 'DELETE',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id
			));

			return $sendRequest;
		} else {
			$this->throwError('删除对象时请指定对象id');
		}			
	}	

	/**
	 * 任何数字字段进行原子增加或减少的功能
	 * @param string $id 对象id
	 * @param string $field 需要修改的数字字段
	 * @param int $amount 不加负号表示增加, 加负号表示减少
	 */
	public function increment($id, $field, $amount)
	{
		
		if($this->_className != '' || !empty($id)){
			
			$this->data[$field] = $this->dataType('increment', $amount);
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改对象时请指定对象id');
		}	
		
	}
	
	/**
	 * 在一个对象中删除一个字段
	 * @param string $id 对象id
	 * @param string $field 需要删除的字段
	 */
	public function deleteField($id, $field)
	{
		
		if($this->_className != '' && !empty($id) && !empty($field)){
			
			$this->data[$field] = $this->dataType('deleteField', $field);
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改对象时请指定对象id');
		}	
		
	}

	/**
	 * 添加数组数据
	 * @param string $field 需要修改的字段
	 * @param string $data 添加的数组
	 */
	public function addArray($field, $data)
	{
		
		if($this->_className != '' && !empty($field) && !empty($data)){
			
			$this->data[$field] = $this->dataType('addArray', $data);
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'classes/'.$this->_className,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('添加对象时请指定对象field或数据');
		}	
		
	}

	/**
	 * 修改数组数据
	 * @param string $id id
	 * @param string $field 需要修改的字段
	 * @param string $data 修改的数组
	 */
	public function updateArray($id, $field, $data)
	{
		
		if($this->_className != '' && !empty($id) &&  !empty($field) && !empty($data)){
			
			$this->data[$field] = $this->dataType('addArray', $data);
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改对象时请指定对象id和field和数据');
		}	
		
	}	

	/**
	 * 删除数组数据
	 * @param string $id id
	 * @param string $field 需要修改的字段
	 * @param string $data 删除的数组
	 */
	public function deleteArray($id, $field, $data)
	{
		
		if($this->_className != '' && !empty($id) &&  !empty($field) && !empty($data)){
			
			$this->data[$field] = $this->dataType('delArray', $data);
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('删除时请指定对象id和field和数据');
		}	
	}		

	/**
	 * 添加关联对象（１对１）
	 * @param string $field 需要添加关联的字段
	 * @param string $otherObject 需要修改的字段
	 * @param string $otherObjectId 删除的数组
	 */
	public function addRelPointer($field, $otherObject, $otherObjectId)
	{
		
		if($this->_className != '' && !empty($field) &&  !empty($otherObject) && !empty($otherObjectId)){
			
			$this->data[$field] = $this->dataType('addRelPointer', array($otherObject, $otherObjectId));

			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'classes/'.$this->_className,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('添加关联对象时请指定对象id和field和数据');
		}	
	}	

	/**
	 * 添加关联对象（１对多）
	 * @param string $field 需要添加关联的字段
	 * @param array $data 关联的数据
	 */
	public function addRelRelation($field, $data)
	{
		
		if($this->_className != '' && !empty($field) &&  !empty($data)){
			
			$this->data[$field] = $this->dataType('addRelRelation', $data);

			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'classes/'.$this->_className,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('添加关联对象时请指定对象id和data');
		}	
	}	

	/**
	 * 修改关联对象（１对１）
	 * @param string $id id
	 * @param string $field 需要添加关联的字段
	 * @param string $otherObject 需要修改的字段
	 * @param string $otherObjectId 删除的数组
	 */
	public function updateRelPointer($id, $field, $otherObject, $otherObjectId)
	{
		
		if($this->_className != '' && !empty($field) &&  !empty($otherObject) && !empty($otherObjectId)){
			
			$this->data[$field] = $this->dataType('addRelPointer', array($otherObject, $otherObjectId));

			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改关联对象时请指定对象id和field和数据');
		}	
	}	

	/**
	 * 修改关联对象（１对多）
	 * @param string $id 
	 * @param string $field 需要添加关联的字段
	 * @param array $data 关联的数据
	 */
	public function updateRelRelation($id, $field, $data)
	{
		
		if($this->_className != '' && !empty($id) &&  !empty($field) && !empty($data)){
			
			$this->data[$field] = $this->dataType('addRelRelation', $data);

			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('修改关联对象时请指定对象id和field和data');
		}	
	}

	/**
	 * 删除关联对象
	 * @param string $id 
	 * @param string $field 需要添加关联的字段
	 * @param array $data 关联的数据
	 */
	public function deleteRelation($id, $field, $data)
	{
		
		if($this->_className != '' && !empty($id) &&  !empty($field) && !empty($data)){
			
			$this->data[$field] = $this->dataType('removeRelation', $data);

			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'sendRequestUrl' => 'classes/'.$this->_className.'/'.$id,
				'data' => $this->data,
			));

			return $sendRequest;
			
		}else {
			$this->throwError('删除关联对象时请指定对象id和field和data');
		}	
	}


	/**
	 * 批量操作
	 * @param array $data 批量操作的数据
	 */
	public function batch($data)
	{
		if(!empty($data)){		
			
			$this->data["requests"] = $data;
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'batch',
				'data' => $this->data,
			));
			return $sendRequest;			
		}else {
			$this->throwError('批量操作时请指定操作的数据');
		}	
	}

	/**
	 * 上传文件
	 * @param array $data 批量操作的数据
	 */
	public function uploadFile($fileName, $filePath)
	{
		if(!empty($fileName) && !empty($filePath) ){		
			
			$this->data = file_get_contents($filePath);
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'files/'.base64_encode($fileName),
				'data' => $this->data,
			));
			return $sendRequest;			
		} else {
			$this->throwError('请指导文件名和文件路径');
		}	
	}	

	/**
	 * 生成缩微图
	 * @param array $data 参数
	 */
	public function imageThumbnail($data)
	{
		if(!empty($data) ){		
			
			$this->data = $data;
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'images/thumbnail',
				'data' => $this->data,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 生成水印
	 * @param array $data 参数
	 */
	public function imagesWatermark($data)
	{
		if(!empty($data) ){		
			
			$this->data = $data;
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'images/watermark',
				'data' => $this->data,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 创建角色
	 * @param array $data 参数
	 */
	public function createRole($data)
	{
		if(!empty($data) ){		
			
			$this->data = $data;
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'sendRequestUrl' => 'roles',
				'data' => $this->data,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}

	/**
	 * 获取角色
	 * @param  $id id
	 */
	public function getRole($id)
	{
		if(!empty($id) ){		
			
			$sendRequest = $this->sendRequest(array(
				'method' => 'GET',
				'sendRequestUrl' => 'roles/'.$id,
			));
			return $sendRequest;			
		} else {
			$this->throwError('id不能为空');
		}	
	}

	/**
	 * 更新角色
	 * @param  $id id
	 */
	public function updateRole($id, $field, $op, $data)
	{
		if(!empty($id) && !empty($field) && !empty($op) && !empty($data) ){		
			
			$this->data[$field] = array("__op"=> $op, "objects"=>$data);
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'data' => $this->data,
				'sendRequestUrl' => 'roles/'.$id,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 删除角色
	 * @param  $id id
	 * @param  $sessionToken sessionToken
	 */
	public function deleteRole($id, $sessionToken)
	{
		if(!empty($id) && !empty($sessionToken) ){					
			$sendRequest = $this->sendRequest(array(
				'method' => 'DELETE',
				'data' => $this->data,
				'sessionToken' => $sessionToken,
				'sendRequestUrl' => 'roles/'.$id,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 保存设备信息
	 * @param  $data data
	 */
	public function addInstallations($data)
	{
		if(!empty($data) ){					
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'data' => $data,
				'sendRequestUrl' => 'installations',
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}

	/**
	 * 更新设备信息
	 * @param  $data data
	 */
	public function updateInstallations($id, $data)
	{
		if(!empty($data) ){					
			$sendRequest = $this->sendRequest(array(
				'method' => 'PUT',
				'data' => $data,
				'sendRequestUrl' => 'installations/'.$id,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}


	/**
	 * 推送
	 * @param  $data data
	 */
	public function push($data)
	{
		if(!empty($data) ){					
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'data' => $data,
				'sendRequestUrl' => 'push',
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}

	/**
	 * 查询订单
	 * @param  $id 
	 */
	public function getOrder($id)
	{
		if(!empty($id) ){					
			$sendRequest = $this->sendRequest(array(
				'method' => 'GET',
				'sendRequestUrl' => 'pay/'.$id,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}

	/**
	 * 发送短信
	 * @param  $mobile  	发送的手机号
	 * @param  $content     短信的内容
	 * @param  $sendTime 	定时发送，比如未来的某一时刻给某个手机发送一条短信，sendTime的格式必须是YYYY-mm-dd HH:ii:ss， 如: 2015-05-26 12:13:14 	  
	 */
	public function sendSms($mobile, $content, $sendTime="")
	{
		if(!empty($mobile) && !empty($content) ){
			$data = array("mobilePhoneNumber"=>$mobile, "content"=>$content);
			if( $sendTime!="" ) {
				$data["sendTime"] = $sendTime;
			}
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'data' => $data,
				'sendRequestUrl' => 'requestSms',
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 发送短信验证码
	 * @param  $mobile  	发送的手机号
	 */
	public function sendSmsVerifyCode($mobile)
	{
		if(!empty($mobile) ){
			$data = array("mobilePhoneNumber"=>$mobile);
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'data' => $data,
				'sendRequestUrl' => 'requestSmsCode',
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 验证短信验证码
	 * @param  $mobile  	发送的手机号
	 * @param  $verifyCode  短信验证码
	 */
	public function verifySmsCode($mobile, $verifyCode)
	{
		if(!empty($mobile) && !empty($verifyCode) ){
			$data = array("mobilePhoneNumber"=>$mobile);
			$sendRequest = $this->sendRequest(array(
				'method' => 'POST',
				'data' => $data,
				'sendRequestUrl' => 'verifySmsCode/'.$verifyCode,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}	

	/**
	 * 查询短信状态
	 * @param  $smsId  	请求短信验证码返回的smsId值
	 */
	public function querySms($smsId)
	{
		if(!empty($smsId) ){
			$sendRequest = $this->sendRequest(array(
				'method' => 'GET',
				'sendRequestUrl' => 'querySms/'.$smsId,
			));
			return $sendRequest;			
		} else {
			$this->throwError('参数不能为空');
		}	
	}		

}

?>