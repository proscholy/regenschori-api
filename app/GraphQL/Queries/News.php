<?php

namespace App\GraphQL\Queries;

use App\Song;

class News
{
    public function resolve($rootValue, array $args)
    {
        return [
            [
                'text' => 'dummy text',
                'fa_icon' => 'fa fa-key',
                'link' => '/o-zpevniku',
                'type' => 2
            ]
        ];
    }
}
