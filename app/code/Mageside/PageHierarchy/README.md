Magento 2 PageHierarchy by Mageside
===========================

####Support
    v1.5.1 - Magento 2.2.* - 2.4.*

####Change list
    v1.5.1 - Added Magento 2.4 compatibility
    v1.5.0  added show global hierarchy breadcrumbs 
    v1.4.0  added hierarchy sort order 
    v1.3.0  added logic Default Route Behavior
    v1.2.0  add Breadcrumbs for hierarchy page
    v1.1.0 - added hierarchy path in page url
    v1.0.1 - Added Magento 2.3 compatibility
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageside/PageHierarchy'.
    3. Unzip the content of archive (use command 'unzip ArchiveName.zip') 
       to directory 'Magento_Root/app/code/Mageside/PageHierarchy'.
    4. Run the command 'php bin/magento module:enable Mageside_PageHierarchy' in Magento root.
       If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageside_PageHierarchy'.
    5. Run the command 'php bin/magento setup:upgrade' in Magento root.
    6. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, 
       or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    7. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'
