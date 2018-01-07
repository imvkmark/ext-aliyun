<?php namespace System\Request\Api;

use Illuminate\Http\JsonResponse;
use Poppy\Framework\Application\Controller;
use System\Classes\Traits\SystemTrait;
use System\Module\Module;

/**
 * Class DashboardsController.
 */
class DashboardsController extends Controller
{
	use SystemTrait;

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function list()
	{
		$dashboards = collect();
		$hidden     = collect();
		$left       = collect();
		$right      = collect();
		$this->getModule()->enabled()->each(function (Module $module) use ($dashboards) {
			$module->offsetExists('dashboards') && collect($module->get('dashboards'))->each(function (
				$definition,
				$identification
			) use ($dashboards) {
				if (is_string($definition['template'])) {
					list($class, $method) = explode('@', $definition['template']);
					if (class_exists($class)) {
						$instance               = $this->getContainer()->make($class);
						$definition['template'] = $this->getContainer()->call([
							$instance,
							$method,
						]);
					}
				}
				$dashboards->put($identification, $definition);
			});
		});
		$dashboards = $dashboards->keyBy('identification');
		$saved      = collect(json_decode($this->getSetting()->get('administration.dashboards', '')));
		$saved->has('hidden') && collect($saved->get('hidden', []))->each(function ($identification) use (
			$dashboards,
			$hidden
		) {
			if ($dashboards->has($identification)) {
				$hidden->push($dashboards->get($identification));
				$dashboards->offsetUnset($identification);
			}
		});
		$saved->has('left') && collect($saved->get('left', []))->each(function ($identification) use (
			$dashboards,
			$left
		) {
			if ($dashboards->has($identification)) {
				$left->push($dashboards->get($identification));
				$dashboards->offsetUnset($identification);
			}
		});
		$saved->has('right') && collect($saved->get('right', []))->each(function ($identification) use (
			$dashboards,
			$right
		) {
			if ($dashboards->has($identification)) {
				$right->push($dashboards->get($identification));
				$dashboards->offsetUnset($identification);
			}
		});
		if ($dashboards->isNotEmpty()) {
			$dashboards->each(function ($definition) use ($left) {
				$left->push($definition);
			});
		}

		return $this->getResponse()->json([
			'data'    => [
				'hidden' => $hidden->toArray(),
				'left'   => $left->toArray(),
				'right'  => $right->toArray(),
			],
			'message' => '获取面板数据成功！',
		]);
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(): JsonResponse
	{
		$this->validate($this->request, [
			'hidden' => [
				Rule::array(),
			],
			'left'   => [
				Rule::array(),
			],
			'right'  => [
				Rule::array(),
			],
		], [
			'hidden.array' => '隐藏数据必须为数组',
			'left.array'   => '左侧数据必须为数组',
			'right.array'  => '右侧数据必须为数组',
		]);
		$data = collect();
		$data->put('hidden', collect($this->request->input('hidden', []))->transform(function (array $data) {
			return $data['identification'];
		}));
		$data->put('left', collect($this->request->input('left', []))->transform(function (array $data) {
			return $data['identification'];
		}));
		$data->put('right', collect($this->request->input('right', []))->transform(function (array $data) {
			return $data['identification'];
		}));
		$this->setting->set('administration.dashboards', json_encode($data->toArray()));

		return $this->response->json([
			'message' => '保存仪表盘页面布局成功！',
		]);
	}
}
