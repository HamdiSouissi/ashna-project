<?php
declare(strict_types=1);
namespace App\Services;

class HttpClient
{
    /**
     * Requete HTTP via cURL.
     * @param array{timeout?:int,user_agent?:string,body?:string|array<string,mixed>,headers?:array<string,string>} $opts
     * @return array{status:int,body:string,error:string}
     */
    public static function request(string $method, string $url, array $opts = []): array
    {
        $timeout = (int)($opts['timeout'] ?? 20);
        $headers = array_map(
            static fn(string $k, string $v): string => "$k: $v",
            array_keys($opts['headers'] ?? []),
            array_values($opts['headers'] ?? [])
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_USERAGENT      => $opts['user_agent'] ?? 'LeadFinder/1.0',
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $body = $opts['body'] ?? '';
            if (is_array($body)) $body = http_build_query($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $res = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($res === false) return ['status' => 0, 'body' => '', 'error' => $err];
        return ['status' => $status, 'body' => (string)$res, 'error' => $err];
    }

    public static function get(string $url, array $opts = []): array  { return self::request('GET', $url, $opts); }
    public static function post(string $url, array $opts = []): array { return self::request('POST', $url, $opts); }
}
