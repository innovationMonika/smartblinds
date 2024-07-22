<?php declare(strict_types=1);

namespace Smartblinds\PostNL\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use TIG\PostNL\Controller\Adminhtml\LabelAbstract;
use TIG\PostNL\Controller\Adminhtml\PdfDownload as GetPdf;
use TIG\PostNL\Helper\Tracking\Track;
use TIG\PostNL\Service\Handler\BarcodeHandler;
use TIG\PostNL\Service\Shipment\CreateShipment;
use TIG\PostNL\Service\Shipment\Labelling\GetLabels;
use TIG\PostNL\Service\Shipment\Packingslip\GetPackingslip;

class CreateShipmentsConfirmAndPrintPackingSlip extends LabelAbstract
{
    private $filter;
    private $collectionFactory;
    private $createShipment;

    private $errors = [];

    public function __construct(
        Context $context,
        GetLabels $getLabels,
        GetPdf $getPdf,
        Filter $filter,
        OrderCollectionFactory $collectionFactory,
        CreateShipment $createShipment,
        Track $track,
        BarcodeHandler $barcodeHandler,
        GetPackingslip $getPackingSlip
    ) {
        parent::__construct($context, $getLabels, $getPdf, $getPackingSlip, $barcodeHandler, $track);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->createShipment = $createShipment;
    }

    public function execute()
    {
        $this->createShipmentsAndLoadPackingSlips();
        $this->handleErrors();

        if (empty($this->labels)) {
            $this->messageManager->addErrorMessage(
                __('[POSTNL-0252] - There are no valid labels generated. Please check the logs for more information')
            );

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return $this->getPdf->get($this->labels, GetPdf::FILETYPE_PACKINGSLIP);
    }

    private function createShipmentsAndLoadPackingSlips()
    {
        $collection = $this->collectionFactory->create();

        try {
            $collection = $this->filter->getCollection($collection);
        } catch (LocalizedException $exception) {
            $this->messageManager->addWarningMessage($exception->getMessage());
            return;
        }

        foreach ($collection as $order) {
            $this->handleOrderToShipment($order);
        }
    }

    private function handleOrderToShipment($order)
    {
        if (!in_array($order->getState(), $this->stateToHandel) && $order->getStatus() !== 'samples') {
            $this->messageManager->addWarningMessage(
            //@codingStandardsIgnoreLine
                __('Can not process order %1, because it is not new or in processing', $order->getIncrementId())
            );
            return;
        }

        $shipments = $this->createShipment->create($order);
        if (!$shipments) {
            return;
        }

        $this->loadLabels($shipments);
    }

    private function handleErrors()
    {
        foreach ($this->errors as $error) {
            $this->messageManager->addErrorMessage($error);
        }

        $shipmentErrors = $this->createShipment->getErrors();
        foreach ($shipmentErrors as $error) {
            $this->messageManager->addErrorMessage($error);
        }

        return $this;
    }

    private function loadLabels($shipments)
    {
        // $shipments will contain a single shipment if it created a new one.
        if (!is_array($shipments)) {
            $this->loadLabel($shipments);
            return;
        }

        foreach ($shipments as $shipment) {
            $this->loadLabel($shipment);
        }
    }

    private function loadLabel($shipment)
    {
        $address  = $shipment->getShippingAddress();

        try {
            $this->barcodeHandler->prepareShipment($shipment->getId(), $address->getCountryId());
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage(
                __('[POSTNL-0070] - Unable to generate barcode for shipment #%1', $shipment->getIncrementId())
            );
            return;
        }

        $this->setTracks($shipment);
        $this->setPackingslip($shipment->getId());
    }
}
