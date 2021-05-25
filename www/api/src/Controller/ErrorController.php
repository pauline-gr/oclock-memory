<?php

// src/Controller/ErrorController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ErrorController - Ce contrôlleur est appelé lors d'un proc d'erreur défini dans config/packages/framework.yaml
 */
class ErrorController {
    public function show(): JsonResponse {
        return new JsonResponse(array('message' => 'Route introuvable','code' => '500'));
    }
}