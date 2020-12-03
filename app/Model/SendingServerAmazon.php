<?php

/**
 * SendingServerAmazon class.
 *
 * An abstract class for different types of Amazon sending servers
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
use Acelle\Library\Lockable;
use Acelle\Library\IdentityStore;
use Acelle\Library\SendingServer\DomainVerificationInterface;
use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetterDirect;
use Mika56\SPFCheck\DNSRecordGetter;

class SendingServerAmazon extends SendingServer implements DomainVerificationInterface
{
    const SNS_TOPIC = 'ACELLEHANDLER';
    const SNS_TYPE = 'amazon'; // @TODO
    const SPF_DNS_RECORD = 'v=spf1 include:amazonses.com ~all';

    public $notificationTypes = array('Bounce', 'Complaint');
    public static $snsClient = null;
    public static $sesClient = null;
    public static $isSnsSetup = false;

    /**
     * Initiate a AWS SNS session and return the session object (snsClient).
     *
     * @return mixed
     */
    public function snsClient()
    {
        if (!self::$snsClient) {
            self::$snsClient = \Aws\Sns\SnsClient::factory(array(
                'credentials' => array(
                    'key' => trim($this->aws_access_key_id),
                    'secret' => trim($this->aws_secret_access_key),
                ),
                'region' => $this->aws_region,
                'version' => '2010-03-31',
            ));
        }

        return self::$snsClient;
    }

    /**
     * Initiate a AWS SES session and return the session object (snsClient).
     *
     * @return mixed
     */
    public function sesClient()
    {
        if (!self::$sesClient) {
            self::$sesClient = \Aws\Ses\SesClient::factory(array(
                'credentials' => array(
                    'key' => trim($this->aws_access_key_id),
                    'secret' => trim($this->aws_secret_access_key),
                ),
                'region' => $this->aws_region,
                'version' => '2010-12-01',
            ));
        }

        return self::$sesClient;
    }

    public function verifyDomain($domain) : array
    {
        $identity = $this->verifyDomainIdentity($domain);
        $dkim = $this->verifyDomainDkim($domain);
        return [
            'identity' => $identity,
            'dkim' => $dkim,
            'spf' => [[ 'type' => 'TXT', 'name' => $domain, 'value' => self::SPF_DNS_RECORD ]],
        ];
    }

    public function verifyDomainIdentity($domain)
    {
        $result = $this->sesClient()->verifyDomainIdentity([
            'Domain' => $domain,
        ]);

        $token = $result->toArray()['VerificationToken'];
        return [
            'type' => 'TXT',
            'name' => "_amazonses.{$domain}",
            'value' => $token,
        ];
    }

    public function verifyDomainDkim($domain)
    {
        $result = $this->sesClient()->verifyDomainDkim([
            'Domain' => $domain,
        ]);

        $tokens = $result->toArray()['DkimTokens'];
        return array_map(function ($token) use ($domain) {
            return [
                'type' => 'CNAME',
                'name' => "{$token}._domainkey.{$domain}",
                'value' => "{$token}.dkim.amazonses.com",
            ];
        }, $tokens);
    }

    /**
     * Setup AWS SNS for bounce and feedback loop.
     *
     * @return mixed
     */
    public function setupSns($message)
    {
        if (self::$isSnsSetup) {
            return true;
        }

        MailLog::info('Set up Amazon SNS for email delivery tracking');

        $fromEmail = array_keys($message->getFrom())[0];

        $awsIdentity = $fromEmail;
        $verifyByDomain = false;
        try {
            $this->sesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity' => $awsIdentity,
                'ForwardingEnabled' => true,
            ));
        } catch (\Exception $e) {
            $verifyByDomain = true;
            MailLog::warning("From Email address {$fromEmail} not verified by Amazon SES, using domain instead");
        }

        if ($verifyByDomain) {
            // Use domain name as Aws Identity
            $awsIdentity = substr(strrchr($fromEmail, '@'), 1); // extract domain from email
            $this->sesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity' => $awsIdentity, // extract domain from email
                'ForwardingEnabled' => true,
            ));
        }

        $topicResponse = $this->snsClient()->createTopic(array('Name' => self::SNS_TOPIC));
        $subscribeUrl = StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::SNS_TYPE);

        $subscribeResponse = $this->snsClient()->subscribe(array(
            'TopicArn' => $topicResponse->get('TopicArn'),
            'Protocol' => stripos($subscribeUrl, 'https') === 0 ? 'https' : 'http',
            'Endpoint' => $subscribeUrl,
        ));

        if (stripos($subscribeResponse->get('SubscriptionArn'), 'pending') === false) {
            $this->subscription_arn = $result->get('SubscriptionArn');
        }

        foreach ($this->notificationTypes as $type) {
            $this->sesClient()->setIdentityNotificationTopic(array(
                'Identity' => $awsIdentity,
                'NotificationType' => $type,
                'SnsTopic' => $topicResponse->get('TopicArn'),
            ));
        }

        self::$isSnsSetup = true;
    }

    /**
     * Setup SNS, make sure the request limit (in case of multi-process) is less than 1 request / second.
     *
     * @return mixed
     */
    public function setupSnsThreadSafe($message)
    {
        if (self::$isSnsSetup) {
            return true;
        }

        $lock = new Lockable(storage_path('locks/sending-server-sns-'.$this->uid));
        $lock->getExclusiveLock(function () use ($message) {
            $this->setupSns($message);
            sleep(1); // SNS request rate limit
        });
    }

    /**
     * Get verified identities (domains and email addresses).
     *
     * @return bool
     */
    public function syncIdentities()
    {
        // Merge the list of identities from Amazon to the local sending domains to get customer information
        $emailOrDomains = $this->sesClient()->listIdentities([
            'MaxItems' => 1000, # @todo, need pagination here
        ])->toArray()['Identities'];

        $identities = [];

        // AWS: can only get verification attributes for up to 100 identities at a time
        foreach(array_chunk($emailOrDomains, 100) as $chunk100) {
            $identities100 = $this->sesClient()->getIdentityVerificationAttributes([
                'Identities' => $chunk100,
            ])->toArray()['VerificationAttributes'];

            $identities = array_merge($identities, $identities100);
        }

        // Domains added by users
        $domainsByUsers = $this->sendingDomains()->whereIn('name', $emailOrDomains)->get();
        $sendersByUsers = $this->senders()->whereIn('name', $emailOrDomains)->get();

        foreach ($domainsByUsers->merge($sendersByUsers) as $domain) {
            if (array_key_exists($domain->name, $identities)) {
                $identities[$domain->name]['UserId'] = $domain->customer->id;
                $identities[$domain->name]['UserName'] = $domain->customer->getFullName();
            }
        }

        foreach ($identities as $key => $attributes) {
            if (!array_key_exists('UserId', $attributes)) {
                $identities[$key]['UserId'] = null;
            }

            if (!array_key_exists('UserName', $attributes)) {
                $identities[$key]['UserName'] = null;
            }
        }

        $identityStore = $this->getIdentityStore();
        $identityStore->update($identities);

        $options = $this->getOptions();
        $options['identities'] = $identityStore->get();
        $this->setOptions($options);
        $this->save();
    }

    public function checkDomainVerificationStatus($domain) : array
    {
        // Identity
        $status = $identitiesWithAttributes = $this->sesClient()->getIdentityVerificationAttributes([
            'Identities' => [$domain],
        ])->toArray()['VerificationAttributes'][$domain]['VerificationStatus'];

        $identity = 'Success' == $status;

        // DKIM
        $status = $identitiesWithAttributes = $this->sesClient()->getIdentityDkimAttributes([
            'Identities' => [$domain],
        ])->toArray()['DkimAttributes'][$domain]['DkimVerificationStatus'];

        $dkim = 'Success' == $status;

        // SPF: hack
        $checker = new SPFCheck(new DNSRecordGetterDirect('8.8.8.8'));
        // $checker = new SPFCheck(new DNSRecordGetter());
        $check = $checker->isIPAllowed(gethostbyname('amazonses.com'), $domain);

        $spf = ($check == SPFCheck::RESULT_PASS || $check == SPFCheck::RESULT_SOFTFAIL);

        // Return all
        return [$identity, $dkim, $spf];
    }

    /**
     * Check an email address if it is verified against AWS.
     *
     * @return bool
     */
    public function sendVerificationEmail($identity)
    {
        // send custom template to Amazon
        $this->createCustomVerificationEmailTemplateFor($identity);

        $this->sesClient()->SendCustomVerificationEmail([
            'EmailAddress' => $identity->email,
            'TemplateName' => $this->getCustomVerificationEmailTemplateNameFor($identity),
        ]);
    }

    /**
     * Check if AWS actions are allowed.
     *
     * @return bool
     */
    public static function testConnection($key, $secret, $region)
    {
        $iamClient = \Aws\Iam\IamClient::factory(array(
            'credentials' => array(
                'key' => trim($key),
                'secret' => trim($secret),
            ),
            'region' => $region,
            'version' => '2010-05-08',
        ));

        // getting API caller
        $arn = $iamClient->getUser()->get('User')['Arn'];

        $username = array_values(array_slice(explode(':', $arn), -1))[0];
        if ($username == 'root') {
            return true;
        }

        $actions = ['ses:VerifyEmailIdentity', 'ses:GetIdentityVerificationAttributes', 'ses:ListIdentities', 'ses:SetIdentityFeedbackForwardingEnabled', 'sns:CreateTopic', 'sns:Subscribe', 'sns:SetIdentityNotificationTopic'];
        $results = $iamClient->simulatePrincipalPolicy(['PolicySourceArn' => $arn, 'ActionNames' => $actions])->toArray();
        foreach ($results['EvaluationResults'] as $result) {
            $action = $result['EvalActionName'];
            $decision = $result['EvalDecision'];

            if ($decision != 'allowed') {
                throw new \Exception("Action {$action} is not allowed");
            }
        }

        return true;
    }

    /**
     * Check if AWS actions are allowed for the corresponding instance.
     *
     * @return bool
     */
    public function test()
    {
        return self::testConnection(
            $this->aws_access_key_id,
            $this->aws_secret_access_key,
            $this->aws_region
        );
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
        return true;
    }

    /**
     * Allow user to verify his/her own emails against AWS.
     *
     * @return bool
     */
    public function allowVerifyingOwnEmailsRemotely()
    {
        return true;
    }

    public function createCustomVerificationEmailTemplateFor($identity)
    {
        if (empty($this->default_from_email)) {
            throw new \Exception("Sending server `{$this->name}` does not have a 'Default FROM Email Address'. Please go to admin area, then sending server setting dashboard and update this value for the sending server");
        }

        // create a seperate template for each $identity
        // for safety, remove it immediately after using
        // It is because Amazon may restrict the number of templates available
        if ($this->checkVerificationEmailTemplateFor($identity)) {
            $this->deleteCustomVerificationEmailTemplateFor($identity); // reset, in case user updates the template
        }

        // Get the template
        $template = Layout::where('alias', 'sender_verification_email_for_amazon_ses')->first();
        $name = $this->getCustomVerificationEmailTemplateNameFor($identity);
        $redirectUrl = $identity->generateVerificationResultUrl();

        // Replace tags
        $html = $template->content;
        $html = str_replace('{USER_NAME}', $identity->name, $html);
        $html = str_replace('{USER_EMAIL}', $identity->email, $html);

        // Push to Amazon
        MailLog::info('Creating custom verification template ' . $this->getCustomVerificationEmailTemplateNameFor($identity));
        $result = $this->sesClient()->CreateCustomVerificationEmailTemplate([
            'TemplateName' => $name,
            'TemplateSubject' => $template->subject,
            'TemplateContent' => $html,
            'FromEmailAddress' => $this->default_from_email,
            'FailureRedirectionURL' => $redirectUrl,
            'SuccessRedirectionURL' => $redirectUrl,
        ]);
    }

    public function checkVerificationEmailTemplateFor($identity)
    {
        // Check if template already exists for $identity
        $name = $this->getCustomVerificationEmailTemplateNameFor($identity);
        $result = $this->sesClient()->ListCustomVerificationEmailTemplates([
            'MaxResults' => 50,
        ]);

        $name = $this->getCustomVerificationEmailTemplateNameFor($identity);
        $names = array_map(function ($r) {
            return $r['TemplateName'];
        }, $result->toArray()['CustomVerificationEmailTemplates']);
        return in_array($name, $names);
    }

    public function deleteCustomVerificationEmailTemplateFor($identity)
    {
        $name = $this->getCustomVerificationEmailTemplateNameFor($identity);
        MailLog::info('Cleaning up old AWS custom verification template ' . $this->getCustomVerificationEmailTemplateNameFor($identity));
        $result = $this->sesClient()->DeleteCustomVerificationEmailTemplate([
            'TemplateName' => $name,
        ]);
        return $result;
    }

    private function getCustomVerificationEmailTemplateNameFor($identity)
    {
        return "verification-email-{$identity->uid}";
    }
}
