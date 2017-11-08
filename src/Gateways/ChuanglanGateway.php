<?php
/*
 * This file is part of the hoseadevops/tiny-sms.
 *
 * (c) hosea <hoseadevops@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HoseaDevops\TinySms\Gateways;

use HoseaDevops\TinySms\Contracts\MessageInterface;
use HoseaDevops\TinySms\Exceptions\GatewayErrorException;
use HoseaDevops\TinySms\Support\Config;
use HoseaDevops\TinySms\Traits\HasHttpRequest;

/**
 * Class ChuanglanGateway.
 *
 * @see https://www.253.com
 */
class ChuanglanGateway extends Gateway
{
    use HasHttpRequest;
    /**
     * @param array|int|string                             $to
     * @param \HoseaDevops\TinySms\Contracts\MessageInterface $message
     * @param \HoseaDevops\TinySms\Support\Config             $config
     * @param string $smsType
     * @return array
     *
     * @throws \HoseaDevops\TinySms\Exceptions\GatewayErrorException;
     */
    public function send($to, MessageInterface $message, Config $config, $smsType)
    {
        $position  = $config->get("{$smsType}.signature_position");

        $content = $message->getSignature() . $message->getContent();
        if($position == 'end'){
            $content = $message->getContent() . $message->getSignature();
        }
        $params = [
            'un'    => $config->get("{$smsType}.username"),
            'pw'    => $config->get("{$smsType}.password"),
            'phone' => $to,
            'msg'   => $content
        ];

        $result       = $this->get($config->get("{$smsType}.url"), $params);

        $formatResult = $this->formatResult($result);

        if (!empty($formatResult[1])) {
            throw new GatewayErrorException($result, $formatResult[1], $formatResult);
        }

        return $result;
    }

    /**
     * @param $result  http return from 253 service
     *
     * @return array
     */
    protected function formatResult($result)
    {
        $result = str_replace("\n", ',', $result);

        return explode(',', $result);
    }
}
