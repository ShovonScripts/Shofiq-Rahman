<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:rss="http://purl.org/rss/1.0/"
  exclude-result-prefixes="rss">

  <xsl:output method="html" encoding="UTF-8" />

  <xsl:template match="/rss">
    <html lang="bn">
      <head>
        <meta charset="utf-8" />
        <title>RSS — <xsl:value-of select="channel/title"/></title>
        <style>
          body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:2rem;line-height:1.6}
          h1{margin:0 0 1rem}
          .meta{color:#666;margin-bottom:1.5rem}
          .item{border:1px solid #eee;border-radius:10px;padding:1rem;margin:0 0 1rem}
          .item a{color:#0d6efd;text-decoration:none}
          .item a:hover{text-decoration:underline}
          .date{color:#888;font-size:.9em}
        </style>
      </head>
      <body>
        <h1><xsl:value-of select="channel/title"/></h1>
        <div class="meta">
          <div><xsl:value-of select="channel/description"/></div>
          <div>Feed URL: <code><xsl:value-of select="channel/atom:link/@href" xmlns:atom="http://www.w3.org/2005/Atom"/></code></div>
        </div>

        <xsl:for-each select="channel/item">
          <div class="item">
            <div><a href="{link}"><xsl:value-of select="title"/></a></div>
            <div class="date"><xsl:value-of select="pubDate"/></div>
            <div><xsl:value-of select="description" disable-output-escaping="yes"/></div>
          </div>
        </xsl:for-each>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
