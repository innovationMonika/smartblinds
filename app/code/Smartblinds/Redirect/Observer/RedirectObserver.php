<?php

namespace Smartblinds\Redirect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

class RedirectObserver implements ObserverInterface
{
    protected $redirect;
    protected $actionFlag;
    protected $request;
        protected $fileDriver;


    public function __construct(
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        RequestInterface $request,
        FileDriver $fileDriver

    ) {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->request = $request;
        $this->fileDriver = $fileDriver;
    }

    public function checkIfFileExists($filePath)
    {
        try {
            // Check if the file exists
            if ($this->fileDriver->isExists($filePath)) {
                // File exists
                return true;
            } else {
                // File does not exist
                return false;
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as file permissions issues
            return false;
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /*if ($this->request->getRequestString() == '/rolgordijnen/?control_type=306' && $this->request->getParam('control_type') == 306) {
            $url = '/rolgordijnen/elektrisch/';
            // setting an action flag to stop processing further hierarchy
             $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
             /// redirecting to error page
             $observer->getControllerAction()->getResponse()->setRedirect($url);
             return $this;
        }*/

        // Path to the CSV file in the var folder
            $csvFilePath = BP . '/var/URLredirects.csv'; // BP is the base path of Magento installation
            $csvData = [];

            // Check if the CSV file exists
            if ($this->checkIfFileExists($csvFilePath) ){
                // Open the CSV file and load data into $csvData array
                if (($handle = fopen($csvFilePath, 'r')) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // Assuming column 0 has the request URL and column 1 has the redirect URL
                        $csvData[$data[1]] = $data[2];
                    }
                    fclose($handle);
                }
            }
            $requestString = ltrim($this->request->getRequestString(), "/");

            // Check if the current request URL matches any entry in the CSV data
            if (array_key_exists($requestString, $csvData)) {
                $redirectUrl = "/".ltrim($csvData[$requestString], "/");
                // Set action flag to stop further processing
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

                // Redirect to the mapped URL from CSV
                $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
                return $this;
            }

    }
}
