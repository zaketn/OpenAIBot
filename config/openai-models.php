<?php

return [
    'models' => [
        'text-davinci-003' => [
            'model' => 'text-davinci-003',
            'max_tokens' => 1024,
            'n' => 1,
            'temperature' => 0.6,
            'stop' => ['AI: ', 'Human: '],
        ],
        'text-curie-001' => [
            'model' => 'text-curie-001',
            'max_tokens' => 1024,
            'n' => 1,
            'temperature' => 0.6,
            'stop' => ['AI: ', 'Human: '],
        ],
        'text-babbage-001' => [
            'model' => 'text-babbage-001',
            'max_tokens' => 1024,
            'n' => 1,
            'temperature' => 0.6,
            'stop' => ['AI: ', 'Human: '],
        ],
        'text-ada-001' => [
            'model' => 'text-ada-001',
            'max_tokens' => 1024,
            'n' => 1,
            'temperature' => 0.6,
            'stop' => ['AI: ', 'Human: '],
        ],
    ],
    'restrictions' => [
        'text-davinci-003' => [
            'max_tokens' => 4096,
        ],
        'text-curie-001' => [
            'max_tokens' => 2048,
        ],
        'text-babbage-001' => [
            'max_tokens' => 2048,
        ],
        'text-ada-001' => [
            'max_tokens' => 2048,
        ],
    ]
];
