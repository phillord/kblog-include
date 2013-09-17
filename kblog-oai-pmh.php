<?php

class kblog_oai_pmh{

    public function retrieve_oai2($harvest,$prefix,$identifier){
        $url =
            "$harvest?verb=GetRecord&metadataPrefix=oai_dc" .
            "&identifier=$prefix$identifier";
        $cached_version = kblog_include_fetch_cache($url);
        if($cached_version){
            return $cached_version;
        }

        $wpresponse = wp_remote_get( $url );

        // add oai-pmn error support
        if(is_wp_error( $wpresponse )){
            return wp_remote_retrieve_response_code($wpresponse);
        }

        // cache for a day
        $response=wp_remote_retrieve_body($wpresponse);
        kblog_include_add_to_cache($url,$response);
        return wp_remote_retrieve_body($wpresponse);
    }
    /*
      Returns an array with keys:
    */
    public function parse_for_metadata($xml){
        $metadata = new kblog_oai_metadata();
        $metadata->xml = $xml;
        try{
            $parsed = new SimpleXMLElement($xml);

            $parsed->registerXpathNamespace
                ( "dc", "http://purl.org/dc/elements/1.1/" );
            $parsed->registerXpathNamespace
                ( "oai", "http://www.openarchives.org/OAI/2.0/" );
            $parsed->registerXpathNamespace
                ( "oai_dc","http://www.openarchives.org/OAI/2.0/oai_dc/" );

            $description = $parsed->xpath("//dc:description");
            if (!empty($description)){
                $metadata->description  =  $description;
            }

            $creator = $parsed->xpath("//dc:creator");
            if(!empty($creator)){
                $metadata->creator = $creator;
            }

            $subject = $parsed->xpath("//dc:subject");
            if(!empty($subject)){
                $metadata->subject = $subject;
            }

            $title = $parsed->xpath( "//dc:title" );
            if(!empty($title)){
                $metadata->title = $title;
            }

            $date = $parsed->xpath("//dc:date");
            if(!empty($date)){
                $metadata->date=$date;
            }

            $type = $parsed->xpath("//dc:type");
            if(!empty($type)){
                $metadata->type=$type;
            }

            $identifier = $parsed->xpath("//dc:identifier");
            if(!empty($identifier)){
                $metadata->identifier=$identifier;
            }
        }
        catch( Exception $exp ){
            $metadata->exception = $exp;
        }

        return $metadata;
    }
}

class kblog_oai_metadata{
    public $source;
    public $oai_identifier;

    public $exception;
    public $title;
    public $creator=array();
    public $subject=array();
    public $description=array();
    public $date;
    public $type;
    public $identifier;
}

global $kblog_oai_pmh;

$kblog_oai_pmh = new kblog_oai_pmh();


?>