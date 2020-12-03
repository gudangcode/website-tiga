<?php

/**
 * SendingDomain class.
 *
 * Model class for sending domains
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

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\MtaSync;
use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetterDirect;
use Mika56\SPFCheck\DNSRecordGetter;

class SendingDomain extends Model
{
    const VERIFIED = 1;
    const UNVERIFIED = 0;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    public function sendingServer()
    {
        return $this->belongsTo('Acelle\Model\SendingServer');
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Get all active items.
     *
     * @return collect
     */
    public static function getAllActive()
    {
        return self::where('status', '=', self::STATUS_ACTIVE);
    }

    /**
     * Get all active system items.
     *
     * @return collect
     */
    public static function getAllAdminActive()
    {
        return self::getAllActive()->whereNotNull('admin_id');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request, $server = null)
    {
        $user = $request->user();
        if ($server) {
            $query = self::select('sending_domains.*')->whereRaw(sprintf('(%s IS NULL or %s = %s)', table('sending_domains.sending_server_id'), table('sending_domains.sending_server_id'), $server->id));
        } else {
            $query = self::select('sending_domains.*');
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('sending_domains.name', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
        }

        // Other filter
        if (!empty($request->customer_id)) {
            $query = $query->where('sending_domains.customer_id', '=', $request->customer_id);
        }

        if (!empty($request->admin_id)) {
            $query = $query->where('sending_domains.admin_id', '=', $request->admin_id);
        }

        // remove customer sending servers
        if (!empty($request->no_customer)) {
            $query = $query->whereNull('customer_id');
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request, $server = null)
    {
        $query = self::filter($request, $server);

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (SendingDomain::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // SendingDomain custom order
            SendingDomain::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;

            // Generate dkim keys
            $item->generateDkimKeys();

            // Generate verification token
            $item->generateVerificationToken();

            // Default status = inactive (until domain verified)
            $item->status = self::STATUS_INACTIVE;
        });

        // Create uid when creating list.
        static::saving(function ($item) {
            // Disable if not verified
            if ($item->isAssociatedWithSendingServer()) {
                // all verification must pass for a remote service
                $item->status = ($item->domain_verified && $item->dkim_verified && $item->spf_verified) ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
            } else {
                // Identity is enough in certain cases (SMTP, Sendmail, etc.)
                $item->status = $item->domain_verified ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'dkim_private', 'dkim_public', 'signing_enabled',
    ];

    /**
     * Get validation rules.
     *
     * @return object
     */
    public static function rules()
    {
        return [
            'name' => 'required|regex:/^([a-z0-9A-Z]+(-[a-z0-9A-Z]+)*\.)+[a-zA-Z]{2,}$/',
        ];
    }

    /**
     * Get the clean public key, strip out the Header and Footer.
     */
    public function getCleanPublicKey()
    {
        $publicKey = str_replace(array('-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'), '', $this->dkim_public);
        $publicKey = trim(preg_replace('/\s+/', '', $publicKey));

        return $publicKey;
    }

    /**
     * Generate the Domain DNS configuration for DKIM.
     */
    public function getDnsDkimConfig()
    {
        return doublequote($this->getDnsDkimConfigWithoutQuote());
    }

    /**
     * Generate the Domain DNS configuration for DKIM.
     */
    public function getEscapedDnsDkimConfig()
    {
        return str_replace(';', '\;', $this->getDnsDkimConfig());
    }

    /**
     * Generate the Domain DNS configuration for DKIM.
     */
    public function getDnsDkimConfigWithoutQuote()
    {
        return sprintf('v=DKIM1; k=rsa; p=%s;', $this->getCleanPublicKey());
    }

    /**
     * Generate SPF: allow sending through the host's server (IP address)
     * See more at: http://www.openspf.org/SPF_Record_Syntax.
     */
    public function getSpf()
    {
        $spf = Setting::get('spf');
        if (is_null($spf) || empty($spf)) {
            return sprintf('v=spf1 +a +mx +ip4:%s ~all', $this->getHostIpAddress());
        } else {
            return $spf;
        }
    }

    /**
     * Get quoted SPF.
     */
    public function getQuotedSpf()
    {
        return sprintf('%s', doublequote($this->getSpf()));
    }

    /**
     * Generate the Domain DNS configuration for DKIM.
     */
    public function getHostIpAddress()
    {
        $root = config('app.url');
        $hostname = parse_url($root, PHP_URL_HOST);
        $ip = gethostbyname($hostname);

        return $ip;
    }

    /**
     * Retrieve the VERIFICATION_TXT_NAME value which is used as TXT name.
     */
    public function getVerificationTxtName()
    {
        if (!is_null($this->verification_hostname)) {
            return $this->verification_hostname;
        } else {
            return Setting::get('verification_hostname');
        }
    }

    /**
     * Retrieve the full verification hostname (including domain name).
     */
    public function getFullVerificationHostName()
    {
        return "{$this->getVerificationTxtName()}.{$this->getDnsHostName()}";
    }

    /**
     * Get DNS host name.
     */
    public function getDnsHostName()
    {
        return "{$this->name}.";
    }

    /**
     * Generate the verification token.
     */
    public function generateVerificationToken()
    {
        $this->verification_token = base64_encode(md5(uniqid()));
    }

    /**
     * Create the private and public key.
     *
     * @var bool
     */
    public function generateDkimKeys()
    {
        $config = array(
            'digest_alg' => 'sha256',
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey['key'];

        $this->dkim_private = $privKey;
        $this->dkim_public = $pubKey;

        return true;
    }

    /**
     * Add customer action log.
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        $data = array_merge($data, $add_datas);

        Log::create([
            'customer_id' => $customer->id,
            'type' => 'sending_domain',
            'name' => $name,
            'data' => json_encode($data),
        ]);
    }

    /**
     * Verify domain DNS.
     */
    public function verify()
    {
        if ($this->isAssociatedWithSendingServer()) {
            // Verify Host, DKIM and SPF
            $server = $this->sendingServer->mapType();
            list($identity, $dkim, $spf) = $server->checkDomainVerificationStatus($this->name);
            
            // update domain record in db
            $this->domain_verified = $identity;
            $this->dkim_verified = $dkim;
            $this->spf_verified = $spf;
            $this->save();
        } else {
            // Verify Host, DKIM and SPF
            $this->verifyDomainDns();
            $this->verifyDkim();
            $this->verifySpf();
        }
        

        $this->syncToMta();
    }

    /**
     * Sync to MTA.
     */
    public function syncToMta()
    {
        // Sync domain with MTA
        $mtaEndpoint = Setting::get('mta.api_endpoint');

        if (!empty($mtaEndpoint)) {
            $mta = new MtaSync($mtaEndpoint, Setting::get('mta.api_key'));
            $mta->addDomain($this->name, [
                'host_verified' => $this->domain_verified,
                'dkim_verified' => $this->dkim_verified,
                'spf_verified' => $this->spf_verified,
                'added_by' => 'Acelle',
            ]);
        }
    }

    /**
     * Verify TXT record, update domain status accordingly.
     *
     * @return mixed
     */
    public function verifyDomainDns()
    {
        $fqdn = sprintf('%s.%s', $this->getVerificationTxtName(), $this->name);
        $results = collect(dns_get_record($fqdn, DNS_TXT));
        $results = $results->where('type', 'TXT')
                           ->whereIn('txt', [$this->verification_token, doublequote($this->verification_token)]);

        $this->domain_verified = $results->isEmpty() ? self::UNVERIFIED : self::VERIFIED;

        $this->save();
    }

    /**
     * Verify DKIM record, update domain status accordingly.
     *
     * @return mixed
     */
    public function verifyDkim()
    {
        $possibles = collect([$this->getDnsDkimConfigWithoutQuote(), $this->getDnsDkimConfig(), $this->getEscapedDnsDkimConfig()]);
        $possibles = $possibles->map(function ($item, $key) {
            return preg_replace('/\s+/', '', $item);
        });

        $fqdn = sprintf('%s.%s', $this->getDkimSelector(), $this->name);
        $results = collect(dns_get_record($fqdn, DNS_TXT))->where('type', 'TXT')->map(function ($item, $key) {
            return preg_replace('/\s+/', '', $item['txt']);
        });
        $results = $results->intersect($possibles);

        $this->dkim_verified = $results->isEmpty() ? self::UNVERIFIED : self::VERIFIED;
        
        $this->save();
    }

    /**
     * Verify DKIM record, update domain status accordingly.
     *
     * @return mixed
     */
    public function verifySpf()
    {
        $checker = new SPFCheck(new DNSRecordGetterDirect('8.8.8.8'));
        // $checker = new SPFCheck(new DNSRecordGetter());
        $check = $checker->isIPAllowed($this->getHostIpAddress(), $this->name);

        $this->spf_verified = ($check == SPFCheck::RESULT_PASS || $check == SPFCheck::RESULT_SOFTFAIL) ? self::VERIFIED : self::UNVERIFIED;

        if ($this->spf_verified == false) {
            // try again with another method
            $checker = new SPFCheck(new DNSRecordGetter());
            $check = $checker->isIPAllowed($this->getHostIpAddress(), $this->name);
            $this->spf_verified = ($check == SPFCheck::RESULT_PASS || $check == SPFCheck::RESULT_SOFTFAIL) ? self::VERIFIED : self::UNVERIFIED;
        }

        $this->save();
    }

    /**
     * Check if domain is verified.
     *
     * @return bool
     */
    public function domainVerified()
    {
        return $this->domain_verified == self::VERIFIED;
    }

    /**
     * Check if DKIM is verified.
     *
     * @return bool
     */
    public function dkimVerified()
    {
        return $this->dkim_verified == self::VERIFIED;
    }

    /**
     * Check if SPF is verified.
     *
     * @return bool
     */
    public function spfVerified()
    {
        return $this->spf_verified == self::VERIFIED;
    }

    /**
     * Get DKIM selector.
     *
     * @return string
     */
    public function getDkimSelector()
    {
        if (!empty($this->dkim_selector)) {
            return $this->dkim_selector.'._domainkey';
        } else {
            return Setting::get('dkim_selector').'._domainkey';
        }
    }

    /**
     * Get DKIM selector parts.
     *
     * @return string
     */
    public function getDkimSelectorParts()
    {
        return explode('.', $this->getDkimSelector());
    }

    /**
     * Get the full DKIM host name (including domain name).
     *
     * @return string
     */
    public function getFullDkimHostName()
    {
        return "{$this->getDkimSelector()}.{$this->name}.";
    }

    /**
     * Set DKIM selector.
     *
     * @return string
     */
    public function setDkimSelector($dkim_selector)
    {
        if (preg_match('/^[a-z0-9]{1,24}$/', $dkim_selector)) {
            $this->dkim_selector = $dkim_selector;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Set VerificationTxtName.
     *
     * @return string
     */
    public function setVerificationTxtName($verification_hostname)
    {
        if (preg_match('/^[a-z0-9]{1,24}$/', $verification_hostname)) {
            $this->verification_hostname = $verification_hostname;
            $this->save();

            return true;
        }

        return false;
    }

    public function verifyWith($server)
    {
        $this->sending_server_id = $server->id;
        $verinfo = $server->verifyDomain($this->name);
        $options = $this->getOptions();
        $options['verification'] = $verinfo;
        $this->setOptions($options);
        $this->save();
    }

    public function getOptions()
    {
        return json_decode($this->options, true);
    }

    public function setOptions($array)
    {
        $this->options = json_encode($array);
    }

    public function isAssociatedWithSendingServer()
    {
        return !is_null($this->sending_server_id);
    }
}
