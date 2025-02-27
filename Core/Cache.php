<?php

namespace Core;

final class Cache {

    private const PATH = "Cache/"; 
    private const CACHE_EXTENSION = ".cache"; 
	private const DEFAULT_CACHE_EXPIRATION = "4";

    private function __construct(){}

    public static function getCache($cache_id)
    {
        $cache_file = self::getCachePath().$cache_id.self::CACHE_EXTENSION; 

        if (file_exists($cache_file)){
            $content = file_get_contents($cache_file); 
            $content = unserialize($content); 
            return $content; 
        } else
            return false; 
    }

    public static function setCache($cache_id, $body) 
    {
        $cache_file = self::getCachePath().$cache_id.self::CACHE_EXTENSION; 

        $body = serialize($body); 

        try {
            $file_opened = fopen($cache_file, 'w'); 
            fwrite($file_opened, $body);
            fclose($file_opened); 	
        } catch (\Exception $e) {
            return false; 
        }

        return true; 
    }

    private static function getCachePath()
    {
        $dir = new \DirectoryIterator(Functions::getRoot().self::PATH);
        $current_dir = ""; 

        foreach($dir as $file) {
            if (!$dir->isDot() && $dir->isDir()) {
                
                $dir_name = $file->getFilename(); 

                $time = explode('_', $dir_name); 
                $time[1] = str_replace('-', ':', $time[1]); 
                $limit_datetime = implode(' ', $time); 
                
                $data1 = $limit_datetime;
                $data2 = date('Y-m-d H:i:s');

                $unix_data1 = strtotime($data1);
                $unix_data2 = strtotime($data2);

                $intervalo = ($unix_data2 - $unix_data1) / 3600;

                if (intval($intervalo) > self::DEFAULT_CACHE_EXPIRATION) {
                    self::removeDirectory(Functions::getRoot().self::PATH.$dir_name); 
                } else {
                    $current_dir = Functions::getRoot().self::PATH.$dir_name."/"; 
                }
            }
        }

        if (empty($current_dir)) {
            $t = date('Y-m-d_H-i-s'); 
            mkdir(Functions::getRoot().self::PATH.$t."/", 0777);
            $current_dir = Functions::getRoot().self::PATH.$t."/"; 
        }

        return $current_dir; 	        	
    }
	
    private static function removeDirectory($path) 
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::removeDirectory($file) : unlink($file);
        }
    
        rmdir($path);
        return;
    }

}
