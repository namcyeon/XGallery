<?php
/**
 * Copyright (c) 2020 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Services\Crawler;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Url\Url;
use stdClass;

/**
 * Class Batdongsan
 * @package App\Services\Crawler
 */
final class Batdongsan extends AbstractCrawler
{
    protected string $name = 'batdongsan';

    /**
     * @param  string  $itemUri
     * @return object|null
     */
    public function getItemDetail(string $itemUri): ?object
    {
        $crawler = null === $itemUri ? $this->crawler : $this->crawl($itemUri);

        try {
            $item     = new stdClass();
            $nameNode = $crawler->filter('.pm-title h1');

            if ($nameNode->count() === 0) {
                Log::warning('Can not get title');

                return null;
            }

            $item->name    = trim($crawler->filter('.pm-title h1')->text(null, false));
            $item->price   = trim($crawler->filter('.gia-title.mar-right-15 strong')->text(null, false));
            $item->size    = trim($crawler->filter('.gia-title')->nextAll()->filter('strong')->text(null, false));
            $item->content = trim($crawler->filter('.pm-content .pm-desc')->html());
            $item->detail  = collect($crawler->filter('#product-other-detail div.row')->each(function ($node) {
                return [
                    trim($node->filter('div.left')->text()),
                    trim($node->filter('div.right')->text()),
                ];
            }));

            $item->info = collect($crawler->filter('#project div.row')->each(function ($node) {
                return [
                    trim($node->filter('div.left')->text()),
                    trim($node->filter('div.right')->text()),
                ];
            }));

            $item->contact = collect($crawler->filter('#divCustomerInfo div.right-content')->each(function ($node) {
                $key   = trim($node->filter('div.left')->text());
                $value = trim($node->filter('div.right')->text());

                if ($key === 'Email') {
                    $value = html_entity_decode($value);
                    $regex = '`([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})`';
                    preg_match_all($regex, $value, $matches);
                    $value = array_unique($matches[0])[0];
                }
                return [
                    $key,
                    $value,
                ];
            }));

            return $item;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param  string|null  $indexUri
     * @return Collection
     */
    public function getItemLinks(string $indexUri = null): ?Collection
    {
        $crawler = null === $indexUri ? $this->crawler : $this->crawl($indexUri);

        try {
            $links = $crawler->filter('.search-productItem')->each(function ($node) {
                return [
                    'url' => $node->filter('h3 a')->attr('href'),
                    'title' => $node->filter('h3 a')->attr('title'),
                    'cover' => $node->filter('.p-main-image-crop img.product-avatar-img')->attr('src'),
                ];
            });

            return collect($links);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param  string|null  $indexUri
     * @return int|null
     */
    public function getIndexPagesCount(string $indexUri = null): int
    {
        $crawler = null === $indexUri ? $this->crawler : $this->crawl($indexUri);

        try {
            $lastPath = explode('/', Url::fromString($crawler->selectLink('>')->attr('href'))->getPath());

            return (int) str_replace('p', '', end($lastPath));
        } catch (Exception $exception) {
            return 1;
        }
    }

    /**
     * @param  array  $conditions
     * @return Collection|null
     */
    public function search(array $conditions = []): ?Collection
    {
        return $this->getIndexLinks($this->buildUrl('tags/'.implode('/', $conditions)));
    }

    /**
     * @param  Url  $url
     * @param  int  $page
     * @return string
     */
    protected function buildUrlWithPage(Url $url, int $page): string
    {
        return $this->buildUrl($url->getPath().'/p'.$page);
    }
}