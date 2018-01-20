<?php namespace Poppy\Framework\Http\Graphql\Inputs;

use GraphQL\Type\Definition\Type;
use Poppy\Framework\GraphQL\Support\InputType;


class InputPaginationType extends InputType
{

	public function __construct($attributes = [])
	{
		parent::__construct($attributes);
		$this->attributes['name']        = 'InputPagination';
		$this->attributes['description'] = trans('poppy::http.graphql.input_pagination_desc');
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'page' => [
				'type'        => Type::int(),
				'description' => trans('poppy::http.graphql.input_pagination_page'),
			],
			'size' => [
				'type'        => Type::int(),
				'description' => trans('poppy::http.graphql.input_pagination_size'),
			],
		];
	}
}
