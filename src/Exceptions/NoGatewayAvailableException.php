<?php

/*
 * This file is part of the hoseadevops/tiny-sms.
 *
 * (c) hosea <hoseadevops@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HoseaDevops\TinySms\Exceptions;

/**
 * Class Exception.
 *
 * @author hosea <hoseadevops@gmail.com>
 */
class NoGatewayAvailableException extends Exception
{
    /**
     * @var array
     */
    public $results = [];

    /**
     * NoGatewayAvailableException constructor.
     *
     * @param array           $results
     * @param int             $code
     * @param Throwable|null $previous
     */
    public function __construct(array $results = [], $code = 0, Throwable $previous = null)
    {
        $this->results = $results;
        parent::__construct('All the gateways have failed.', $code, $previous);
    }
}
