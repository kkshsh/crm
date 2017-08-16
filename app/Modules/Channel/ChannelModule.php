<?php
namespace App\Modules\Channel;

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/5/17
 * Time: 上午10:11
 */
use Exception;
use App\Modules\Channel\Adapter\AmazonAdapter;
use App\Modules\Channel\Adapter\AliexpressAdapter;
use App\Modules\Channel\Adapter\EbayAdapter;


class ChannelModule
{
    public function driver($adapter, $config)
    {
        $driverMethod = 'create' . ucfirst(strtolower($adapter)) . 'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new Exception("Driver [{$adapter}] not supported.");
        }
    }

    /**
     * 亚马逊接口驱动
     *
     * @param $config
     * @return AmazonAdapter
     */
    public function createAmazonDriver($config)
    {
        return new AmazonAdapter($config);
    }

    public function createEbayDriver($config)
    {
        return new EbayAdapter($config);
    }

    public function createAliexpressDriver($config)
    {
        return new AliexpressAdapter($config);
    }

}