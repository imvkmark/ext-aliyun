<?php namespace System\Request\Api;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Collection;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use System\Classes\Traits\SystemTrait;
use System\Setting\Events\SettingUpdated;


class HomeController extends Controller
{
	use SystemTrait;

	/**
	 * Get page defined
	 * @param $path
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function page($path)
	{
		if (is_post()) {
			$inputs = json_decode(\Input::getContent(), true);
			foreach ($inputs as $input) {
				$this->getSetting()->set($input['key'], $input['value'] ?? $input['default']);
			}

			$this->getEvent()->dispatch(new SettingUpdated());

			return Resp::web(Resp::SUCCESS, '更新成功');
		}
		$page = $this->getBackend()->pages()->filter(function($definition) use ($path) {
			return $definition['initialization']['path'] == $path;
		})->first();

		if (isset($page['tabs']) && $page['tabs'] instanceof Collection && count($page['tabs']) > 0) {
			foreach ($page['tabs'] as $key => $tab) {
				$tab['submit']      = url($tab['submit']);
				$page['tabs'][$key] = $tab;
			}
		}
		return $this->getResponse()->json([
			'data'    => $page,
			'message' => '获取数据成功！',
		]);
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function information()
	{

		$data = $this->getResponse()->json([
			'data'    => [
				'navigation'  => $this->getBackend()->navigations()->toArray(),
				'pages'       => $this->getBackend()->pages()->toArray(),
				'scripts'     => $this->getBackend()->scripts()->toArray(),
				'stylesheets' => $this->getBackend()->stylesheets()->toArray(),
			],
			'message' => '获取模块和插件信息成功！',
		]);
		return $data;
	}

	/**
	 * @api                 {get} /api/system/captcha [O]图像验证码
	 * @apiDescription      通过url地址过去验证图片, 用于发送验证码的流量限制
	 * @apiVersion          1.0.0
	 * @apiName             SystemCaptcha
	 * @apiGroup            System
	 * @apiParam   {String} mobile   手机号
	 */
	public function captcha()
	{
		$input     = [
			'mobile' => \Input::get('mobile'),
		];
		$validator = \Validator::make($input, [
			'mobile' => 'required|mobile',
		], [], [
			'mobile' => '手机号',
		]);
		if ($validator->fails()) {
			return Resp::web(Resp::ERROR, $validator->messages(), 'json|1');
		}
		$phraseBuilder = new PhraseBuilder(4);
		//生成验证码图片的Builder对象，配置相应属性
		$builder = new CaptchaBuilder(null, $phraseBuilder);
		//可以设置图片宽高及字体
		$builder->build($width = 180, $height = 50, $font = null);
		//获取验证码的内容
		$phrase = $builder->getPhrase();

		//把内容存入 缓存
		\Cache::put('captcha_' . $input['mobile'], $phrase, 5);
		ob_clean();
		ob_start();
		//生成图片
		$builder->output();
		$content = ob_get_clean();
		return response($content, 200, [
			'Content-Type'  => 'image/png',
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}


	/**
	 * @api                 {get} /api/system/permission [O]权限获取
	 * @apiVersion          1.0.0
	 * @apiName             SystemPermission
	 * @apiGroup            System
	 * @apiParam   {Integer} role_id  角色ID
	 * @apiSuccess {String}  title                           描述
	 * @apiSuccess {String}  root                            根
	 * @apiSuccess {String}  groups                          所有分组
	 * @apiSuccess {String}  groups.group                    分组
	 * @apiSuccess {String}  groups.title                    标题
	 * @apiSuccess {String}  groups.permissions              所有权限
	 * @apiSuccess {String}  groups.permissions.description  描述
	 * @apiSuccess {String}  groups.permissions.id           权限ID
	 * @apiSuccess {String}  groups.permissions.value        选中值
	 * @apiSuccessExample   data:
	 * {
	 *     "global": {
	 *         "title": "全局",
	 *         "root": "global",
	 *         "groups": {
	 *             "addon": {
	 *                 "group": "addon",
	 *                 "title": "插件权限",
	 *                 "permissions": [
	 *                     {
	 *                         "description": "全局插件管理权限",
	 *                         "id": 1,
	 *                         "value": 0
	 *                     }
	 *                 ]
	 *             },
	 *         }
	 *     }
	 * }
	 */
	public function permission()
	{
		$validator = \Validator::make($this->getRequest()->all(), [
			'role_id' => Rule::required(),
		]);
		if ($validator->fails()) {
			return Resp::web(Resp::ERROR, $validator->messages(), 'json|1');
		}

		$role_id = $this->getRequest()->get('role_id');

		$permission = app('act.role')->permissions($role_id);

		return $this->getResponse()->json([
			'data'    => $permission,
			'message' => '获取数据成功！',
			'status'  => Resp::SUCCESS,
		], 200, [], JSON_UNESCAPED_UNICODE);
	}
}
