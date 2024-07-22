<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="xml" indent="yes" version="1.0" encoding="UTF-8"/>

    <xsl:template match="/">
        <orderRegistrationRequest>
            <order>
                <xsl:attribute name="id"><xsl:value-of select="increment_id" /></xsl:attribute>
                <xsl:attribute name="type"><xsl:value-of select="order_type" /></xsl:attribute>
                <xsl:attribute name="reference"><xsl:value-of select="reference" /></xsl:attribute>
                <xsl:attribute name="referencedOrderId"><xsl:value-of select="base_increment_id" /></xsl:attribute>
                <deliveryAddress>
                    <countryCode><xsl:value-of select="country_code" /></countryCode>
                    <firstName><xsl:value-of select="firstname" /></firstName>
                    <lastName><xsl:value-of select="lastname" /></lastName>
                    <email><xsl:value-of select="email" /></email>
                    <company><xsl:value-of select="company" /></company>
                    <street><xsl:value-of select="street" /></street>
                    <postcode><xsl:value-of select="postcode" /></postcode>
                    <city><xsl:value-of select="city" /></city>
                    <telephone><xsl:value-of select="telephone" /></telephone>
                </deliveryAddress>
                <xsl:call-template name="itemsTemplate">
                    <xsl:with-param name="orderItems" select="items"/>
                </xsl:call-template>
            </order>
        </orderRegistrationRequest>
    </xsl:template>

    <xsl:template name="itemsTemplate">
        <xsl:param name="orderItems"/>
        <items>
            <xsl:for-each select="$orderItems/list">
                <item>
                    <xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute>
                    <xsl:attribute name="quantity"><xsl:value-of select="quantity" /></xsl:attribute>
                    <xsl:attribute name="productCategory"><xsl:value-of select="category" /></xsl:attribute>
                    <xsl:attribute name="reference"><xsl:value-of select="name" /></xsl:attribute>
                    <xsl:attribute name="salesPrice"><xsl:value-of select="salesPrice" /></xsl:attribute>
                    <xsl:call-template name="optionsTemplate">
                        <xsl:with-param name="itemOptions" select="options"/>
                    </xsl:call-template>
                </item>
            </xsl:for-each>
        </items>
    </xsl:template>

    <xsl:template name="optionsTemplate">
        <xsl:param name="itemOptions"/>
        <xsl:for-each select="$itemOptions/list">
            <option>
                <xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
                <xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
            </option>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
