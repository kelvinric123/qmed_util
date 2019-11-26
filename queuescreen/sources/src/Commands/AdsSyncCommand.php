<?php

namespace Rasque\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

function remoteCheckSize($url){
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    return $size;
}

class AdsSyncCommand extends BaseCommand
{
    const MAX_VIDEO_SIZE = 200000000;

    protected $playlistPath;

    /**
     * @var null|array
     */
    protected $currentDownload = null;

    public function configure()
    {
        $this->setName('sync');

        $this->playlistPath = $this->basePath . '/www/playlist-map.json';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isSyncing())
            return $this->write($output, 'Another synchronization process is running!');

        $command = $this;

        register_shutdown_function(function() use ($command) {
            $error = error_get_last();

            if ($error['type'] != E_ERROR)
                return;

            if (!$command->currentDownload)
                return;

            $command->insertMapValue('skipped', $command->currentDownload['id']);
        });

        set_time_limit(0);

        ini_set('memory_limit', '400M');

        $localVideosPath = $this->basePath . '/www';

        $localPlaylistPath = $this->playlistPath;

        $latestPlaylist = [
            ['id' => '4607df0c49990dc506fbfb36b74b9802', 'url' => 'http://ads.58.my/medias/4607df0c49990dc506fbfb36b74b9802.mp4'],
            ['id' => '76066d118532f2b23a56bdbc6436f912', 'url' => 'http://ads.58.my/medias/76066d118532f2b23a56bdbc6436f912.mp4'],
            ['id' => '7440809846c078442f1074959c1085d0', 'url' => 'http://ads.58.my/medias/7440809846c078442f1074959c1085d0.mp4']
        ];

        $apiPath = $this->getConfig('host', 'https://qmed.asia') . '/api/installation/' . $this->getConfig('installation_id') . '/ads_playlist';

        $latestPlaylist = json_decode(file_get_contents($apiPath), true);

        $localPlaylist = @file_get_contents($localPlaylistPath);

        $localPlaylist = $localPlaylist ? json_decode($localPlaylist, true)['playlist'] : [];

        // delete non-existent
        foreach ($localPlaylist as $media) {
            $id = $media['id'];

            if (!$this->mediaExists($localPlaylist, $id)) {
                unlink($this->basePath . '/www/' . $media['filename']);
                $this->removeMedia($localPlaylist, $id);
            }
        }

        $this->initializeSync($localPlaylist, $latestPlaylist);

        // from latest, check
        foreach ($latestPlaylist as $media) {
            $id = $media['id'];

            $segments = explode('/', $media['url']);
            $filename = $segments[count($segments) - 1];

            if ($this->mediaExists($localPlaylist, $id)) {
                continue;
            }

            if (file_exists($localVideosPath . '/' . $filename)) {
                $localPlaylist[] = [
                    'id' => $id,
                    'filename' => $filename
                ];

                $this->updatePlaylist($localPlaylist);
                continue;
            }

            // check size
            if (remoteCheckSize($media['url']) > static::MAX_VIDEO_SIZE) {
                $this->insertMapValue('skipped', $media['id']);
                continue;
            }

            $this->currentDownload = $media;

            if (file_put_contents($localVideosPath . '/' . $filename, file_get_contents($media['url']))) {
                $localPlaylist[] = [
                    'id' => $id,
                    'filename' => $filename
                ];
            }

            $this->currentDownload = null;

            $output->writeln('Downloaded ' . $media['url']);

            $this->updatePlaylist($localPlaylist);
        }

        $this->updateMapValue('sync', null);

        return 0;
    }

    protected function isSyncing()
    {
        $sync = $this->getMapValue('sync');

        if ($sync !== null)
            return true;

        return false;
    }

    protected function initializeSync($local, $latest)
    {
        $total = 0;

        // compare
        foreach ($latest as $media) {
            if (!$this->mediaExists($local, $media['id']))
                $total++;
        }

        $this->updateMapValue('sync', [
            'total' => $total,
            'completed' => 0
        ]);
    }

    protected function updatePlaylist(array $playlist)
    {
        $this->updateMapValue('playlist', $playlist);

        $sync = $this->getMapValue('sync');

        $sync['completed'] = count($playlist);

        $this->updateMapValue('sync', $sync);
    }

    protected function getMapValue($key, $default = null)
    {
        $map = file_exists($this->playlistPath) ? json_decode(file_get_contents($this->playlistPath), true) : [];

        if (!isset($map[$key]))
            return $default;

        return $map[$key];
    }

    protected function updateMapValue($key, $data)
    {
        $map = file_exists($this->playlistPath) ? json_decode(file_get_contents($this->playlistPath), true) : [];

        $map[$key] = $data;

        file_put_contents($this->playlistPath, json_encode($map, JSON_PRETTY_PRINT));
    }

    protected function insertMapValue($key, $data)
    {
        $map = file_exists($this->playlistPath) ? json_decode(file_get_contents($this->playlistPath), true) : [];

        if (!isset($map[$key]))
            $map[$key] = [];

        if (!in_array($data, $map[$key])) {
            $map[$key][] = $data;
            file_put_contents($this->playlistPath, json_encode($map, JSON_PRETTY_PRINT));
        }
    }

    protected function removeMedia(&$playlist, $id)
    {
        foreach ($playlist as $index => $media) {
            if ($id == $media['id'])
                array_splice($playlist, $index);
        }

        return $playlist;
    }

    protected function mediaExists($playlist, $id)
    {
        foreach ($playlist as $media) {
            if ($id == $media['id'])
                return true;
        }

        return false;
    }
}