<?php
class kblog_server{

    function __construct(){
        kblog_include_add_oai_server("arxiv","http://arXiv.org/oai2",
                                     "oai:arXiv.org:",
                                     array( $this, "arxiv"));

        kblog_include_add_oai_server("eprint.ncl.ac.uk",
                                     "http://eprint.ncl.ac.uk:9003",
                                     "oai:eprint.ncl.ac.uk:",
                                     array($this,"ncl"));

        kblog_include_add_server("greycite",
                                 array("callback"=>
                                       array($this,"greycite"),
                                       "type"=>"greycite"));
    }

    function fetch_greycite($url){
        $url = "http://greycite.knowledgeblog.org/json?uri=" . $url;
        $response = kblog_include_fetch_cache($url);
        if( $response ){
            return $response;
        }

        $wpresponse = wp_remote_get( $url );
        if( is_wp_error( $wpresponse ) ){
            return wp_remote_retrieve_response_code($wpresponse);
        }
        $response = wp_remote_retrieve_body($wpresponse);
        kblog_include_add_to_cache($url,$response);
        return $response;
    }

    function greycite($url){
        $response = $this->fetch_greycite($url);
        $json = json_decode($response,true);
        $authors=array();
        $retn ="";
        foreach($json["author"] as $author){
            $authors[]=$author["given"] . " " .
                $author["family"];
        }

        $issued = $json["issued"]["date-parts"][0];
        $date =
            kblog_clean_concat_array_date
            (kblog_clean_year_month_day_array($issued));
        $this->do_date_shortcode($date);
        $this->do_author_shortcode($authors);
        $retn .= $this->ul_list_maybe($authors);

        $retn .= "<a href=\"".  $json["URL"] .
            "\">"  . $json["URL"] . "</a>";

        if($json["title"]){
            global $kblog_include;
            $kblog_include->set_post_title_maybe($json["title"]);
        }

        return $retn;
    }


    function ncl($metadata){
        $retn = "";
        $this->set_post_title_maybe($metadata);
        $authors = $this->clean_authors
            ("kblog_clean_author_detitleify",
             $metadata->creator);
        $this->do_author_shortcode($authors);
        // ncl dates are totally erratic
        $retn .= $this->ul_list_maybe($authors);

        $identifiers = $this->hyperlinkify_list($metadata->identifier);
        $retn .= $this->ul_list_maybe($identifiers);
        return $retn;
    }

    function arxiv($metadata){
        $retn = "";
        $this->set_post_title_maybe($metadata);
        if(array_key_exists(0,$metadata->description)){
            $retn .= "<p>{$metadata->description[0]}</p>";
        }

        $authors
            = $this->clean_authors("kblog_clean_author_last_comma_first",
                                   $metadata->creator);

        $this->do_author_shortcode($authors);
        $this->do_date_shortcode($metadata->date[0]);
        $retn .= $this->ul_list_maybe($authors);
        //TODO need doiify
        $identifiers = kblog_clean_doi_url($metadata->identifier);
        $identifiers = $this->hyperlinkify_list($identifiers);
        $retn .= $this->ul_list_maybe($identifiers);
        return $retn;
    }

    function hyperlinkify_list($items){
        $retn = array();
        if(count($items)>0){
            foreach($items as $item){
                $retn[]="<a href=\"$item\">$item</a>";
            }
        }
        return $retn;
    }

    function ul_list_maybe($items){
        $retn="";
        if(count($items)>0){
            $retn.="<ul>";
            foreach($items as $item){
                $retn .= "<li>$item</li>";
            }
            $retn.="</ul>";
        }
        return $retn;
    }

    function clean_authors($callback,$authors){
        $retn = array();
        if(count($authors)>0){
            foreach($authors as $author){
                $retn[] = call_user_func($callback,$author);
            }
        }
        return $retn;
    }
    function do_date_shortcode($date){
        do_shortcode("[date]{$date}[/date]");
    }

    function do_author_shortcode($authors){
        foreach($authors as $author){
            do_shortcode("[author]{$author}[/author]");
        }
    }

    function set_post_title_maybe($metadata){
        if(array_key_exists(0,$metadata->title)){
            $this->set_post_title_maybe($metadata->title[0]);
            global $kblog_include;
            $kblog_include->set_post_title_maybe($metadata->title[0]);
        }
    }
}

global $kblog_server;
$kblog_server = new kblog_server();

?>
