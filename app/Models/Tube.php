<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tube
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\TubeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Tube newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tube newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tube query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tube whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tube whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tube whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tube whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tube extends Model
{
    use HasFactory;
}
