<?php 
	
	namespace App\Common;

	use PhalApi\Response\JsonResponse;

	/**
	 * 
	 */
	class MyResponse extends JsonResponse
	{
		
		public function getResult(){

			$res = parent::getResult();
			return $res['data'];
		}
	}

 ?>