<?php

namespace App\Service;

class Youtube
{
  public function __construct(private string $basePath)
  {
  }

  public function download(string $videoId, bool $execute = true): string
  {
    try {
      $downloadPath = $this->basePath . '/var/downloads/';
      if (!file_exists($downloadPath)) {
        if (!file_exists(dirname($downloadPath))) {
          mkdir(dirname($downloadPath), 0777, true);
        }

        mkdir($downloadPath, 0777, true);        
      }
      
      $cmd = 'yt-dlp -S "res:1080,fps" -o "' . $downloadPath . '%(upload_date)s.%(ext)s" ' . $videoId . '  --force-overwrites';
      // $cmd .= ' --cookies-from-browser chrome --cookies ~/cookies.txt';
      if ($execute) {
        shell_exec($cmd);
      }
    } catch (\Throwable $e) {
      dd('Something went wrong: ' . $e->getMessage());
    } finally {
      return $cmd;
    }
  }
}
