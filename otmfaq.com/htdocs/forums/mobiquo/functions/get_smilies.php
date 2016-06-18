<?php

define('IN_MOBIQUO', true);

require_once('./global.php');

function get_smilies_func()
{
    global $db;
    
    $result = $db->query_read_slave("
        SELECT smilietext AS text, smiliepath AS path, smilie.title, smilieid,
            imagecategory.title AS category
        FROM " . TABLE_PREFIX . "smilie AS smilie
        LEFT JOIN " . TABLE_PREFIX . "imagecategory AS imagecategory USING(imagecategoryid)
        ORDER BY imagecategory.displayorder, imagecategory.title, smilie.displayorder
    ");

    $categories = array();
    while ($smilie = $db->fetch_array($result))
    {
        $categories[$smilie['category']][] = $smilie;
    }
    
    $categories_xmlrpc = array();
    foreach ($categories as $cname => $category)
    {
        $smilies_xmlrpc = array();
        foreach ($category as $smiley)
        {
            $smiley_xmlrpc = new xmlrpcval(array(
                'code'  => new xmlrpcval(mobiquo_encode($smiley['text']), 'base64'),
                'url'   => new xmlrpcval($smiley['path'], 'string'),
                'title' => new xmlrpcval(mobiquo_encode($smiley['title']), 'base64'),
            ), 'struct');
            
            $smilies_xmlrpc[] = $smiley_xmlrpc;
        }
        
        $categories_xmlrpc[$cname] = new xmlrpcval($smilies_xmlrpc, 'array');
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'list' => new xmlrpcval($categories_xmlrpc, 'struct'),
    ), 'struct'));
}