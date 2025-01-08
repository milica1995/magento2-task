<?php

declare(strict_types=1);

namespace Younify\OrderExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Filesystem\Driver\File;

class ExportOrders
{
    private $orderCollectionFactory;
    private $directoryList;
    private $file;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    public function export($statuses, $group, $startDate, $endDate)
    {
        $fileName = 'var/export/orders_' . date('Ymd_His') . '.csv';
   
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('created_at', ['gteq' => $startDate])
            ->addFieldToFilter('created_at', ['lteq' => $endDate]);

        if ($statuses && $statuses != null) {
            $orders->addFieldToFilter('status', ['in' => $statuses]);
        }

        if ($group && $group != null) {
            $orders->addFieldToFilter('customer_group_id', $group);
        }

        $header = ['Order ID', 'Customer Name', 'Customer Group', 'Order Total', 'Order Date', 'Order Status'];
        $data = [$header];

        foreach ($orders as $order) {
            $data[] = [
                $order->getIncrementId(),
                $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                $order->getCustomerGroupId(),
                $order->getGrandTotal(),
                $order->getCreatedAt(),
                $order->getStatus(),
            ];
        }

        if (!empty($data)) {
            $this->saveCsv($fileName, $data);
        }

        return $fileName;
    }

    private function saveCsv($fileName, $data)
    {
        $exportDir = $this->directoryList->getRoot() . '/var/export';
        if (!$this->file->isDirectory($exportDir)) {
            $this->file->createDirectory($exportDir);
        }

        $filePath = $this->directoryList->getRoot() . '/' . $fileName;

        $csvData = array_map(function ($fields) {
            return implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $fields));
        }, $data);

        $this->file->filePutContents($filePath, implode(PHP_EOL, $csvData));
    }
}