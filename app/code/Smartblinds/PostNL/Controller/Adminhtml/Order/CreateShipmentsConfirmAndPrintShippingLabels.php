<?php declare(strict_types=1);

namespace Smartblinds\PostNL\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use TIG\PostNL\Controller\Adminhtml\LabelAbstract;
use TIG\PostNL\Controller\Adminhtml\PdfDownload as GetPdf;
use TIG\PostNL\Helper\Tracking\Track;
use TIG\PostNL\Service\Converter\CanaryIslandToIC;
use TIG\PostNL\Service\Handler\BarcodeHandler;
use TIG\PostNL\Service\Shipment\CreateShipment;
use TIG\PostNL\Service\Shipment\Labelling\GetLabels;
use TIG\PostNL\Service\Shipment\Packingslip\GetPackingslip;

class CreateShipmentsConfirmAndPrintShippingLabels extends LabelAbstract implements HttpGetActionInterface
{
    private $filter;
    private $collectionFactory;
    private $createShipment;
    private $canaryConverter;

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
        GetPackingslip $getPackingSlip,
        CanaryIslandToIC $canaryConverter
    ) {
        parent::__construct($context, $getLabels, $getPdf, $getPackingSlip, $barcodeHandler, $track);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->createShipment = $createShipment;
        $this->canaryConverter = $canaryConverter;
    }

    public function execute()
    {
        $this->createShipmentsAndLoadLabels();
        $this->handleErrors();

        if (empty($this->labels)) {
            $this->messageManager->addErrorMessage(
                __('[POSTNL-0252] - There are no valid labels generated. Please check the logs for more information')
            );

            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return $this->getPdf->get($this->labels);
    }

    private function createShipmentsAndLoadLabels()
    {
        $collection = $this->collectionFactory->create();

        try {
            $collection = $this->filter->getCollection($collection);
        } catch (LocalizedException $exception) {
            $this->messageManager->addWarningMessage($exception->getMessage());
            return;
        }

        foreach ($collection as $order) {
            $this->loadLabels($order);
        }
    }

    private function loadLabels($order)
    {
        if (!in_array($order->getState(), $this->stateToHandel) && $order->getStatus() !== 'samples') {
            $this->messageManager->addWarningMessage(
            //@codingStandardsIgnoreLine
                __('Can not process order %1, because it is not new or in processing', $order->getIncrementId())
            );
            return;
        }

        $shipments = $this->createShipment->create($order);
        // $shipments will contain a single shipment if it created a new one.
        if (!is_array($shipments)) {
            $shipments = [$shipments];
        }

        foreach ($shipments as $shipment) {
            $this->loadLabel($shipment);
        }
    }

    /**
     * @param $shipment
     *
     * If a shipment is null or false, it means Magento errored on creating the shipment.
     * Magento will already throw their own Exceptions, so we won't have to.
     */
    private function loadLabel($shipment)
    {
        if (!$shipment) {
            return;
        }
        $address = $this->canaryConverter->convert($shipment->getShippingAddress());

        try {
            $this->barcodeHandler->prepareShipment($shipment->getId(), $address->getCountryId());
            $this->setTracks($shipment);
            $this->setLabel($shipment->getId());
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage(
                __('[POSTNL-0070] - Unable to generate barcode for shipment #%1', $shipment->getIncrementId())
            );
        }
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
}
