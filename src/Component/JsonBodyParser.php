<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Component;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonBodyParser
{
    public function parse(Request $request)
    {
        $body = $request->getContent();
        if (empty($body) || ($json = json_decode($body, true)) === null) {
            return new JsonResponse(
                [
                    'status'  => 'Bad Request',
                    'message' => 'Json body expected'
                ],
                400
            );
        }
        
        return $json;
    }
}
