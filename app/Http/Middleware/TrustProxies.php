<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * '*' = percayai SEMUA proxy.
     * Railway menggunakan reverse proxy/load balancer,
     * sehingga tanpa ini HTTPS detection dan IP forwarding tidak benar.
     */
    protected $proxies = '*';

    /**
     * Header yang digunakan untuk mendeteksi proxy.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
