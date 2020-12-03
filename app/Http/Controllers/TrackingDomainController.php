<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\TrackingDomain;
use Acelle\Model\SendingServer;
use Acelle\Model\Setting;

class TrackingDomainController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->customer->can('read', new TrackingDomain())) {
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->user()->customer->id));

        $trackingDomains = TrackingDomain::search($request);

        return view('tracking_domains.index', [
            'trackingDomains' => $trackingDomains,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if (!$request->user()->customer->can('read', new TrackingDomain())) {
            return $this->notAuthorized();
        }

        $request->merge(array("customer_id" => $request->user()->customer->id));

        $trackingDomains = TrackingDomain::search($request)->paginate($request->per_page);

        return view('tracking_domains._list', [
            'trackingDomains' => $trackingDomains,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $domain = new TrackingDomain([
            'signing_enabled' => true,
        ]);
        $domain->status = TrackingDomain::STATUS_UNVERIFIED;
        $domain->uid = '0';
        $domain->fill($request->old());

        // authorize
        if (!$request->user()->customer->can('create', $domain)) {
            return $this->notAuthorized();
        }

        return view('tracking_domains.create', [
            'domain' => $domain,
            'readonly' => '0',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get current user
        $current_user = $request->user();
        $domain = new TrackingDomain();

        // authorize
        if (!$request->user()->customer->can('create', $domain)) {
            return $this->notAuthorized();
        }

        // save posted data
        $this->validate($request, $domain->rules());

        // Save current user info
        $domain->fill($request->all());
        $domain->customer_id = $request->user()->customer->id;
        $domain->status = TrackingDomain::STATUS_UNVERIFIED;

        if ($domain->save()) {
            $request->session()->flash('alert-success', trans('messages.tracking_domain.created'));
            return redirect()->action('TrackingDomainController@index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $domain = TrackingDomain::findByUid($id);
        $hostname = parse_url(url('/'), PHP_URL_HOST);

        return view('tracking_domains.show', [
            'domain' => $domain,
            'hostname' => $hostname,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = TrackingDomain::findByUid($row[0]);

            // authorize
            if (!$request->user()->customer->can('update', $item)) {
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->save();
        }

        echo trans('messages.tracking_domain.custom_order.updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items = TrackingDomain::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->customer->can('delete', $item)) {
                $item->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.tracking_domains.deleted');
    }

    /**
     * Verify sending domain.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function verify($uid)
    {
        $domain = TrackingDomain::findByUid($uid);
        $domain->verify();
        return redirect()->action('TrackingDomainController@show', ['uid' => $uid]);
    }

    /**
     * sending domain's records.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function records($id)
    {
        $domain = TrackingDomain::findByUid($id);

        if ($domain->isAssociatedWithSendingServer()) {
            $options = $domain->getOptions()['verification'];
            $identity = $options['identity'];
            $dkims = $options['dkim'];
            $spf = array_key_exists('spf', $options) ? $options['spf'] : [];

            return view('tracking_domains._records_aws', [
                'domain' => $domain,
                'identity' => $identity,
                'dkims' => $dkims,
                'spf' => $spf,
            ]);
        } else {
            return view('tracking_domains._records', [
                'domain' => $domain,
            ]);
        }
    }
}
