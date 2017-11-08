<?php

/*
 * This file is part of the hoseadevops/tiny-sms.
 *
 * (c) hosea <hoseadevops@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HoseaDevops\TinySms;

use HoseaDevops\TinySms\Contracts\MessageInterface;
use HoseaDevops\TinySms\Exceptions\GatewayErrorException;
use HoseaDevops\TinySms\Exceptions\NoGatewayAvailableException;
use HoseaDevops\TinySms\Support\Config;

/**
 * Class Messenger.
 */
class Messenger
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERRED   = 'error';

    /**
     * @var \HoseaDevops\TinySms\TinySms
     */
    protected $tinySms;

    /**
     * Messenger constructor.
     *
     * @param \HoseaDevops\TinySms\TinySms $tinySms
     */
    public function __construct(TinySms $tinySms)
    {
        $this->tinySms = $tinySms;
    }

    /**
     * Send a message.
     *
     * @param string|array                                              $to
     * @param string|array|\HoseaDevops\TinySms\Contracts\MessageInterface $message
     * @param array                                                     $gateways
     *
     * @return array
     *
     * @throws \HoseaDevops\TinySms\Exceptions\NoGatewayAvailableException
     */
    public function send($to, $message, array $gateways = [])
    {
        $message = $this->formatMessage($message);

        if (empty($gateways)) {
            $gateways = $message->getGateways();
        }

        if (empty($gateways)) {
            $gateways = $this->tinySms->getConfig()->get('default.gateways', []);
        }

        $gateways = $this->formatGateways($gateways);

        $strategyAppliedGateways = $this->tinySms->strategy()->apply($gateways);

        $results = [];
        $hasSucceed = false;
        foreach ($strategyAppliedGateways as $gateway)
        {
            try {
                $results[$gateway] = [
                    'status' => self::STATUS_SUCCESS,
                    'result' => $this->tinySms->gateway($gateway)->send($to, $message, new Config($gateways[$gateway]), $this->tinySms->getSmsType()),
                ];
                $hasSucceed = true;

                break;
            } catch (GatewayErrorException $e) {
                $results[$gateway] = [
                    'status' => self::STATUS_ERRED,
                    'exception' => $e,
                ];

                continue;
            }
        }

        if (!$hasSucceed)
        {
            throw new NoGatewayAvailableException($results);
        }

        return $results;
    }

    /**
     * @param array|string|\HoseaDevops\TinySms\Contracts\MessageInterface $message
     *
     * @return \HoseaDevops\TinySms\Contracts\MessageInterface
     */
    protected function formatMessage($message)
    {
        if ( !($message instanceof MessageInterface) )
        {
            if (!is_array($message))
            {
                $message = [
                    'content'  => strval($message),
                    'template' => strval($message),
                ];
            }
            $message['signature'] = $this->tinySms->getConfig()->get('signature');

            $message = new Message($message);
        }

        return $message;
    }

    /**
     * @param array $gateways
     *
     * @return array
     */
    protected function formatGateways(array $gateways)
    {
        $formatted = [];
        $config    = $this->tinySms->getConfig();

        foreach ($gateways as $gateway => $setting)
        {
            if (is_int($gateway) && is_string($setting))
            {
                $gateway = $setting;
                $setting = [];
            }

            $formatted[$gateway] = $setting;
            $globalSetting = $config->get("gateways.{$gateway}", []);

            if (is_string($gateway) && !empty($globalSetting) && is_array($setting))
            {
                $formatted[$gateway] = array_merge($globalSetting, $setting);
            }
        }

        return $formatted;
    }
}
