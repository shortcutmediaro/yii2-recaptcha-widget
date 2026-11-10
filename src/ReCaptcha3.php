<?php
/**
 * @link https://github.com/shortcutmediaro/yii2-recaptcha-widget
 * @copyright Copyright (c) 2014-2019 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace shortcutmediaro\yii2\recaptcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Yii2 Google reCAPTCHA v3 widget.
 *
 * For example:
 *
 *```php
 * <?= $form->field($model, 'reCaptcha')->widget(
 *  ReCaptcha3::className(),
 *  [
 *   'siteKey' => 'your siteKey', // unnecessary is reCaptcha component was set up
 *   'threshold' => 0.5,
 *   'action' => 'homepage',
 *  ]
 * ) ?>
 *```
 *
 * or
 *
 *```php
 * <?= ReCaptcha3::widget([
 *  'name' => 'reCaptcha',
 *  'siteKey' => 'your siteKey', // unnecessary is reCaptcha component was set up
 *  'threshold' => 0.5,
 *  'action' => 'homepage',
 *  'widgetOptions' => ['class' => 'col-sm-offset-3'],
 * ]) ?>
 *```
 *
 * @see https://developers.google.com/recaptcha/docs/v3
 * @author HimikLab
 * @package shortcutmediaro\yii2\recaptcha
 */
class ReCaptcha3 extends InputWidget
{
    /** @var string|null Your sitekey. */
    public ?string $siteKey = null;

    /**
     * @var string|null Use [[ReCaptchaConfig::JS_API_URL_ALTERNATIVE]] when [[ReCaptchaConfig::JS_API_URL_DEFAULT]]
     * is not accessible.
     */
    public ?string $jsApiUrl = null;

    /** @var string reCAPTCHA v3 action for this page. */
    public ?string $action = null;

    /** @var string|null Your JS callback function that's executed when reCAPTCHA executed. */
    public ?string $jsCallback = null;

    /** @var string */
    public string $configComponentName = 'reCaptcha';

    public function __construct($siteKey = null, $jsApiUrl = null, $config = [])
    {
        if ($siteKey && !$this->siteKey) {
            $this->siteKey = $siteKey;
        }
        if ($jsApiUrl && !$this->jsApiUrl) {
            $this->jsApiUrl = $jsApiUrl;
        }

        parent::__construct($config);
    }

    public function init(): void
    {
        parent::init();
        $this->configComponentProcess();
    }

    public function run(): void
    {
        parent::run();
        $view = $this->view;

        $arguments = \http_build_query([
            'render' => $this->siteKey,
        ]);

        $view->registerJsFile(
            $this->jsApiUrl . '?' . $arguments,
            ['position' => $view::POS_END]
        );
        $view->registerJs(
            <<<JS
            "use strict";
            grecaptcha.ready(function() {
                grecaptcha.execute("{$this->siteKey}", {action: "{$this->action}"}).then(function(token) {
                    jQuery("#" + "{$this->getReCaptchaId()}").val(token);
                    const jsCallback = "{$this->jsCallback}";
                    if (jsCallback) {
                        eval("(" + jsCallback + ")(token)");
                    }
                });
            });
        JS
        , $view::POS_READY);

        $this->customFieldPrepare();
    }

    protected function customFieldPrepare(): void
    {
        if ($this->hasModel()) {
            $inputName = Html::getInputName($this->model, $this->attribute);
        } else {
            $inputName = $this->name;
        }

        $options = $this->options;
        $options['id'] = $this->getReCaptchaId();

        echo Html::input('hidden', $inputName, null, $options);
    }

    protected function getReCaptchaId()
    {
        if (isset($this->options['id'])) {
            return $this->options['id'];
        }

        if ($this->hasModel()) {
            return Html::getInputId($this->model, $this->attribute);
        }

        return $this->id . '-' . $this->inputNameToId($this->name);
    }

    protected function inputNameToId($name): array|string
    {
        return \str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], \strtolower($name));
    }

    protected function configComponentProcess(): void
    {
        /** @var ReCaptchaConfig $reCaptchaConfig */
        $reCaptchaConfig = Yii::$app->get($this->configComponentName, false);

        if (!$this->siteKey) {
            if ($reCaptchaConfig && $reCaptchaConfig->siteKeyV3) {
                $this->siteKey = $reCaptchaConfig->siteKeyV3;
            } else {
                throw new InvalidConfigException('Required `siteKey` param isn\'t set.');
            }
        }
        if (!$this->jsApiUrl) {
            if ($reCaptchaConfig && $reCaptchaConfig->jsApiUrl) {
                $this->jsApiUrl = $reCaptchaConfig->jsApiUrl;
            } else {
                $this->jsApiUrl = ReCaptchaConfig::JS_API_URL_DEFAULT;
            }
        }
        if (!$this->action) {
            $this->action = \preg_replace('/[^a-zA-Z\d\/]/', '', \urldecode(Yii::$app->request->url));
        }
    }
}
