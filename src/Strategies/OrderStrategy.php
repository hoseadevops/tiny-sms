<?php

/*
 * This file is part of the hoseadevops/tiny-sms.
 *
 * (c) hosea <hoseadevops@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace HoseaDevops\TinySms\Strategies;

use HoseaDevops\TinySms\Contracts\StrategyInterface;

/**
 * Class OrderStrategy.
 */
class OrderStrategy implements StrategyInterface
{
    /**
     * Apply the strategy and return result.
     *
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways)
    {
        return array_keys($gateways);
    }
}
