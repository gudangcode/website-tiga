<?php

/**
 * SendingServerMailgun class.
 *
 * Abstract class for Mailgun sending servers
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;
use Mailgun\Mailgun;

class SendingServerMailgun extends SendingServer
{
    const WEBHOOK = 'mailgun';

    protected $table = 'sending_servers';
    public static $client = null;
    public static $isWebhookSetup = false;

    // Inherit class to implementation of this method
    public function send($message, $params = array())
    {
        // for overwriting
    }

    /**
     * Get authenticated to Mailgun and return the session object.
     *
     * @return mixed
     */
    public function client()
    {
        if (!self::$client) {
            self::$client = new Mailgun($this->api_key);
        }

        return self::$client;
    }

    /**
     * Setup webhooks for processing bounce and feedback loop.
     *
     * @return mixed
     */
    public function setupWebhooks()
    {
        if (self::$isWebhookSetup) {
            return true;
        }

        MailLog::info('Setting up webhooks for bounce/complaints');

        $domain = $this->domain;
        $subscribeUrl = StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::WEBHOOK);

        MailLog::info('Webhook set to: '.$subscribeUrl);

        try {
            $result = $this->client()->delete("domains/$domain/webhooks/bounce");
        } catch (\Exception $e) {
            // just ignore
        }

        try {
            $result = $this->client()->delete("domains/$domain/webhooks/spam");
        } catch (\Exception $e) {
            // just ignore
        }

        $result = $this->client()->post("domains/$domain/webhooks", array(
            'id' => 'bounce',
            'url' => $subscribeUrl,
        ));

        MailLog::info('Bounce webhook created');

        $result = $this->client()->post("domains/$domain/webhooks", array(
            'id' => 'spam',
            'url' => $subscribeUrl,
        ));

        MailLog::info('Complaint webhook created');

        self::$isWebhookSetup = true;
    }

    /**
     * Get verified identities (domains and email addresses).
     *
     * @return bool
     */
    public function syncIdentities()
    {
        $response = $this->client()->get('domains');
        $json = json_decode(json_encode($response), true);

        $identities = [];
        foreach ($json['http_response_body']['items'] as $domain) {
            $name = $domain['name'];
            $identities[$name] = ['VerificationStatus' => $domain['state'] == 'active'];
        }

        $identityStore = $this->getIdentityStore();
        $identityStore->update($identities);

        $options = $this->getOptions();
        $options['identities'] = $identityStore->get();
        $this->setOptions($options);
        $this->save();
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        $response = $this->client()->get('domains');

        return true;
    }

    /**
     * Allow user to verify his/her own sending domain against Acelle Mail.
     *
     * @return bool
     */
    public function allowVerifyingOwnDomains()
    {
        return false;
    }

    /**
     * Allow user to verify his/her own sending domain against Acelle Mail.
     *
     * @return bool
     */
    public function allowVerifyingOwnEmails()
    {
        return false;
    }

    /**
     * Allow user to verify his/her own emails against AWS.
     *
     * @return bool
     */
    public function allowVerifyingOwnDomainsRemotely()
    {
        return false;
    }

    /**
     * Allow user to verify his/her own emails against AWS.
     *
     * @return bool
     */
    public function allowVerifyingOwnEmailsRemotely()
    {
        return false;
    }
}
