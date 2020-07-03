<?php 
    // ------------------------------------ //
    //    The mighty Zoneramatorapitorus    //
    //    copyright westwardharbor0         //
    // ------------------------------------ //

    // removes . and .. rom scandir  --- mostly useless
    function filterDot($arr){
        $res = array();
        foreach($arr as $ar){
            if($ar != "." && $ar != ".."){
                array_push($res, $ar);
            }
        }
        return $res;
    }

    // removes all expired caches
    function emptyFolder($folder){
        $files = filterDot(scandir($folder));
        foreach($files as $file){
          if(is_file($folder.$file))
            unlink($folder.$file);
        }
    }

    // generates name for cache file
    function cache_name(){
        return "./cache/" . date('d_m_Y') . ".cache";
    }
    
    // checks if there is some cache already stored
    function is_cache(){
        if(file_exists(cache_name())){
            return true;
        }
        return false;
    }
    
    // saves the content to cache
    function save_cache($content){
        emptyFolder("./cache/");
        file_put_contents(cache_name(), $content);
    }
    
    // loads the content from cache
    function load_cache(){
        return file_get_contents(cache_name());
    }
    
    // checking if we have cache
    if(is_cache()){
        // if we have we load from there
        echo load_cache();
        exit();
    }
    
    
    // if we dont have cache or its expired we generate new content 
    // cache is stored for one day 
    $urlpostfix = $_GET["album"]; // Username/Account_ID
    $html = file_get_contents('https://www.zonerama.com/'.$urlpostfix);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
    $start_substr = "var result =";
    $end_substr = '"isEnd":true}';
    $start = strpos($html, $start_substr);
    $end = strpos($html, $end_substr);
    $len = strlen($html);
  
    $json_str = substr($html, $start + strlen($start_substr), ($end - $start) + 1);
    $json_obj = json_decode(utf8_encode($json_str));
    
    $res = array();
    
    foreach($json_obj->items as $key => $val){
        if(!strpos($val->html, "data-url=")){
            continue;
        }
        
        $dom = new DomDocument();
        $dom->loadHTML($val->html);
        $height = $val->height;
        $width  = $val->width;
        
        foreach($dom->getElementsByTagName('a') as $link) {
            $img = $link->getElementsByTagName('img');
            $thumbnail = $img[0]->getAttribute("data-pattern");
            $thumbnail_url = str_replace("&topStrip=True", "", str_replace("{height}", $height, str_replace("{width}", $width, $thumbnail)));
            $url  = $link->getAttribute('href');
            $name = $link->getAttribute('title');
            $item = array(
                "height"    => $height,
                "width"     => $width,
                "thumbnail" => $thumbnail_url,
                "name"      => $name,
                "url"       => $url, 
            );
            array_push($res, $item);
        }
    }
    $res = json_encode($res);
    // save generated response to cache
    save_cache($res);
    echo $res;
?>
