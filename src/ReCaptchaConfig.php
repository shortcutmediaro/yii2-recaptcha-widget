<?php
/**
 * @link https://github.com/shortcutmediaro/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014-2019 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace shortcutmediaro\yii2\recaptcha;

/**
 * Yii2 Google reCAPTCHA widget global config.
 *
 * @see https://developers.google.com/recaptcha
 * @author HimikLab
 * @package shortcutmediaro\yii2\recaptcha
 */
class ReCaptchaConfig
{
    const string JS_API_URL_DEFAULT = '//www.google.com/recaptcha/api.js';
    const string JS_API_URL_ALTERNATIVE = '//www.recaptcha.net/recaptcha/api.js';

    const string SITE_VERIFY_URL_DEFAULT = 'https://www.google.com/recaptcha/api/siteverify';
    const string SITE_VERIFY_URL_ALTERNATIVE = 'https://www.recaptcha.net/recaptcha/api/siteverify';

    /** @var string|null Your sitekey for reCAPTCHA v2. */
    public ?string $siteKeyV2 = null;

    /** @var string|null Your secret for reCAPTCHA v2. */
    public ?string $secretV2 = null;

    /** @var string|null Your v3 sitekey for reCAPTCHA v3. */
    public ?string $siteKeyV3 = null;

    /** @var string|null Your secret for reCAPTCHA v3. */
    public ?string $secretV3 = null;

    /** @var string|null Use [[JS_API_URL_ALTERNATIVE]] when [[JS_API_URL_DEFAULT]] is not accessible. */
    public ?string $jsApiUrl = null;

    /** @var string|null Use [[SITE_VERIFY_URL_ALTERNATIVE]] when [[SITE_VERIFY_URL_DEFAULT]] is not accessible. */
    public ?string $siteVerifyUrl = null;

    /** @var boolean Check host name. */
    public bool $checkHostName = false;

    /** @var yii\httpclient\Request|null */
    public ?yii\httpclient\Request $httpClientRequest = null;
}
