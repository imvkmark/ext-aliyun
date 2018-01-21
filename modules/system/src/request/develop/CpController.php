<?php namespace System\Request\Develop;

use Curl\Curl;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\RawCookieHelper;


class CpController extends InitController
{

	public function __construct()
	{
		$this->middleware(['web', 'auth:develop']);
		parent::__construct();
	}

	/**
	 * 开发者控制台
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('system::develop.cp.cp');
	}

	/**
	 * graphi 控制面板
	 * @param string $schema
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function graphi($schema = 'default')
	{
		$token = RawCookieHelper::get('dev_token#' . $schema);
		return view('system::graphql.graphiql', [
			'graphqlPath' => route('api.graphql', $schema),
			'token'       => $token,
			'schema'      => ucfirst($schema),
		]);
	}

	public function api()
	{
		$this->title('接口调试平台');
		$tokenGet = function($cookie_key) {
			if (RawCookieHelper::has($cookie_key)) {
				$token = RawCookieHelper::get($cookie_key);

				// check token is valid
				$curl   = new Curl();
				$access = route('system:api.access');
				$curl->setHeader('x-requested-with', 'XMLHttpRequest');
				$curl->setHeader('Authorization', 'Bearer ' . $token);
				$curl->post($access);
				if ($curl->httpStatusCode === 401) {
					RawCookieHelper::remove($cookie_key);
				}
			}
			return RawCookieHelper::get($cookie_key);
		};

		return view('system::develop.cp.api', [
			'token_default' => $tokenGet('dev_token#default'),
			'token_web'     => $tokenGet('dev_token#web'),
			'token_backend' => $tokenGet('dev_token#backend'),
			'url_system'    => route('system:develop.cp.doc', 'system'),
		]);
	}


	public function apiLogin()

	{
		$type = \Input::get('type');
		if (is_post()) {
			$access = route('api.token', [$type]);
			$curl   = new Curl();
			$data   = $curl->post($access, [
				'passport' => \Input::get('passport'),
				'password' => \Input::get('password'),
			]);
			if ($curl->httpStatusCode === 200) {
				$token = 'dev_token#' . $type;
				RawCookieHelper::set($token, $data->data);
				return Resp::web(Resp::SUCCESS, '登录成功', 'top_reload|1');
			}
			else {
				return Resp::web(Resp::ERROR, $curl->errorMessage);
			}
		}
		return view('system::develop.cp.api_login', compact('type'));
	}

	/**
	 * token
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector|\Illuminate\View\View
	 */
	public function setToken()
	{
		$type      = \Input::get('type');
		$cookieKey = 'dev_token#' . $type;
		if (is_post()) {
			$token = \Input::get('token');
			if (!$token) {
				return Resp::web(Resp::ERROR, 'token 不存在');
			}
			RawCookieHelper::remove($cookieKey);
			RawCookieHelper::set($cookieKey, $token);
			return Resp::web(Resp::SUCCESS, '设置 token 成功', 'top_reload|1');
		}
		$token = RawCookieHelper::get($cookieKey);
		return view('system::develop.cp.set_token', compact('type', 'token'));
	}

	/**
	 * 文档地址
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function doc()
	{
		$type = \Input::get('type', 'system');
		return redirect(url('docs/' . $type));
	}
}
