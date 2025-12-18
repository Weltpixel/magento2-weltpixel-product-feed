<?php
namespace WeltPixel\ProductFeed\Controller\Feed;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use WeltPixel\ProductFeed\Model\ProductFeedFactory;
use WeltPixel\ProductFeed\Helper\Data as FeedHelper;

class Download extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProductFeedFactory
     */
    protected $feedFactory;

    /**
     * @var FeedHelper
     */
    protected $feedHelper;

    /**
     * Download constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Filesystem $filesystem
     * @param ProductFeedFactory $feedFactory
     * @param FeedHelper $feedHelper
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Filesystem $filesystem,
        ProductFeedFactory $feedFactory,
        FeedHelper $feedHelper
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->filesystem = $filesystem;
        $this->feedFactory = $feedFactory;
        $this->feedHelper = $feedHelper;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $feed = $this->feedFactory->create()->load($id);

        if (!$feed->getId()) {
            $this->messageManager->addErrorMessage(__('Feed not found.'));
            return $this->_redirect('*/*/');
        }

        $fileName = $feed->getFileName() . '.' . $feed->getFileType();
        $storeId = $feed->getStoreId();
        $directory = $this->feedHelper->getStorageFolder($storeId) === 'media' ? DirectoryList::MEDIA : DirectoryList::VAR_DIR;
        $directoryPath = $this->filesystem->getDirectoryRead($directory)->getAbsolutePath();
        $filePath = $directoryPath . FeedHelper::FEEDS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $feed->getId() . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($filePath)) {
            $this->messageManager->addErrorMessage(__('File not found.'));
            return $this->_redirect('*/*/');
        }

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader('Content-Type', mime_content_type($filePath));
        $resultRaw->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $resultRaw->setContents(file_get_contents($filePath));

        return $resultRaw;
    }
}
