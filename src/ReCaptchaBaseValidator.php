<?php
/**
 * @link https://github.com/shortcutmediaro/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014-2019 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace shortcutmediaro\yii2\recaptcha;

use Yii;
use yii\base\Exception;
use yii\httpclient\Client as HttpClient;
use yii\validators\Validator;

/**
 * ReCaptcha widget validator base class.
 *
 * @author HimikLab
 * @package shortcutmediaro\yii2\recaptcha
 */
abstract class ReCaptchaBaseValidator extends Validator
{
    /** @var boolean Whether to skip this validator if the input is empty. */
    public bool $skipOnEmpty = false;

    /** @var string|null The shared key between your site and ReCAPTCHA. */
    public ?string $secret = null;

    /**
     * @var string|null Use ReCaptchaConfig::SITE_VERIFY_URL_ALTERNATIVE when ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT
     * is not accessible. Default is ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT.
     */
    public ?string $siteVerifyUrl = null;

    /** @var yii\httpclient\Request|null */
    public ?Yii\httpclient\Request $httpClientRequest = null;

    /** @var string */
    public string $configComponentName = 'reCaptcha';

    /** @var boolean Check host name. Default is false. */
    public bool $checkHostName = false;

    /** @var boolean */
    protected bool $isValid = false;

    public function __construct(
        $siteVerifyUrl,
        $checkHostName,
        $httpClientRequest,
        $config
    )
    {
        if ($siteVerifyUrl && !$this->siteVerifyUrl) {
            $this->siteVerifyUrl = $siteVerifyUrl;
        }
        if ($checkHostName && $this->checkHostName !== null) {
            $this->checkHostName = $checkHostName;
        }
        if ($httpClientRequest && !$this->httpClientRequest) {
            $this->httpClientRequest = $httpClientRequest;
        }

        parent::__construct($config);
    }

    public function init(): void
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The verification code is incorrect.');
        }
    }

    /**
     * @param string $value
     * @return array
     * @throws Exception
     * @throws \yii\base\InvalidParamException
     */
    protected function getResponse(string $value): array
    {
        $response = $this->httpClientRequest
            ->setMethod('GET')
            ->setUrl($this->siteVerifyUrl)
            ->setData(['secret' => $this->secret, 'response' => $value, 'remoteip' => Yii::$app->request->userIP])
            ->send();
        if (!$response->isOk) {
            throw new Exception('Unable connection to the captcha server. Status code ' . $response->statusCode);
        }

        return $response->data;
    }

    protected function configComponentProcess(): void
    {
        /** @var ReCaptchaConfig $reCaptchaConfig */
        $reCaptchaConfig = Yii::$app->get($this->configComponentName, false);

        if (!$this->siteVerifyUrl) {
            if ($reCaptchaConfig && $reCaptchaConfig->siteVerifyUrl) {
                $this->siteVerifyUrl = $reCaptchaConfig->siteVerifyUrl;
            } else {
                $this->siteVerifyUrl = ReCaptchaConfig::SITE_VERIFY_URL_DEFAULT;
            }
        }

        if ($this->checkHostName === null) {
            if ($reCaptchaConfig && $reCaptchaConfig->checkHostName !== null) {
                $this->checkHostName = $reCaptchaConfig->checkHostName;
            } else {
                $this->checkHostName = false;
            }
        }

        if (!$this->httpClientRequest) {
            if ($reCaptchaConfig && $reCaptchaConfig->httpClientRequest) {
                $this->httpClientRequest = $reCaptchaConfig->httpClientRequest;
            } else {
                $this->httpClientRequest = (new HttpClient())->createRequest();
            }
        }
    }
}
