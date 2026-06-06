<?php

use MauticPlugin\MauticAIAssistantBundle\Controller\AIChatController;

return [
    'name'        => 'AI 助手',
    'description' => '外贸营销自动化平台 AI 对话助手',
    'version'     => '1.0.0',
    'author'      => 'Gelab',
    'routes'      => [
        'main' => [
            'mautic_ai_assistant_chat' => [
                'path'       => '/ai-assistant/chat',
                'controller' => AIChatController::class . '::proxyAction',
                'method'     => ['POST'],
            ],
        ],
    ],
];
