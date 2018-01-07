<?php namespace System\Classes;

use Carbon\Carbon;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\ImageHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class Uploader
{

	use AppTrait;

	protected $destination = '';

	protected $disk = 'public';

	private $folder = '';

	private $returnUrl = '';

	private $allowedExtensions = ['zip'];

	private $quality = 70;

	private $resizeDistrict = 1920;

	protected static $extensions = [
		'image' => [
			'extension'   => 'jpg,jpeg,png,gif',
			'description' => '请选择图片',
		],
		'zip'   => [
			'extension'   => 'zip',
			'description' => '选择压缩包',
		],
		'rp'    => [
			'extension'   => 'rp',
			'description' => '请选择 Rp 文件',
		],
		'rplib' => [
			'extension'   => 'rplib',
			'description' => '请选择原型组件',
		],
		'xls'   => [
			'extension'   => 'xls,xlsx',
			'description' => '请选择xls文件',
		],
	];


	public function __construct($folder = 'uploads')
	{
		$this->folder    = $folder;
		$this->returnUrl = config('app.url') . '/';
	}

	public function setExtension($extension = [])
	{
		$this->allowedExtensions = $extension;
	}

	/**
	 * District Size.
	 * @param int $resize
	 */
	public function setResizeDistrict($resize)
	{
		$this->resizeDistrict = $resize;
	}

	/**
	 * 获取本地磁盘存储
	 * @return \Illuminate\Filesystem\FilesystemAdapter
	 */
	public function storage()
	{
		return \Storage::disk($this->disk);
	}

	/**
	 * 使用文件/form 表单形式上传获取并且保存
	 * @param UploadedFile $file
	 * @return mixed
	 */
	public function saveFile($file)
	{
		if (!$file) {
			return $this->setError('没有上传任何文件');
		}

		if ($file->isValid()) {
			// 存储
			if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()), $this->allowedExtensions)) {
				return $this->setError('你只允许上传 "' . implode(',', $this->allowedExtensions) . '" 格式');
			}

			// 磁盘对象
			$Disk      = $this->storage();
			$extension = $file->getClientOriginalExtension();
			if (!$extension) {
				$extension = 'png';
			}
			$fileRelativePath = $this->genRelativePath($extension);
			$zipContent       = file_get_contents($file);

			// bmp 处理
			$type = mime_content_type($file->getRealPath());
			if ($type == 'image/x-ms-bmp') {
				$img = ImageHelper::imageCreateFromBmp($file->getRealPath());
				if ($img) {
					ob_start();
					imagepng($img);
					$imgContent = ob_get_clean();
					$zipContent = $imgContent;
				}
			}

			$Disk->put($fileRelativePath, $zipContent);

			// 缩放图片
			if (in_array($extension, self::type('image', 'ext_array')) && $extension != 'gif') {
				$Image  = \Image::make($zipContent);
				$width  = $Image->width();
				$height = $Image->height();

				try {
					if ($width >= $this->resizeDistrict || $height >= $this->resizeDistrict) {
						$r_width  = ($width > $height) ? $this->resizeDistrict : null;
						$r_height = ($width > $height) ? null : $this->resizeDistrict;
						$Disk->put($fileRelativePath, $this->resize($Image, $r_width, $r_height));
					}
				} catch (\Exception $e) {
					return $this->setError($e->getMessage());
				}
			}

			$this->destination = $fileRelativePath;
			return true;
		}
		else {

			return $this->setError($file->getErrorMessage());
		}
	}

	/**
	 * 裁剪和压缩
	 * @param      $content
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop 是否进行裁剪
	 * @return \Psr\Http\Message\StreamInterface
	 */
	public function resize($content, $width = 1920, $height = 1440, $crop = false)
	{
		if ($content instanceof Image) {
			$Image = $content;
		}
		else {
			$Image = \Image::make($content);
		}

		if ($crop) {
			$widthCalc  = $Image->width() > $width ? $width : $Image->width();
			$heightCalc = $Image->height() > $height ? $height : $Image->height();
			$widthMax   = $Image->width() < $width ? $width : $Image->width();
			$heightMax  = $Image->height() < $height ? $height : $Image->height();

			// calc x, calc y
			$x = 0;
			$y = 0;
			if ($widthCalc >= $width) {
				$x = ceil(($widthMax - $widthCalc) / 2);
			}
			if ($heightCalc >= $height) {
				$y = ceil(($heightMax - $heightCalc) / 2);
			}
			if ($x || $y) {
				$Image->crop($width, $height, $x, $y);
			}
		}

		$Image->resize($width, $height, function (Constraint $constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});

		return $Image->stream('png', $this->quality);
	}

	/**
	 * 保存内容或者流方式上传
	 * @param $content
	 * @return bool
	 */
	public function saveInput($content)
	{
		// 磁盘对象
		$Disk = $this->storage();

		$fileRelativePath = $this->genRelativePath();


		// 缩放图片
		$Image = \Image::make($content);
		$Image->resize(1920, null, function (Constraint $constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});
		$Disk->put($fileRelativePath, $Image->stream('png', 60));
		$this->destination = $fileRelativePath;
		return true;
	}

	/**
	 * 类型
	 * @param        $type
	 * @param string $return_type
	 * @return array
	 */
	public static function type($type, $return_type = 'ext_string')
	{
		if (!isset(self::$extensions[$type])) {
			$ext = self::$extensions['image'];
		}
		else {
			$ext = self::$extensions[$type];
		}
		switch ($return_type) {
			case 'desc':
				return $ext['description'];
				break;
			case 'ext_array':
				return explode(',', $ext['extension']);
				break;
			case 'ext_string':
			default:
				return $ext['extension'];
				break;
		}
	}


	public function setDestination($destination)
	{
		$this->destination = $destination;
	}


	public function getDestination()
	{
		return $this->destination;
	}

	/**
	 * 图片url的地址
	 * @return string
	 */
	public function getUrl()
	{
		// 磁盘 public_uploads 对应的是根目录下的 uploads, 所以这里的目录是指定的
		return $this->returnUrl . $this->destination;
	}


	private function genRelativePath($extension = 'png')
	{
		$now      = Carbon::now();
		$fileName = $now->format('is') . str_random(8) . '.' . $extension;
		return ($this->folder ? $this->folder . '/' : '') . $now->format('Ym/d/H/') . $fileName;
	}
}