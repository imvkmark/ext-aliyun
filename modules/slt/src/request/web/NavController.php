<?php namespace Slt\Request\Web\Controllers;

use Curl\Curl;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Sour\Poppy\Models\SiteTag;
use Sour\Poppy\Models\SiteUrlRelTag;
use Sour\Lemon\Helper\FileHelper;
use Sour\Lemon\Support\Resp;
use Sour\Poppy\Action\ActCollection;
use Sour\Poppy\Auth\FeUser;
use Sour\Poppy\Models\SiteCollection;
use Sour\Poppy\Models\SiteRelCat;
use Sour\Poppy\Models\SiteUrl;
use Sour\Poppy\Models\SiteUserUrl;
use Sour\System\Models\BaseCategory;
use Sour\System\Models\BaseCategoryGroup;

class NavController extends InitController
{

	/**
	 * 导航地址
	 * @return mixed
	 */
	public function index()
	{
		$tag  = \Input::get('tag');
		$pam  = FeUser::instance()->getPam();
		$tags = SiteUrlRelTag::userTag($pam->id);
		$Db   = SiteUserUrl::where('account_id', $pam->id);
		if ($tag) {
			$arrTag = explode('|', $tag);
			$ids    = SiteTag::whereIn('title', $arrTag)->pluck('id');
			$urlIds = SiteUrlRelTag::whereIn('tag_id', $ids)
				->havingRaw('count(tag_id)=' . count($ids))
				->groupBy('user_url_id')
				->pluck('user_url_id');
			$Db->whereIn('id', $urlIds);
		}
		$urls =
			$Db->with(['siteUrlRelTag', 'siteUrl'])
				->paginate($this->pagesize);
		return view('web.nav.index', [
			'items' => $urls,
			'tags'  => $tags,
		]);
	}

	public function jump($id)
	{
		$referer = \Input::server('HTTP_REFERER');
		if (parse_url($referer)['host'] != parse_url(config('app.url'))['host']) {
			return Resp::web('来源非法');
		}
		/** @var SiteUrl $url */
		$url       = SiteUrl::find($id);
		$url->hits += 1;
		$url->save();
		return Resp::web('OK~正在访问链接', 'location|' . $url->url);
	}

	public function jumpUser($id)
	{
		/** @var SiteUrl $url */
		$rel            = SiteUserUrl::with('url')->find($id);
		$rel->url->hits += 1;
		$rel->url->save();

		$rel->hits += 1;
		$rel->save();
		return Resp::web('正在访问链接', 'location|' . $rel->url->url);
	}


	/**
	 * 设定为已读
	 * @param null $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function collection($id = null)
	{
		if (is_post()) {
			$Collection = (new ActCollection())->setUser(FeUser::instance());
			if ($Collection->establish(\Input::all(), $id)) {
				return Resp::web('OK~操作成功!', 'top_reload|1');
			}
			else {
				return Resp::web($Collection->getError());
			}
		}
		if ($id) {
			/** @var SiteCollection $item */
			$item = SiteCollection::find($id);
			\View::share('item', $item);
		}

		$icons = FileHelper::listFile(public_path('project/sour/images/collection_icon'));

		$options = [];
		foreach ($icons as $icon) {
			$key       = basename($icon, '.png');
			$src       = str_replace(base_path('public'), '', $icon);
			$options[] = '<option ' . ((isset($item) && $item->icon == $key) ? ' selected="selected" ' : '') . ' value="' . $key . '" data-img-src="' . config('app.url') . '/' . $src . '">' . $key . '</option>';
		}
		return view('web.nav.collection', [
			'options' => $options,
		]);
	}


	public function url($id = null)
	{
		$Collection = (new ActCollection())->setUser(FeUser::instance());
		if (is_post()) {
			if ($Collection->establishUrl(\Input::all(), $id)) {
				return Resp::web('OK~操作成功!', 'top_reload|1');
			}
			else {
				return Resp::web($Collection->getError());
			}
		}

		$url         = '';
		$title       = '';
		$description = '';
		$icon        = '';
		if ($id) {
			if ($Collection->initUrl($id)) {
				// todo
			}
			else {
				return Resp::web($Collection->getError());
			}

		}
		else {
			$url         = rtrim(\Input::get('url'), '/');
			$title       = \Input::get('title');
			$description = \Input::get('description');
			$img_url     = \Input::get('img_url');
			$imgUrls     = explode(',', $img_url);
			if (count($imgUrls)) {
				$icon = $imgUrls[0];
			}
			if ($url && $Collection->hasCreate($url)) {
				return Resp::web('您已经添加了该网址, 请不要重复添加!');
			}
		}

		return view('web.nav.url', [
			'url'         => $url,
			'title'       => $title,
			'description' => $description,
			'icon'        => $icon,
		]);
	}

	public function tag()
	{
		$kw   = strval(trim(\Input::get('search')));
		$user = FeUser::instance();
		$tags = SiteUrlRelTag::userTag($user->getPam()->id, $kw);
		$data = [];
		if (count($tags)) {
			foreach ($tags as $tag) {
				$data[] = [
					'value' => $tag['title'],
					'text'  => $tag['title'],
				];
			}
		}
		echo json_encode($data);
	}

	/**
	 * 批量删除
	 * @param null $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function collectionDestroy($id = null)
	{
		if (!$id) {
			return Resp::web('请选中要删除的信息');
		}
		$Collection = (new ActCollection())->setUser(FeUser::instance());
		if ($Collection->destroy($id)) {
			return Resp::web('OK~删除成功', 'top_reload | 1');
		}
		else {
			return Resp::web($Collection->getError());
		}
	}


	public function urlDestroy($id)
	{
		if (!$id) {
			return Resp::web('请选中要删除的链接');
		}
		$Collection = (new ActCollection())->setUser(FeUser::instance());
		if ($Collection->destroyUrl($id)) {
			return Resp::web('OK~删除成功', 'top_reload | 1');
		}
		else {
			return Resp::web($Collection->getError());
		}
	}

	/**
	 * 获取标题
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function title(Request $request)
	{
		$url = $request->input('url');
		if (!$url) {
			return Resp::web('请填写url地址!');
		}
		$curl = new Curl();
		$curl->setTimeout(2);
		if (!preg_match(' /^http(s)?:\/\/.*?/', $url)) {
			$url = 'http://' . $url;
		}
		$content = $curl->get($url);
		if (preg_match("/<title>(.*?)<\/title>/i", $content, $match)) {
			return Resp::web('OK~获取到标题', [
				'title'  => $match[1],
				'url'    => $url,
				'forget' => true,
			]);
		}
		else {
			return Resp::web('没有找到相关网页标题', [
				'title'  => $url,
				'url'    => $url,
				'forget' => true,
			]);
		}
	}
}

