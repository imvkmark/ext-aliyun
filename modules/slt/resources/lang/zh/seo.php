<?php
/*
|--------------------------------------------------------------------------
| 自动 seo
|--------------------------------------------------------------------------
| slt:nav.index  => slt::seo.nav_index
| 也就是 key 就是 路由名称的转换, 里边需要有
| title       : 标题(没有默认为网站名称)
| description : 描述(没有默认是网站描述, 有则替换, 以后可能加入替换项目)
*/
return [
	'nav_index' => [
		'title' => '导航',
	],
];