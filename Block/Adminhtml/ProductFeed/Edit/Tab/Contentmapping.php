<?php
namespace WeltPixel\ProductFeed\Block\Adminhtml\ProductFeed\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Contentmapping extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \WeltPixel\ProductFeed\Model\Config\AttributeCollection
     */
    protected $atributeCollection;

    /**
     * @var \WeltPixel\ProductFeed\Model\Config\GoogleAttributeCollection
     */
    protected $googleAtributeCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \WeltPixel\ProductFeed\Model\Config\AttributeCollection $attributeCollection
     * @param \WeltPixel\ProductFeed\Model\Config\GoogleAttributeCollection $googleAtributeCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \WeltPixel\ProductFeed\Model\Config\AttributeCollection $attributeCollection,
        \WeltPixel\ProductFeed\Model\Config\GoogleAttributeCollection $googleAtributeCollection,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->atributeCollection = $attributeCollection;
        $this->googleAtributeCollection = $googleAtributeCollection;
        $this->setNameInLayout('weltpixel_productfeed_edit_tab_contentmapping');
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Content Mappings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Content Mappings');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('weltpixel_productfeed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'contentmapping_fieldset', $formName = 'weltpixelproductfeed_productfeed_form')
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('contentmappings_');


        $renderer = $this->_rendererFieldset
            ->setTemplate('WeltPixel_ProductFeed::contentmappings.phtml')
        ;

        $magentoAttributes = $this->atributeCollection->getAttributesArray();
        $googleFeedAttributes = $this->googleAtributeCollection->getAttributesArray();


        if (!$model->getId()) {
            $model->setContentMappings($this->googleAtributeCollection->getDefaultGoogleFeedMappings());
        }

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => '']
        )->setRenderer($renderer)
        ->setFormData($model->getData())
        ->setMagentoAttributes($magentoAttributes)
        ->setGoogleAttributes($googleFeedAttributes);

        $form->setValues($model->getData());
        return $form;
    }
}
