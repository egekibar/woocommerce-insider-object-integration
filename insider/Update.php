<?php

namespace Insider;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Update
{
    public function config()
    {
        $cacheFile = dirname(__DIR__, 1)."/update_cache";

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $body = file_get_contents($cacheFile);
        } else {

            $github_repo_url = 'https://api.github.com/repos/egekibar/woocommerce-insider-object-plugin/releases/latest';

            $response = wp_safe_remote_get($github_repo_url, array(
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json',
                ),
            ));

            $body = wp_remote_retrieve_body($response);

            if ($response['response']['code'] == 200)
                file_put_contents($cacheFile, $body);
        }

        $json_data = json_decode($body, true);

        $zipFilePath = dirname(__DIR__, 1)."/update/{$json_data['tag_name']}.zip";

        if (!file_exists($zipFilePath)){
            $temp_dir = dirname(__DIR__, 1)."/temp";

            if (!is_dir($temp_dir))
                mkdir($temp_dir, 0755, true);

            $ch = curl_init($json_data['zipball_url']);
            $fp = fopen($temp_dir.'/temp.zip', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MyCustomUserAgent/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            $zip = new ZipArchive;
            $res = $zip->open($temp_dir.'/temp.zip');
            if ($res === TRUE) {
                $zip->extractTo($temp_dir);
                $zip->close();

                rename(glob($temp_dir."/*", GLOB_ONLYDIR)[0], $temp_dir."/woocommerce-insider-object-plugin");

                $sourceDir = glob($temp_dir."/*", GLOB_ONLYDIR)[0];

                if (!is_dir(dirname(__DIR__, 1)."/update"))
                    mkdir(dirname(__DIR__, 1)."/update", 0755, true);

                $zip = new ZipArchive();

                if ($zip->open($zipFilePath, ZipArchive::CREATE) !== true) {
                    die('Failed to create zip file');
                }

                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($sourceDir) + 1);

                        $zip->addFile($filePath, "woocommerce-insider-object-plugin/".$relativePath);
                    }
                }

                $zip->close();

                $it = new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if ($file->isLink()) {
                        unlink($file->getPathname());
                    } else if ($file->isDir()){
                        rmdir($file->getPathname());
                    } else {
                        unlink($file->getPathname());
                    }
                }

                rmdir($temp_dir);
            }
        }

        return (object) array(
            'new_version' => $json_data['tag_name'],
            'package'     => plugins_url("woocommerce-insider-object-plugin/update/{$json_data['tag_name']}.zip"),
            'url'         => '',
        );
    }
}