<?php 

namespace App\Services;



class UrlServices
{
    public function getMainlUrl()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "https") . "://" . $_SERVER['HTTP_HOST'];
    }


    public function rewriteUrl($correctedUrl)
    {
        function Redirect($url, $permanent = false)
                {
                    header('Location: ' . $url, true, $permanent ? 301 : 302);
                    exit();
                }
                Redirect($correctedUrl, false);
        
    }
    
}