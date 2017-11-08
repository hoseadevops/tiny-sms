<?php

/*
 * This file is part of the hoseadevops/tiny-sms.
 *
 * (c) hosea <hoseadevops@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HoseaDevops\TinySms\Contracts;

use HoseaDevops\TinySms\Support\Config;

/**
 * Class GatewayInterface.
 */
interface GatewayInterface
{
    /**
     * Send a short message.
     *
     * @param int|string|array                             $to
     * @param \HoseaDevops\TinySms\Contracts\MessageInterface $message
     * @param \HoseaDevops\TinySms\Support\Config             $config
     * @param string                                       $smsType
     *
     * @return array
     */
    public function send($to, MessageInterface $message, Config $config, $smsType);
}
