<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/26
 * Time: 00:14
 */

namespace Ttg;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class WechatAccount implements ContainerAware
{
    use ContainerAwareTrait;

    private $appId;
    private $secret;
    private $accessToken;
    private $accessTokenExpiresIn;
    private $users = null;

    public function __construct($appId, $secret)
    {
        $this->appId = $appId;
        $this->secret = $secret;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken()
    {
        if ($this->accessToken && time() < $this->accessTokenExpiresIn) {
            return $this->accessToken;
        }

        $client = new Client();
        $res = $client->get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->secret}");
        $res = json_decode($res->getBody()->getContents(), true);

        if (!$res) {
            throw new \Exception('获取 access token 失败');
        }

        if (isset($res['errcode'])) {
            throw new \Exception('获取 access token 错误: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
        }

        $this->accessToken = $res['access_token'];
        $this->accessTokenExpiresIn = time() + intval($res['expires_in']) - 200;

        return $this->accessToken;
    }

    public function getUsers()
    {
        assert(!is_null($this->users));
        return $this->users;
    }

    /**
     * @return \Generator
     * @throws \Exception
     */
    public function asyncGetUsers()
    {
        if ($this->users) {
            return;
        }

        $this->users = [];

        $client = new Client();
        $next = null;
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$this->getAccessToken()}";

        while (true) {
            if ($next) {
                $url .= "&next_openid={$next}";
            }

            $res = $client->get($url);
            $res = json_decode($res->getBody()->getContents(), true);

            if (!$res) {
                throw new \Exception('获取用户失败');
            }

            if (isset($res['errcode'])) {
                throw new \Exception('获取用户错误: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
            }

            if ($res['count'] === 0) {
                return;
            }

            array_push($this->users, ...$res['data']['openid']);
            $next = $res['next_openid'];

            yield;
        }
    }

    /**
     * @param Client $client
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \Exception
     */
    public function asyncSendTemplateMessage(Client $client, $openId)
    {
        $openId = 'o8umIjobnDAml_kpoa9Y02vnGK9M'; // TODO 测试用
        $uri = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->getAccessToken()}";
        $conf = $this->container->config->get('template-message');
        $data = [
            'touser' => $openId,
            'template_id' => $conf['id'],
            'url' => $conf['url'],
            'data' => $conf['data']
        ];

        return $client->postAsync($uri, ['json' => $data])
            ->then(function(Response $response) {
                $res = json_decode($response->getBody()->getContents(), true);
                if (!$res || $res['errcode'] > 0) {
                    $this->container->log->error('推送模版消息失败', $res);
                    return false;
                }

                return true;
            }, function ($reason) {
                $this->container->log->error($reason);
                throw $reason;
            });
    }
}