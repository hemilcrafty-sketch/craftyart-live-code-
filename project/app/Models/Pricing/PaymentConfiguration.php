<?php

namespace App\Models\Pricing;

use App\Http\Controllers\Utils\CryptoJsAes;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Pricing\PaymentConfiguration
 *
 * @property int $id
 * @property string $payment_scope
 * @property string $gateway
 * @property array $credentials
 * @property array $payment_types
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|PaymentConfiguration newModelQuery()
 * @method static Builder|PaymentConfiguration newQuery()
 * @method static Builder|PaymentConfiguration query()
 * @method static Builder|PaymentConfiguration whereCreatedAt($value)
 * @method static Builder|PaymentConfiguration whereCredentials($value)
 * @method static Builder|PaymentConfiguration whereGateway($value)
 * @method static Builder|PaymentConfiguration whereId($value)
 * @method static Builder|PaymentConfiguration wherePaymentScope($value)
 * @method static Builder|PaymentConfiguration wherePaymentTypes($value)
 * @method static Builder|PaymentConfiguration whereUpdatedAt($value)
 * @method static Builder|PaymentConfiguration whereJsonContains(string $column, mixed $value)
 * @mixin Eloquent
 */
class PaymentConfiguration extends Model
{
    use HasFactory;

    protected $table = 'payment_configurations';
    protected $connection = 'crafty_pricing_mysql';

    protected $fillable = [
        'payment_scope',
        'gateway',
        'credentials',
        'payment_types',
        'is_active'
    ];

    protected $casts = [
        'credentials' => 'array',
        'payment_types' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @param string $types
     * @param string $scope
     * @return PaymentConfiguration|null
     */

    public static function getCredentialsByScope(string $types, string $scope): ?self
    {
        /** @var PaymentConfiguration|null $paymentConfig */
        $paymentConfig = self::whereJsonContains('payment_types', $types)
            ->wherePaymentScope($scope)
            ->first();

        if (!$paymentConfig) return null;

        $paymentConfig->credentials = self::decryptCredentials($paymentConfig->credentials);

        return $paymentConfig;
    }

    public static function getCredentialsByScopeWithFallback(string $types, string $scope): ?self
    {
        /** @var PaymentConfiguration|null $paymentConfig */
        $paymentConfig = self::whereJsonContains('payment_types', $types)
            ->wherePaymentScope($scope)
            ->first();

        if (!$paymentConfig) {
            /** @var PaymentConfiguration|null $paymentConfig */
            $paymentConfig = self::wherePaymentScope($scope)->first();
        }

        if (!$paymentConfig) {
            return null;
        }
        $paymentConfig->credentials = self::decryptCredentials($paymentConfig->credentials);

        return $paymentConfig;
    }

    public static function decryptCredentials($credentials)
    {
        foreach ($credentials as $key => $value) {
            try {
                $decrypted = CryptoJsAes::decrypt($value);

                $credentials[$key] = $decrypted;

            } catch (\Exception $e) {
                $credentials[$key] = $value;
            }
        }
        return $credentials;
    }

    public static function getAllPaymentConfig(): Collection
    {
        $paymentConfigs = self::all();
        foreach ($paymentConfigs as $paymentConfig) {
            $paymentConfig->credentials = self::decryptCredentials($paymentConfig->credentials);
        }
        return $paymentConfigs;
    }

    /**
     * @param Collection|null $paymentConfigs
     * @param string|null $scope
     * @param string $gateway
     * @return PaymentConfiguration|null
     */
    public static function getCredentialsByName(?Collection $paymentConfigs, ?string $scope, string $gateway): PaymentConfiguration|null
    {
        if (!$paymentConfigs) $paymentConfigs = self::getAllPaymentConfig();
        if ($scope) $paymentConfigs->where('payment_scope', $scope);
        return $paymentConfigs->where('gateway', $gateway)->first();
    }

    public static function getCredentialsByNameWithFallback(?Collection $paymentConfigs, string $scope, string $gateway)
    {
        if (!$paymentConfigs) $paymentConfigs = self::getAllPaymentConfig();
        $paymentConfig = $paymentConfigs->where('payment_scope', $scope)->Where('gateway', $gateway)->first();
        if (!$paymentConfig) {
            $paymentConfig = $paymentConfigs->where('payment_scope', $scope)->first();
        }
        return $paymentConfig;
    }
}
