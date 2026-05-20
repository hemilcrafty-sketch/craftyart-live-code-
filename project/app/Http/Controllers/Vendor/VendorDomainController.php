<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Utils\ApiController;
use App\Models\UserData;
use App\Models\Vendor\Tenants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

class VendorDomainController extends ApiController
{

    private string $serverIp = "82.112.234.4";

    public function domainChecker(Request $request): JsonResponse
    {
        $domain = $request->query('domain');

        if (empty($domain)) {
            return Response::json(['error' => 'Domain is required'], 403);
        }

        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }
//
//        if (str_ends_with($domain, '.craftyartapp.in') || str_ends_with($domain, '.craftyartapp.com')) {
//            return Response::json();
//        }

        if (Tenants::where('domain', $domain)->whereStatus('active')->exists()) {
            return Response::json();
        }

        return Response::json(['error' => 'Domain is not valid'], 403);
    }

    public function getDomainStatus(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        $serverIp = $this->serverIp;
        $domain_id   = $request->input('id');

        $tenants = Tenants::whereId($domain_id)->whereUserId($this->uid)->first();

        if(!$tenants) return $this->failed(msg: "No Records Found");

        $domain = $tenants->domain;

        if (empty($domain)) return $this->failed(msg: 'Domain is required');

        $dns             = $this->analyzeDns($domain);
        $hostingProvider = $this->detectHostingProvider($dns['current_ips'][0] ?? '');

        $base = [
            'domain'           => $domain,
            'url'              => 'https://' . $domain,
            'hosting_provider' => $hostingProvider,
            'hosting_login'    => $this->getHostingLoginLink($hostingProvider),
            'records'          => $dns['records'],
            'warnings'         => $dns['warnings'],
            'errors'           => $dns['errors'],
        ];

        if ($dns['points_to_server']) {
            return $this->successed(
                msg: empty($dns['warnings']) ? 'Domain is active and pointing correctly.' : 'DNS records are pointing correctly but there are some issues to review.',
                datas: array_merge($base, ['status' => 'active', 'dns_ok' => true])
            );
        }

        return $this->successed(
            msg: 'DNS records are not pointing to our server.',
            datas: array_merge($base, [
                'status'      => 'pending',
                'dns_ok'      => false,
                'instruction' => 'Log in to your hosting provider\'s DNS management and update these records.',
                'required_records' => [
                    ['type' => 'A',     'name' => '@', 'update_to' => $serverIp, "current_value" => $dns['current_ips']],
                    ['type' => 'CNAME', 'name' => 'www', 'update_to' => 'vendor.craftyartapp.in',"current_value" => $domain ],
                ],
            ])
        );
    }

    public function addDomain(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        $domain = $this->sanitizeDomain($request->input('domain'));
        if (empty($domain)) return $this->failed(msg: 'Domain is required');

        $domainError = $this->validateDomainFormat($domain);
        if ($domainError) return $this->failed(msg: $domainError);

        $dnsExists = $this->isDomainLive($domain);

        if (!$dnsExists) {
            return $this->failed(msg: 'Domain is not active. It may be available for purchase.');
        }

        if (str_ends_with($domain, '.craftyartapp.in') && Tenants::whereUserId($this->uid)->whereType('default')->exists()) {
            return $this->failed(msg: 'Only one subdomain (craftyartapp.in) is allowed for auto-generated default domain.');
        }

        if (Tenants::where('domain', $domain)->exists()) {
            return $this->failed(msg: 'Domain is already registered');
        }

        $tenant = Tenants::create([
            'user_id' => $this->uid,
            'domain'  => $domain,
            'type' => str_ends_with($domain, '.craftyartapp.in') ? "default" : "custom",
            'status'  =>  str_ends_with($domain, '.craftyartapp.in') ? "active" : "pending",
        ]);

        $dns = $this->analyzeDns($domain);

        // Blocking errors — keep pending, return errors so user can fix
        if (!empty($dns['errors'])) {
            return $this->failed(msg: 'Domain added but has DNS errors that must be resolved before activation.', datas: [
                'id' => $tenant->id, 'domain' => $domain, 'status' => 'pending',
                'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
            ]);
        }

        if ($dns['points_to_server']) {
            $tenant->update(['status' => 'active']);
            return $this->successed(msg: 'Domain added and DNS verified successfully', datas: [
                'id' => $tenant->id, 'domain' => $domain, 'status' => 'active',
                'warnings' => $dns['warnings'],
            ]);
        }

        return $this->successed(msg: 'Domain added. Point your A record to ' . $this->serverIp . ' to activate.', datas: [
            'id' => $tenant->id, 'domain' => $domain, 'status' => 'pending',
            'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
        ]);
    }

    public function editDomain(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        $id = $request->input('id');
        if (!$id) return $this->failed(msg: 'Domain id is required');

        $tenant = Tenants::where('id', $id)->where('user_id', $this->uid)->first();
        if (!$tenant) return $this->failed(msg: 'Domain not found');

        // Default subdomain (craftyartapp.in) cannot be edited
//        if (str_ends_with($tenant->domain, '.craftyartapp.in')) {
//            return $this->failed(msg: 'Default subdomain cannot be edited');
//        }

        $domain = $this->sanitizeDomain($request->input('domain'));
        if (empty($domain)) return $this->failed(msg: 'Domain is required');

        $domainError = $this->validateDomainFormat($domain);
        if ($domainError) return $this->failed(msg: $domainError);

        if (Tenants::where('domain', $domain)->where('id', '!=', $id)->exists()) {
            return $this->failed(msg: 'Domain is already registered');
        }

        $tenant->update(['domain' => $domain, 'status' => 'pending']);

        $dns = $this->analyzeDns($domain);

        if (!empty($dns['errors'])) {
            return $this->failed(msg: 'Domain updated but has DNS errors that must be resolved before activation.', datas: [
                'id' => $tenant->id, 'domain' => $domain, 'status' => 'pending',
                'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
            ]);
        }

        if ($dns['points_to_server']) {
            $tenant->update(['status' => 'active']);
            return $this->successed(msg: 'Domain updated and DNS verified successfully', datas: [
                'id' => $tenant->id, 'domain' => $domain, 'status' => 'active',
                'warnings' => $dns['warnings'],
            ]);
        }

        return $this->successed(msg: 'Domain updated. Point your A record to ' . $this->serverIp . ' to activate.', datas: [
            'id' => $tenant->id, 'domain' => $domain, 'status' => 'pending',
            'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
        ]);
    }

    public function listDomains(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        // Ensure default domain exists
        $exists = Tenants::where('user_id', $this->uid)->where('type', 'default')->exists();
        if (!$exists) {
            $user = UserData::where('uid', $this->uid)->first();
            if ($user && $user->user_name) {
                Tenants::create([
                    'user_id' => $this->uid,
                    'domain' => $user->user_name . ".craftyartapp.in",
                    'type' => 'default',
                    'status' => 'active',
                ]);
            }
        }

        $domains = Tenants::where('user_id', $this->uid)
            ->orderBy('type', 'asc')
            ->get();

        return $this->successed(msg: 'Domains loaded', datas: ['domains' => $domains]);
    }

    public function deleteDomain(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        $id = $request->input('id');
        if (!$id) return $this->failed(msg: 'Domain id is required');

        $tenant = Tenants::where('id', $id)->where('user_id', $this->uid)->first();
        if (!$tenant) return $this->failed(msg: 'Domain not found');

//        // Default subdomain cannot be deleted
//        if ($tenant->type == "default") {
//            return $this->failed(msg: 'Default subdomain cannot be deleted');
//        }

        $tenant->delete();
        return $this->successed(msg: 'Domain removed successfully');
    }

    private function sanitizeDomain(?string $domain): string
    {
        if (empty($domain)) return '';

        $domain = strtolower(trim($domain));

        // Remove http:// or https://
        $domain = preg_replace('#^https?://#i', '', $domain);

        // Remove www.
        $domain = preg_replace('/^www\./i', '', $domain);

        // Remove trailing slash if any
        return rtrim($domain, '/');
    }

    private function validateDomainFormat(string $domain): ?string
    {

        // Basic domain validation
        if (!preg_match('/^(?!\-)([a-z0-9\-]+\.)+[a-z]{2,}$/', $domain)) {
            return 'Invalid domain name format (e.g. example.com)';
        }

        if ($domain === 'craftyartapp.in' ||  $domain === 'vendor.craftyartapp.in' || $domain === 'craftyartapp.com' || str_ends_with($domain, '.craftyartapp.com')) {
            return 'This domain is reserved';
        }

        // ✅ Allow only single-level subdomain for craftyartapp.in
        if (str_ends_with($domain, '.craftyartapp.in')) {
            $sub = str_replace('.craftyartapp.in', '', $domain);

            if (empty($sub)) {
                return 'Invalid subdomain';
            }

            if (substr_count($sub, '.') > 0) {
                return 'Only single-level subdomains are allowed';
            }

            if (!preg_match('/^[a-z0-9\-]+$/', $sub)) {
                return 'Invalid subdomain format';
            }

            return null;
        }

        // ✅ Split domain parts
        $parts = explode('.', $domain);
        $count = count($parts);

        // ❌ Reject deep subdomains (more than 3 parts)
        if ($count > 3) {
            return 'Subdomains are not allowed. Use a root domain (e.g. example.com)';
        }

        // ❌ Reject 3-part domains unless it's a valid country TLD like co.in, gov.in
        if ($count === 3) {
            $tld = $parts[1] . '.' . $parts[2];

            $allowedSecondLevelTlds = [
                'co.in',
                'gov.in',
                'org.in',
                'net.in',
                'ac.in',
                'edu.in',
                'firm.in',
                'gen.in',
                'ind.in',
                'bank.in',
                'mil.in'
            ];

            if (!in_array($tld, $allowedSecondLevelTlds)) {
                return 'Subdomains are not allowed. Use a root domain (e.g. example.com)';
            }
        }

        return null;
    }

    private function isDomainLive($domain): bool
    {
        return checkdnsrr($domain, 'A') || checkdnsrr($domain, 'CNAME');
    }

    public function verifyDomain(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return $this->failed(msg: "Unauthorized");
        }

        $validationError = self::isValidVendor($this->uid);
        if ($validationError) return $this->failed(msg: $validationError);

        $id = $request->input('id');
        if (!$id) return $this->failed(msg: 'Domain id is required');

        $tenant = Tenants::where('id', $id)->where('user_id', $this->uid)->first();
        if (!$tenant) return $this->failed(msg: 'Domain not found');

        $isDefault = $tenant->type;
        $dns       = $this->analyzeDns($tenant->domain);

        if (!empty($dns['errors'])) {
            $tenant->update(['status' => 'failed']);
            return $this->failed(msg: 'DNS verification failed due to errors.', datas: [
                'id' => $tenant->id, 'domain' => $tenant->domain, 'status' => 'failed',
                'is_default' => $isDefault, 'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
            ]);
        }

        if ($dns['points_to_server']) {
            $tenant->update(['status' => 'active']);
            return $this->successed(msg: 'DNS verified. Domain is now active.', datas: [
                'id' => $tenant->id, 'domain' => $tenant->domain, 'status' => 'active',
                'is_default' => $isDefault, 'warnings' => $dns['warnings'],
            ]);
        }

        $tenant->update(['status' => 'failed']);
        return $this->failed(msg: 'DNS verification failed. Ensure your A record for ' . $tenant->domain . ' points to ' . $this->serverIp . '.', datas: [
            'id' => $tenant->id, 'domain' => $tenant->domain, 'status' => 'failed',
            'is_default' => $isDefault, 'errors' => $dns['errors'], 'warnings' => $dns['warnings'],
        ]);
    }

    private function checkDnsVerification(string $domain): bool
    {
        return $this->analyzeDns($domain)['points_to_server'];
    }

    /**
     * Full DNS analysis for a domain.
     * Returns:
     *   points_to_server  bool   — A record matches SERVER_IP
     *   warnings          array  — non-blocking issues (e.g. Cloudflare proxy)
     *   errors            array  — blocking issues that prevent activation
     *   current_ips       array  — all resolved A record IPs
     *   records           array  — full DNS record list (A, AAAA, CNAME)
     */
    private function analyzeDns(string $domain): array
    {
        $serverIp = $this->serverIp;
        $warnings = [];
        $errors   = [];

        // ── Collect all DNS records via dns_get_record (single source of truth) ─
        $aRaw    = dns_get_record($domain, DNS_A)    ?: [];
        $aaaaRaw = dns_get_record($domain, DNS_AAAA) ?: [];
        $cnameRaw= dns_get_record($domain, DNS_CNAME)?: [];

        $resolvedIps = array_column($aRaw, 'ip');

        Log::info("DNS analyze — domain: {$domain}, server: {$serverIp}, resolved: " . json_encode($resolvedIps));

        $pointsToServer = !empty($serverIp) && in_array($serverIp, $resolvedIps);

        // ── Build display records with ok flag ────────────────────────────────
        $records = [];
        foreach ($aRaw as $r) {
            $records[] = ['type' => 'A',    'name' => '@',   'value' => $r['ip'],           'ok' => $r['ip'] === $serverIp];
        }
        foreach ($aaaaRaw as $r) {
            $records[] = ['type' => 'AAAA', 'name' => '@',   'value' => $r['ipv6'] ?? '',   'ok' => false];
        }
        foreach ($cnameRaw as $r) {
            $records[] = ['type' => 'CNAME','name' => 'www', 'value' => $r['target'] ?? '', 'ok' => false];
        }

        // ── Cloudflare proxy IP ranges ────────────────────────────────────────
        $cfRanges = [
            '172.64.', '172.65.', '172.66.', '172.67.',
            '104.16.', '104.17.', '104.18.', '104.19.', '104.20.', '104.21.',
            '198.41.', '190.93.', '188.114.', '197.234.', '162.158.',
        ];

        $cfDetected = false;
        foreach ($resolvedIps as $ip) {
            foreach ($cfRanges as $range) {
                if (str_starts_with($ip, $range)) {
                    $cfDetected = true;
                    break 2;
                }
            }
        }

        if ($cfDetected) {
            $warnings[] = [
                'code'    => 'CLOUDFLARE_PROXY',
                'message' => 'Your domain is using a Cloudflare Proxy (orange-cloud). This may interfere with SSL provisioning and domain routing. To fix this, log in to Cloudflare, go to DNS settings for ' . $domain . ', and set the proxy status to "DNS only" (grey-cloud) for the A record.',
            ];
        }

        // ── Errors ────────────────────────────────────────────────────────────
        if (empty($resolvedIps)) {
            $errors[] = [
                'code'    => 'NO_DNS_RECORD',
                'message' => "Domain {$domain} does not resolve to any IP address. Please add an A record pointing @ to {$serverIp}.",
            ];
        } elseif (!$pointsToServer) {
            $currentList = implode(', ', $resolvedIps);
            $errors[] = [
                'code'    => 'WRONG_IP',
                'message' => "Domain {$domain} is pointing to {$currentList} but needs to point to {$serverIp}. Update your A record in your DNS provider.",
            ];
        }

        return [
            'points_to_server' => $pointsToServer,
            'warnings'         => $warnings,
            'errors'           => $errors,
            'current_ips'      => $resolvedIps,
            'records'          => $records,
        ];
    }

    private function getHostingLoginLink(string $provider): ?string
    {
        if (empty($provider)) return null;

        $provider = strtolower($provider);

        $map = [
            'hostinger'      => 'https://hpanel.hostinger.com',
            'amazon aws'     => 'https://console.aws.amazon.com',
            'amazonaws'      => 'https://console.aws.amazon.com',
            'aws'            => 'https://console.aws.amazon.com',
            'google cloud'   => 'https://console.cloud.google.com',
            'googleuser'     => 'https://console.cloud.google.com',
            'azure'          => 'https://portal.azure.com',
            'microsoft azure'=> 'https://portal.azure.com',
            'digitalocean'   => 'https://cloud.digitalocean.com',
            'linode'         => 'https://cloud.linode.com',
            'akamai'         => 'https://cloud.linode.com',
            'vultr'          => 'https://my.vultr.com',
            'hetzner'        => 'https://console.hetzner.cloud',
            'ovh'            => 'https://www.ovh.com/manager',
            'cloudflare'     => 'https://dash.cloudflare.com',
            'godaddy'        => 'https://sso.godaddy.com',
            'bluehost'       => 'https://my.bluehost.com',
            'siteground'     => 'https://my.siteground.com',
        ];

        foreach ($map as $keyword => $url) {
            if (str_contains($provider, $keyword)) {
                return $url;
            }
        }

        return null; // unknown provider
    }

    /**
     * Detect the hosting provider name for a given IP.
     * 1. PTR reverse DNS — often reveals the host (e.g. "hostinger.com", "amazonaws.com")
     * 2. Falls back to ip-api.com free JSON endpoint for org/ISP name.
     */
    private function detectHostingProvider(string $ip): ?string
    {
        if (empty($ip)) return 'Unknown';

        // 1. PTR lookup
        $ptr = @gethostbyaddr($ip);
        if ($ptr && $ptr !== $ip) {
            $knownHosts = [
                'hostinger'    => 'Hostinger',
                'amazonaws'    => 'Amazon AWS',
                'googleuser'   => 'Google Cloud',
                'azure'        => 'Microsoft Azure',
                'digitalocean' => 'DigitalOcean',
                'linode'       => 'Linode / Akamai',
                'vultr'        => 'Vultr',
                'hetzner'      => 'Hetzner',
                'ovh'          => 'OVH',
                'cloudflare'   => 'Cloudflare',
                'godaddy'      => 'GoDaddy',
                'bluehost'     => 'Bluehost',
                'siteground'   => 'SiteGround',
            ];
            foreach ($knownHosts as $keyword => $name) {
                if (stripos($ptr, $keyword) !== false) return $name;
            }
        }

        // 2. ip-api.com fallback (free, no key, 45 req/min limit)
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=org,isp");
            if ($response) {
                $data = json_decode($response, true);
                $provider = $data['org'] ?? $data['isp'] ?? null;
                if ($provider) return $provider;
            }
        } catch (\Throwable $e) {}

        return ($ptr && $ptr !== $ip) ? $ptr : null;
    }

    public static function isValidVendor($uid): ?string
    {
        $latestTransactionLog = SubscriptionController::getActivePlan($uid);

        if (!$latestTransactionLog) return "Subscription Not Found";

        if ($latestTransactionLog->expired_at < now()) return "Subscription is Expired";

        $planLimits = $latestTransactionLog->plan_limit;
        $isVendor   = collect($planLimits)->firstWhere('slug', 'is_vendor');

        if (!$isVendor || empty($isVendor['meta_value'])) {
            return "User does not have vendor access.";
        }

        return null;
    }
}
