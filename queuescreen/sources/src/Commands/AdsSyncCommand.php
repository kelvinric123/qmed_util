<?php

namespace Rasque\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdsSyncCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sync');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(10000);

        $localVideosPath = $this->basePath . '/www';

        $localPlaylistPath = $this->basePath . '/www/playlist-map.json';

        $latestPlaylist = [
//            '76066d118532f2b23a56bdbc6436f912' => 'http://ads.58.my/medias/76066d118532f2b23a56bdbc6436f912.mp4',
            '4607df0c49990dc506fbfb36b74b9802' => 'http://ads.58.my/medias/4607df0c49990dc506fbfb36b74b9802.mp4',
//            '7440809846c078442f1074959c1085d0' => 'http://ads.58.my/medias/7440809846c078442f1074959c1085d0.mp4'
        ];

        $localPlaylist = @file_get_contents($localPlaylistPath);

        $localPlaylist = $localPlaylist ? json_decode($localPlaylist, true) : [];

        // delete non-existent
        foreach ($localPlaylist as $id => $filename) {
            if (!isset($latestPlaylist[$id])) {
                unlink($this->basePath . '/www/' . $filename);
                unset($localPlaylist[$id]);
            }
        }

        foreach ($latestPlaylist as $id => $url) {
            $segments = explode('/', $url);
            $filename = $segments[count($segments) - 1];

            if (isset($localPlaylist[$id])) {
                continue;
            }

            if (@file_put_contents($localVideosPath . '/' . $filename, file_get_contents($url)))
                $localPlaylist[$id] = $filename;
        }

        file_put_contents($localPlaylistPath, json_encode($localPlaylist, JSON_PRETTY_PRINT));

        return 0;
    }
}