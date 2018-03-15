<?php namespace Poppy\Framework\GraphQL\Error;

use GraphQL\Error\Error;

class ValidationError extends Error
{
	public $validator;
	
	public function setValidator($validator)
	{
		$this->validator = $validator;
		
		return $this;
	}
	
	public function getValidator()
	{
		return $this->validator;
	}
	
	public function getValidatorMessages()
	{
		return $this->validator ? $this->validator->messages() : [];
	}
}
