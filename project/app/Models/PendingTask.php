<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PendingTask extends Model
{
  protected $connection = 'mysql';
  use HasFactory;

  protected $fillable = [
    'string_id',
    'table_name',
    'record_id',
    'action',
    'changes_title',
    'changes_desc',
    'preview_route',
    'data',
    'emp_id',
    'change_log',
    'id_name',
    'page_type'
  ];

  public function getStatusTypeAttribute()
  {
    return $this->status == 0
      ? "Pending"
      : ($this->status == 1
        ? "Approve"
        : "Rejected");
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'emp_id', 'id');
  }

  public function user2()
  {
    return $this->belongsTo(User::class, 'group_leader_id', 'id');
  }

  /**
   * Get the requestor's name from the users table.
   */
  public function getRequestorNameAttribute()
  {
    return $this->user ? $this->user->name : 'Unknown';
  }

  public function getGroupLeaderNameAttribute()
  {
    if ($this->group_leader_id == auth()->user()->id) {
      return auth()->user()->name;
    } else {
      return $this->user2 ? $this->user2->name : 'Unknown';
    }
  }

}