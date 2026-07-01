<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API untuk sistem booking fasilitas asrama / Roomora',
    title: 'Booking Fasilitas API',
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class SwaggerInfo {}