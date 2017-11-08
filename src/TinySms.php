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

use Closure;
use HoseaDevops\TinySms\Contracts\GatewayInterface;
use HoseaDevops\TinySms\Contracts\StrategyInterface;
use HoseaDevops\TinySms\Exceptions\InvalidArgumentException;
use HoseaDevops\TinySms\Strategies\OrderStrategy;
use HoseaDevops\TinySms\Support\Config;
use RuntimeException;

/**
 * Class TinySms.
 */
class TinySms
{
    /**
     * @var \HoseaDevops\TinySms\Support\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $defaultGateway;

    /**
     * @var array
     */
    protected $customCreators = [];

    /**
     * @var array
     */
    protected $gateways = [];

    /**
     * @var \HoseaDevops\TinySms\Messenger
     */
    protected $messenger;

    /**
     * @var array
     */
    protected $strategies = [];

    /**
     * @var string
     */
    protected $smsType = 'VERIFY';

    /**
     * TinySms constructor.
     * @param array $config
     * @param $organization
     * @param string $smsType
     */
    public function __construct(array $config, $organization, $smsType='VERIFY')
    {
        $this->config    = new Config($config, $organization);

        $this->smsType   = $smsType;

        if (!empty($this->config['default'])) {
            $this->setDefaultGateway($this->config['default']);
        }
    }

    /**
     * Send a message.
     *
     * @param string|array                                       $to
     * @param \HoseaDevops\TinySms\Contracts\MessageInterface|array $message
     * @param array                                              $gateways
     *
     * @return array
     */
    public function send($to, $message, array $gateways = [])
    {
        return $this->getMessenger()->send($to, $message, $gateways);
    }

    /**
     * Create a gateway.
     *
     * @param string|null $name
     *
     * @return \HoseaDevops\TinySms\Contracts\GatewayInterface
     */
    public function gateway($name = null)
    {
        $name = $name ?: $this->getDefaultGateway();

        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->createGateway($name);
        }

        return $this->gateways[$name];
    }

    /**
     * Get a strategy instance.
     *
     * @param string|null $strategy
     *
     * @return \HoseaDevops\TinySms\Contracts\StrategyInterface
     *
     * @throws \HoseaDevops\TinySms\Exceptions\InvalidArgumentException
     */
    public function strategy($strategy = null)
    {
        if (is_null($strategy)) {
            $strategy = $this->config->get('default.strategy', OrderStrategy::class);
        }

        if (!class_exists($strategy)) {
            $strategy = __NAMESPACE__.'\Strategies\\'.ucfirst($strategy);
        }

        if (!class_exists($strategy)) {
            throw new InvalidArgumentException("Unsupported strategy \"{$strategy}\"");
        }

        if (empty($this->strategies[$strategy]) || !($this->strategies[$strategy] instanceof StrategyInterface)) {
            $this->strategies[$strategy] = new $strategy($this);
        }

        return $this->strategies[$strategy];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($name, Closure $callback)
    {
        $this->customCreators[$name] = $callback;

        return $this;
    }

    /**
     * @return \HoseaDevops\TinySms\Support\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getSmsType()
    {
        return $this->smsType;
    }

    /**
     * Get default gateway name.
     *
     * @return string
     *
     * @throws if no default gateway configured
     */
    public function getDefaultGateway()
    {
        if (empty($this->defaultGateway)) {
            throw new RuntimeException('No default gateway configured.');
        }

        return $this->defaultGateway;
    }

    /**
     * Set default gateway name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setDefaultGateway($name)
    {
        $this->defaultGateway = $name;

        return $this;
    }

    /**
     * @return \HoseaDevops\TinySms\Messenger
     */
    public function getMessenger()
    {
        return $this->messenger ?: $this->messenger = new Messenger($this);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return GatewayInterface
     */
    protected function createGateway($name)
    {
        if (isset($this->customCreators[$name])) {
            $gateway = $this->callCustomCreator($name);
        } else {
            $className = $this->formatGatewayClassName($name);
            $gateway = $this->makeGateway($className, $this->config->get("gateways.{$name}", []));
        }

        if (!($gateway instanceof GatewayInterface)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" not inherited from %s.', $name, GatewayInterface::class));
        }

        return $gateway;
    }

    /**
     * Make gateway instance.
     *
     * @param string $gateway
     * @param array  $config
     *
     * @throws InvalidArgumentException
     * @return \HoseaDevops\TinySms\Contracts\GatewayInterface
     */
    protected function makeGateway($gateway, $config)
    {
        if (!class_exists($gateway)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" not exists.', $gateway));
        }

        return new $gateway($config);
    }

    /**
     * Format gateway name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatGatewayClassName($name)
    {
        if (class_exists($name)) {
            return $name;
        }

        $name = ucfirst(str_replace(['-', '_', ''], '', $name));

        return __NAMESPACE__."\\Gateways\\{$name}Gateway";
    }

    /**
     * Call a custom gateway creator.
     *
     * @param string $gateway
     *
     * @return mixed
     */
    protected function callCustomCreator($gateway)
    {
        return call_user_func($this->customCreators[$gateway], $this->config->get($gateway, []));
    }
}
