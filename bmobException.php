<?php

/**
 * bmobException 异常类
 * @author newjueqi
 * @authort 2014.03.28
 * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
 */
class bmobException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) {
		//codes are only set by a bmob.com error
		if($code != 0){
			$message = "bmob.cn error: ".$message;
		}

		parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

}