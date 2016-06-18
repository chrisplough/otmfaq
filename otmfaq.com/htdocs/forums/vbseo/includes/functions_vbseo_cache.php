<?php

/************************************************************************************
* vBSEO 3.6.0 for vBulletin v3.x & v4.x by Crawlability, Inc.                       *
*                                                                                   *
* Copyright © 2011, Crawlability, Inc. All rights reserved.                         *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

function vbseo_cache_start()
{
global $vbseo_cache;
if (!$vbseo_cache)
$vbseo_cache = new vbseoCache();
}
class vbseoCache
{
var $cache_type = 0;
var $mchandle = 0;
var $mcconnected = false;
var $thestorage = false;
function vbseoCache()
{
$this->cache_type = VBSEO_CACHE_TYPE;
switch ($this->cache_type)
{
case 1: 
if (!class_exists('Memcache'))
$this->cache_type = 0;
else
$this->mchandle = new Memcache();
break;
case 2: 
if (!function_exists('apc_fetch'))
$this->cache_type = 0;
break;
case 3: 
if (!function_exists('xcache_get'))
$this->cache_type = 0;
break;
case 4: 
if (!function_exists('eaccelerator_get'))
$this->cache_type = 0;
break;
}
}
function connect()
{
if ($this->cache_type == 1 && !$this->mcconnected && VBSEO_MEMCACHE_HOSTS)
{
$hostlines = preg_split('#[\r\n]+#', VBSEO_MEMCACHE_HOSTS);
foreach($hostlines as $hl)
{
list($p1, $weight) = explode(',', $hl);
list($mhost, $mport) = explode(':', $p1);
$this->mchandle->AddServer($mhost, $mport, VBSEO_MEMCACHE_PERS,
$weight, VBSEO_MEMCACHE_TIMEOUT, VBSEO_MEMCACHE_RETRY);
}
$this->mcconnected = true;
}
return $this->mcconnected;
}
function cacheget($name)
{
if (!$this->cache_type)return;
$rdata2 = false;
if (!$this->thestorage)
{
switch ($this->cache_type)
{
case 1: 
if ($this->connect())
$rdata = $this->mchandle->get(VBSEO_CACHE_VAR);
break;
case 2: 
$rdata = apc_fetch(VBSEO_CACHE_VAR);
break;
case 3: 
if (xcache_isset(VBSEO_CACHE_VAR))
$rdata = xcache_get(VBSEO_CACHE_VAR);
break;
case 4: 
$rdata = eaccelerator_get(VBSEO_CACHE_VAR);
break;
}
if ($rdata)
$this->thestorage = unserialize($rdata);
if(!is_array($this->thestorage))
$this->thestorage = array();
}
$rdata2 = $this->thestorage[$name];
return $rdata2;
}
function cachereset()
{
if (!$this->cache_type)return;
switch ($this->cache_type)
{
case 1: 
if ($this->connect())
$rdata = $this->mchandle->delete(VBSEO_CACHE_VAR);
break;
case 2: 
apc_delete(VBSEO_CACHE_VAR);
break;
case 3: 
$rdata = xcache_unset(VBSEO_CACHE_VAR);
break;
case 4: 
$rdata = eaccelerator_rm(VBSEO_CACHE_VAR);
break;
}
return $rdata;
}
function cacheset($name, $value)
{
if (!$this->cache_type)return;
if (!$this->thestorage)
$this->cacheget($name);
if ($this->thestorage[$name] == $value)
return;
$this->thestorage[$name] = $value;
$value = serialize($this->thestorage);
switch ($this->cache_type)
{
case 1: 
if ($this->connect())
$this->mchandle->set(VBSEO_CACHE_VAR, $value, 0, VBSEO_MEMCACHE_TTL);
break;
case 2: 
apc_delete(VBSEO_CACHE_VAR);
apc_store(VBSEO_CACHE_VAR, $value, VBSEO_MEMCACHE_TTL);
break;
case 3: 
xcache_set(VBSEO_CACHE_VAR, $value, VBSEO_MEMCACHE_TTL);
break;
case 4: 
if (eaccelerator_lock(VBSEO_CACHE_VAR))
{
eaccelerator_rm(VBSEO_CACHE_VAR);
eaccelerator_put(VBSEO_CACHE_VAR, $value);
eaccelerator_unlock(VBSEO_CACHE_VAR);
}
break;
}
}
function close()
{
if ($this->mcconnected)
$this->mchandle->close();
}
}
?>