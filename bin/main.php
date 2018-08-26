<?php

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ttg\WechatAccount;

require __DIR__ . '/../vendor/autoload.php';

$app = new \Ttg\Container();

try {
    $app->accounts->loadUsers();
    $ca = count($app->accounts->all());
    $cu = array_reduce($app->accounts->all(), function ($i, WechatAccount $u) {
        return count($u->getUsers());
    }, 0);

    echo "初始化完成，从 {$ca} 个账户中获取到 {$cu} 个用户\n";
} catch (\Exception $e) {
    $app->log->error($e);
    throw new \Exception('初始化错误', 1, $e);
}

try {
    echo "开始推送模版消息\n";

    $client = new Client();

    $ts = microtime(true);
    $count = 0;
    $eCount = 0;

    $requests = function () use ($app, $client, &$count, &$eCount) {
        foreach ($app->accounts->all() as $account) {
            foreach ($account->getUsers() as $openId) {
                yield function() use ($client, $account, $openId, &$count, &$eCount) {
                    return $account->asyncSendTemplateMessage($client, $openId)
                        ->then(function ($success) use (&$count, &$eCount) {
                            $count++;
                            if (!$success) {
                                $eCount++;
                            }
                            if (!$success || $count % 1000 === 0) {
                                echo "已推送 $count 条数据, 失败 $eCount\n";
                            }

                            return $success;
                        }, function () use (&$count, &$eCount) {
                            $eCount++;
                            $count++;
                            echo "已推送 $count 条数据, 失败 $eCount\n";
                        });
                };
            }
        }
    };


    $pool = new Pool($client, $requests(), [
        'concurrency' => $app->config->get('template-message.concurrency')
    ]);

    $promise = $pool->promise();
    $promise->wait();

    $te = microtime(true);

    $diff = $te - $ts;
    echo sprintf("完成！共推送 %d 条，用时 %02d:%02d, 平均每秒推送 %.2f 条\n",
        $count, intval($diff / 60), $diff % 60, $count / $diff
    );
    exit(0);

} catch (\Exception $e) {
    $app->log->error($e);
}