<?php

namespace MauticPlugin\MauticAIAssistantBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AIChatController extends AbstractController
{
    private const API_URL = 'https://api.deepseek.com/v1/chat/completions';
    private const MODEL   = 'deepseek-v4-flash';

    public function proxyAction(Request $request): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';

        if (empty($apiKey)) {
            return new JsonResponse(['error' => 'API key not configured'], 500);
        }

        $systemPrompt = file_get_contents(
            __DIR__ . '/../Resources/system_prompt.txt'
        );
        if (empty($systemPrompt)) {
            return new JsonResponse(['error' => 'System prompt not found'], 500);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['messages']) || !is_array($data['messages'])) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $data['messages']
        );

        $payload = json_encode([
            'model'       => self::MODEL,
            'messages'    => $messages,
            'max_tokens'  => 1000,
            'temperature' => 0.7,
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return new JsonResponse(
                ['error' => 'API connection failed: ' . $curlError],
                502
            );
        }

        return new JsonResponse(json_decode($response, true), $httpCode);
    }
}
