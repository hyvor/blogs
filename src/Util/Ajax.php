<?php
namespace Project\Util;

class Ajax {
	static function success($returnArray = null) {
		$array = array (
			'status' => true
		);
		if ($returnArray !== null) {
			$array = array_merge($returnArray, $array);
		}
        return response()->json($array);
	}

	static function error($errorMessage = '', $errorCode = 0) {
		return response()->json(array(
			'status' => false,
			'error' => $errorMessage,
			'errorCode' => $errorCode 
		));
	}
}