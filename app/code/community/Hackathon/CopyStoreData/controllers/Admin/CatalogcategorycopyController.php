<?php

class Hackathon_CopyStoreData_Admin_CatalogcategorycopyController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        $copyFromStore = $this->getRequest()->getParam('copy_from_store');
        $copyToStores = $this->getRequest()->getParam('copy_to_stores');
        $categoryIds = $this->getRequest()->getParam('category_ids');
        foreach ($copyToStores as $copyToStore) {
            $categoriesToCopy = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->setStoreId($copyFromStore)
                ->addAttributeToFilter('entity_id', array('in' => $categoryIds));
            try {
                foreach ($categoriesToCopy as $categoryToCopy) {
                    $categoryDataArray = $categoryToCopy->getData();
                    foreach ($categoryDataArray as $key => $value) {
                        $attribute = $categoryToCopy->getResource()->getAttribute($key);
                        if (!is_object($value) && is_object($attribute)) {
                            if ($attribute->getBackendType() != 'static' && $attribute->getIsGlobal() == 0) {
                                $categoryToCopy->setData($key, $value);
                                $categoryToCopy->setStoreId($copyToStore)->getResource()->saveAttribute($categoryToCopy, $key);
                            }
                        }

                    }
                }

                Mage::getSingleton('adminhtml/session')
                    ->init('core', 'adminhtml')
                    ->addSuccess('Categories were successfully copied.');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')
                    ->init('core', 'adminhtml')
                    ->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
