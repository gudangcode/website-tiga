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
use GuzzleHttp\Client;

class TrackingDomain extends Model
{
    const STATUS_VERIFIED = 'verified';
    const STATUS_UNVERIFIED = 'unverified';

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
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

            // Default status = inactive (until domain verified)
            $item->status = self::STATUS_UNVERIFIED;
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'scheme'
    ];

    /**
     * Get validation rules.
     *
     * @return object
     */
    public function rules()
    {
        return [
            'name' => 'required|regex:/^([a-z0-9A-Z]+(-[a-z0-9A-Z]+)*\.)+[a-zA-Z]{2,}$/',
        ];
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('tracking_domains.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('tracking_domains.name', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;

        // filter by status
        if (!empty($request->status)) {
            $query = $query->where('tracking_domains.status', '=', $request->status);
        }

        // by customer
        if (!empty($request->customer_id)) {
            $query = $query->where('tracking_domains.customer_id', '=', $request->customer_id);
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
     * get verified domains.
     *
     * @return collect
     */
    public static function scopeVerified($query)
    {
        return $query->where('status', '=', self::STATUS_VERIFIED);
    }

    public function isVerified()
    {
        return $this->status == self::STATUS_VERIFIED;
    }

    public function getFQDN($trailingDot = true)
    {
        return $this->name . (( $trailingDot ) ? '.' : '');
    }

    public function getUrl()
    {
        return $this->scheme.'://'.$this->name;
    }

    public function getVerificationUrl()
    {
        return $this->getUrl().route('appkey', [], false);
    }

    public function setVerified()
    {
        $this->status = self::STATUS_VERIFIED;
    }

    public function verify()
    {
        try {
            $client = new Client();
            $response = $client->request('GET', $this->getVerificationUrl());
            
            if ($response->getBody() == get_app_identity()) {
                $this->setVerified();
                $this->save();
            } else {
                throw new \Exception("Verification failed");
            }
            return true;
        } catch (\Exception $ex) {
            // loggging here
            return false;
        }
    }
}