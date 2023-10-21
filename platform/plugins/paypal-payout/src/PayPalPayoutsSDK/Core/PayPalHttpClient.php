<?php

namespace Botble\PayPalPayout\PayPalPayoutsSDK\Core;

use PayPalHttp\HttpClient;

class PayPalHttpClient extends HttpClient
{
    private $refreshToken;
    public $authInjector;

    public function __construct(PayPalEnvironment $environment, $refreshToken = null)
    {
        parent::__construct($environment);
        $this->refreshToken = $refreshToken;
        $this->authInjector = new AuthorizationInjector($this, $environment, $refreshToken);
        $this->addInjector($this->authInjector);
        $this->addInjector(new GzipInjector());
        $this->addInjector(new FPTIInstrumentationInjector());
    }

    public function userAgent()
    {
        return UserAgent::getValue();
    }
}
