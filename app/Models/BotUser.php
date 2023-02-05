<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'chat_id';

    protected $fillable = [
        'chat_id',
        'context',
        'condition',
        'condition_step',
        'model',
    ];

    public function resetCondition()
    {
        $this->update([
            'condition' => null,
            'condition_step' => 0,
            'is_started' => false,
            'context' => null,
        ]);
    }
}
