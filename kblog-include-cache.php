<?php

class kblog_include_cache{

    private $kblog_cache_slug = "kblog_include_cache";

    // store a cached version. In case of a mismatch, we ignore cache.
    //(progn (forward-line)(end-of-line)(zap-to-char -1 ?=)(insert "= " (number-to-string (float-time)))(insert ";"))
    private $kblog_cache_version = 1364991848.48531;
    function fetch_cache($url){
        $transient_slug = $this->slug($url);
        $cached_version = get_option($transient_slug);
        if($cached_version["kblog_cache_version"]==$this->kblog_cache_version &&
           intval($cached_version["expire"]) > time()
           ){
            return $cached_version["content"];
        }
        return false;
    }
    function add_to_cache($url,$content){
        $cached = array( "content"=>$content,
                         "expire"=>time() + 60*60*24,
                         "kblog_cache_version"=>$this->kblog_cache_version
                         );
        $transient_slug = $this->slug($url);
        delete_option($transient_slug);
        add_option($transient_slug,$cached,'','no');
    }

    function slug($url){
        return crc32( $this->kblog_cache_slug . $url );
    }
}

global $kblog_include_cache;
$kblog_include_cache = new kblog_include_cache();

function kblog_include_fetch_cache($url){
    global $kblog_include_cache;
    return $kblog_include_cache->fetch_cache($url);
}

function kblog_include_add_to_cache($url,$content){
    global $kblog_include_cache;
    return $kblog_include_cache->add_to_cache($url,$content);
}
?>