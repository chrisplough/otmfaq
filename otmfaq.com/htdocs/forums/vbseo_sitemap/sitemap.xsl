<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
  version="1.0"
  xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:template match="/">
        <html>
            <head>
                <title>vBSEO Search Engine XML Sitemap</title>
                <style type="text/css">
                body {
                        background-color: #e0e0e0;
                        font: normal 85%  "Tahoma", sans-serif;
                        margin:0;
                        text-align:center;
                        
                }
                .content {
                        border: 1px solid #b5b5b5;
                        margin: 10px auto;
                        width:1000px;
                        text-align:left;
                        -moz-border-radius:5px;
                        -webkit-border-radius:5px;
                        border-top-left-radius:5px;
                }
                a:link {
                        color: #0180AF;
                        text-decoration: none;
                }
                a:hover {
                        color: #666;
                }
                a:visited {
                        color: #0180AF;
                        text-decoration: underline;
                }
                h1 {
                        background:#fff;
                        padding:20px;
                        color:#598f24;
                        text-align:left;
                        font-size:32px;
                        margin:0px;
                        -moz-border-radius-topleft:5px;
                        -moz-border-radius-topright:5px;
                        -webkit-border-top-left-radius:5px;
                        -webkit-border-top-right-radius:5px;
                        border-top-left-radius:5px;
                        border-top-right-radius:5px;
                }
                h3 {
                        font-size:12px;
                        background:#7db249;
                        margin:0px;
                        padding:10px;
                }
                h3 span.url {
                        float:right;
                        font-weight:normal;
                        display:block;
                        color: #333;
                }
                h3 span.url a { color: #000; }
                .footer a:link { color: #eee; }
                table { width: 1000px; overflow: hidden;}
                th {
                        text-align:center;
                        background:#dce4c3;
                        color:#4e4e4e;
                        padding:4px;
                        font-weight:normal;
                        font-size:12px;
                        text-align: left;
                }
                td {
                        font-size:12px;
                        font-family: Helvetica,Trebuchet MS;
                        padding:6px 4px;
                        text-align:left;
                        overflow: hidden;
                }
                td a:link { color: #4b7226; text-decoration: none; }
                td a:visited { color: #4b7226; }
                tr {
                        background: #fff;
                    }
                tr:nth-child(odd) {
                        background: #f5f5f5;
                    }
                .overflow { width: 540px; overflow: hidden; }
                .footer {
                        background:#555;
                        color: #fff;
                        padding:10px;
                        -moz-border-radius-bottomleft:5px;
                        -moz-border-radius-bottomright:5px;
                        -webkit-border-bottom-left-radius:5px;
                        -webkit-border-bottom-right-radius:5px;
                        border-bottom-left-radius:5px;
                        border-bottom-right-radius:5px;
                }
                </style>
            </head>
            <body>
                <div class="content">
                    <h1>vBSEO Search Engine XML Sitemap</h1>
                    <h3>
                        <span class="url"><a href="http://www.vbseo.com">Created by vBSEO.com</a></span>
                        <xsl:choose>
                            <xsl:when test="sm:sitemapindex"> 
                                The number of sitemap files included: <xsl:value-of select="count(sm:sitemapindex/sm:sitemap)"/>
                            </xsl:when>
                            <xsl:otherwise> 
                                The number of URLs in sitemap file: <xsl:value-of select="count(sm:urlset/sm:url)"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </h3>
                    <xsl:apply-templates/>
                    <div class="footer">Created with vBSEO Search Engine XML Sitemap, Copyright (c) 2010 <a href="http://www.crawlability.com">Crawlability Inc.</a></div>
                </div>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="sm:sitemapindex">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <th>URL</th>
                <th />
                <th>Last Modified</th>
            </tr>
            <xsl:for-each select="sm:sitemap">
                <tr>
                    <xsl:variable name="loc">
                        <xsl:value-of select="sm:loc" />
                    </xsl:variable>
                    <xsl:variable name="lastmodified">
                        <xsl:value-of select="sm:lastmodified"/>
                    </xsl:variable>
                    <td><a href="{$loc}"><xsl:value-of select="sm:loc"/></a></td>
                    <td><xsl:value-of select="sm:lastmodified"/></td>
                    <xsl:apply-templates/>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
    <xsl:template match="sm:urlset">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <th width="5%" />
                <th width="55%">URL</th>
                <th width="10%">Priority</th>
                <th width="20%">Last Modified</th>
                <th width="10%">Change Frequency</th>
            </tr>
            <xsl:for-each select="sm:url">
                <tr>
                    <xsl:variable name="loc">
                        <xsl:value-of select="sm:loc"/>
                    </xsl:variable>
                    <xsl:variable name="priority">
                        <xsl:value-of select="sm:priority"/>
                    </xsl:variable>
                    <xsl:variable name="lastmodified">
                        <xsl:value-of select="sm:lastmodified"/>
                    </xsl:variable>
                    <xsl:variable name="changefreq">
                        <xsl:value-of select="sm:changefreq"/>
                    </xsl:variable>
                    <xsl:variable name="pos">
                            <xsl:value-of select="position()"/>
                    </xsl:variable>
		    <td><xsl:value-of select="$pos"/></td>
                    <td><div class="overflow"><a href="{$loc}" target="_blank"><xsl:value-of select="sm:loc"/></a></div></td>
                    <xsl:apply-templates/>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
    <xsl:template match="sm:loc|image:image">
    </xsl:template>
    <xsl:template match="sm:lastmod|sm:changefreq|sm:priority">
    <td>
    	<xsl:apply-templates/>
    </td>
    </xsl:template>
</xsl:stylesheet>