<?php


function kblog_clean_author_last_comma_first($author){
    $namecomps = explode(",",$author);
    return "$namecomps[1] $namecomps[0]";
}

function kblog_clean_author_detitleify($author){
    $titles = array("Dr", "Professor", "Doctor");

    foreach($titles as $title){
        if (substr($author, 0, strlen($title)) == $title) {
            $author = substr($author, strlen($title), strlen($author));
            // check them all, as titles can stack
        }
    }
    return $author;
}

function kblog_clean_doi_url($identifiers){
    $retn = array();
    foreach($identifiers as $identifier){
        if(substr($identifier,0,4)=="doi:"){
            $retn[] = "http://dx.doi.org/" .
                substr($identifier,4,strlen($identifier));
        }
        else{
            $retn[] = $identifier;
        }
    }
    return $retn;
}

/*
 * Takes an array with some of year, month, day components.
 * If year is missing, return false.
 * If month or day is missing, pick earliest.
 */
function kblog_clean_year_month_day_array($date){
    $retn = array();
    if( !array_key_exists(0,$date)){
        return false;
    }
    $retn[0]=$date[0];
    if( !array_key_exists(1,$date)){
        $retn[1]=1;
    }
    else{
        $retn[1]=$date[1];
    }

    if( !array_key_exists(2,$date)){
        $retn[2]=1;
    }
    else{
        $retn[2]=$date[2];
    }
    return $retn;
}

function kblog_clean_concat_array_date($date){
    return sprintf("%04d-%02d-%02d", $date[0],$date[1],$date[2]);
}

?>