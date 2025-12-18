<?php
namespace WeltPixel\ProductFeed\Controller\Adminhtml\ProductFeed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use WeltPixel\ProductFeed\Model\Feed\Generator;
use Magento\Framework\Controller\ResultInterface;
use WeltPixel\ProductFeed\Model\ProductFeedFactory;

class Generatefeed extends \WeltPixel\ProductFeed\Controller\Adminhtml\ProductFeed
{
    /**
     * @var Generator
     */
    protected $feedGenerator;

    /**
     * @var ProductFeedFactory
     */
    protected $productFeedFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Generator $feedGenerator
     * @param ProductFeedFactory $productFeedFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Generator $feedGenerator,
        ProductFeedFactory $productFeedFactory
    ) {
        $this->feedGenerator = $feedGenerator;
        $this->productFeedFactory = $productFeedFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Generate feed action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->productFeedFactory->create();
                $model->load($id);

                if ($model->getId()) {
                    $this->feedGenerator->generate($model);

                    if ($this->getRequest()->isAjax()) {
                        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
                        return $resultJson->setData([
                            'success' => true,
                            'message' => __('Feed has been generated.')
                        ]);
                    }

                    $this->messageManager->addSuccessMessage(__('Feed has been generated.'));
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
            } catch (\Exception $e) {
                if ($this->getRequest()->isAjax()) {
                    $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
                    return $resultJson->setData([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]);
                }

                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        if ($this->getRequest()->isAjax()) {
            $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            return $resultJson->setData([
                'error' => true,
                'message' => __('We can\'t find the feed to generate.')
            ]);
        }

        $this->messageManager->addErrorMessage(__('We can\'t find the feed to generate.'));
        return $resultRedirect->setPath('*/*/');
    }
}
