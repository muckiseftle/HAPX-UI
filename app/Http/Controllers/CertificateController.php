<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    protected CertificateService $certs;

    public function __construct(CertificateService $certs)
    {
        $this->certs = $certs;
    }

    public function index()
    {
        $certificates = $this->certs->listCertificates();
        return view('certificates.index', compact('certificates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'days' => 'required|integer|min:1'
        ]);

        try {
            $this->certs->createSelfSigned($request->domain, $request->days);
            return back()->with('success', "Certificate for {$request->domain} created.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create certificate: ' . $e->getMessage());
        }
    }

    public function storeLetsEncrypt(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:certificates,domain',
            'validation_method' => 'required|in:http,dns',
            'dns_provider' => 'required_if:validation_method,dns|string|nullable',
            'dns_credentials' => 'required_if:validation_method,dns|array|nullable',
        ]);

        $certificate = Certificate::create([
            'domain' => $validated['domain'],
            'type' => 'letsencrypt',
            'validation_method' => $validated['validation_method'],
            'status' => 'pending',
            'dns_provider' => $validated['dns_provider'] ?? null,
            'dns_credentials' => $validated['dns_credentials'] ?? null,
        ]);

        // Trigger request in background or immediately
        // For now, we do it immediately but we should probably use a Job
        if ($this->certs->requestLetsEncrypt($certificate)) {
            return back()->with('success', "Let's Encrypt certificate for {$validated['domain']} requested successfully.");
        }

        return back()->with('error', "Failed to request Let's Encrypt certificate: " . $certificate->error_message);
    }

    public function renew(Certificate $certificate)
    {
        if ($certificate->type !== 'letsencrypt') {
            return back()->with('error', 'Only Let\'s Encrypt certificates can be renewed automatically.');
        }

        if ($this->certs->requestLetsEncrypt($certificate)) {
            return back()->with('success', "Certificate for {$certificate->domain} renewed.");
        }

        return back()->with('error', "Renewal failed: " . $certificate->error_message);
    }

    public function sync()
    {
        $this->certs->syncWithFileSystem();
        return back()->with('success', 'Database synced with filesystem.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:certificates,id']);
        
        if ($this->certs->delete($request->id)) {
            return back()->with('success', 'Certificate deleted.');
        }

        return back()->with('error', 'Failed to delete certificate.');
    }
}
