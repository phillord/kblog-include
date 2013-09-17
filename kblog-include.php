<?php

/*
  Plugin Name: Kblog Include
  Description: Include from various places
  Version: 0.1
  Author: Phillip Lord
  Author URI: http://knowledgeblog.org
  Email: knowledgeblog@googlegroups.com
*/


/*
 * The contents of this file are subject to the GPL License, Version 3.0.
 *
 * Copyright (C) 2013, Phillip Lord, Newcastle University
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class kblog_include{
    public $server = array();

    function __construct(){
        add_shortcode( 'kblog-inc', array($this, 'kblog_inc'));
    }

    // get this to do a call back to something!
    function kblog_inc($atts,$content){
        global $kblog_oai_pmh;

        if(array_key_exists($atts['server'],$this->server)){
            $server=$atts['server'];
            $type=$this->server[$server]['type'];
            $callback=$this->server[$server]['callback'];

            if($type=="oai"){
                $location=$this->server[$server]['location'];
                $prefix=$this->server[$server]['prefix'];
                $metadata =
                    $kblog_oai_pmh->parse_for_metadata
                    ($kblog_oai_pmh->retrieve_oai2
                     ($location,$prefix, urlencode( $content )));
                if($metadata->exception){
                    //return "There was an exception parsing the record:" .
                    //    $content;
                    return "Exception!!!:" . $metadata->exception .
                        "for data:" . $metadata->xml . ":data ends";
                }
                return call_user_func( $callback, $metadata );
            }

            if($type=="greycite"){
                return call_user_func($callback,$content);
            }
        }
        return "<strong>Server not known:{$atts['server']}</strong>";
    }

    function set_post_title_maybe($title){
        if(get_the_title() != $title){
            $post =
                array( 'ID'=>get_the_id(),
                       'post_title' => $title);
            wp_update_post($post);
        }
    }
}


global $kblog_include;
$kblog_include = new kblog_include();

function kblog_include_add_server($servername,$config){
    global $kblog_include;
    $kblog_include->server[$servername]= $config;
}


function kblog_include_add_oai_server($servername,$location,$prefix,$callback){
    kblog_include_add_server($servername,
                             array("location"=>$location,
                                   "prefix"=>$prefix,
                                   "callback"=>$callback,
                                   "type"=>"oai"));
}

require_once(dirname(__FILE__) . "/kblog-oai-pmh.php");
require_once(dirname(__FILE__) . "/kblog-server.php");
require_once(dirname(__FILE__) . "/kblog-clean.php");
require_once(dirname(__FILE__) . "/kblog-include-cache.php");

?>
