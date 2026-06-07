<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'message', 'file', 'line', 'trace', 'url', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function capture(\Throwable $e, ?int $userId = null): void
    {
        static::create([
            'message' => substr($e->getMessage(), 0, 500),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => substr($e->getTraceAsString(), 0, 5000),
            'url'     => request()->fullUrl(),
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
