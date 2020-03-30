<?php

namespace Tests\Feature\Crawler;

use App\Services\Crawler\Truyenchon;
use Tests\TestCase;
use Tests\Traits\HasCrawler;

/**
 * Class TruyenchonTest
 * @package Tests\Feature\Crawler
 */
class TruyenchonTest extends TestCase
{
    use HasCrawler;

    protected array $itemDetails = [
        'http://truyenchon.com/truyen/thuan-tinh-luc-thieu/chap-138/549502' => [
            'url',
            'images',
            'title'
        ]
    ];

    protected array $itemLinks = [
        'http://truyenchon.com/the-loai/action' => [
            'url',
            'cover',
            'title'
        ]
    ];

    protected array $indexLinks = [
        'http://truyenchon.com/the-loai/action' => [
            'pageCount' => 100,
            'itemPerPage' => 47
        ]
    ];

    protected string $crawlerClass = Truyenchon::class;
}
