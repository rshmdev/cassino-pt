<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Gateway extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gateways';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // TriboPay
        'tribopay_uri',
        'tribopay_cliente_id',
        'tribopay_cliente_secret',
        'tribopay_api_token',

        // VeoPag
        'veopag_client_id',
        'veopag_client_secret',

        // BlackPearlPay
        'blackpearlpay_uri',
        'blackpearlpay_api_token',
    ];

}
