<?php

namespace App\Jobs;

use App\Http\Controllers\Jobs\AiCaricatureJobController;
use App\Http\Controllers\Utils\FacebookEvent;
use App\Http\Controllers\Utils\FbPixel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FbPixelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    private FacebookEvent $eventName;
    private ?string $clientIp;
    private ?string $userAgent;
    private ?string $fbclid;
    private ?string $fbp;
    private ?string $name;
    private ?string $email;
    private ?string $phone;
    private ?string $url;
    private ?array $purchaseData;
    private bool $isOfferPixel;

    /**
     * @param FacebookEvent $eventName
     * @param string|null $clientIp
     * @param string|null $userAgent
     * @param string|null $fbclid
     * @param string|null $fbp
     * @param string|null $name
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $url
     * @param array|null $purchaseData
     * @param bool $isOfferPixel
     */
    public function __construct(
        FacebookEvent $eventName,
        ?string       $clientIp,
        ?string       $userAgent,
        ?string       $fbclid,
        ?string       $fbp,
        ?string       $name = null,
        ?string       $email = null,
        ?string       $phone = null,
        ?string       $url = null,
        ?array        $purchaseData = null,
        bool          $isOfferPixel = false
    )
    {
        $this->eventName = $eventName;
        $this->clientIp = $clientIp;
        $this->userAgent = $userAgent;
        $this->fbclid = $fbclid;
        $this->fbp = $fbp;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->url = $url;
        $this->purchaseData = $purchaseData;
        $this->isOfferPixel = $isOfferPixel;
    }

    public function handle(): void
    {
        FbPixel::purchaseJobEvent(
            $this->eventName,
            $this->clientIp,
            $this->userAgent,
            $this->fbclid,
            $this->fbp,
            $this->name,
            $this->email,
            $this->phone,
            $this->url,
            $this->purchaseData,
            $this->isOfferPixel
        );
    }
}
