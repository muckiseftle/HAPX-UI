<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProxyHostRequest;
use App\Models\ProxyHost;
use App\Models\ProxyBackend;
use App\Services\HAProxyService;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProxyHostController extends Controller
{
    protected HAProxyService $haproxy;
    protected CertificateService $certs;

    public function __construct(HAProxyService $haproxy, CertificateService $certs)
    {
        $this->haproxy = $haproxy;
        $this->certs = $certs;
    }

    public function index()
    {
        $hosts = ProxyHost::with('backends')->latest()->get();
        return view('proxies.index', compact('hosts'));
    }

    public function create()
    {
        $certificates = $this->certs->listCertificates();
        return view('proxies.create', compact('certificates'));
    }

    public function store(StoreProxyHostRequest $request)
    {
        DB::transaction(function () use ($request) {
            $host = ProxyHost::create($request->validated());
            foreach ($request->backends as $backend) {
                $host->backends()->create($backend);
            }
        });

        $this->haproxy->syncConfig();

        return redirect()->route('proxies.index')->with('success', 'Proxy host created successfully.');
    }

    public function edit(ProxyHost $proxy)
    {
        $proxy->load('backends');
        $certificates = $this->certs->listCertificates();
        return view('proxies.edit', compact('proxy', 'certificates'));
    }

    public function update(StoreProxyHostRequest $request, ProxyHost $proxy)
    {
        DB::transaction(function () use ($request, $proxy) {
            $proxy->update($request->validated());
            
            $proxy->backends()->delete();
            foreach ($request->backends as $backend) {
                $proxy->backends()->create($backend);
            }
        });

        $this->haproxy->syncConfig();

        return redirect()->route('proxies.index')->with('success', 'Proxy host updated successfully.');
    }

    public function destroy(ProxyHost $proxy)
    {
        $proxy->delete();
        $this->haproxy->syncConfig();

        return redirect()->route('proxies.index')->with('success', 'Proxy host deleted.');
    }
}
