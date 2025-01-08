<?php

declare(strict_types=1);

namespace Younify\OrderExport\Controller\Adminhtml\Form;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Younify\OrderExport\Model\ExportOrders;

class Export extends Action
{
    protected $exportOrders;

    public function __construct(
        Action\Context $context,
        ExportOrders $exportOrders,
    ) {
        parent::__construct($context);
        $this->exportOrders = $exportOrders;
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();

        $startDate = $postData['start_date'];
        $endDate = $postData['end_date'];
        $statuses = isset($_POST['status']) ? $_POST['status'] : null;
        //$statuses = isset($postData['status']) && trim($postData['status']) !== '' ? explode(',', $postData['status']) : null;
        $customerGroup = $postData['customer_group'] ?? null;

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$startDate || !$endDate) {
            $this->messageManager->addErrorMessage(__('Start Date and End Date are required.'));
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $fileName = $this->exportOrders->export($statuses, $customerGroup, $startDate, $endDate);
            $this->messageManager->addSuccessMessage(__('Export completed successfully. File: %1', $fileName));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred during export: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
