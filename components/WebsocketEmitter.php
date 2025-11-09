<?php
namespace app\components;
use yii\base\Component;

use GuzzleHttp\Client;
use Yii;

class WebsocketEmitter  extends Component
{
    const WS_SECRET = 'f3b0c89f4f5b2f8e7e76cc24e7a7a58e8bb7a2e013f40b71d3d158b7f2d9c8af';

public static function getSocketEmitUrl(): string
{
    $req  = Yii::$app->request;
    $host = $req->hostName;

    // Local environment — accept any port
    if (in_array($host, ['localhost', '127.0.0.1'])) {
        $port = 3000; // Default port for local dev
        return "http://localhost:{$port}/emit-order-update";
    }

    // Production
    if ($host === 'app.esteticanow.com') {
        return 'https://app-socket.esteticanow.com/emit-order-update';
    }

    // Test
    if ($host === 'test.esteticanow.com') {
        return 'https://test-socket.esteticanow.com/emit-order-update';
    }

    // Default (staging or other)
    return 'https://test-socket.esteticanow.com/emit-order-update';
}


    /**
     * Generic emitter.
     * @param string $roomType e.g. 'vendor' | 'user' | 'group'
     * @param int|string $roomId e.g. vendor_id or user_id
     * @param string $event socket event name
     * @param array $data arbitrary payload (NOT tied to 'order')
     * @param array $meta optional extras (trace ids, actor, etc.)
     * @return bool true if emit succeeded, false otherwise
     */
    public static function emitGeneric(string $roomType, $roomId, string $event, array $data, array $meta = []): bool
    {
        try {
            $client = new Client(['timeout' => 3, 'verify' => true]);
            $client->post(self::getSocketEmitUrl(), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-WS-KEY'     => self::WS_SECRET,
                ],
                'json' => [
                    'room_type' => $roomType,             // server will build room name like "{$roomType}_{$roomId}"
                    'room_id'   => (string)$roomId,
                    'event'     => $event,
                    'data'      => $data,                 // <-- generic payload
                    'meta'      => $meta,
                ],
            ]);
            return true;
        } catch (\Throwable $e) {
            Yii::error('WS emit failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    // Convenience wrappers
    public static function emitToVendor(string $event, int $vendorId, array $data, array $meta = []): bool
    {
        return self::emitGeneric('vendor', $vendorId, $event, $data, $meta);
    }

    public static function emitToUser(string $event, int $userId, array $data, array $meta = []): bool
    {
        return self::emitGeneric('user', $userId, $event, $data, $meta);
    }
}
