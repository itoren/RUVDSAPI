<?php

namespace Ruvds;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RuvdsApi
{
    private $baseUrl = 'https://api.ruvds.com';
    private $token;
    private $client;

    public function __construct($token)
    {
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
    }

    // Получение списка действий пользователя
    public function getActions($page = 1, $perPage = 25, $sort = 'id', $order = 'asc')
    {
        return $this->sendRequest('GET', '/v2/actions', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
                'sort' => $sort,
                'order' => $order,
            ],
        ]);
    }

    // Получение информации о балансе
    public function getBalance($type = 'default', $currencyId = 1)
    {
        return $this->sendRequest('GET', '/v2/balance', [
            'query' => [
                'type' => $type,
                'currency_id' => $currencyId,
            ],
        ]);
    }

    // Получение списка дата-центров
    public function getDatacenters()
    {
        return $this->sendRequest('GET', '/v2/datacenters');
    }

    // Получение списка оповещений
    public function getNotifications($status = 'all', $page = 1, $perPage = 25, $sort = 'add_dt', $order = 'asc')
    {
        return $this->sendRequest('GET', '/v2/notifications', [
            'query' => [
                'status' => $status,
                'page' => $page,
                'per_page' => $perPage,
                'sort' => $sort,
                'order' => $order,
            ],
        ]);
    }

    // Изменение статуса оповещения
    public function updateNotificationStatus($notificationId, $status)
    {
        return $this->sendRequest('PUT', "/v2/notifications/$notificationId", [
            'json' => ['status' => $status],
        ]);
    }

    // Получение списка операционных систем
    public function getOperatingSystems()
    {
        return $this->sendRequest('GET', '/v2/os');
    }

    // Получение списка платежей
    public function getPayments($page = 1, $perPage = 25, $sort = 'dt', $order = 'asc')
    {
        return $this->sendRequest('GET', '/v2/payments', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
                'sort' => $sort,
                'order' => $order,
            ],
        ]);
    }

    // Создание виртуального сервера
    public function createServer($datacenter, $tariffId, $osId, $paymentPeriod, $cpu, $ram, $drive, $driveTariffId, $ip, $computerName, $userComment = null)
    {
        return $this->sendRequest('POST', '/v2/servers', [
            'json' => [
                'datacenter' => $datacenter,
                'tariff_id' => $tariffId,
                'os_id' => $osId,
                'payment_period' => $paymentPeriod,
                'cpu' => $cpu,
                'ram' => $ram,
                'drive' => $drive,
                'drive_tariff_id' => $driveTariffId,
                'ip' => $ip,
                'computer_name' => $computerName,
                'user_comment' => $userComment,
            ],
        ]);
    }

    // Получение списка серверов
    public function getServers($page = 1, $perPage = 25, $sort = 'virtual_server_id', $order = 'asc', $getPaidTill = false, $getNetwork = false, $search = null)
    {
        return $this->sendRequest('GET', '/v2/servers', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
                'sort' => $sort,
                'order' => $order,
                'get_paid_till' => $getPaidTill,
                'get_network' => $getNetwork,
                'search' => $search,
            ],
        ]);
    }

    // Управление SSH ключами: добавление ключа
    public function addSshKey($publicKey, $name)
    {
        return $this->sendRequest('POST', '/v2/ssh_keys', [
            'json' => [
                'public_key' => $publicKey,
                'name' => $name,
            ],
        ]);
    }

    // Получение списка SSH ключей
    public function getSshKeys()
    {
        return $this->sendRequest('GET', '/v2/ssh_keys');
    }

    // Удаление SSH ключа
    public function deleteSshKey($sshKeyId)
    {
        return $this->sendRequest('DELETE', "/v2/ssh_keys/$sshKeyId");
    }

    // Получение информации о токенах
    public function getTokens()
    {
        return $this->sendRequest('GET', '/v2/tokens');
    }

    // Создание нового токена
    public function createToken($tokenName, $tokenRole, $tokenExpiry = null)
    {
        return $this->sendRequest('POST', '/v2/tokens', [
            'json' => [
                'token_name' => $tokenName,
                'token_role' => $tokenRole,
                'token_expiry' => $tokenExpiry,
            ],
        ]);
    }

    // Отправка команды виртуальному серверу
    public function sendServerAction($serverId, $actionType)
    {
        return $this->sendRequest('PUT', "/v2/servers/$serverId/actions", [
            'json' => ['type' => $actionType],
        ]);
    }

    // Получение начального пароля для сервера
    public function getServerStartPassword($serverId, $responseFormat = 'base64')
    {
        return $this->sendRequest('GET', "/v2/servers/$serverId/start_password", [
            'query' => ['response_format' => $responseFormat],
        ]);
    }

    // Вспомогательный метод для отправки запросов
    private function sendRequest($method, $uri, $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody();
                throw new \Exception("Error: " . json_decode($responseBody, true)['message']);
            }
            throw new \Exception("HTTP request failed: " . $e->getMessage());
        }
    }
}

// Пример использования:
$token = 'your_api_token_here';
$ruvdsApi = new RuvdsApi($token);

try {
    // Получение списка серверов
    $servers = $ruvdsApi->getServers();
    print_r($servers);

    // Создание нового сервера
    $newServer = $ruvdsApi->createServer(
        1, // datacenter
        14, // tariff_id
        52, // os_id
        2, // payment_period
        2, // cpu
        2, // ram
        20, // drive
        3, // drive_tariff_id
        1, // ip
        'SQLSRV-01', // computer_name
        'Server created via API.' // user_comment
    );
    print_r($newServer);

    // Получение начального пароля для сервера
    $password = $ruvdsApi->getServerStartPassword(1232);
    print_r($password);

} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
