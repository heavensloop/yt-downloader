<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

class Videos
{
  private array $videos;
  private array $params = [];
  private int $currentIndex = 0;
  private int $page = 0;
  private int $itemNo = 0;
  private ?string $nextPageToken = null;
  private bool $nextPageIsLast = false;

  public function __construct(
    private readonly CacheInterface $cache,
    private readonly ParameterBagInterface $parameterBag
  ) {
    $this->loadNextPage();
  }

  public function getNext(): array|bool
  {
    $apiKey = $this->parameterBag->get('youtube_api_key');

    if (!isset($this->videos[$this->currentIndex])) {
      $this->currentIndex = 0;

      if (!$this->loadNextPage()) {
        return false;
      }
    }

    if (!isset($this->videos[$this->currentIndex])) {
      return false;
    }

    $video = $this->videos[$this->currentIndex];
    $directoryName = '/tmp/' . $video['id']['videoId'] . '.mp4';
    
    $this->itemNo++;
    $this->currentIndex++;

    return $video;
  }

  private function loadNextPage(): bool
  {
    if ($this->nextPageIsLast) {
      return false;
    }

    $this->page++;
    $data = $this->fetchData();

    try {
      $this->params = [
        "regionCode" => $data['regionCode'],
        "pageInfo" => $data['pageInfo']
      ];
    } catch (\Throwable $t) {
      return false;
    }

    $this->nextPageToken = $data['nextPageToken'] ?? null;

    if (!$this->nextPageToken) {
      $this->nextPageIsLast = true;
    }

    $this->videos = $data['items'];

    foreach ($this->videos as $key => $video) {
      if (!isset($video['id']['videoId']) || $video['id']['videoId'] === 'W1It4nQcjx4') {
        unset($this->videos[$key]);
      }
    }

    $this->videos = array_values($this->videos);

    return true;
  }

  private function fetchData(): array
  {
    return $this->cache->get('page_' . ($this->nextPageToken ?? ''), function () {
      $apiKey = $this->parameterBag->get('youtube_api_key');
      $channelId = 'UC9Wwn9jdsgQFBBvpjkA7aAA';
      $apiUrl = 'https://www.googleapis.com/youtube/v3/search?key='
        . $apiKey . '&channelId=' . $channelId . '&part=snippet,id&order=date&maxResults=50';

      if ($this->nextPageToken) {
        $apiUrl .= '&pageToken=' . $this->nextPageToken;
      }

      try {
        sleep(3);
        $jsonContent = file_get_contents($apiUrl);
        $data = json_decode($jsonContent, true);
      } catch (\Throwable $t) {
        return false;
      }

      return $data;
    });
  }
}
