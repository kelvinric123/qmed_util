<?php

namespace Rasque\Commands;

use Rasque\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdsSyncCommand extends BaseCommand
{
    const MAX_VIDEO_SIZE = 200000000;

    protected $playlistPath;

    /**
     * @var null|array
     */
    protected $currentDownload = null;

    protected $videosPath = '/var/www/html/ads';

    public function configure()
    {
        $this->setName('sync');

//        $this->playlistPath = $this->basePath . '/www/playlist-map.json';

        $this->videosPath = $this->basePath . '/www/ads';

        $this->playlistPath = $this->videosPath . '/playlist-map.json';
    }

    protected function getSize($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    /**
     * Check whether the playlist is modified
     */
    protected function needSync($maxSize = 0)
    {
        $ids = [];

        if (!file_exists($this->playlistPath))
            return true;

        foreach (json_decode(file_get_contents($this->playlistPath), true)['playlist'] as $media) {
            $ids[] = $media['id'];
        }

        $localHash = md5(implode('', $ids));

        $liveHash = json_decode(file_get_contents($this->getScreenApiPath() . '/ads/playlist/hash?max_size=' . $maxSize), true)['data'];

        return $localHash != $liveHash;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isSyncing())
            return $this->write($output, 'Another synchronization process is running!');

        // TODO to remove this debug code later
        $maxSize = $this->getConfig('max_size', static::MAX_VIDEO_SIZE);
        
        if (!$this->needSync($maxSize))
            return $this->write($output, 'Playlist hasn\'t changed.');

        $command = $this;

        register_shutdown_function(function() use ($command) {
            $error = error_get_last();

            if ($error['type'] != E_ERROR)
                return;

            if (!$command->currentDownload)
                return;

            $command->insertMapValue('skipped', $command->currentDownload['id']);
        });

        $syncId = time();

        Logger::instance()->log('SYNC_STARTED_' . $syncId);

        set_time_limit(0);

        ini_set('memory_limit', '1300M');

//        $localVideosPath = $this->basePath . '/www';
        $localVideosPath = $this->videosPath;

//        $apiPath = $this->getConfig('host', 'https://qmed.asia') . '/apis/installation/' . $this->getConfig('installation_id') . '/ads_playlist';

        $apiPath = $this->getScreenApiPath() . '/ads/playlist';

        $latestContent = @file_get_contents($apiPath);

        if (!$latestContent)
            return $this->write($output, 'No internet!');

        $latestPlaylist = json_decode($latestContent, true)['data'];

        $localPlaylist = @file_get_contents($this->playlistPath);

        $localPlaylist = $localPlaylist ? json_decode($localPlaylist, true)['playlist'] : [];

        // delete non-existent
        foreach ($localPlaylist as $media) {
            $id = $media['id'];

            if (!$this->mediaExists($latestPlaylist, $id)) {
                unlink($this->videosPath . '/' . $media['filename']);
                $this->removeMedia($localPlaylist, $id);
                $output->writeln('Removed ' . $media['filename']);
            }
        }

        $this->initializeSync($localPlaylist, $latestPlaylist);

        // from latest, check
        $totalDownloaded = 0;

        $totalSize = 0;

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
            /*if ($this->getSize($media['url']) > $maxSize) {
                $this->insertMapValue('skipped', $media['id']);
                continue;
            }*/

            $this->currentDownload = $media;
            
            $output->writeLn('Downloading.. ' . $media['url']);

            if (file_put_contents($localVideosPath . '/' . $filename, file_get_contents($media['url']))) {
                $totalDownloaded++;

                $totalSize += $this->getSize($media['url']);

                $localPlaylist[] = [
                    'id' => $id,
                    'filename' => $filename
                ];
            }

            $this->currentDownload = null;

            $output->writeln('Downloaded ' . $media['url']);

            $this->updatePlaylist($localPlaylist);
        }
        
        Logger::instance()->log('SYNC_FINISHED_' . $syncId, [
                'total_download' => $totalDownloaded,
                'total_size' => $totalSize,
                'time_taken' => time() - $syncId
            ]);

        $this->recorrectOrdering($localPlaylist, $latestPlaylist);

        $this->updateMapValue('sync', null);

        return 0;
    }

    protected function recorrectOrdering($local, $latest)
    {
        $playlist = [];

        foreach ($latest as $media) {
            foreach ($local as $med) {
                if ($med['id'] == $media['id'])
                    $playlist[] = $med;
            }
        }

        $this->updatePlaylist($playlist);
    }

    protected function isSyncing()
    {
        // $running = shell_exec("ps auxww|grep syncer.php|grep -v grep|wc -l");
        $running = shell_exec("ps auxww|grep syncer.php");
        
        
        $process = 0;
        
        foreach (explode("\n", $running) as $line) {
            if (strpos($line, 'syncer.php') === false)
                continue;
                
            if (strpos($line, 'bin/sh -c') !== false)
                continue;
                
            if (strpos($line, 'auxww') !== false)
                continue;
                
            if (strpos($line, 'grep') !== false)
                continue;
                
            $process++;
        }
        
//        file_put_contents('isrunning', $process);
        
        return $process > 1;
        
        file_put_contents('isrunning', $running);
        
        file_put_contents('psaux', shell_exec("ps auxww"));
        
        //echo "ps aux|grep syncer.php|grep -v grep|wc -l";
        
        if ((int) $running > 1)
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

    public function getMapValue($key, $default = null)
    {
        $map = file_exists($this->playlistPath) ? json_decode(file_get_contents($this->playlistPath), true) : [];

        if (!isset($map[$key]))
            return $default;

        return $map[$key];
    }

    public function updateMapValue($key, $data)
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
