<?php

require_once DIR_CATALOG_NEW . 'model/extension/payment/yandex_money/autoload.php';

class YandexMoneyWalletModel extends \YandexMoneyModule\Model\WalletModel
{
    public function setIsEnabled($value)
    {
        $this->enabled = $value ? true : false;
    }

    public function setAccountId($value)
    {
        $this->accountId = $value;
    }

    public function setApplicationId($value)
    {
        $this->applicationId = $value;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function setSuccessOrderStatusId($value)
    {
        $this->successOrderStatus = (int)$value;
    }

    public function setMinPaymentAmount($value)
    {
        if ($value < 0) {
            $value = 0;
        }
        $this->minPaymentAmount = (int)$value;
    }

    public function setGeoZoneId($value)
    {
        $this->geoZone = $value;
    }

    public function setDisplayName($value)
    {
        $this->displayName = $value;
    }

    /**
     * @param bool $value
     */
    public function setCreateOrderBeforeRedirect($value)
    {
        $this->createOrderBeforeRedirect = (bool)$value;
    }

    /**
     * @param bool $value
     */
    public function setClearCartBeforeRedirect($value)
    {
        $this->clearCartAfterOrderCreation = (bool)$value;
    }
}