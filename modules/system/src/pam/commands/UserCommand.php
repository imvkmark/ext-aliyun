<?php namespace System\Pam\Commands;

use System\Models\PamAccount;
use Illuminate\Console\Command;
use System\Models\PamRole;
use System\Models\SysConfig;
use User\Pam\Action\Pam;

/**
 * User
 */
class UserCommand extends Command
{

	/**
	 * 前端部署.
	 * @var string
	 */
	protected $signature = 'system:user 
		{do : actions in "reset_pwd"}
		{--account= : Account Name}
		{--pwd= : Account password}
		';

	/**
	 * 描述
	 * @var string
	 */
	protected $description = 'user handler.';


	/**
	 * Execute the console command.
	 * @return mixed
	 */
	public function handle()
	{

		$do = $this->argument('do');
		switch ($do) {
			case 'reset_pwd':
				$username = $this->ask('Your username?');
				if ($userid = PamAccount::getIdByUsername($username)) {
					$pwd = trim($this->ask('Your aim password'));

					$pam = PamAccount::find($userid);
					/** @var Pam $pam */
					$actPam = app('act.pam');
					$actPam->setPassword($pam, $pwd);
					$this->info('Reset user password success');
				}
				else {
					$this->error('Your account not exists');
				}
				break;
			case 'create_root':
				$username = $this->option('account');
				if (!PamAccount::getIdByUsername($username)) {
					$pwd    = $this->option('pwd');
					$actPam = app('act.pam');
					if ($actPam->register($username, $pwd, PamRole::BE_ROOT)) {
						$this->info('User ' . $username . ' created');
					}
					else {
						$this->error($actPam->getError());
					}
				}
				else {
					$this->error('user ' . $username . ' exists');
				}
				break;
			case 'init_role':
				$roles = [
					[
						'name'      => PamRole::FE_USER,
						'title'     => '用户',
						'type'      => PamAccount::TYPE_USER,
						'is_system' => SysConfig::YES,
					],
					[
						'name'      => PamRole::BE_ROOT,
						'title'     => '超级管理员',
						'type'      => PamAccount::TYPE_BACKEND,
						'is_system' => SysConfig::YES,
					],
					[
						'name'      => PamRole::DEV_USER,
						'title'     => '开发者',
						'type'      => PamAccount::TYPE_DEVELOP,
						'is_system' => SysConfig::YES,
					],
				];
				foreach ($roles as $role) {
					if (!PamRole::where('name', $role['name'])->exists()) {
						PamRole::create($role);
					}
				}
				$this->info('Init Role success');
				break;
			default:
				$this->error('Please type right action![reset_pwd, init_role, create_root]');
				break;
		}

	}
}