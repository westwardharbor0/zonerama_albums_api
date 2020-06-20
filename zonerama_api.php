
<?php 
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
    echo json_encode($res);

?>
