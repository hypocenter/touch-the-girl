<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/26
 * Time: 00:13
 */

namespace Ttg;


use Ttg\Task\Scheduler;

class Accounts extends Component
{
    /**
     * @var WechatAccount[]
     */
    private $accounts = [];

    public function boot()
    {
        foreach ($this->container->config->get('accounts') as $k => $v) {
            $this->accounts[$k] = $this->container->make(WechatAccount::class, $v['app_id'], $v['secret']);
        }
    }

    public function all()
    {
        return $this->accounts;
    }

    public function get($key)
    {
        return $this->accounts[$key];
    }

    /**
     * @throws \Exception
     */
    public function loadUsers()
    {
        $scheduler = new Scheduler();
        foreach ($this->accounts as $account) {
            $scheduler->newTask($account->asyncGetUsers());
        }
        $scheduler->run();
    }
}