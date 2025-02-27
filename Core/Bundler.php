<?php

namespace Core;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

class Bundler
{
    public static function build(array $config){

        if(!file_exists(Functions::getRoot()."public/assets/css")){
            mkdir(Functions::getRoot()."public/assets/css",755);
        }

        if(!file_exists(Functions::getRoot()."public/assets/js")){
            mkdir(Functions::getRoot()."public/assets/js",755);
        }

        self::deleteFiles(Functions::getRoot()."public/assets/css");
        self::deleteFiles(Functions::getRoot()."public/assets/js");

        $configCss = isset($config["css"])?$config["css"]:[];
       
        foreach ($configCss as $key => $files){
            $minifier = new CSS();

            self::getFiles($minifier,Functions::getRoot()."Resources/Css",$files);

            $minifier->minify(Functions::getRoot()."public/assets/css/".$key."_".Functions::genereteId().".css");
        }
        
        $minifier = new CSS();

        self::getFiles($minifier,Functions::getRoot()."Resources/Css");

        $minifier->minify(Functions::getRoot()."public/assets/css/"."ALL_".Functions::genereteId().".css");

        print_r(scandir(Functions::getRoot()."public/assets/css"));
        echo PHP_EOL;

        $configJs = isset($config["js"])?$config["js"]:[];
        
        foreach ($configJs as $key => $files){
            $minifier = new JS();

            self::getFiles($minifier,Functions::getRoot()."Resources/Js",$files);

            $minifier->minify(Functions::getRoot()."public/assets/js/".$key."_".Functions::genereteId().".js");
        }
        
        $minifier = new JS();

        self::getFiles($minifier,Functions::getRoot()."Resources/Js");

        $minifier->minify(Functions::getRoot()."public/assets/js/"."ALL_".Functions::genereteId().".js");
        
        print_r(scandir(Functions::getRoot()."public/assets/js"));
        echo PHP_EOL;
    }

    public static function getCssFile($key = "ALL"):string
    {
        $files = scandir(Functions::getRoot()."public/assets/css");

        foreach ($files as $file){
            if(str_contains($file,$key))
                return $file;
        }

        return isset($files[2])?$files[2]:"";
    }

    public static function getJsFile($key = "ALL"):string
    {
        $files = scandir(Functions::getRoot()."public/assets/js");

        foreach ($files as $file){
            if(str_contains($file,$key))
                return $file;
        }

        return isset($files[2])?$files[2]:"";
    }

    private static function deleteFiles(string $path){
        
        $files = scandir($path);
        foreach ($files as $file){
            
            if($file == "." || $file == ".."){
                continue;
            }

            if(!is_dir($path.DIRECTORY_SEPARATOR.$file))
                unlink($path.DIRECTORY_SEPARATOR.$file);
        }
    }

    private static function getFiles(CSS|JS &$minifier,string $path,array $filesConfig = []){

        $files = scandir($path);

        foreach ($files as $file){
            
            if($file == "." || $file == ".."){
                continue;
            }

            if(!str_contains($file,'.js') && !str_contains($file,'.css')){
                continue;
            }

            if($filesConfig && !in_array($file,$filesConfig)){
                continue;
            }

            if(is_dir($path.DIRECTORY_SEPARATOR.$file)){
                self::getFiles($minifier,$path.DIRECTORY_SEPARATOR.$file);
            }

            if(!is_dir($path.DIRECTORY_SEPARATOR.$file)){
                $minifier->add($path.DIRECTORY_SEPARATOR.$file);
            }
        }
    }
}
